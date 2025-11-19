<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  $obj = new Despacho;
  $obj->setProperties($_POST);
  $obj->creada = date('Y-m-d H:i:s');
  $obj->save();

  foreach($_POST['despacho'] as $producto) {
    $dp = new DespachoProducto;
    $dp->setProperties($producto);
    $dp->id_despachos = $obj->id;
    $dp->estado = "En despacho";
    $dp->save();

    if($producto['tipo'] == "Barril") {
      $barril = new Barril($producto['id_barriles']);
      $barril->estado = "En despacho";
      $barril->registrarCambioDeEstado();
      $barril->save();
    }
  }

  Historial::guardarAccion("Despacho #".$obj->id." ingresado.",$GLOBALS['usuario']);
  NotificacionControl::trigger('Nuevo Despacho',$obj);

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $obj;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
