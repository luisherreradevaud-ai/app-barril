<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  try {
    if(isset($_POST['id']) && $_POST['id']) {
      $tablero = new KanbanTablero($_POST['id']);

      // Verificar permisos si es edición
      if($tablero->id && !$tablero->usuarioTieneAcceso($usuario->id)) {
        throw new Exception("No tienes permisos para editar este tablero");
      }
    } else {
      $tablero = new KanbanTablero();
    }

    // Only update fields that are provided
    if(isset($_POST['nombre'])) {
      $tablero->nombre = $_POST['nombre'];
    }
    if(isset($_POST['descripcion'])) {
      $tablero->descripcion = $_POST['descripcion'];
    }
    if(isset($_POST['id_entidad'])) {
      $tablero->id_entidad = $_POST['id_entidad'];
    }
    if(isset($_POST['orden'])) {
      $tablero->orden = $_POST['orden'];
    }

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
