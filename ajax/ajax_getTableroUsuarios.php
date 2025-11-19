<?php

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  try {
    $id_tablero = isset($_GET['id_tablero']) ? $_GET['id_tablero'] : null;

    if(!$id_tablero || !is_numeric($id_tablero)) {
      throw new Exception("ID de tablero inválido");
    }

    $tablero = new Tablero($id_tablero);

    if(!$tablero->id) {
      throw new Exception("Tablero no encontrado");
    }

    // Get usuarios asignados al tablero
    $usuarios_obj = $tablero->getUsuarios();
    $usuarios = array();

    foreach($usuarios_obj as $user) {
      $usuarios[] = array(
        'id' => $user->id,
        'nombre' => $user->nombre,
        'apellido' => $user->apellido,
        'email' => $user->email
      );
    }

    $response["status"] = "OK";
    $response["mensaje"] = "Usuarios del tablero obtenidos correctamente";
    $response["usuarios"] = $usuarios;

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al obtener usuarios del tablero: " . $e->getMessage();
    error_log("Error en ajax_getTableroUsuarios.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
