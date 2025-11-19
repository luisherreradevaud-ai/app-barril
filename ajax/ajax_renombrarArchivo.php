<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession();

  if(!isset($_POST['id']) || !isset($_POST['nombre'])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "ID y nombre de archivo requeridos";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  try {
    $id = $_POST['id'];
    $nuevo_nombre = $_POST['nombre'];

    if(!is_numeric($id) || $id <= 0) {
      throw new Exception("ID de archivo inválido");
    }

    if(empty($nuevo_nombre)) {
      throw new Exception("Nombre de archivo inválido");
    }

    $media = new Media($id);

    if(!$media->id) {
      throw new Exception("Archivo no encontrado");
    }

    // Update the nombre field in database
    $media->nombre = $nuevo_nombre;
    $media->save();

    $response["status"] = "OK";
    $response["mensaje"] = "Archivo renombrado correctamente";

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al renombrar archivo: " . $e->getMessage();
    error_log("Error en ajax_renombrarArchivo.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
