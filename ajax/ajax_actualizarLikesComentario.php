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
  $accion = isset($_POST['accion']) ? $_POST['accion'] : 'toggle'; // toggle, add, remove

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

  // Obtener likes actuales
  $likes_actuales = json_decode($comentario->likes, true);
  if($likes_actuales === null) $likes_actuales = array();
  $id_usuario = $GLOBALS['usuario']->id;

  // Debug
  $response["debug_antes"] = array(
    'likes_raw' => $comentario->likes,
    'likes_array' => $likes_actuales,
    'id_usuario' => $id_usuario,
    'tiene_like' => in_array($id_usuario, $likes_actuales)
  );

  // Aplicar acción
  if($accion == 'toggle') {
    if(in_array($id_usuario, $likes_actuales)) {
      $likes_actuales = $comentario->quitarLike($id_usuario);
      $response["debug_accion"] = "quitarLike";
    } else {
      $likes_actuales = $comentario->agregarLike($id_usuario);
      $response["debug_accion"] = "agregarLike";
    }
  } else if($accion == 'add') {
    $likes_actuales = $comentario->agregarLike($id_usuario);
    $response["debug_accion"] = "agregarLike (forzado)";
  } else if($accion == 'remove') {
    $likes_actuales = $comentario->quitarLike($id_usuario);
    $response["debug_accion"] = "quitarLike (forzado)";
  }

  // Recargar comentario para verificar
  $comentario_verificado = new ConversacionInternaComentario($id_comentario);
  $likes_en_db = json_decode($comentario_verificado->likes, true);

  $response["status"] = "OK";
  $response["mensaje"] = "Likes actualizados.";
  $response["likes"] = $likes_actuales;
  $response["total_likes"] = count($likes_actuales);
  $response["likes_en_db"] = $likes_en_db;
  $response["debug_comentario"] = array(
    'id' => $comentario_verificado->id,
    'likes_raw' => $comentario_verificado->likes
  );

} catch (Exception $e) {
  $response["status"] = "ERROR";
  $response["mensaje"] = "Error al actualizar likes: " . $e->getMessage();
  error_log("Error en ajax_actualizarLikesComentario.php: " . $e->getMessage());
}

print json_encode($response, JSON_PRETTY_PRINT);

?>
