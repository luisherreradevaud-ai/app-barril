<?php

if($_POST == array()) {
    die();
}

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

$id = isset($_POST['id']) ? $_POST['id'] : 0;
$estado = isset($_POST['estado']) ? $_POST['estado'] : '';

if(empty($id)) {
    $response = array(
        "status" => "ERROR",
        "mensaje" => "ID de despacho no proporcionado"
    );
    print json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

if(empty($estado)) {
    $response = array(
        "status" => "ERROR",
        "mensaje" => "Estado no proporcionado"
    );
    print json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

$despacho = new Despacho($id);

if(empty($despacho->id)) {
    $response = array(
        "status" => "ERROR",
        "mensaje" => "Despacho no encontrado"
    );
    print json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

$estado_anterior = $despacho->estado;
$despacho->estado = $estado;
$despacho->save();

// Si se marca como entregado, actualizar estado de barriles
if($estado == 'Entregado') {
    $productos = DespachoProducto::getAll("WHERE id_despachos='".$despacho->id."'");
    foreach($productos as $producto) {
        if($producto->tipo == 'Barril' && $producto->id_barriles > 0) {
            $barril = new Barril($producto->id_barriles);
            $barril->estado = "En terreno";
            $barril->id_clientes = $despacho->id_clientes;
            $barril->registrarCambioDeEstado();
            $barril->save();
        }
    }
}

// Registrar en historial
$historial_msg = "Despacho #" . $despacho->id . " actualizado: " . $estado_anterior . " -> " . $estado;
Historial::guardarAccion($historial_msg, $GLOBALS['usuario']);

$response = array(
    "status" => "OK",
    "mensaje" => "Despacho actualizado correctamente"
);

print json_encode($response, JSON_PRETTY_PRINT);

?>
