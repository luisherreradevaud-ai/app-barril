<?php

  if($_POST == array()) {
    die();
  }

  require_once("../php/app.php");

  $usuario = new Usuario($_POST['id_usuarios']);

  if($usuario->password == $usuario->passwordHash($_POST['password-anterior'])) {

    $usuario->password = $usuario->passwordHash($_POST['password']);
    $usuario->save();

    $response["status"] = "OK";
    $response["mensaje"] = "OK";
    $response["obj"] = $usuario;

    $_SESSION = $usuario->sessionArray();

  } else {
    $response["status"] = "NO";
    $response["mensaje"] = "Password anterior incorrecto.";
  }

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
