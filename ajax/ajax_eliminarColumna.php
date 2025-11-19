<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if(!isset($_POST['id'])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "ID de columna requerido";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  try {
    $columna = new KanbanColumna($_POST['id']);

    if(!$columna->id) {
      $response["status"] = "ERROR";
      $response["mensaje"] = "Columna no encontrada";
    } else {
      // Verificar permisos del tablero
      $tablero = $columna->getTablero();
      if($tablero && !$tablero->usuarioTieneAcceso($usuario->id)) {
        throw new Exception("No tienes permisos para eliminar esta columna");
      }

      $columna->delete();

      $response["status"] = "OK";
      $response["mensaje"] = "Columna eliminada correctamente";
    }

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al eliminar columna: " . $e->getMessage();
    error_log("Error en ajax_eliminarColumna.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
