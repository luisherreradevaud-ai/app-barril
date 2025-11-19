<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  $obj = new Sugerencia;
  $obj->setPropertiesNoId($_POST);
  $obj->save();

  Historial::guardarAccion("Sugerencia #".$obj->id." creada.",$GLOBALS['usuario']);
  NotificacionControl::trigger('Nueva Sugerencia',$obj);

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $obj;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
