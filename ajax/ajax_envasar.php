<?php

if($_POST == array()) {
  die();
}

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

// Recibir parametros
$origen_tipo = isset($_POST['origen_tipo']) ? $_POST['origen_tipo'] : 'fermentador';
$id_batches_activos = isset($_POST['id_batches_activos']) ? intval($_POST['id_batches_activos']) : 0;
$id_barriles = isset($_POST['id_barriles']) ? intval($_POST['id_barriles']) : 0;
$id_batches = isset($_POST['id_batches']) ? intval($_POST['id_batches']) : 0;
$id_activos = isset($_POST['id_activos']) ? intval($_POST['id_activos']) : 0;
$id_recetas = isset($_POST['id_recetas']) ? intval($_POST['id_recetas']) : 0;
$volumen_origen_ml = isset($_POST['volumen_origen_ml']) ? intval($_POST['volumen_origen_ml']) : 0;
$volumen_total_usado_ml = isset($_POST['volumen_total_usado_ml']) ? intval($_POST['volumen_total_usado_ml']) : 0;
$merma_total_ml = isset($_POST['merma_total_ml']) ? intval($_POST['merma_total_ml']) : 0;

// Recibir lineas de envasado (JSON)
$lineas_json = isset($_POST['lineas']) ? $_POST['lineas'] : '';
$lineas = json_decode($lineas_json, true);

if(!is_array($lineas) || empty($lineas)) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Debe agregar al menos una linea de envasado'));
  exit;
}

// Validar origen segun tipo
if($origen_tipo == 'fermentador') {
  if($id_batches_activos <= 0) {
    echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Debe seleccionar un fermentador'));
    exit;
  }
  $batchActivo = new BatchActivo($id_batches_activos);
  if(empty($batchActivo->id)) {
    echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Fermentador no encontrado'));
    exit;
  }
  // Usar el id_activos del batch activo
  $id_activos = $batchActivo->id_activos;
  $id_batches = $batchActivo->id_batches;

  // Validar que el BatchActivo esté en estado válido para envasado
  // Permitido: Maduración o Fermentadores INOX (cualquier estado)
  $activo = new Activo($id_activos);
  $es_inox = (stripos($activo->nombre, 'inox') !== false || stripos($activo->marca, 'inox') !== false);
  $es_maduracion = ($batchActivo->estado == 'Maduración');

  if(!$es_maduracion && !$es_inox) {
    echo json_encode(array(
      'status' => 'ERROR',
      'mensaje' => 'Solo se puede envasar desde fermentadores en Maduración o fermentadores INOX'
    ));
    exit;
  }
} else {
  if($id_barriles <= 0) {
    echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Debe seleccionar un barril'));
    exit;
  }
  $barril = new Barril($id_barriles);
  if(empty($barril->id)) {
    echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Barril no encontrado'));
    exit;
  }
}

// Calcular volumen total a usar desde las lineas
$volumen_total_a_usar_ml = 0;
foreach($lineas as $linea) {
  $cantidad = isset($linea['cantidad']) ? intval($linea['cantidad']) : 0;
  $volumen_ml = isset($linea['volumen_ml']) ? intval($linea['volumen_ml']) : 0;
  $volumen_total_a_usar_ml += $cantidad * $volumen_ml;
}

// Obtener volumen disponible real del origen
if($origen_tipo == 'fermentador') {
  $volumen_disponible_ml = floatval($batchActivo->litraje) * 1000;
} else {
  $volumen_disponible_ml = floatval($barril->litros_cargados) * 1000;
}

// Validar que no exceda el disponible
if($volumen_total_a_usar_ml > $volumen_disponible_ml) {
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => 'El volumen a envasar (' . number_format($volumen_total_a_usar_ml/1000, 2) . 'L) excede el disponible (' . number_format($volumen_disponible_ml/1000, 2) . 'L)'
  ));
  exit;
}

// Validar cada linea
$tipos_validos = array('Lata', 'Botella');
foreach($lineas as $index => $linea) {
  $tipo = isset($linea['tipo']) ? $linea['tipo'] : '';
  $id_formato = isset($linea['id_formatos_de_envases']) ? intval($linea['id_formatos_de_envases']) : 0;
  $cantidad = isset($linea['cantidad']) ? intval($linea['cantidad']) : 0;

  if(!in_array($tipo, $tipos_validos)) {
    echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Linea ' . ($index + 1) . ': Tipo de envase no valido'));
    exit;
  }

  if($cantidad <= 0) {
    echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Linea ' . ($index + 1) . ': La cantidad debe ser mayor a 0'));
    exit;
  }

  $formato = new FormatoDeEnvases($id_formato);
  if(empty($formato->id)) {
    echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Linea ' . ($index + 1) . ': Formato de envase no encontrado'));
    exit;
  }

  if($formato->tipo != $tipo) {
    echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Linea ' . ($index + 1) . ': El formato no corresponde al tipo de envase'));
    exit;
  }
}

