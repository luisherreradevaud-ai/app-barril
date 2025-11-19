<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  try {
    if(isset($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0) {
      $tablero = new KanbanTablero($_POST['id']);
    } else {
      $tablero = new KanbanTablero();
    }

    $tablero->nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
    $tablero->descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
    $tablero->id_entidad = isset($_POST['id_entidad']) ? $_POST['id_entidad'] : '';
    $tablero->orden = isset($_POST['orden']) ? $_POST['orden'] : 0;

    $tablero->save();

    $response["status"] = "OK";
    $response["mensaje"] = "Tablero guardado correctamente";
    $response["tablero"] = $tablero->toArray();

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al guardar tablero: " . $e->getMessage();
    error_log("Error en ajax_guardarTablero.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
