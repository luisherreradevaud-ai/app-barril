<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession();

  if(!validaIdExists($_POST,'id')) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "ID no válido";
    print json_encode($response);
    die();
  }

  $cliente = new Cliente($_POST['id']);
  $estado_anterior = $cliente->estado;
  $cliente->estado = $_POST['estado'];
  $cliente->save();

  $accion = ($cliente->estado == 'Bloqueado') ? 'bloqueado' : 'desbloqueado';
  Historial::guardarAccion("Cliente #".$cliente->id." ".$cliente->nombre." ".$accion.".",$GLOBALS['usuario']);

  $response["status"] = "OK";
  $response["mensaje"] = "Cliente ".$accion." exitosamente";
  $response["obj"] = $cliente;

  print json_encode($response);

?>
