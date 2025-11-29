<?php

if($_POST == array()) {
    die();
}

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

$id_traspaso = isset($_POST['id_traspaso']) ? intval($_POST['id_traspaso']) : 0;

if($id_traspaso <= 0) {
    $response = array(
        "status" => "ERROR",
        "mensaje" => "ID de traspaso no proporcionado"
    );
    print json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

$traspaso = new BatchTraspaso($id_traspaso);

if(empty($traspaso->id)) {
    $response = array(
        "status" => "ERROR",
        "mensaje" => "Traspaso no encontrado"
    );
    print json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

// Obtener los activos involucrados
$activo_origen = new Activo($traspaso->id_fermentadores_inicio);
$activo_destino = new Activo($traspaso->id_fermentadores_final);

// Verificar que el activo origen esté disponible (id_batches=0)
if($activo_origen->id_batches != 0) {
    $response = array(
        "status" => "ERROR",
        "mensaje" => "El fermentador origen (" . $activo_origen->codigo . ") ya está ocupado con otro batch"
    );
    print json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

// Buscar el BatchActivo actual en el fermentador destino
$batch_activos_destino = BatchActivo::getAll("WHERE id_batches='" . $traspaso->id_batches . "' AND id_activos='" . $activo_destino->id . "' AND litraje > 0");

if(count($batch_activos_destino) == 0) {
    $response = array(
        "status" => "ERROR",
        "mensaje" => "No se encontró cerveza en el fermentador destino. Es posible que ya se haya procesado."
    );
    print json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

$batch_activo_destino = $batch_activos_destino[0];

// Buscar el BatchActivo origen (con litraje=0)
$batch_activos_origen = BatchActivo::getAll("WHERE id_batches='" . $traspaso->id_batches . "' AND id_activos='" . $activo_origen->id . "' AND litraje=0");

if(count($batch_activos_origen) == 0) {
    $response = array(
        "status" => "ERROR",
        "mensaje" => "No se encontró el registro del fermentador origen"
    );
    print json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

$batch_activo_origen = $batch_activos_origen[0];

// Calcular litraje a devolver (litraje actual + merma que se perdió)
$litraje_a_devolver = $batch_activo_destino->litraje + $traspaso->merma_litros;

// Revertir: devolver litraje al origen
$batch_activo_origen->litraje = $litraje_a_devolver;
$batch_activo_origen->estado = 'Fermentación';
$batch_activo_origen->save();

// Vaciar el destino
$batch_activo_destino->litraje = 0;
$batch_activo_destino->save();

// Actualizar activos
$activo_origen->id_batches = $traspaso->id_batches;
$activo_origen->save();

$activo_destino->id_batches = 0;
$activo_destino->save();

// Eliminar el registro de traspaso
$traspaso->delete();

// Registrar en historial
$batch = new Batch($traspaso->id_batches);
$historial_msg = "Traspaso #" . $id_traspaso . " revertido: cerveza devuelta de " . $activo_destino->codigo . " a " . $activo_origen->codigo . " para Batch #" . $batch->batch_nombre . ".";
if($traspaso->merma_litros > 0) {
    $historial_msg .= " (Merma de " . $traspaso->merma_litros . "L recuperada)";
}
Historial::guardarAccion($historial_msg, $GLOBALS['usuario']);

$response = array(
    "status" => "OK",
    "mensaje" => "Traspaso revertido exitosamente. " . $litraje_a_devolver . "L devueltos a " . $activo_origen->codigo
);

print json_encode($response, JSON_PRETTY_PRINT);

?>
