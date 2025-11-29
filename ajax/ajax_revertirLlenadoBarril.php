<?php

if($_POST == array()) {
    die();
}

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

$id_barril = isset($_POST['id_barril']) ? intval($_POST['id_barril']) : 0;

if($id_barril <= 0) {
    $response = array(
        "status" => "ERROR",
        "mensaje" => "ID de barril no proporcionado"
    );
    print json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

$barril = new Barril($id_barril);

if(empty($barril->id)) {
    $response = array(
        "status" => "ERROR",
        "mensaje" => "Barril no encontrado"
    );
    print json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

// Validar que el barril tenga carga
if($barril->litros_cargados <= 0) {
    $response = array(
        "status" => "ERROR",
        "mensaje" => "El barril no tiene carga para revertir"
    );
    print json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

// Validar que el barril esté en planta
if($barril->estado != 'En planta') {
    $response = array(
        "status" => "ERROR",
        "mensaje" => "Solo se puede revertir el llenado de barriles que están en planta. Estado actual: " . $barril->estado
    );
    print json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

// Guardar datos para el historial antes de modificar
$litros_a_devolver = $barril->litros_cargados;
$id_batches_activos = $barril->id_batches_activos;
$id_batches = $barril->id_batches;

// Buscar el BatchActivo para devolver los litros
$batch_activo = null;
if($id_batches_activos > 0) {
    $batch_activo = new BatchActivo($id_batches_activos);
}

// Devolver litros al BatchActivo si existe
if($batch_activo && !empty($batch_activo->id)) {
    $batch_activo->litraje += $litros_a_devolver;
    $batch_activo->save();
}

// Vaciar el barril
$barril->litros_cargados = 0;
$barril->id_batches = 0;
$barril->id_activos = 0;
$barril->id_batches_activos = 0;
$barril->save();

// Registrar en historial
$batch = new Batch($id_batches);
$historial_msg = "Llenado de barril " . $barril->codigo . " revertido: " . $litros_a_devolver . "L devueltos";
if($batch_activo && !empty($batch_activo->id)) {
    $activo = new Activo($batch_activo->id_activos);
    $historial_msg .= " al fermentador " . $activo->codigo;
}
$historial_msg .= " (Batch #" . $batch->batch_nombre . ").";
Historial::guardarAccion($historial_msg, $GLOBALS['usuario']);

$response = array(
    "status" => "OK",
    "mensaje" => "Llenado revertido. " . $litros_a_devolver . "L devueltos al fermentador."
);

print json_encode($response, JSON_PRETTY_PRINT);

?>
