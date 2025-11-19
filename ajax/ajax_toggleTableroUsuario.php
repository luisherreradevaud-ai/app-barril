<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  try {
    $id_tablero = isset($_POST['id_tablero']) ? $_POST['id_tablero'] : null;
    $id_usuario = isset($_POST['id_usuario']) ? $_POST['id_usuario'] : null;
    $action = isset($_POST['action']) ? $_POST['action'] : null;

    if(!$id_tablero || !is_numeric($id_tablero)) {
      throw new Exception("ID de tablero inválido");
    }

    if(!$id_usuario || !is_numeric($id_usuario)) {
      throw new Exception("ID de usuario inválido");
    }

    if($action !== 'agregar' && $action !== 'quitar') {
      throw new Exception("Acción inválida");
    }

    $tablero = new Tablero($id_tablero);
    if(!$tablero->id) {
      throw new Exception("Tablero no encontrado");
    }

    $usuario_asignar = new Usuario($id_usuario);
    if(!$usuario_asignar->id) {
      throw new Exception("Usuario no encontrado");
    }

    if($action === 'agregar') {
      $tablero->agregarUsuario($usuario_asignar);
      $mensaje = "Usuario agregado al tablero correctamente";
    } else {
      $tablero->quitarUsuario($usuario_asignar);
      $mensaje = "Usuario quitado del tablero correctamente";
    }

    $response["status"] = "OK";
    $response["mensaje"] = $mensaje;

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al modificar usuario del tablero: " . $e->getMessage();
    error_log("Error en ajax_toggleTableroUsuario.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
