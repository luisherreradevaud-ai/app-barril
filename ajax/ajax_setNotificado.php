<?php

    require_once('../php/app.php');

    $usuario = new Usuario;
    session_start();
    $usuario->checkSession($_SESSION);

    $notificaciones = Notificacion::getAll("WHERE id_usuarios='".$usuario->id."'");

    foreach($notificaciones as $n) {
        $n->vista = "1";
        $n->vista_datetime = date('Y-m-d H:i:s');
        $n->save();
    }

    $response['status'] = "OK";
    $response['mensaje'] = "OK";
    $response['obj'] = [];

    print json_encode($response,JSON_PRETTY_PRINT);




 ?>
