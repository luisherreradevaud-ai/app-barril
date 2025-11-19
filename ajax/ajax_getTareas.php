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

    // Get tasks
    $tareas = Tarea::getTareasPorEntidad($id_entidad);

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
    $etiquetas_arr = Etiqueta::getTodasLasEtiquetas();
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
    $response["tareas"] = $tareas;
    $response["usuarios"] = $usuarios;
    $response["etiquetas"] = $etiquetas;

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al obtener tareas: " . $e->getMessage();
    error_log("Error en ajax_getTareas.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
