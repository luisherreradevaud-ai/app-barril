<?php

if($_POST == array()) {
  die();
}

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

$id_caja = isset($_POST['id_caja']) ? intval($_POST['id_caja']) : 0;

if($id_caja <= 0) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'ID de caja no valido'));
  exit;
}

// Obtener la caja
$caja = new CajaDeEnvases($id_caja);
if(empty($caja->id)) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Caja no encontrada'));
  exit;
}

// Verificar que la caja este en planta (no despachada)
if($caja->estado != 'En planta') {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Solo se pueden eliminar cajas que estan en planta. Esta caja tiene estado: ' . $caja->estado));
  exit;
}

// Contar envases que se liberaran
$envases = $caja->getEnvases();
$cantidad_envases = count($envases);

// Guardar codigo antes de eliminar para el historial
$codigo_caja = $caja->codigo;

// Liberar los envases (devolver al estado disponible)
$caja->liberarEnvases();

// Eliminar la caja (soft delete)
$caja->estado = 'eliminado';
$caja->save();

// Registrar en historial
$historial_msg = "Caja de envases " . $codigo_caja . " eliminada. " . $cantidad_envases . " envases liberados.";
Historial::guardarAccion($historial_msg, $GLOBALS['usuario']);

$response = array(
  'status' => 'OK',
  'mensaje' => 'Caja eliminada exitosamente',
  'envases_liberados' => $cantidad_envases
);

echo json_encode($response);

?>
