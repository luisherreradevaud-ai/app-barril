<?php

require_once("./../php/app.php");

// Initialize AJAX security with comprehensive checks
$ajax = AjaxSecurity::init([
  'methods' => ['POST'],
  'csrf' => true,
  'auth' => true,
  'rate_limit' => true,
  'required_params' => ['entidad'],
  'input_rules' => [
    'entidad' => 'string',
    'id' => 'string'
  ]
]);

try {
  // Get validated input
  $entidad = $ajax->input('entidad');
  $id = $ajax->input('id', '');

  // Determine action (create or update)
  $accion = !empty($id) ? "modificado" : "creado";

  // Create object and set properties
  $obj = createObjFromTableName($entidad, $id);

  // Set properties from sanitized input (excluding id and csrf_token)
  $properties = $ajax->input();
  unset($properties['entidad']);
  unset($properties['csrf_token']);

  $obj->setPropertiesNoId($properties);
  $obj->save();

  // Handle entity-specific logic
  if (method_exists($obj, 'setSpecifics')) {
    $obj->setSpecifics($properties);
  }

  // Log action to history
  Historial::guardarAccion(
    get_class($obj) . " #" . $obj->id . " " . $accion . ".",
    $ajax->user()
  );

  // Send success response
  $ajax->success([
    'obj' => $obj,
    'id' => $obj->id,
    'accion' => $accion
  ], ucfirst($accion) . " exitosamente");

} catch (Exception $e) {
  error_log("Error en ajax_guardarEntidad.php: " . $e->getMessage());
  $ajax->error("Error al guardar: " . $e->getMessage(), 'save_error', 500);
}

?>
