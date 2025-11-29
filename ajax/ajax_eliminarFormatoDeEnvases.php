<?php

if($_POST == array()) {
  die();
}

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if(empty($id)) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'ID de formato no proporcionado'));
  exit;
}

$formato = new FormatoDeEnvases($id);

if(empty($formato->id)) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Formato no encontrado'));
  exit;
}

$nombre = $formato->nombre;

// Verificar que no este siendo usado en batches de envases
$batches_usando = BatchDeEnvases::getAll("WHERE id_formatos_de_envases='" . $id . "'");
if(count($batches_usando) > 0) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'No se puede eliminar: el formato esta siendo usado en ' . count($batches_usando) . ' batch(es) de envases'));
  exit;
}

// Verificar que no este siendo usado en productos
$productos_usando = Producto::getAll("WHERE id_formatos_de_envases='" . $id . "'");
if(count($productos_usando) > 0) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'No se puede eliminar: el formato esta asignado a ' . count($productos_usando) . ' producto(s)'));
  exit;
}

// Eliminar formato
$formato->delete();

$response = array();
$response['status'] = 'OK';
$response['mensaje'] = 'Formato eliminado exitosamente';
$response['id'] = $id;

echo json_encode($response);

?>
