<?php

    if($_POST == array()) {
        die();
    }

    require_once('../php/app.php');

    $usuario = new Usuario;
    session_start();
    $usuario->checkSession($_SESSION);

    foreach($_POST['ids_gastos'] as $id_gastos) {
        $gasto = new Gasto($id_gastos);
        $gasto->estado = $_POST['estado'];
        //print_r($gasto);
        $gasto->save();
    }

    Historial::guardarAccion("Gastos #".implode(' #',$_POST['ids_gastos'])." marcados como ".$_POST['estado'].".",$GLOBALS['usuario']);


    $response['status'] = 'OK';
    $response['mensaje'] = 'OK';
    //$response['obj'] = $obj;

    print json_encode($response,JSON_PRETTY_PRINT);

 ?>
