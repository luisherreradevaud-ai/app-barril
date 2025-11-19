<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  if(!isset($_POST['modo'])) {
    print "error";
    die();
  }

  if(!validaIdExists($_POST,'id')) {
    die();
  }

  $obj = new Tarea($_POST['id']);
  $email_tareas = new Email;
  $email_tareas->enviarCorreoTareas($obj);

  $response["status"] = "OK";
  $response["obj"] = $obj;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
