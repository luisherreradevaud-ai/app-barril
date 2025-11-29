<?php

if($_POST == array()) {
  die();
}

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

$id_productos = isset($_POST['id_productos']) ? intval($_POST['id_productos']) : 0;
$asignaciones_json = isset($_POST['asignaciones']) ? $_POST['asignaciones'] : '';

// Decodificar asignaciones
$asignaciones = json_decode($asignaciones_json, true);
if(!is_array($asignaciones) || empty($asignaciones)) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Debe asignar envases de al menos un batch'));
  exit;
}

// Obtener producto
$producto = new Producto($id_productos);
if(empty($producto->id)) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Producto no encontrado'));
  exit;
}

// Validar que el producto tenga configuracion de envases
if($producto->id_formatos_de_envases <= 0 || $producto->cantidad_de_envases <= 0) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'El producto no esta configurado para envases'));
  exit;
}

// Calcular total de envases asignados
$total_asignados = 0;
foreach($asignaciones as $id_batch => $cantidad) {
  $total_asignados += intval($cantidad);
}

// Validar que el total coincida con la cantidad requerida
if($total_asignados != $producto->cantidad_de_envases) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'La cantidad de envases asignados (' . $total_asignados . ') no coincide con la cantidad requerida (' . $producto->cantidad_de_envases . ')'));
  exit;
}

// Validar que cada batch tenga suficientes envases disponibles
foreach($asignaciones as $id_batch => $cantidad) {
  if($cantidad <= 0) continue;

  $batch = new BatchDeEnvases($id_batch);
  if(empty($batch->id)) {
    echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Batch de envases #' . $id_batch . ' no encontrado'));
    exit;
  }

  // Verificar formato
  if($batch->id_formatos_de_envases != $producto->id_formatos_de_envases) {
    echo json_encode(array('status' => 'ERROR', 'mensaje' => 'El batch #' . $id_batch . ' tiene un formato de envase diferente al del producto'));
    exit;
  }

  // Verificar tipo de envase
  if($batch->tipo != $producto->tipo_envase) {
    echo json_encode(array('status' => 'ERROR', 'mensaje' => 'El batch #' . $id_batch . ' es de tipo ' . $batch->tipo . ' pero el producto es de tipo ' . $producto->tipo_envase));
    exit;
  }

  // Verificar disponibilidad
  $disponibles = Envase::contarDisponiblesPorBatch($id_batch);
  if($disponibles < $cantidad) {
    echo json_encode(array('status' => 'ERROR', 'mensaje' => 'El batch #' . $id_batch . ' solo tiene ' . $disponibles . ' envases disponibles, pero se intentan usar ' . $cantidad));
    exit;
  }
}

// Crear la caja
$caja = new CajaDeEnvases();
$caja->generarCodigo();
$caja->id_productos = $id_productos;
$caja->cantidad_envases = $total_asignados;
$caja->id_usuarios = $GLOBALS['usuario']->id;
$caja->estado = "En planta";

// Debug: mostrar campos de la tabla
$caja->tableFields('cajas_de_envases');
$campos_tabla = array();
foreach($caja->table_fields as $field) {
  $campos_tabla[] = $field['name'];
}

$caja->save();

// Verificar que se guardo correctamente
if(empty($caja->id)) {
  // Obtener error de MySQL
  $mysqli = $GLOBALS['mysqli'];
  $mysql_error = $mysqli->error;
  echo json_encode(array(
    'status' => 'ERROR',
    'mensaje' => 'Error al crear la caja en la base de datos',
    'mysql_error' => $mysql_error,
    'campos_tabla' => $campos_tabla,
    'datos_caja' => array(
      'codigo' => $caja->codigo,
      'id_productos' => $caja->id_productos,
      'cantidad_envases' => $caja->cantidad_envases,
      'id_usuarios' => $caja->id_usuarios,
      'estado' => $caja->estado
    )
  ));
  exit;
}

// Asignar envases a la caja
$caja->asignarEnvases($asignaciones);

// Registrar en historial
$historial_msg = "Caja de envases " . $caja->codigo . " creada con " . $total_asignados . " envases para producto " . $producto->nombre . ".";
Historial::guardarAccion($historial_msg, $GLOBALS['usuario']);

$response = array();
$response['status'] = 'OK';
$response['mensaje'] = 'Caja ' . $caja->codigo . ' creada exitosamente';
$response['codigo'] = $caja->codigo;
$response['cantidad_envases'] = $total_asignados;

echo json_encode($response);

?>
