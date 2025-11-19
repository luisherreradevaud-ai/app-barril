<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession();

  if(!isset($_POST['id_tarea'])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "ID de tarea requerido";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  if(!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "No se seleccionaron archivos";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  try {
    $id_tarea = $_POST['id_tarea'];

    if(!is_numeric($id_tarea) || $id_tarea <= 0) {
      throw new Exception("ID de tarea inválido");
    }

    // Verify task exists
    $tarea = new KanbanTarea($id_tarea);
    if(!$tarea->id) {
      throw new Exception("Tarea no encontrada");
    }

    $archivos_subidos = array();
    $files = $_FILES['files'];
    $file_count = count($files['name']);

    for($i = 0; $i < $file_count; $i++) {
      if($files['error'][$i] === UPLOAD_ERR_OK) {
        // Create Media object and use newMedia method
        $media = new Media();
        $media->nombre = $files['name'][$i];
        $media->descripcion = 'Archivo adjunto a tarea';

        // Prepare file array in the format newMedia expects
        $file_data = array(
          'name' => $files['name'][$i],
          'tmp_name' => $files['tmp_name'][$i],
          'type' => $files['type'][$i],
          'size' => $files['size'][$i],
          'error' => $files['error'][$i]
        );

        $media->newMedia($file_data);

        if($media->id) {
          // Create relationship using createRelation method
          $media->createRelation($tarea);

          $archivos_subidos[] = array(
            'id' => $media->id,
            'nombre' => $media->nombre,
            'url' => './media/images/' . $media->url
          );
        }
      }
    }

    $response["status"] = "OK";
    $response["mensaje"] = "Archivo(s) subido(s) correctamente";
    $response["archivos"] = $archivos_subidos;

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al subir archivo: " . $e->getMessage();
    error_log("Error en ajax_subirArchivo.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
