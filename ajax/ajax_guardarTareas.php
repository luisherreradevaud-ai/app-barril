<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if(!isset($_POST['entidad'])) {
    print "error";
    die();
  }

  $entidad = $_POST['entidad'];
  $random_int = rand(0, 999999);

  $ids_tareas = array();

  

  if(isset($_POST['tareas-repetir'])) {
    
    if($_POST['tareas-repetir'] != "No") {

        $_POST['tareas'] = array();

        $ts_date = strtotime($_POST['plazo_maximo']);
        $ts_hasta = strtotime($_POST['tareas-hasta']);

        if($_POST['tareas-repetir'] == "Cada semana") {

          while($ts_date <= $ts_hasta) {

            $ts_date = strtotime("+1 week", $ts_date);

            $_POST['tareas'][] = array(
              'tarea' => $_POST['tarea'],
              'importancia' => $_POST['importancia'],
              'plazo_maximo' => date('Y-m-d',$ts_date)
            );

          }
        }
        if($_POST['tareas-repetir'] == "Cada mes") {

          while($ts_date <= $ts_hasta) {

            $ts_date = strtotime("+1 month", $ts_date);

            $_POST['tareas'][] = array(
              'tarea' => $_POST['tarea'],
              'importancia' => $_POST['importancia'],
              'plazo_maximo' => date('Y-m-d',$ts_date)
            );
            
          }
        }
    }

  }

  if(!isset($_POST['tareas'])) {

    $obj = createObjFromTableName($entidad,'');
    $obj->setPropertiesNoId($_POST);
    $obj->id_usuarios_emisor = $usuario->id;
    $obj->tipo_envio = "Usuario";
    $obj->random_int = $random_int;
    $obj->save();
    $ids_tareas[] = $obj->id;

    if($_POST['enviar_email'] == "true") {
        $email_tareas = new Email;
        $email_tareas->enviarCorreoTareas($obj);
    }

    $response["mensaje"] = "Tarea";

  } else
  if(is_array($_POST['tareas'])) {
    foreach($_POST['tareas'] as $tarea) {

        $obj = createObjFromTableName($entidad,'');
        $obj->setPropertiesNoId($_POST);
        $obj->setPropertiesNoId($tarea);
        $obj->id_usuarios_emisor = $usuario->id;
        $obj->tipo_envio = "Usuario";
        $obj->random_int = $random_int;
        $obj->save();
        $ids_tareas[] = $obj->id;


    }
    if($_POST['tarea'] != '') {

        $obj = createObjFromTableName($entidad,'');
        $obj->setPropertiesNoId($_POST);
        $obj->id_usuarios_emisor = $usuario->id;
        $obj->tipo_envio = "Usuario";
        $obj->random_int = $random_int;
        $obj->save();
        $ids_tareas[] = $obj->id;

    }

    if($_POST['enviar_email'] == "true") {
        $email_tareas = new Email;
        $email_tareas->enviarCorreoTareasMultiple($random_int);
    }

    $response["mensaje"] = "Tareas";

  }

  Historial::guardarAccion("Tareas #".implode(' #',$ids_tareas)." creadas.",$GLOBALS['usuario']);
  NotificacionControl::trigger('Nueva Tarea',$obj);

  $response["status"] = "OK";
  $response["obj"] = $obj;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
