<?php

// Debug endpoint para diagnosticar problemas
ob_start();

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession();

ob_clean();
header('Content-Type: application/json');

$debug = array();

// Test 1: Verificar clases
$debug['classes_exist'] = array(
  'ConversacionInterna' => class_exists('ConversacionInterna'),
  'ConversacionInternaComentario' => class_exists('ConversacionInternaComentario'),
  'ConversacionInternaArchivo' => class_exists('ConversacionInternaArchivo'),
  'ConversacionInternaTag' => class_exists('ConversacionInternaTag')
);

// Test 2: Verificar tablas
try {
  $mysqli = $GLOBALS['mysqli'];

  $tables = array();
  $result = $mysqli->query("SHOW TABLES LIKE 'conversaciones_internas%'");
  while($row = $result->fetch_array()) {
    $tables[] = $row[0];
  }
  $debug['tables_exist'] = $tables;
} catch (Exception $e) {
  $debug['tables_error'] = $e->getMessage();
}

// Test 3: Usuario actual
$debug['usuario'] = array(
  'id' => $GLOBALS['usuario']->id,
  'nombre' => $GLOBALS['usuario']->nombre,
  'nivel' => $GLOBALS['usuario']->nivel
);

// Test 4: Intentar crear conversación
try {
  $conv = ConversacionInterna::obtenerOCrearPorVistaEntidad('test', 'test-123', $GLOBALS['usuario']->id);
  $debug['conversacion_test'] = array(
    'success' => true,
    'id' => $conv->id,
    'nombre_vista' => $conv->nombre_vista,
    'id_entidad' => $conv->id_entidad
  );

  // Obtener datos completos
  $data = $conv->obtenerCompleta();
  $debug['conversacion_completa'] = array(
    'comentarios_count' => count($data['comentarios']),
    'conversacion_id' => $data['conversacion']['id']
  );

} catch (Exception $e) {
  $debug['conversacion_test'] = array(
    'success' => false,
    'error' => $e->getMessage(),
    'trace' => explode("\n", $e->getTraceAsString())
  );
}

echo json_encode($debug, JSON_PRETTY_PRINT);
exit;

?>
