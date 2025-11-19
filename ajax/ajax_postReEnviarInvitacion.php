<?php

  if($_POST == array()) {
    die();
  }

  require_once("../php/app.php");

  if(!validaIdExists($_POST,'id')) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "No se encuentra ID de usuario.";
    die();
  }

  $usuario = new Usuario($_POST['id']);

  if($usuario->id == "") {
    $response["status"] = "ERROR";
    $response["mensaje"] = "No se encuentra ID de usuario.";
    die();
  }

  $usuario->setProperties($_POST);
  $usuario->save();
  $usuario->setInvitacion();

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $usuario;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
