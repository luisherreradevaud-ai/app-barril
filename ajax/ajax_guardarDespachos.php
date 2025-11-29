<?php

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);


  // PROCESAMIENTO DE PEDIDOS PRODUCTOS EN LA DROPZONE DE PEDIDOS

  if(isset($_POST['pedidos']) && is_array($_POST['pedidos'])) {
    foreach($_POST['pedidos'][0] as $pedido) {
      if(!validaIdExists($pedido,'id_pedidos_productos')) {
        continue;
      }
      $despacho_producto = new DespachoProducto;
      $despacho_producto->getFromDatabase('id_pedidos_productos',$pedido['id_pedidos_productos']);
      if( $despacho_producto->id != '' && $despacho_producto->id != 0 ) {
        if($despacho_producto->id_barriles != 0) {
          $barril = new Barril($despacho_producto->id_barriles);
          $barril->estado = 'En planta';
          $barril->registrarCambioDeEstado();
          $barril->save();
        }
        $despacho_producto->delete();
        $pedido_producto = new PedidoProducto($pedido['id_pedidos_productos']);
        $pedido_producto->estado = 'Solicitado';
        $pedido_producto->save();
      }
    }
  }

  // CONSOLIDACION DE DESPACHOS

  $despachos_repartidores = [];

  if(isset($_POST['repartidores']) && is_array($_POST['repartidores'])) {
    foreach($_POST['repartidores'] as $repartidor_array) {
      $id_usuarios_repartidor = $repartidor_array['id_usuarios_repartidor'];
      $despachos = Despacho::getAll("WHERE id_usuarios_repartidor='".$id_usuarios_repartidor."'");
      if (count($despachos) === 0) {
        $nuevo_despacho = new Despacho();
        $nuevo_despacho->id_usuarios_repartidor = $id_usuarios_repartidor;
        $nuevo_despacho->save();
        $despachos_repartidores[$id_usuarios_repartidor] = $nuevo_despacho;
      } else
      if (count($despachos) === 1) {
        $despachos_repartidores[$id_usuarios_repartidor] = $despachos[0];
      } else {
        $principal = $despachos[0];
        $despachos_repartidores[$id_usuarios_repartidor] = $despachos[0];
        for ($i = 1; $i < count($despachos); $i++) {
            $despacho_actual = $despachos[$i];
            $productos = DespachoProducto::getAll("WHERE id_despachos='".$despacho_actual->id."'");
            foreach ($productos as $dp) {
                $dp->id_despachos = $principal->id;
                $dp->save();
            }
            $despacho_actual->delete();
        }
      }
    }
  }


  // PEDIDOS PRODUCTOS Y REPARTIDORES

  if(isset($_POST['repartidores']) && is_array($_POST['repartidores'])) {
    foreach($_POST['repartidores'] as $repartidor_array) {
      $despachos = Despacho::getAll("WHERE id_usuarios_repartidor='".$repartidor_array['id_usuarios_repartidor']."'");
      if(!isset($repartidor_array['barriles']) || !is_array($repartidor_array['barriles']) ) {
        $despachos_productos = DespachoProducto::getAll("WHERE id_despachos='".$despachos_repartidores[$repartidor_array['id_usuarios_repartidor']]->id."'");
        foreach($despachos_productos as $despacho_producto) {
          if($despacho_producto->id_pedidos_productos != '' && $despacho_producto->id_pedidos_productos != 0) {
            $pedido_producto = new PedidoProducto($despacho_producto->id_pedidos_productos);
            $pedido_producto->estado = 'En despacho';
            $pedido_producto->save();
          }
          $despacho_producto->delete();
        }
        continue;
      } else {

        foreach($repartidor_array['barriles'] as $pedido_producto_array) {

          if(!validaIdExists($pedido_producto_array,'id_pedidos_productos')) {
            continue;
          }


          $pedido_producto = new PedidoProducto($pedido_producto_array['id_pedidos_productos']);
          $pedido_producto->estado = 'En despacho';
          $pedido_producto->save();

          $barril = new Barril($pedido_producto_array['id_barriles']);
          $barril->estado = 'En despacho';
          $barril->registrarCambioDeEstado();
          $barril->save();

          $despacho_producto = new DespachoProducto;
          $despacho_producto->getFromDatabase('id_pedidos_productos',$pedido_producto->id);
          $despacho_producto->tipo = $pedido_producto->tipo;
          $despacho_producto->cantidad = $pedido_producto->cantidad;
          $despacho_producto->tipos_cerveza = $pedido_producto->tipos_cerveza;
          $despacho_producto->id_productos = $pedido_producto->id_productos;
          $despacho_producto->clasificacion = 'Cerveza';
          $despacho_producto->codigo = $barril->codigo;
          $despacho_producto->id_barriles = $barril->id;
          $despacho_producto->id_despachos = $despachos_repartidores[$repartidor_array['id_usuarios_repartidor']]->id;
          $despacho_producto->id_pedidos_productos = $pedido_producto_array['id_pedidos_productos'];
          $despacho_producto->save();

        }
      }
    }
  }

  // ELIMINAMOS DESPACHOS QUE NO TIENEN DESPACHOS PRODUCTOS

  $despachos = Despacho::getAll();
  foreach ($despachos as $despacho) {
    $productos = DespachoProducto::getAll("WHERE id_despachos='".$despacho->id."'");
    if (count($productos) === 0) {
        $despacho->delete();
    }
  }

  $cdd = new CentralDeDespacho;

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $cdd->getDataPage();

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