// Procesar cada linea de envasado
$batches_creados = array();
$total_latas = 0;
$total_botellas = 0;

foreach($lineas as $linea) {
  $tipo = $linea['tipo'];
  $id_formato = intval($linea['id_formatos_de_envases']);
  $cantidad = intval($linea['cantidad']);
  $volumen_ml = intval($linea['volumen_ml']);
  $rendimiento_ml = $cantidad * $volumen_ml;

  $formato = new FormatoDeEnvases($id_formato);

  // Crear BatchDeEnvases para esta linea
  $batchDeEnvases = new BatchDeEnvases();
  $batchDeEnvases->tipo = $tipo;
  $batchDeEnvases->id_batches = $id_batches;
  $batchDeEnvases->id_activos = $id_activos;
  $batchDeEnvases->id_barriles = $id_barriles;
  $batchDeEnvases->id_batches_activos = $id_batches_activos;
  $batchDeEnvases->id_formatos_de_envases = $id_formato;
  $batchDeEnvases->id_recetas = $id_recetas;
  $batchDeEnvases->cantidad_de_envases = $cantidad;
  $batchDeEnvases->volumen_origen_ml = $volumen_origen_ml;
  $batchDeEnvases->rendimiento_ml = $rendimiento_ml;
  $batchDeEnvases->merma_ml = 0; // La merma se calcula al final
  $batchDeEnvases->id_usuarios = $GLOBALS['usuario']->id;
  $batchDeEnvases->estado = "Cargado en planta";
  $batchDeEnvases->save();

  if(empty($batchDeEnvases->id)) {
    echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Error al crear el batch de envases para linea tipo ' . $tipo));
    exit;
  }

  // Crear los envases individuales
  for($i = 0; $i < $cantidad; $i++) {
    $envase = new Envase();
    $envase->id_formatos_de_envases = $id_formato;
    $envase->volumen_ml = $formato->volumen_ml;
    $envase->id_batches_de_envases = $batchDeEnvases->id;
    $envase->id_batches = $id_batches;
    $envase->id_barriles = $id_barriles;
    $envase->id_activos = $id_activos;
    $envase->id_cajas_de_envases = 0;
    $envase->estado = "Envasado";
    $envase->save();
  }

  $batches_creados[] = array(
    'id' => $batchDeEnvases->id,
    'tipo' => $tipo,
    'cantidad' => $cantidad
  );

  if($tipo == 'Lata') {
    $total_latas += $cantidad;
  } else {
    $total_botellas += $cantidad;
  }
}

// Vaciar el origen
if($origen_tipo == 'fermentador') {
  // Vaciar el fermentador (BatchActivo)
  $batchActivo->litraje = 0;
  $batchActivo->save();

  // Tambien actualizar el activo
  $activo = new Activo($id_activos);
  $activo->id_batches = 0;
  $activo->save();
} else {
  // Vaciar el barril
  $barril->litros_cargados = 0;
  $barril->id_batches = 0;
  $barril->id_activos = 0;
  $barril->id_batches_activos = 0;
  $barril->save();
}

// Construir mensaje de respuesta
$mensaje_partes = array();
if($total_latas > 0) {
  $mensaje_partes[] = $total_latas . ' latas';
}
if($total_botellas > 0) {
  $mensaje_partes[] = $total_botellas . ' botellas';
}
$mensaje = 'Envasado exitoso: ' . implode(' y ', $mensaje_partes);

// Registrar en historial
$batch = new Batch($id_batches);
$receta = new Receta($batch->id_recetas);
if($origen_tipo == 'fermentador') {
  $activo = new Activo($id_activos);
  $origen_desc = "fermentador " . $activo->codigo;
} else {
  $origen_desc = "barril " . $barril->codigo;
}
$historial_msg = "Envasado desde " . $origen_desc . " para Batch #" . $batch->batch_nombre . " (" . $receta->nombre . "): " . implode(' y ', $mensaje_partes) . ".";
Historial::guardarAccion($historial_msg, $GLOBALS['usuario']);

$response = array();
$response['status'] = 'OK';
$response['mensaje'] = $mensaje;
$response['batches_creados'] = $batches_creados;
$response['total_latas'] = $total_latas;
$response['total_botellas'] = $total_botellas;

echo json_encode($response);

?>
