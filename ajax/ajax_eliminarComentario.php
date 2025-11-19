<?php

if($_POST == array()) {
  die();
}

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession();

if(!isset($_POST['id_comentario'])) {
  $response["status"] = "ERROR";
  $response["mensaje"] = "Falta parámetro: id_comentario es requerido.";
  print json_encode($response, JSON_PRETTY_PRINT);
  die();
}

try {
  $id_comentario = $_POST['id_comentario'];

  // Obtener comentario
  $comentario = new ConversacionInternaComentario();
  $datos_comentario = $comentario->getInfoDatabase('id', $id_comentario);

  if(!$datos_comentario) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "Comentario no encontrado.";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  $comentario->setProperties($datos_comentario);

  // Verificar que el usuario sea el autor o administrador
  if($comentario->id_autor != $GLOBALS['usuario']->id && $GLOBALS['usuario']->nivel != 'Administrador') {
    $response["status"] = "ERROR";
    $response["mensaje"] = "No tienes permisos para eliminar este comentario.";
    print json_encode($response, JSON_PRETTY_PRINT);
    die();
  }

  // Eliminar comentario completo (incluye archivos y tags)
  $comentario->eliminarCompleto();

  // Registrar en historial
  Historial::guardarAccion("Comentario #".$id_comentario." eliminado.", $GLOBALS['usuario']);

  $response["status"] = "OK";
  $response["mensaje"] = "Comentario eliminado exitosamente.";

} catch (Exception $e) {
  $response["status"] = "ERROR";
  $response["mensaje"] = "Error al eliminar comentario: " . $e->getMessage();
  error_log("Error en ajax_eliminarComentario.php: " . $e->getMessage());
}

print json_encode($response, JSON_PRETTY_PRINT);

?>
