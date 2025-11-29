<?php

if($_POST == array()) {
  die();
}

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

$id_batch_de_envases = isset($_POST['id_batch_de_envases']) ? intval($_POST['id_batch_de_envases']) : 0;

// Validar que existe el batch de envases
if($id_batch_de_envases <= 0) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'ID de batch de envases no proporcionado'));
  exit;
}

$batchDeEnvases = new BatchDeEnvases($id_batch_de_envases);
if(empty($batchDeEnvases->id)) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Batch de envases no encontrado'));
  exit;
}

// Determinar verbo segun tipo
$tipo = $batchDeEnvases->tipo;
$verbo = ($tipo == 'Botella') ? 'embotellado' : 'enlatado';

// Validar que todos los envases estan disponibles (no asignados a cajas)
$envases_en_cajas = Envase::getAll("WHERE id_batches_de_envases='" . $id_batch_de_envases . "' AND id_cajas_de_envases != 0");
if(count($envases_en_cajas) > 0) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'No se puede revertir: ' . count($envases_en_cajas) . ' envases ya estan asignados a cajas'));
  exit;
}

// Obtener datos del origen para restaurar
$volumen_a_devolver_ml = $batchDeEnvases->volumen_origen_ml;
$volumen_a_devolver_l = $volumen_a_devolver_ml / 1000;
$id_batches = $batchDeEnvases->id_batches;
$id_activos = $batchDeEnvases->id_activos;
$id_barriles = $batchDeEnvases->id_barriles;
$id_batches_activos = $batchDeEnvases->id_batches_activos;

// Determinar el tipo de origen
$origen_tipo = ($id_barriles > 0) ? 'barril' : 'fermentador';

// Eliminar todos los envases del batch
$envases = Envase::getAll("WHERE id_batches_de_envases='" . $id_batch_de_envases . "'");
$cantidad_envases = count($envases);
foreach($envases as $envase) {
  $envase->delete();
}

// Devolver el volumen al origen
if($origen_tipo == 'fermentador') {
  // Restaurar el fermentador (BatchActivo)
  if($id_batches_activos > 0) {
    $batchActivo = new BatchActivo($id_batches_activos);
    if(!empty($batchActivo->id)) {
      $batchActivo->litraje = $volumen_a_devolver_l;
      $batchActivo->save();
    }
  }

  // Restaurar el activo
  if($id_activos > 0) {
    $activo = new Activo($id_activos);
    if(!empty($activo->id)) {
      $activo->id_batches = $id_batches;
      $activo->save();
    }
  }

  $origen_nombre = "fermentador";
} else {
  // Restaurar el barril
  if($id_barriles > 0) {
    $barril = new Barril($id_barriles);
    if(!empty($barril->id)) {
      $barril->litros_cargados = $volumen_a_devolver_l;
      $barril->id_batches = $id_batches;
      $barril->id_activos = $id_activos;
      $barril->id_batches_activos = $id_batches_activos;
      $barril->save();
    }
  }

  $origen_nombre = "barril";
}

// Eliminar el batch de envases
$batchDeEnvases->delete();

// Registrar en historial
$batch = new Batch($id_batches);
$historial_msg = "Revertido " . $verbo . " de Batch #" . $batch->batch_nombre . ": " . $cantidad_envases . " envases eliminados y " . $volumen_a_devolver_l . "L devueltos al " . $origen_nombre . ".";
Historial::guardarAccion($historial_msg, $GLOBALS['usuario']);

$response = array();
$response['status'] = 'OK';
$response['mensaje'] = 'Reversion exitosa: ' . $cantidad_envases . ' envases eliminados y ' . $volumen_a_devolver_l . 'L devueltos al ' . $origen_nombre;
$response['tipo'] = $tipo;

echo json_encode($response);

?>
