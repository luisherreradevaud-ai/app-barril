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
  $batch_activo = $batch->editarActivo($_POST);

  $response['status'] = 'OK';
  $response['msg'] = 'OK';
  $response['obj'] = $batch_activo;

  echo json_encode($response);

?>