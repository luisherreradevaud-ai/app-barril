<?php

require_once("./../php/app.php");

// Initialize AJAX security - delete requires higher minimum level
$ajax = AjaxSecurity::init([
  'methods' => ['POST'],
  'csrf' => true,
  'auth' => true,
  'min_level' => 'Operario', // Minimum level required to delete
  'rate_limit' => true,
  'required_params' => ['modo', 'id'],
  'input_rules' => [
    'modo' => 'string',
    'id' => 'string'
  ]
]);

try {
  // Get validated input
  $modo = $ajax->input('modo');
  $id = $ajax->input('id');

  // Create object
  $obj = createObjFromTableName($modo, $id);

  if (!$obj || !$obj->id) {
    $ajax->error("Registro no encontrado", 'not_found', 404);
  }

  // Handle entity-specific deletion logic
  if (method_exists($obj, 'deleteSpecifics')) {
    $obj->deleteSpecifics($ajax->input());
  }

  // Perform soft delete
  $obj->delete();

  // Log action to history
  Historial::guardarAccion(
    get_class($obj) . " #" . $obj->id . " eliminado.",
    $ajax->user()
  );

  // Send success response
  $ajax->success([
    'id' => $id,
    'table_name' => $modo
  ], "Eliminado exitosamente");

} catch (Exception $e) {
  error_log("Error en ajax_eliminarEntidad.php: " . $e->getMessage());
  $ajax->error("Error al eliminar: " . $e->getMessage(), 'delete_error', 500);
}

?>
