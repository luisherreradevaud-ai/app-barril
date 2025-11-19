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
      $columna = new KanbanColumna($_POST['id']);

      // Verificar permisos del tablero al editar
      if($columna->id) {
        $tablero = $columna->getTablero();
        if($tablero && !$tablero->usuarioTieneAcceso($usuario->id)) {
          throw new Exception("No tienes permisos para editar esta columna");
        }
      }
    } else {
      $columna = new KanbanColumna();
    }

    $columna->nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
    $columna->id_kanban_tableros = isset($_POST['id_kanban_tableros']) ? $_POST['id_kanban_tableros'] : 0;
    $columna->orden = isset($_POST['orden']) ? $_POST['orden'] : 0;
    $columna->color = isset($_POST['color']) ? $_POST['color'] : '#6A1693';

    // Verificar permisos del tablero al crear
    if(!$columna->id && $columna->id_kanban_tableros) {
      $tablero = new KanbanTablero($columna->id_kanban_tableros);
      if($tablero->id && !$tablero->usuarioTieneAcceso($usuario->id)) {
        throw new Exception("No tienes permisos para crear columnas en este tablero");
      }
    }

    $columna->save();

    $response["status"] = "OK";
    $response["mensaje"] = "Columna guardada correctamente";
    $response["columna"] = $columna->toArray();

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al guardar columna: " . $e->getMessage();
    error_log("Error en ajax_guardarColumna.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
