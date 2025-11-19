<?php

    if($_POST == array()) {
        die();
    }

    require_once('../php/app.php');

    $usuario = new Usuario;
    session_start();
    $usuario->checkSession($_SESSION);

    if(!validaIdExists($_POST,'id_tareas')) {
        $response['status'] = 'ERROR';
        $response['mensaje'] = 'Encabezados incompletos.';
        print json_encode($response,JSON_PRETTY_PRINT);
        die();
    }

    $obj = new Tarea($_POST['id_tareas']);

    if($obj->id == '') {
        $response['status'] = 'ERROR';
        $response['mensaje'] = 'No se encontró esta tarea.';
        print json_encode($response,JSON_PRETTY_PRINT);
        die();
    }

    if( ($obj->tipo_envio == 'Usuario' && $_SESSION['id'] != $obj->destinatario ) || ($obj->tipo_envio == 'Nivel' && $_SESSION['nivel'] != $obj->destinatario )) {
        $response['status'] = 'ERROR';
        $response['mensaje'] = 'No se encontro esta tarea.';
        print json_encode($response,JSON_PRETTY_PRINT);
        die();
    }

    $obj->estado = $_POST['estado'];
    $obj->save();

    Historial::guardarAccion("Tarea #".$obj->id." marcada como ".$obj->estado.".",$GLOBALS['usuario']);

    if($obj->estado == "Realizada") {
        NotificacionControl::trigger('Tarea Realizada',$obj);
    }


    $response['status'] = 'OK';
    $response['mensaje'] = 'OK';
    $response['obj'] = $obj;

    print json_encode($response,JSON_PRETTY_PRINT);

 ?>
