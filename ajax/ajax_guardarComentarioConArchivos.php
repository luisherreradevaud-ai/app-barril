<?php

if($_POST == array()) {
  die();
}

require_once("./../php/app.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession();

if(!isset($_POST['id_conversacion'])) {
  $response["status"] = "ERROR";
  $response["mensaje"] = "Falta parámetro: id_conversacion es requerido.";
  print json_encode($response, JSON_PRETTY_PRINT);
  die();
}

if(!isset($_POST['contenido']) || trim($_POST['contenido']) == '') {
  $response["status"] = "ERROR";
  $response["mensaje"] = "El contenido del comentario es requerido.";
  print json_encode($response, JSON_PRETTY_PRINT);
  die();
}

try {
  $id_conversacion = $_POST['id_conversacion'];
  $contenido = $_POST['contenido'];
  $tags = isset($_POST['tags']) ? json_decode($_POST['tags'], true) : array();
  $estado = isset($_POST['estado']) ? $_POST['estado'] : 'activo';

  // Crear comentario
  $comentario = new ConversacionInternaComentario();
  $comentario->id_conversacion_interna = $id_conversacion;
  $comentario->contenido = $contenido;
  $comentario->id_autor = $GLOBALS['usuario']->id;
  $comentario->estado = $estado;
  $comentario->likes = json_encode(array());
  $comentario->guardar($GLOBALS['usuario']->id);

  // Procesar archivos si existen
  $archivos_guardados = array();

  if(isset($_FILES) && !empty($_FILES)) {
    $archivos_para_guardar = array();

    foreach($_FILES as $key => $file) {
      if($file['error'] == UPLOAD_ERR_OK) {
        $meta = array(
          'name' => $file['name'],
          'description' => '',
          'status' => 'activo'
        );

        // Buscar metadata asociada si existe
        if(isset($_POST[$key.'_meta'])) {
          $meta_data = json_decode($_POST[$key.'_meta'], true);
          if($meta_data) {
            $meta = array_merge($meta, $meta_data);
          }
        }

        $archivos_para_guardar[] = array(
          'file' => $file,
          'meta' => $meta,
          'id_usuario' => $GLOBALS['usuario']->id
        );
      }
    }

    if(!empty($archivos_para_guardar)) {
      $archivos_guardados = $comentario->agregarArchivos($archivos_para_guardar);
    }
  }

  // Agregar tags/menciones si existen
  if(!empty($tags) && is_array($tags)) {
    $comentario->agregarTags($tags, $GLOBALS['usuario']->id);
  }

  // Registrar en historial
  Historial::guardarAccion("Comentario agregado en conversación #".$id_conversacion, $GLOBALS['usuario']);

  $response["status"] = "OK";
  $response["mensaje"] = "Comentario guardado exitosamente.";
  $response["comentario_id"] = $comentario->id;
  $response["archivos_count"] = count($archivos_guardados);

} catch (Exception $e) {
  $response["status"] = "ERROR";
  $response["mensaje"] = "Error al guardar comentario: " . $e->getMessage();
  error_log("Error en ajax_guardarComentarioConArchivos.php: " . $e->getMessage());
}

print json_encode($response, JSON_PRETTY_PRINT);

?>
