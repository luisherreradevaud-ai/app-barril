<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if($usuario->nivel != 'Administrador') {
    $response['status'] = 'OK';
    $response['msg_content'] = 'Su nivel de usuario no le permite realizar esta operacion.';
    $response['obj'] = $batch_activo;

    echo json_encode($response);
    die();
  }

  if(!validaIdExistsVarios($_POST,['id_batches'])) {
    die();
  }

  $batch = new Batch($_POST['id_batches']);
  $batch_activo_obj = new BatchActivo($_POST['id_batches_activos']);
  $activo = new Activo($batch_activo_obj->id_activos);

  $batch_activo = $batch->eliminarActivo($_POST);

  Historial::guardarAccion("Fermentador ".$activo->codigo." quitado de Batch #".$batch->batch_nombre.".",$GLOBALS['usuario']);

  $response['status'] = 'OK';
  $response['msg_content'] = 'Fermentador quitado de batch '.$batch->id.'.';
  $response['obj'] = $batch_activo;

  echo json_encode($response);

?>