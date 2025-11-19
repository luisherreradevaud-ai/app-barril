<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if(!isset($_POST['id']) || !isset($_POST['id_kanban_columnas']) || !isset($_POST['orden'])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Datos incompletos";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  try {
    $tarea = new KanbanTarea($_POST['id']);

    if(!$tarea->id) {
      $response["status"] = "ERROR";
      $response["mensaje"] = "Tarea no encontrada";
    } else {
      $tarea->id_kanban_columnas = $_POST['id_kanban_columnas'];
      $tarea->orden = $_POST['orden'];
      $tarea->save();

      $response["status"] = "OK";
      $response["mensaje"] = "Tarea movida correctamente";
      $response["tarea"] = $tarea->toArray();
    }

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al mover tarea: " . $e->getMessage();
    error_log("Error en ajax_moverTarea.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
