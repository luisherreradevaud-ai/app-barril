<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if(!isset($_POST['id'])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "ID de tablero requerido";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  try {
    $tablero = new Tablero($_POST['id']);

    if(!$tablero->id) {
      $response["status"] = "ERROR";
      $response["mensaje"] = "Tablero no encontrado";
    } else {
      $tablero->delete();

      $response["status"] = "OK";
      $response["mensaje"] = "Tablero eliminado correctamente";
    }

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al eliminar tablero: " . $e->getMessage();
    error_log("Error en ajax_eliminarTablero.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
