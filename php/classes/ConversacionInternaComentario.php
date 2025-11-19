<?php

class ConversacionInternaComentario extends Base {

  public $id_conversacion_interna = "";
  public $contenido = "";
  public $id_autor = "";
  public $estado = "activo";
  public $likes = "";
  public $fecha_creacion;
  public $fecha_actualizacion;

  public function __construct($id = null) {
    $this->tableName("conversaciones_internas_comentarios");
    if($id) {
      $this->id = intval($id);
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    } else {
      $this->fecha_creacion = date('Y-m-d H:i:s');
      $this->fecha_actualizacion = date('Y-m-d H:i:s');
      $this->likes = json_encode(array());
    }
  }

  public function guardar($id_usuario_actual = null) {
    $this->save();

    // Actualizar fecha de modificación de la conversación
    if($this->id_conversacion_interna) {
      $conversacion = new ConversacionInterna($this->id_conversacion_interna);
      $conversacion->actualizarFechaModificacion($id_usuario_actual);
    }

    return $this;
  }

  public function agregarArchivos($archivos) {
    if(!$this->id || !is_array($archivos)) {
      return false;
    }

    $archivos_guardados = array();

    foreach($archivos as $archivo_data) {
      if(!isset($archivo_data['file']) || !isset($archivo_data['meta'])) {
        continue;
      }

      $archivo = new ConversacionInternaArchivo();
      $archivo->id_comentario = $this->id;
      $archivo->nombre = isset($archivo_data['meta']['name']) ? $archivo_data['meta']['name'] : $archivo_data['file']['name'];
      $archivo->descripcion = isset($archivo_data['meta']['description']) ? $archivo_data['meta']['description'] : '';
      $archivo->estado = isset($archivo_data['meta']['status']) ? $archivo_data['meta']['status'] : 'activo';
      $archivo->id_subido_por = isset($archivo_data['id_usuario']) ? $archivo_data['id_usuario'] : '';

      // Guardar archivo físico
      $ruta_guardada = $this->guardarArchivoFisico($archivo_data['file']);

      if($ruta_guardada) {
        $archivo->ruta_archivo = $ruta_guardada;

        // Guardar metadata
        $metadata = array(
          'mimetype' => isset($archivo_data['file']['type']) ? $archivo_data['file']['type'] : '',
          'size' => isset($archivo_data['file']['size']) ? $archivo_data['file']['size'] : 0,
          'original_name' => isset($archivo_data['file']['name']) ? $archivo_data['file']['name'] : ''
        );

        if(isset($archivo_data['meta']['metadata']) && is_array($archivo_data['meta']['metadata'])) {
          $metadata = array_merge($metadata, $archivo_data['meta']['metadata']);
        }

        $archivo->metadata = json_encode($metadata);

        $archivo->save();
        $archivos_guardados[] = $archivo;
      }
    }

    return $archivos_guardados;
  }

  private function guardarArchivoFisico($file_info) {
    $upload_dir = $_SERVER['DOCUMENT_ROOT'].'/media/conversaciones/';

    if(!file_exists($upload_dir)) {
      mkdir($upload_dir, 0755, true);
    }

    $extension = pathinfo($file_info['name'], PATHINFO_EXTENSION);
    $nombre_archivo = uniqid('conv_').time().'.'.$extension;
    $ruta_completa = $upload_dir.$nombre_archivo;

    if(isset($file_info['tmp_name']) && move_uploaded_file($file_info['tmp_name'], $ruta_completa)) {
      return '/media/conversaciones/'.$nombre_archivo;
    }

    return false;
  }

  public function actualizarLikes($user_ids) {
    global $mysqli;

    if(!is_array($user_ids)) {
      $user_ids = array();
    }

    $user_ids = array_unique($user_ids);
    $user_ids = array_values($user_ids);

    $this->likes = json_encode($user_ids);
    $this->fecha_actualizacion = date('Y-m-d H:i:s');

    // Usar query directa para asegurar que se guarda
    $likes_escaped = addslashes($this->likes);
    $fecha_escaped = addslashes($this->fecha_actualizacion);
    $id_escaped = addslashes($this->id);

    $query = "UPDATE conversaciones_internas_comentarios
              SET likes = '$likes_escaped',
                  fecha_actualizacion = '$fecha_escaped'
              WHERE id = '$id_escaped'";

    $mysqli->query($query);

    return $user_ids;
  }

  public function agregarLike($id_usuario) {
    $likes_actuales = json_decode($this->likes, true);
    if($likes_actuales === null) $likes_actuales = array();

    if(!in_array($id_usuario, $likes_actuales)) {
      $likes_actuales[] = $id_usuario;
      return $this->actualizarLikes($likes_actuales);
    }

    return $likes_actuales;
  }

  public function quitarLike($id_usuario) {
    $likes_actuales = json_decode($this->likes, true);
    if($likes_actuales === null) $likes_actuales = array();

    $likes_actuales = array_filter($likes_actuales, function($id) use ($id_usuario) {
      return $id !== $id_usuario;
    });

    return $this->actualizarLikes(array_values($likes_actuales));
  }

  public function agregarTags($user_ids, $id_creado_por) {
    if(!$this->id || !is_array($user_ids)) {
      return false;
    }

    $tags_guardados = array();

    foreach($user_ids as $id_usuario) {
      global $mysqli;
      $check_query = "SELECT id FROM conversaciones_internas_tags
                      WHERE id_comentario = '".$this->id."'
                      AND id_usuario = '".addslashes($id_usuario)."'";

      $result = $mysqli->query($check_query);

      if($result && $result->num_rows > 0) {
        continue;
      }

      $tag = new ConversacionInternaTag();
      $tag->id_comentario = $this->id;
      $tag->id_usuario = $id_usuario;
      $tag->id_creado_por = $id_creado_por;
      $tag->save();

      $tags_guardados[] = $tag;
    }

    return $tags_guardados;
  }

  public function eliminarCompleto() {
    global $mysqli;

    if(!$this->id) return false;

    // Eliminar archivos físicos
    $query = "SELECT ruta_archivo FROM conversaciones_internas_archivos
              WHERE id_comentario = '".$this->id."'";

    $result = $mysqli->query($query);

    if($result) {
      while($row = mysqli_fetch_assoc($result)) {
        $ruta_completa = $_SERVER['DOCUMENT_ROOT'].$row['ruta_archivo'];
        if(file_exists($ruta_completa)) {
          unlink($ruta_completa);
        }
      }
    }

    // Eliminar registros
    $mysqli->query("DELETE FROM conversaciones_internas_archivos WHERE id_comentario = '".$this->id."'");
    $mysqli->query("DELETE FROM conversaciones_internas_tags WHERE id_comentario = '".$this->id."'");
    $mysqli->query("DELETE FROM conversaciones_internas_comentarios WHERE id = '".$this->id."'");

    return true;
  }
}

?>
