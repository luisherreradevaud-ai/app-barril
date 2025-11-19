<?php

// Test más básico posible
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en output
ini_set('log_errors', 1);

// Capturar TODO el output
ob_start();

try {
    require_once("./../php/app.php");

    $usuario = new Usuario;
    session_start();
    $usuario->checkSession();

    // Limpiar cualquier output anterior
    $captured_output = ob_get_clean();
    ob_start();

    header('Content-Type: application/json');

    $response = array(
        'status' => 'OK',
        'message' => 'Test exitoso',
        'captured_output' => $captured_output,
        'captured_output_length' => strlen($captured_output),
        'usuario_id' => $GLOBALS['usuario']->id ?? 'null',
        'base_dir' => $GLOBALS['base_dir'] ?? 'null'
    );

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    $captured_output = ob_get_clean();
    ob_start();

    header('Content-Type: application/json');

    echo json_encode(array(
        'status' => 'ERROR',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'captured_output' => $captured_output,
        'trace' => explode("\n", $e->getTraceAsString())
    ), JSON_PRETTY_PRINT);
}

ob_end_flush();

?>
