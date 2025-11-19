<?php

  if($_POST == array()) {
    die();
  }

  require_once("../php/app.php");

  $usuario = new Usuario;
  $usuario->getFromDatabase('recuperacion',$_POST['invitacion']);



  $usuario->password = $usuario->passwordHash($_POST['password']);
  $usuario->invitacion = "";
  $usuario->recuperacion = "";
  $usuario->save();

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $usuario;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
