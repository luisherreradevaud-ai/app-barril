<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  // Validar duplicados antes de crear el despacho
  $ids_barriles = array();
  $ids_cajas = array();
  foreach($_POST['despacho'] as $producto) {
    if($producto['tipo'] == "Barril" && isset($producto['id_barriles']) && $producto['id_barriles'] != '0') {
      if(in_array($producto['id_barriles'], $ids_barriles)) {
        $response = array(
          "status" => "ERROR",
          "mensaje" => "El barril con código " . $producto['codigo'] . " está duplicado en el despacho"
        );
        print json_encode($response, JSON_PRETTY_PRINT);
        exit;
      }
      $ids_barriles[] = $producto['id_barriles'];
    }
    if($producto['tipo'] == "CajaEnvases" && isset($producto['id_cajas_de_envases']) && $producto['id_cajas_de_envases'] != '0') {
      if(in_array($producto['id_cajas_de_envases'], $ids_cajas)) {
        $response = array(
          "status" => "ERROR",
          "mensaje" => "La caja con código " . $producto['codigo'] . " está duplicada en el despacho"
        );
        print json_encode($response, JSON_PRETTY_PRINT);
        exit;
      }
      $ids_cajas[] = $producto['id_cajas_de_envases'];
    }
  }

  $obj = new Despacho;
  $obj->setProperties($_POST);
  $obj->creada = date('Y-m-d H:i:s');
  $obj->save();

  foreach($_POST['despacho'] as $producto) {
    $dp = new DespachoProducto;
    $dp->setProperties($producto);
    $dp->id_despachos = $obj->id;
    $dp->estado = "En despacho";

    // Manejar CajaEnvases
    if($producto['tipo'] == "CajaEnvases" && isset($producto['id_cajas_de_envases'])) {
      $dp->id_cajas_de_envases = $producto['id_cajas_de_envases'];
      // Obtener el id_productos desde la caja
      $caja_temp = new CajaDeEnvases($producto['id_cajas_de_envases']);
      if($caja_temp->id_productos > 0) {
        $dp->id_productos = $caja_temp->id_productos;
      }
    }

    $dp->save();

    if($producto['tipo'] == "Barril") {
      $barril = new Barril($producto['id_barriles']);
      $barril->estado = "En despacho";
      $barril->registrarCambioDeEstado();
      $barril->save();
    }

    // Cambiar estado de CajaDeEnvases a "En despacho"
    if($producto['tipo'] == "CajaEnvases" && isset($producto['id_cajas_de_envases'])) {
      $caja = new CajaDeEnvases($producto['id_cajas_de_envases']);
      $caja->estado = "En despacho";
      $caja->save();
    }
  }

  Historial::guardarAccion("Despacho #".$obj->id." ingresado.",$GLOBALS['usuario']);
  NotificacionControl::trigger('Nuevo Despacho',$obj);

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $obj;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
