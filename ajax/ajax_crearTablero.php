<?php

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  try {
    // Create new tablero with default values
    $tablero = new KanbanTablero();
    $tablero->nombre = "Nuevo Tablero";
    $tablero->descripcion = "";
    $tablero->id_entidad = $GLOBALS['usuario']->id;
    $tablero->orden = 0;
    $tablero->save();

    // Create default columns
    $columnas_default = array(
      array('nombre' => 'Por Hacer', 'color' => '#808080'),
      array('nombre' => 'En Progreso', 'color' => '#FFA500'),
      array('nombre' => 'Completado', 'color' => '#28a745')
    );

    foreach($columnas_default as $index => $col_data) {
      $columna = new KanbanColumna();
      $columna->nombre = $col_data['nombre'];
      $columna->id_kanban_tableros = $tablero->id;
      $columna->orden = $index;
      $columna->color = $col_data['color'];
      $columna->save();
    }

    $response["status"] = "OK";
    $response["mensaje"] = "Tablero creado correctamente";
    $response["tablero_id"] = $tablero->id;

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al crear tablero: " . $e->getMessage();
    error_log("Error en ajax_crearTablero.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
