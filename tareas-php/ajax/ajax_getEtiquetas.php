<?php

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  try {
    $etiquetas_arr = KanbanEtiqueta::getTodasLasEtiquetas();
    $etiquetas = array();

    foreach($etiquetas_arr as $etiqueta) {
      $etiquetas[] = array(
        'id' => $etiqueta->id,
        'nombre' => $etiqueta->nombre,
        'codigo_hex' => $etiqueta->codigo_hex
      );
    }

    $response["status"] = "OK";
    $response["mensaje"] = "OK";
    $response["etiquetas"] = $etiquetas;

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al obtener etiquetas: " . $e->getMessage();
    error_log("Error en ajax_getEtiquetas.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
