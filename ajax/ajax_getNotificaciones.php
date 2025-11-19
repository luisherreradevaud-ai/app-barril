<?php

    require_once('../php/app.php');

    $usuario = new Usuario;
    session_start();
    $usuario->checkSession($_SESSION);

    $notificaciones['sinleer'] = Notificacion::getAll("WHERE id_usuarios='".$usuario->id."' AND vista='0' ORDER BY id desc");
    if(count($notificaciones['sinleer'])<10) {
        $notificaciones['leidas'] = Notificacion::getAll("WHERE id_usuarios='".$usuario->id."' AND vista='1' ORDER BY id desc LIMIT ".(10 - count($notificaciones['sinleer'])));
    } else {
        $notificaciones['leidas'] = [];
    }

    $response['status'] = "OK";
    $response['mensaje'] = "OK";
    $response['obj'] = $notificaciones;

    print json_encode($response,JSON_PRETTY_PRINT);




 ?>
