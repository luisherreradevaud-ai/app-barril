<?php

    require_once('../php/app.php');

    $usuario = new Usuario;
    session_start();
    $usuario->checkSession($_SESSION);

    $cdp = new CentralDeDespacho;

    $response['status'] = 'OK';
    $response['mensaje'] = 'OK';
    $response['obj'] = $cdp->getDataPage();

    print json_encode($response,JSON_PRETTY_PRINT);

 ?>
