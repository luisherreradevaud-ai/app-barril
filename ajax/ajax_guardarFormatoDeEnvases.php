<?php

if($_POST == array()) {
  die();
}

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

$id = isset($_POST['id']) ? $_POST['id'] : '';
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 'Lata';
$volumen_ml = isset($_POST['volumen_ml']) ? intval($_POST['volumen_ml']) : 0;
$estado = isset($_POST['estado']) ? $_POST['estado'] : 'activo';

// Validaciones
if(empty($nombre)) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Nombre requerido'));
  exit;
}

if($volumen_ml <= 0) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'El volumen debe ser mayor a 0'));
  exit;
}

// Validar tipo
$tipos_validos = array('Lata', 'Botella');
if(!in_array($tipo, $tipos_validos)) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Tipo de envase no valido'));
  exit;
}

// Crear o actualizar
if(!empty($id)) {
  $formato = new FormatoDeEnvases($id);
  $accion = "modificado";
} else {
  $formato = new FormatoDeEnvases();
  $accion = "creado";
}

$formato->nombre = $nombre;
$formato->tipo = $tipo;
$formato->volumen_ml = $volumen_ml;
$formato->estado = $estado;
$formato->save();

// Verificar que se guardo correctamente
if(empty($formato->id)) {
  echo json_encode(array('status' => 'ERROR', 'mensaje' => 'Error al guardar el formato en la base de datos'));
  exit;
}

$response = array();
$response['status'] = 'OK';
$response['mensaje'] = 'Formato ' . $accion . ' exitosamente';
$response['id'] = $formato->id;

echo json_encode($response);

?>
