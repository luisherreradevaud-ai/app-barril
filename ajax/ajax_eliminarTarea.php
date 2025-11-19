<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession();

  if(!isset($_POST['id'])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "ID de tarea requerido";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  try {
    $tarea = new KanbanTarea($_POST['id']);

    if(!$tarea->id) {
      $response["status"] = "ERROR";
      $response["mensaje"] = "Tarea no encontrada";
    } else {
      // Verificar permisos
      $tablero = $tarea->getTablero();
      if($tablero && !$tablero->usuarioTieneAcceso($usuario->id)) {
        throw new Exception("No tienes permisos para eliminar esta tarea");
      }

      // Delete related records first
      $query = "DELETE FROM kanban_tareas_usuarios WHERE id_kanban_tareas='".$tarea->id."'";
      $tarea->runQuery($query);

      $query = "DELETE FROM kanban_tareas_etiquetas WHERE id_kanban_tareas='".$tarea->id."'";
      $tarea->runQuery($query);

      // Delete all media files attached to this task
      $tarea->deleteAllMedia();

      // Delete the task
      $tarea->delete();

      // Log action
      Historial::guardarAccion("Kanban Tarea #".$tarea->id." eliminada.", $usuario);

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
