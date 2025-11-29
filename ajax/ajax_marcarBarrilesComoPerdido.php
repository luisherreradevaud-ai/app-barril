<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if(!isset($_POST['id'])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "error";
    print json_encode($response,JSON_PRETTY_PRINT);
    die();
  }

  $obj = new Barril($_POST['id']);
  $obj->estado = "Perdido";
  $obj->registrarCambioDeEstado();
  $obj->save();

  Historial::guardarAccion("Barril #".$obj->id." marcado como Perdido.",$GLOBALS['usuario']);
  NotificacionControl::trigger('Barril Perdido',$obj);

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $obj;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
