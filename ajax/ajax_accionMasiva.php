<?php

    if($_POST == array()) {
        die();
    }

    require_once('../php/app.php');

    $usuario = new Usuario;
    session_start();
    $usuario->checkSession($_SESSION);

    foreach($_POST['ids'] as $id) {
        $obj = createObjFromTablename($_POST['table_name'],$id);
        if($_POST['accion'] == "eliminar") {
            $obj->delete();
            $accion_msg = "eliminados";
        }
        if($_POST['accion'] == "tareas-marcar-como") {
            $obj->estado = $_POST['estado'];
            $accion_msg = "marcadas como ".$obj->estado;
            $obj->save();
        }
    }

    Historial::guardarAccion(get_class($obj)." #".implode(' #',$_POST['ids'])." ".$accion_msg.".",$GLOBALS['usuario']);

    $response['status'] = 'OK';
    $response['mensaje'] = 'OK';
    $response['obj'] = $obj;

    print json_encode($response,JSON_PRETTY_PRINT);

 ?>
