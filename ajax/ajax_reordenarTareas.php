<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if(!isset($_POST['tareas']) || !isset($_POST['id_kanban_columnas'])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Datos incompletos";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  try {
    $tareas = json_decode($_POST['tareas'], true);
    $id_kanban_columnas = $_POST['id_kanban_columnas'];
    $id_tarea_movida = isset($_POST['id_tarea_movida']) ? $_POST['id_tarea_movida'] : null;

    if (!is_array($tareas)) {
      $response["status"] = "ERROR";
      $response["mensaje"] = "Formato de tareas inválido";
      print json_encode($response, JSON_PRETTY_PRINT);
      die();
    }

    // Update the column for the moved task if specified
    if ($id_tarea_movida) {
      $tarea_movida = new KanbanTarea($id_tarea_movida);
      if ($tarea_movida->id) {
        $tarea_movida->id_kanban_columnas = $id_kanban_columnas;
        $tarea_movida->save();
      }
    }

    // Update order for all tasks in the array
    foreach ($tareas as $tarea_data) {
      if (isset($tarea_data['id']) && isset($tarea_data['orden'])) {
        $tarea = new KanbanTarea($tarea_data['id']);
        if ($tarea->id) {
          $tarea->orden = $tarea_data['orden'];
          $tarea->save();
        }
      }
    }

    Historial::guardarAccion("Tareas reordenadas en columna #".$id_kanban_columnas, $usuario);

    $response["status"] = "OK";
    $response["mensaje"] = "Tareas reordenadas correctamente";

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al reordenar tareas: " . $e->getMessage();
    error_log("Error en ajax_reordenarTareas.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
