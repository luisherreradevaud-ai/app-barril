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
    $response["mensaje"] = "ID de tarea requerido";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  try {
    $tarea = new Tarea($_POST['id']);

    if(!$tarea->id) {
      $response["status"] = "ERROR";
      $response["mensaje"] = "Tarea no encontrada";
    } else {
      // Delete related records first
      $query = "DELETE FROM tareas_usuarios WHERE id_tareas='".$tarea->id."'";
      $tarea->runQuery($query);

      $query = "DELETE FROM tareas_etiquetas WHERE id_tareas='".$tarea->id."'";
      $tarea->runQuery($query);

      // Delete the task
      $tarea->delete();

      // Log action
      Historial::guardarAccion("Tarea #".$tarea->id." eliminada.", $usuario);

      $response["status"] = "OK";
      $response["mensaje"] = "Tarea eliminada correctamente";
    }

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al eliminar tarea: " . $e->getMessage();
    error_log("Error en ajax_eliminarTarea.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
