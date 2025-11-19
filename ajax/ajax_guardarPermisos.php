<?php

    if($_POST == array()) {
        die();
    }

    require_once('../php/app.php');

    $permisos = Permiso::getAll();
    foreach($permisos as $permiso) {
        $permiso->delete();
    }

    foreach($_POST as $key => $post) {
        $explode_arr = explode("_",$key);
        if(count($explode_arr) == 3) {
            if($explode_arr[0] == "permiso") {
                $permiso = new Permiso;
                $permiso->id_secciones = $explode_arr[1];
                $permiso->id_usuarios_niveles = $explode_arr[2];
                if($post == "false") {
                    $permiso->acceso = 0;
                }
                if($post == "true") {
                    $permiso->acceso = 1;
                }
                $permiso->save();
            }
        }
    }

    $response['status'] = 'OK';
    $response['mensaje'] = 'OK';
    $response['obj'] = [];

    print json_encode($response,JSON_PRETTY_PRINT);

 ?>
