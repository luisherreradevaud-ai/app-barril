<?php

  if($_GET == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if(!isset($_GET['id_entidad'])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "ID de entidad requerido";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  try {
    $id_entidad = $_GET['id_entidad'];
    $id_usuario_actual = $usuario->id;

    // Get boards with columns and tasks
    $tableros_arr = KanbanTablero::getTablerosPorEntidad($id_entidad);
    $tableros = array();

    foreach($tableros_arr as $tablero) {
      // Solo incluir tableros a los que el usuario tiene acceso
      if($tablero->usuarioTieneAcceso($id_usuario_actual)) {
        $tableros[] = $tablero->toArray();
      }
    }

    // Get all users
    $usuarios_arr = Usuario::getAll("ORDER BY nombre ASC");
    $usuarios = array();
    foreach($usuarios_arr as $user) {
      $usuarios[] = array(
        'id' => $user->id,
        'nombre' => $user->nombre,
        'email' => $user->email
      );
    }

    // Get all labels
    $etiquetas_arr = KanbanEtiqueta::getTodasLasEtiquetas();
    $etiquetas = array();
    foreach($etiquetas_arr as $etiqueta) {
      $etiquetas[] = array(
        'id' => $etiqueta->id,
        'nombre' => $etiqueta->nombre,
        'codigo_hex' => $etiqueta->codigo_hex
      );
    }

    $response["status"] = "OK";
    $response["mensaje"] = "OK";
    $response["tableros"] = $tableros;
    $response["usuarios"] = $usuarios;
    $response["etiquetas"] = $etiquetas;

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al obtener tableros: " . $e->getMessage();
    error_log("Error en ajax_getTableros.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
