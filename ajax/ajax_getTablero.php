<?php

  if($_GET == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession();

  if(!isset($_GET['id'])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "ID de tablero requerido";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  try {
    $id_tablero = $_GET['id'];
    $id_usuario_actual = $usuario->id;

    // Get tablero with columns and tasks
    $tablero = new KanbanTablero($id_tablero);

    if(!$tablero->id) {
      throw new Exception("Tablero no encontrado");
    }

    // Debug: Log información del tablero y usuario
    error_log("DEBUG ajax_getTablero.php - Tablero ID: " . $tablero->id);
    error_log("DEBUG ajax_getTablero.php - Creador: " . $tablero->id_usuario_creador);
    error_log("DEBUG ajax_getTablero.php - Usuario actual: " . $id_usuario_actual);

    // Verificar permisos: solo creador o usuarios asignados
    $tiene_acceso = $tablero->usuarioTieneAcceso($id_usuario_actual);
    error_log("DEBUG ajax_getTablero.php - Tiene acceso: " . ($tiene_acceso ? 'SI' : 'NO'));

    if(!$tiene_acceso) {
      throw new Exception("No tienes permisos para acceder a este tablero");
    }

    // Get all users
    $usuarios_arr = Usuario::getAll("WHERE nivel != 'Cliente' ORDER BY nombre ASC");
    $usuarios = array();
    foreach($usuarios_arr as $user) {
      $usuarios[] = array(
        'id' => $user->id,
        'nombre' => $user->nombre,
        'apellido' => $user->apellido,
        'email' => $user->email
      );
    }

    // Get all labels
    $etiquetas_arr = KanbanEtiqueta::getTodasLasEtiquetas();
    $etiquetas = array();
    foreach($etiquetas_arr as $etiqueta) {
      $etiquetas[] = array(
        'id' => $etiqueta->id,
        'nombre' => $etiqueta->nombre,
        'codigo_hex' => $etiqueta->codigo_hex
      );
    }

    // Get all tableros for the entity (for dropdown) - solo los que el usuario puede ver
    $allTableros_arr = KanbanTablero::getTablerosPorEntidad($tablero->id_entidad);
    $allTableros = array();
    foreach($allTableros_arr as $t) {
      // Solo incluir tableros a los que el usuario tiene acceso
      if($t->usuarioTieneAcceso($id_usuario_actual)) {
        $allTableros[] = array(
          'id' => $t->id,
          'nombre' => $t->nombre,
          'id_entidad' => $t->id_entidad
        );
      }
    }

    $response["status"] = "OK";
    $response["mensaje"] = "OK";
    $response["tablero"] = $tablero->toArray();
    $response["usuarios"] = $usuarios;
    $response["etiquetas"] = $etiquetas;
    $response["allTableros"] = $allTableros;

  } catch (Exception $e) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Error al obtener tablero: " . $e->getMessage();
    error_log("Error en ajax_getTablero.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
  }

  header('Content-Type: application/json');
  print json_encode($response, JSON_PRETTY_PRINT);

?>
