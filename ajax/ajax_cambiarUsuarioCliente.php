<?php

  if($_POST == array()) {
    die();
  }

  require_once("../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if(!validaIdExists($_POST,'id_clientes')) {
    die();
  }

  $relations = $usuario->getRelations('clientes');
  if(!in_array($_POST['id_clientes'],$relations)) {
    die();
  }

  $usuario->id_clientes = $_POST['id_clientes'];
  $usuario->save();

  $cliente = new Cliente($usuario->id_clientes);

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $cliente;

  print json_encode($response,JSON_PRETTY_PRINT);



?>