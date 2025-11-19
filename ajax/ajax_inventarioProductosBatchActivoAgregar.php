<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if(!validaIdExistsVarios($_POST,['id_batches','id_activos'])) {
    die();
  }

  $batch = new Batch($_POST['id_batches']);
  $batch_activo = $batch->agregarActivo($_POST);

  $activo = new Activo($_POST['id_activos']);

  Historial::guardarAccion("Fermentador ".$activo->codigo." agregado a Batch #".$batch->batch_nombre.".",$GLOBALS['usuario']);

  $response['status'] = 'OK';
  $response['msg_content'] = 'Fermentador <b>'.$activo->codigo.'</b> agregado a batch <b>'.$batch->id.'</b> correctamente.';
  $response['obj'] = $batch_activo;

  echo json_encode($response);

?>