<?php

  if($_POST == array()) {
    die();
  }

  require_once("../php/app.php");

  $usuario = new Usuario;
  $usuario->getFromDatabase('email',$_POST['email']);

  if($usuario->id == "") {

    $response["status"] = "NO";
    $response["mensaje"] = "Email no corresponde a un usuario registrado en nuestra plataforma.";

  } else {

    $usuario->recuperacion = $usuario->passwordHash($usuario->id);
    $usuario->save();

    $email = new Email;
    $email->enviarCorreoRecuperacion($usuario);

    $response["status"] = "OK";
    $response["mensaje"] = "Se ha enviado un correo con instrucciones a su email.";

  }

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
