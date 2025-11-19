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
    $response["mensaje"] = "ID de archivo requerido";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  try {
    $id = $_POST['id'];

    if(!is_numeric($id) || $id <= 0) {
      throw new Exception("ID de archivo inválido");
    }

    $media = new Media($id);

    if(!$media->id) {
      throw new Exception("Archivo no encontrado");
    }

    // Delete physical file and thumbnails using deleteMedia method
    $media->deleteMedia();

    $response["status"] = "OK";
    $response["mensaje"] = "Archivo eliminado correctamente";

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al eliminar archivo: " . $e->getMessage();
    error_log("Error en ajax_eliminarArchivo.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
