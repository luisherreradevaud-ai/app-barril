<?php

class ConversacionInternaArchivo extends Base {

  public $id_comentario = "";
  public $ruta_archivo = "";
  public $nombre = "";
  public $descripcion = "";
  public $estado = "activo";
  public $metadata = "";
  public $id_subido_por = "";
  public $fecha_creacion;
  public $fecha_actualizacion;

  public function __construct($id = null) {
    $this->tableName("conversaciones_internas_archivos");
    if($id) {
      $this->id = intval($id);
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    } else {
      $this->fecha_creacion = date('Y-m-d H:i:s');
      $this->fecha_actualizacion = date('Y-m-d H:i:s');
    }
  }

  public function eliminarCompleto() {
    global $mysqli;

    if(!$this->id) return false;

    // Eliminar archivo físico
    if($this->ruta_archivo) {
      $ruta_completa = $_SERVER['DOCUMENT_ROOT'].$this->ruta_archivo;
      if(file_exists($ruta_completa)) {
        unlink($ruta_completa);
      }
    }

    // Eliminar registro
    $mysqli->query("DELETE FROM conversaciones_internas_archivos WHERE id = '".$this->id."'");

    // Actualizar conversación padre
    if($this->id_comentario) {
      $comentario = new ConversacionInternaComentario($this->id_comentario);
      if($comentario->id_conversacion_interna) {
        $conversacion = new ConversacionInterna($comentario->id_conversacion_interna);
        $conversacion->actualizarFechaModificacion();
      }
    }

    return true;
  }

  public function obtenerTamanoLegible() {
    $metadata = json_decode($this->metadata, true);

    if(!$metadata || !isset($metadata['size'])) {
      return 'Desconocido';
    }

    $size = $metadata['size'];

    if($size < 1024) {
      return $size.' B';
    } else if($size < 1048576) {
      return round($size / 1024, 2).' KB';
    } else {
      return round($size / 1048576, 2).' MB';
    }
  }

  public function obtenerMimetype() {
    $metadata = json_decode($this->metadata, true);

    if(!$metadata || !isset($metadata['mimetype'])) {
      return 'application/octet-stream';
    }

    return $metadata['mimetype'];
  }

  public function esImagen() {
    $mimetype = $this->obtenerMimetype();
    return strpos($mimetype, 'image/') === 0;
  }

  public function obtenerIcono() {
    $mimetype = $this->obtenerMimetype();

    if(strpos($mimetype, 'image/') === 0) {
      return 'file-image';
    } else if(strpos($mimetype, 'video/') === 0) {
      return 'file-video';
    } else if(strpos($mimetype, 'audio/') === 0) {
      return 'file-audio';
    } else if(strpos($mimetype, 'application/pdf') === 0) {
      return 'file-text';
    } else if(strpos($mimetype, 'application/zip') !== false || strpos($mimetype, 'application/x-rar') !== false) {
      return 'file-archive';
    } else if(strpos($mimetype, 'application/msword') !== false || strpos($mimetype, 'application/vnd.openxmlformats-officedocument') !== false) {
      return 'file-text';
    } else {
      return 'file';
    }
  }
}

?>
