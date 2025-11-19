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

  if(!isset($_POST['nombre'])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Nombre de tablero requerido";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  try {
    $tableroOriginal = new KanbanTablero($_POST['id']);

    if(!$tableroOriginal->id) {
      $response["status"] = "ERROR";
      $response["mensaje"] = "Tablero original no encontrado";
    } else {
      // Verificar permisos: debe tener acceso al tablero original
      if(!$tableroOriginal->usuarioTieneAcceso($usuario->id)) {
        throw new Exception("No tienes permisos para copiar este tablero");
      }

      // Create new tablero with the new name
      $nuevoTablero = new KanbanTablero();
      $nuevoTablero->nombre = $_POST['nombre'];
      $nuevoTablero->id_entidad = $tableroOriginal->id_entidad;
      $nuevoTablero->save();

      if(!$nuevoTablero->id) {
        throw new Exception("Error al crear el nuevo tablero");
      }

      // Copy all columns from original tablero
      $columnasOriginales = KanbanColumna::getAll("WHERE id_kanban_tableros = " . $tableroOriginal->id . " ORDER BY orden ASC");

      foreach($columnasOriginales as $columnaOriginal) {
        $nuevaColumna = new KanbanColumna();
        $nuevaColumna->nombre = $columnaOriginal->nombre;
        $nuevaColumna->color = $columnaOriginal->color;
        $nuevaColumna->orden = $columnaOriginal->orden;
        $nuevaColumna->id_kanban_tableros = $nuevoTablero->id;
        $nuevaColumna->save();

        if(!$nuevaColumna->id) {
          continue;
        }

        // Copy all tasks from this column
        $tareasOriginales = KanbanTarea::getAll("WHERE id_kanban_columnas = " . $columnaOriginal->id . " ORDER BY orden ASC");

        foreach($tareasOriginales as $tareaOriginal) {
          $nuevaTarea = new KanbanTarea();
          $nuevaTarea->nombre = $tareaOriginal->nombre;
          $nuevaTarea->descripcion = $tareaOriginal->descripcion;
          $nuevaTarea->estado = $tareaOriginal->estado;
          $nuevaTarea->fecha_inicio = $tareaOriginal->fecha_inicio;
          $nuevaTarea->fecha_vencimiento = $tareaOriginal->fecha_vencimiento;
          $nuevaTarea->orden = $tareaOriginal->orden;
          $nuevaTarea->id_kanban_columnas = $nuevaColumna->id;
          $nuevaTarea->checklist = $tareaOriginal->checklist;
          $nuevaTarea->links = $tareaOriginal->links;
          $nuevaTarea->save();

          // Copy task-user relationships
          if($nuevaTarea->id && $tareaOriginal->id) {
            $usuariosTarea = $tareaOriginal->getUsuarios();
            foreach($usuariosTarea as $id_usuario) {
              $nuevaTarea->addUsuario($id_usuario);
            }

            // Copy task-label relationships
            $etiquetasTarea = $tareaOriginal->getEtiquetas();
            foreach($etiquetasTarea as $id_etiqueta) {
              $nuevaTarea->addEtiqueta($id_etiqueta);
            }
          }
        }
      }

      // Copy tablero-user relationships
      $usuariosTablero = $tableroOriginal->getUsuarios();
      foreach($usuariosTablero as $relacion) {
        $nuevoTablero->addUsuario($relacion->id_usuario);
      }

      $response["status"] = "OK";
      $response["mensaje"] = "Tablero copiado correctamente";
      $response["tablero"] = array(
        "id" => $nuevoTablero->id,
        "nombre" => $nuevoTablero->nombre
      );
    }

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al copiar tablero: " . $e->getMessage();
    error_log("Error en ajax_copiarTablero.php: " . $e->getMessage());
  }

  print json_encode($response, JSON_PRETTY_PRINT);

?>
