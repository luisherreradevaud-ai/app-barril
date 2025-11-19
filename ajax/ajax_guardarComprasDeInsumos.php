<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");
  
  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  $obj = new CompraDeInsumo;
  $obj->setProperties($_POST);
  $obj->creada = date('Y-m-d H:i:s');
  $obj->save();

  if(isset($_POST['insumos'])) {

    $observaciones_str = "";

    foreach($_POST['insumos'] as $insumo) {

      $insumo_obj = new Insumo($insumo['id']);
      $cdii = new CompraDeInsumoInsumo;
      $cdii->setPropertiesNoId($insumo);
      $cdii->id_insumos = $insumo_obj->id;
      $cdii->id_compras_de_insumos = $obj->id;
      $cdii->save();

      if($insumo['ingresar_a'] = "Bodega") {
        $insumo_obj->bodega += $insumo['cantidad'];
      } else
      if($insumo['ingresar_a'] = "Despacho") {
        $insumo_obj->despacho += $insumo['cantidad'];
      }
      $insumo_obj->save();
  
      $obj->monto += $insumo['monto'];
      $observaciones_str .= $insumo_obj->nombre."    ".$insumo['cantidad']." ".$insumo_obj->unidad_de_medida."\n";
    }
  
    $obj->save();

    $gasto = new Gasto;
    $gasto->tipo_de_gasto = "Insumos";
    $gasto->item = "Compra de Insumos #".$obj->id;
    $gasto->observaciones = $observaciones_str;
    $gasto->estado = $obj->estado;
    $gasto->creada = date('Y-m-d');
    $gasto->monto = $obj->monto;
    $gasto->id_usuarios = $_POST['id_usuarios'];
    $gasto->save();

    $obj->id_gastos = $gasto->id;
    $obj->save();

  }

  Historial::guardarAccion("Compra de Insumos #".$obj->id." ingresada.",$GLOBALS['usuario']);
  NotificacionControl::trigger('Nueva Compra de Insumos',$obj);
  
  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $obj;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
