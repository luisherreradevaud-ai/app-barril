<?php

  if($_POST == array()) {
    die();
  }

  require_once("../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  $obj = new Usuario;
  $obj->getFromDatabase('email',$_POST['email']);

  if($obj->id != "" && $_POST['id'] == "") {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Ya existe un usuario con este email";
    print json_encode($response,JSON_PRETTY_PRINT);
    die();
  }

  $obj->setPropertiesNoId($_POST);
  $obj->id = "";

  // Solo hashear password si está en modo registro
  if($_POST['modo'] == "Registro" && isset($_POST['password_input'])) {
    $obj->password = $obj->passwordHash($_POST['password_input']);
  }

  $obj->fecha_creacion = date('Y-m-d');
  $obj->estado = "Activo";

  // Solo asignar id_clientes si existe en POST
  if(isset($_POST['id_clientes']) && is_array($_POST['id_clientes']) && count($_POST['id_clientes']) > 0) {
    $obj->id_clientes = $_POST['id_clientes'][0];
  }

  error_log("DEBUG ajax_nuevoUsuario - Antes de save: " . json_encode($obj));
  $obj->save();
  error_log("DEBUG ajax_nuevoUsuario - Después de save, ID: " . $obj->id);

  // Verificar si el usuario se guardó correctamente
  if(!$obj->id || $obj->id == "" || $obj->id == 0) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al guardar usuario en la base de datos";
    error_log("ERROR ajax_nuevoUsuario - No se generó ID después de save()");
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  if($_POST['modo'] == "Invitacion") {
    $obj->setInvitacion();
  }

  if(isset($_POST['id_clientes']) && is_array($_POST['id_clientes'])) {
    foreach($_POST['id_clientes'] as $id_clientes) {
      if($id_clientes) {
        $cliente = new Cliente($id_clientes);
        $obj->createRelation($cliente);
      }
    }
  }

  Historial::guardarAccion("Usuario #".$obj->id." creado.",$GLOBALS['usuario']);


  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $obj;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
