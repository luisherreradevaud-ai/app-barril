<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  try {
    // Determine if creating or updating
    if(isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0) {
      $etiqueta = new KanbanEtiqueta($_POST['id']);
    } else {
      $etiqueta = new KanbanEtiqueta();
    }

    $etiqueta->nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
    $etiqueta->codigo_hex = isset($_POST['codigo_hex']) ? $_POST['codigo_hex'] : '#6A1693';

    $etiqueta->save();

    $response["status"] = "OK";
    $response["mensaje"] = "Etiqueta guardada correctamente";
    $response["etiqueta"] = array(
      'id' => $etiqueta->id,
      'nombre' => $etiqueta->nombre,
      'codigo_hex' => $etiqueta->codigo_hex
    );

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al guardar etiqueta: " . $e->getMessage();
    error_log("Error en ajax_guardarEtiqueta.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
