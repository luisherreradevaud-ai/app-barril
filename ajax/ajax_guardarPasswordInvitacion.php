<?php

  if($_POST == array()) {
    die();
  }

  require_once("../php/app.php");

  $usuario = new Usuario;
  $usuario->getFromDatabase('invitacion',$_POST['invitacion']);
  $usuario->setPropertiesNoId($_POST);
  $usuario->password = $usuario->passwordHash($_POST['password']);
  $usuario->invitacion = "";
  $usuario->recuperacion = "";
  $usuario->save();

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $usuario;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
