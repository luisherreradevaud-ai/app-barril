<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if(!isset($_POST['columnas'])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Datos de columnas requeridos";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  try {
    $columnas = json_decode($_POST['columnas'], true);

    if(!is_array($columnas)) {
      $response["status"] = "ERROR";
      $response["mensaje"] = "Formato de datos inválido";
      print json_encode($response, JSON_PRETTY_PRINT);
      die();
    }

    // Update order for each column
    foreach($columnas as $columnaData) {
      if(isset($columnaData['id']) && isset($columnaData['orden'])) {
        $columna = new KanbanColumna($columnaData['id']);
        if($columna->id) {
          $columna->orden = $columnaData['orden'];
          $columna->save();
        }
      }
    }

    $response["status"] = "OK";
    $response["mensaje"] = "Columnas reordenadas correctamente";

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al reordenar columnas: " . $e->getMessage();
    error_log("Error en ajax_reordenarColumnas.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
