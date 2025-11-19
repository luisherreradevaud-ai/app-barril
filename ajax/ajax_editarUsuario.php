<?php

  if($_POST == array()) {
    die();
  }

  require_once("../php/app.php");

  $usuario = new Usuario($_POST['id']);
  //$usuario->getFromDatabase('email',$_POST['email']);
  $usuario->setPropertiesNoId($_POST);
  $usuario->id_clientes = $_POST['id_clientes'][0];
  $usuario->save();

  foreach($usuario->getRelations('clientes') as $id_clientes) {
    $cliente = new Cliente($id_clientes);
    $usuario->deleteRelation($cliente);
  }
  foreach($_POST['id_clientes'] as $id_clientes) {
    $cliente = new Cliente($id_clientes);
    $usuario->createRelation($cliente);
  }

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $usuario;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
