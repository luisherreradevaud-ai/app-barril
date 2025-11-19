<?php

  if($_GET == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession();

  if(!isset($_GET['id_tarea'])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "ID de tarea requerido";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  try {
    $id_tarea = $_GET['id_tarea'];

    if(!is_numeric($id_tarea) || $id_tarea <= 0) {
      throw new Exception("ID de tarea inválido");
    }

    // Get task and its media
    $tarea = new KanbanTarea($id_tarea);
    if(!$tarea->id) {
      throw new Exception("Tarea no encontrada");
    }

    // Get media using getMedia() method
    $media_arr = $tarea->getMedia();

    $archivos = array();
    if(is_array($media_arr)) {
      foreach($media_arr as $media) {
        // Skip default "no image" entry
        if($media['id'] != 0) {
          $archivos[] = array(
            'id' => $media['id'],
            'nombre' => $media['nombre'],
            'url' => './media/images/' . $media['url'],
            'tipo' => $media['tipo'],
            'descripcion' => $media['descripcion']
          );
        }
      }
    }

    $response["status"] = "OK";
    $response["mensaje"] = "Archivos obtenidos correctamente";
    $response["archivos"] = $archivos;

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al obtener archivos: " . $e->getMessage();
    error_log("Error en ajax_getArchivos.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
