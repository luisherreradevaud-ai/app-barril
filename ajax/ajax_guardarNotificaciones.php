<?php

    if($_POST == array()) {
        die();
    }

    require_once('../php/app.php');

    if(!isset($_POST['id_usuarios_niveles'])) {
        die();
    }

    $nuns = TipoDeNotificacionUsuarioNivel::getAll("WHERE id_usuarios_niveles='".$_POST['id_usuarios_niveles']."'");
    foreach($nuns as $nun) {
        $nun->delete();
    }

    foreach($_POST as $key => $post) {
        $explode_arr = explode("_",$key);
        if(count($explode_arr) == 3) {
            if($explode_arr[0] == "nun") {
                $nuns_rel = TipoDeNotificacionUsuarioNivel::getAll("WHERE id_usuarios_niveles='".$_POST['id_usuarios_niveles']."' AND id_tipos_de_notificaciones='".$explode_arr[1]."'");
                if(count($nuns_rel)>0) {
                    $nun = $nuns_rel[0];
                } else {
                    $nun = new NotificacionUsuarioNivel;
                }
                $nun->id_usuarios_niveles = $_POST['id_usuarios_niveles'];
                $nun->id_tipos_de_notificaciones = $explode_arr[1];
                if($explode_arr[2] == "app") {
                    if($post == "false") {
                        $nun->app = 0;
                    }
                    if($post == "true") {
                        $nun->app = 1;
                    }
                }
                if($explode_arr[2] == "email") {
                    if($post == "false") {
                        $nun->email = 0;
                    }
                    if($post == "true") {
                        $nun->email = 1;
                    }
                }
                
                $nun->save();
            }
        }
    }

    $response['status'] = 'OK';
    $response['mensaje'] = 'OK';
    $response['obj'] = [];

    print json_encode($response,JSON_PRETTY_PRINT);

 ?>
