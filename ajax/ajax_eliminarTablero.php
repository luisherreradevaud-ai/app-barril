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
    $response["mensaje"] = "ID de tablero requerido";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  try {
    $tablero = new KanbanTablero($_POST['id']);

    if(!$tablero->id) {
      $response["status"] = "ERROR";
      $response["mensaje"] = "Tablero no encontrado";
    } else {
      // Verificar permisos: solo el creador puede eliminar
      if($tablero->id_usuario_creador != $usuario->id) {
        throw new Exception("Solo el creador del tablero puede eliminarlo");
      }

      $tablero->delete();

      $response["status"] = "OK";
      $response["mensaje"] = "Tablero eliminado correctamente";
    }

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al eliminar tablero: " . $e->getMessage();
    error_log("Error en ajax_eliminarTablero.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
