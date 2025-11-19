<?php

require_once("./../php/app.php");

// Initialize AJAX security - GET request, no CSRF required
$ajax = AjaxSecurity::init([
  'methods' => ['GET'],
  'csrf' => false, // GET requests don't need CSRF
  'auth' => true,
  'rate_limit' => true,
  'required_params' => ['nombre_vista', 'id_entidad'],
  'input_rules' => [
    'nombre_vista' => 'string',
    'id_entidad' => 'string'
  ]
]);

try {
  // Get validated input
  $nombre_vista = $ajax->input('nombre_vista');
  $id_entidad = $ajax->input('id_entidad');

  // Obtain or create conversation
  $conversacion = ConversacionInterna::obtenerOCrearPorVistaEntidad(
    $nombre_vista,
    $id_entidad,
    $ajax->user()->id
  );

  if (!$conversacion) {
    $ajax->error("No se pudo crear u obtener la conversación", 'conversation_error', 500);
  }

  // Get complete conversation data
  $data = $conversacion->obtenerCompleta();

  // Send success response
  $ajax->success($data, "Conversación obtenida exitosamente");

} catch (Exception $e) {
  error_log("Error en ajax_getConversacion.php: " . $e->getMessage());
  $ajax->error("Error al obtener conversación: " . $e->getMessage(), 'fetch_error', 500);
}

?>
