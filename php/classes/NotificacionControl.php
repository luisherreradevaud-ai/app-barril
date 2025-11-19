<?php

  class NotificacionControl  {

    public function __construct($id = null) {

    }

    public static function trigger($nombre,$obj) {

      if($nombre == "") {
        return false;
      }

      $tipo_de_notificacion = new TipoDeNotificacion;
      $tipo_de_notificacion->getFromDatabase('nombre',$nombre);

      if($tipo_de_notificacion->id == "") {
        return false;
      }

      $tdn_usuarios_niveles = TipoDeNotificacionUsuarioNivel::getAll("WHERE id_tipos_de_notificaciones='".$tipo_de_notificacion->id."' AND (app='1' OR email='1')");
      foreach($tdn_usuarios_niveles as $tdnun) {
        $usuario_nivel = new UsuarioNivel($tdnun->id_usuarios_niveles);

        if($tdnun->email == 1) {

          if($usuario_nivel->nombre == "Cliente") {

            if($tipo_de_notificacion->nombre == "Nueva Entrega") {
              $cliente = new Cliente($obj->id_clientes);
              $email = new Email;
              $email->enviarCorreosEntrega_NotificacionControl($cliente->email,$obj);
            } else 

            if($tipo_de_notificacion->nombre == "Nueva Mantencion") {
              $activo = new Activo($obj->id_activos);
              if( $activo->ubicacion != "En planta" && $activo->id_clientes_ubicacion != 0) {
                $cliente = new Cliente($activo->id_clientes_ubicacion);
                $email = new Email;
                $email->enviarCorreoMantencion_NotificacionControl($cliente->email,$obj);
              }
            } else 

            if($tipo_de_notificacion->nombre == "Nueva Sugerencia") {
              $cliente = new Cliente($obj->id_clientes);
              $email = new Email;
              $email->enviarCorreoSugerencia_NotificacionControl($cliente->email,$obj);
            } else

            if($tipo_de_notificacion->nombre == "Nuevo Pedido") {
              $cliente = new Cliente($obj->id_clientes);
              $email = new Email;
              $email->enviarCorreoPedido_NotificacionControl($cliente->email,$obj);
            } else

            if($tipo_de_notificacion->nombre == "Pago Webpay") {
              $cliente = new Cliente($obj->id_clientes);
              $email = new Email;
              $email->enviarCorreoPago_NotificacionControl($cliente->email,$obj);
            }

          } else {

            $usuarios = Usuario::getAll("WHERE nivel='".$usuario_nivel->nombre."'");
            foreach($usuarios as $u) {

              if($tipo_de_notificacion->nombre == "Nueva Entrega") {
                $email = new Email;
                $email->enviarCorreosEntregaRespaldo_NotificacionControl($u->email,$obj);
              } else 

              if($tipo_de_notificacion->nombre == "Nueva Mantencion") {
                $email = new Email;
                $email->enviarCorreoMantencionRespaldo_NotificacionControl($u->email,$obj);
              } else 

              if($tipo_de_notificacion->nombre == "Barril Perdido") {
                  $email = new Email;
                  $email->enviarCorreoBarrilPerdido_NotificacionControl($u->email,$obj);
              } else 

              if($tipo_de_notificacion->nombre == "Nueva Compra de Insumos") {
                $email = new Email;
                $email->enviarCorreoNuevaCompraDeInsumos_NotificacionControl($u->email,$obj);
              } else

              if($tipo_de_notificacion->nombre == "Nueva Sugerencia") {
                $email = new Email;
                $email->enviarCorreoSugerenciaRespaldo_NotificacionControl($u->email,$obj);
              } else

              if($tipo_de_notificacion->nombre == "Nueva Tarea") {

              } else

              if($tipo_de_notificacion->nombre == "Nuevo Batch") {
                $email = new Email;
                $email->enviarCorreoNuevoBatch_NotificacionControl($u->email,$obj);
              } else

              if($tipo_de_notificacion->nombre == "Nuevo Despacho") {
                $email = new Email;
                $email->enviarCorreoNuevoDespacho_NotificacionControl($u->email,$obj);
              } else

              if($tipo_de_notificacion->nombre == "Nuevo Gasto") {
                $email = new Email;
                $email->enviarCorreoNuevoGasto_NotificacionControl($u->email,$obj);
              } else

              if($tipo_de_notificacion->nombre == "Nuevo Pedido") {
                $email = new Email;
                $email->enviarCorreoPedidoRespaldo_NotificacionControl($u->email,$obj);
              } else

              if($tipo_de_notificacion->nombre == "Pago Webpay") {
                $email = New Email;
                $email->enviarCorreoPagoRespaldo_NotificacionControl($u->email,$obj);
              } else

              if($tipo_de_notificacion->nombre == "Tarea Realizada") {
                if($u->id == $obj->id_usuarios_emisor) {
                  $email = new Email;
                  $email->enviarCorreoTareaRealizada_NotificacionControl($u->email,$obj);
                }
              } else

              if($tipo_de_notificacion->nombre == "Insumos insuficientes para Batches") {
                $email = new Email;
                $email->enviarCorreoInsumosInsuficientes_NotificacionControl($u->email,$obj);
              } else

              if($tipo_de_notificacion->nombre == "Gasto Vencido") {
                $email = new Email;
                $email->enviarCorreoGastoVencido_NotificacionControl($u->email,$obj);
              } else

              if($tipo_de_notificacion->nombre == "Nuevo Comentario de Tarea") {
                $tarea_comentario = $obj;
                $tarea = new Tarea($tarea_comentario->id_tareas);
                $tareas_comentarios = TareaComentario::getAll("WHERE id_tareas='".$tarea->id."'");
                $usuarios_tareas_comentarios = array();
                foreach($tareas_comentarios as $tc) {
                  if(!in_array($tc->id_usuarios,$usuarios_tareas_comentarios)) {
                    $usuarios_tareas_comentarios[] = $tc->id_usuarios;
                  }
                }
                if(($u->id == $tarea->destinatario || in_array($u->id,$usuarios_tareas_comentarios)) && $tarea_comentario->id_usuarios != $u->id) {
                  $email = new Email;
                  $email->enviarCorreoNuevoComentarioDeTarea_NotificacionControl($u->email,$obj);
                }
              }
            }

          }

        }

        if($tdnun->app == 1) {

          $usuarios = Usuario::getAll("WHERE nivel='".$usuario_nivel->nombre."'");
          foreach($usuarios as $u) {

            if($tipo_de_notificacion->nombre == "Nueva Entrega") {
              if( $u->nivel == "Cliente" && $obj->id_clientes == $u->id_clientes ) {
                $notificacion = new Notificacion;
                $notificacion->id_usuarios = $u->id;
                $notificacion->texto = "Nueva Entrega realizada.";
                $notificacion->link = "./?s=detalle-entregas&id=".$obj->id;
                $notificacion->save();
              } else
              if( $u->nivel!="Cliente" ) {
                $notificacion = new Notificacion;
                $notificacion->id_usuarios = $u->id;
                $notificacion->texto = "Nueva Entrega realizada.";
                $notificacion->link = "./?s=detalle-entregas&id=".$obj->id;
                $notificacion->save();
              }
            } else 

            if($tipo_de_notificacion->nombre == "Nueva Mantencion") {
              $activo = new Activo($obj->id_activos);
              if( $u->nivel == "Cliente" && $activo->id_clientes_ubicacion == $u->id_clientes && $activo->ubicacion != "En planta" && $activo->id_clientes_ubicacion != 0) {
                $notificacion = new Notificacion;
                $notificacion->id_usuarios = $u->id;
                $notificacion->texto = "Nueva Mantencion realizada.";
                $notificacion->link = "./?s=detalle-mantenciones&id=".$obj->id;
                $notificacion->save();
              } else
              if( $u->nivel!="Cliente" ) {
                $notificacion = new Notificacion;
                $notificacion->id_usuarios = $u->id;
                $notificacion->texto = "Nueva Mantencion realizada.";
                $notificacion->link = "./?s=detalle-mantenciones&id=".$obj->id;
                $notificacion->save();
              }
            } else 

            if($tipo_de_notificacion->nombre == "Barril Perdido") {
              $notificacion = new Notificacion;
              $notificacion->id_usuarios = $u->id;
              $notificacion->texto = "Barril ".$obj->codigo." marcado como Perdido.";
              $notificacion->link = "./?s=detalle-barriles&id=".$obj->id;
              $notificacion->save();
            } else 

            if($tipo_de_notificacion->nombre == "Nueva Compra de Insumos") {
              $notificacion = new Notificacion;
              $notificacion->id_usuarios = $u->id;
              $notificacion->texto = "Nueva Compra de Insumos.";
              $notificacion->link = "./?s=detalle-compras_de_insumos&id=".$obj->id;
              $notificacion->save();
            } else

            if($tipo_de_notificacion->nombre == "Nueva Sugerencia") {
              $cliente = new Cliente($obj->id_clientes);
              $notificacion = new Notificacion;
              $notificacion->id_usuarios = $u->id;
              $notificacion->texto = "Nueva Sugerencia de ".$cliente->nombre.".";
              $notificacion->link = "./?s=detalle-sugerencias&id=".$obj->id;
              $notificacion->save();
            } else

            if($tipo_de_notificacion->nombre == "Nueva Tarea") {
              if($obj->tipo_envio == "Usuario" && $obj->destinatario == $u->id) {
                $notificacion = new Notificacion;
                $notificacion->id_usuarios = $u->id;
                $notificacion->texto = "Nuevas Tareas asignadas.";
                $notificacion->link = "./?s=tareas";
                $notificacion->save();
              } else
              if($obj->tipo_envio == "Nivel" && $u->nivel == $usuario_nivel->nombre) {
                $notificacion = new Notificacion;
                $notificacion->id_usuarios = $u->id;
                $notificacion->texto = "Nuevas Tareas asignadas.";
                $notificacion->link = "./?s=tareas";
                $notificacion->save();
              }
              
            } else

            if($tipo_de_notificacion->nombre == "Nuevo Batch") {
              $notificacion = new Notificacion;
              $notificacion->id_usuarios = $u->id;
              $notificacion->texto = "Nuevo Batch creado.";
              $notificacion->link = "./?s=detalle-batches&id=".$obj->id;
              $notificacion->save();
            } else

            if($tipo_de_notificacion->nombre == "Nuevo Despacho") {
              $notificacion = new Notificacion;
              $notificacion->id_usuarios = $u->id;
              $notificacion->texto = "Nuevo Despacho #".$obj->id." creado.";
              $notificacion->link = "./?s=central-despacho";
              $notificacion->save();
            } else

            if($tipo_de_notificacion->nombre == "Nuevo Gasto") {
              $notificacion = new Notificacion;
              $notificacion->id_usuarios = $u->id;
              $notificacion->texto = "Nuevo Gasto creado.";
              $notificacion->link = "./?s=detalle-gastos&id=".$obj->id;
              $notificacion->save();
            } else

            if($tipo_de_notificacion->nombre == "Nuevo Pedido") {
              $notificacion = new Notificacion;
              $notificacion->id_usuarios = $u->id;
              $notificacion->texto = "Nuevo Pedido #".$obj->id." creado.";
              $notificacion->link = "./?s=pedidos";
              $notificacion->save();
            } else

            if($tipo_de_notificacion->nombre == "Pago Webpay") {
              $cliente = new Cliente($obj->id_clientes);
              $notificacion = new Notificacion;
              $notificacion->id_usuarios = $u->id;
              $notificacion->texto = "Nuevo Pago #".$obj->id." ($".number_format($obj->amount).") realizado por cliente ".$cliente->nombre.".";
              $notificacion->link = "./?s=detalle-pagos&id=".$obj->id;
              $notificacion->save();
            } else

            if($tipo_de_notificacion->nombre == "Tarea Realizada") {
              if($u->id == $obj->id_usuarios_emisor) {
                $notificacion = new Notificacion;
                $notificacion->id_usuarios = $u->id;
                $notificacion->texto = "Tarea #".$obj->id." marcada como Realizada.";
                $notificacion->link = "./?s=detalle-tareas&id=".$obj->id;
                $notificacion->save();
              }
              
            } else

            if($tipo_de_notificacion->nombre == "Insumos insuficientes para Batches") {

              $notificacion = new Notificacion;
              $notificacion->id_usuarios = $u->id;
              $notificacion->texto = "Insumos insuficientes para Receta ".$obj->nombre.".";
              $notificacion->link = "./?s=insumos";
              $notificacion->save();
              
            } else

            if($tipo_de_notificacion->nombre == "Vence Gasto") {

              $notificacion = new Notificacion;
              $notificacion->id_usuarios = $u->id;
              $notificacion->texto = "Gasto #".$obj->id." ha vencido.";
              $notificacion->link = "./?s=detalle-gastos&id=".$obj->id;
              $notificacion->save();
              
            } else

            if($tipo_de_notificacion->nombre == "Nuevo Comentario de Tarea") {
              $tarea_comentario = $obj;
              $tarea = new Tarea($tarea_comentario->id_tareas);
              $tareas_comentarios = TareaComentario::getAll("WHERE id_tareas='".$tarea->id."'");
              $usuarios_tareas_comentarios = array();
              foreach($tareas_comentarios as $tc) {
                if(!in_array($tc->id_usuarios,$usuarios_tareas_comentarios)) {
                  $usuarios_tareas_comentarios[] = $tc->id_usuarios;
                }
              }
              if(($u->id == $tarea->destinatario || in_array($u->id,$usuarios_tareas_comentarios)) && $tarea_comentario->id_usuarios != $u->id) {
                $notificacion = new Notificacion;
                $notificacion->id_usuarios = $u->id;
                $notificacion->texto = "Nuevo comentario en Tarea #".$tarea->id.".";
                $notificacion->link = "./?s=detalle-tareas&id=".$tarea->id;
                $notificacion->save();
              }
            }

          }
    
        }

      }
      
    }

  }
?>
