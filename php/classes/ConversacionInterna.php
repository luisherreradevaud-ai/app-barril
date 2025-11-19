<?php

class ConversacionInterna extends Base {

  public $nombre_vista = "";
  public $id_entidad = "";
  public $estado = "abierto";
  public $id_propietario = "";
  public $id_creado_por = "";
  public $id_actualizado_por = "";
  public $fecha_creacion;
  public $fecha_actualizacion;

  public function __construct($id = null) {
    $this->tableName("conversaciones_internas");
    if($id) {
      $this->id = intval($id);
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    } else {
      $this->fecha_creacion = date('Y-m-d H:i:s');
      $this->fecha_actualizacion = date('Y-m-d H:i:s');
    }
  }

  public static function obtenerOCrearPorVistaEntidad($nombre_vista, $id_entidad, $id_usuario_actual = null) {
    global $mysqli;

    $query = "SELECT * FROM conversaciones_internas
              WHERE nombre_vista = '".addslashes($nombre_vista)."'
              AND id_entidad = '".addslashes($id_entidad)."'
              LIMIT 1";

    $result = $mysqli->query($query);

    if($result && $result->num_rows > 0) {
      $data = mysqli_fetch_assoc($result);
      return new ConversacionInterna($data['id']);
    }

    // Si no existe, crear una nueva
    $conversacion = new ConversacionInterna();
    $conversacion->nombre_vista = $nombre_vista;
    $conversacion->id_entidad = $id_entidad;
    $conversacion->estado = "abierto";

    if($id_usuario_actual) {
      $conversacion->id_propietario = $id_usuario_actual;
      $conversacion->id_creado_por = $id_usuario_actual;
      $conversacion->id_actualizado_por = $id_usuario_actual;
    }

    $conversacion->save();

    return $conversacion;
  }

  public function obtenerComentarios() {
    global $mysqli;

    if(!$this->id) return array();

    $query = "SELECT
                c.*,
                u.nombre as nombre_autor,
                u.email as email_autor
              FROM conversaciones_internas_comentarios c
              LEFT JOIN usuarios u ON c.id_autor = u.id
              WHERE c.id_conversacion_interna = '".$this->id."'
              AND c.estado = 'activo'
              ORDER BY c.fecha_creacion DESC";

    $result = $mysqli->query($query);

    if(!$result) return array();

    $comentarios = array();
    while($row = mysqli_fetch_assoc($result)) {
      // Obtener archivos
      $row['archivos'] = $this->obtenerArchivosComentario($row['id']);

      // Parsear likes
      $row['likes'] = json_decode($row['likes'], true);
      if($row['likes'] === null) $row['likes'] = array();

      // Obtener info de likes
      $row['likes_info'] = $this->obtenerInfoLikes($row['likes']);

      // Obtener tags
      $row['tags'] = $this->obtenerTagsComentario($row['id']);

      $comentarios[] = $row;
    }

    return $comentarios;
  }

  private function obtenerArchivosComentario($id_comentario) {
    global $mysqli;

    $query = "SELECT
                a.*,
                u.nombre as nombre_subido_por
              FROM conversaciones_internas_archivos a
              LEFT JOIN usuarios u ON a.id_subido_por = u.id
              WHERE a.id_comentario = '".addslashes($id_comentario)."'
              AND a.estado = 'activo'
              ORDER BY a.fecha_creacion ASC";

    $result = $mysqli->query($query);

    if(!$result) return array();

    $archivos = array();
    while($row = mysqli_fetch_assoc($result)) {
      $row['metadata'] = json_decode($row['metadata'], true);
      if($row['metadata'] === null) $row['metadata'] = array();
      $archivos[] = $row;
    }

    return $archivos;
  }

  private function obtenerInfoLikes($user_ids) {
    global $mysqli;

    if(empty($user_ids) || !is_array($user_ids)) return array();

    // Filtrar valores vacíos
    $user_ids = array_filter($user_ids, function($id) {
      return !empty($id);
    });

    if(empty($user_ids)) return array();

    // Convertir a strings si son numéricos
    $ids_seguros = array_map(function($id) {
      return "'".addslashes(strval($id))."'";
    }, $user_ids);

    $query = "SELECT id, nombre, email
              FROM usuarios
              WHERE id IN (".implode(',', $ids_seguros).")";

    $result = $mysqli->query($query);

    if(!$result) {
      error_log("Error en obtenerInfoLikes query: " . $mysqli->error);
      return array();
    }

    $usuarios = array();
    while($row = mysqli_fetch_assoc($result)) {
      $usuarios[] = $row;
    }

    return $usuarios;
  }

  private function obtenerTagsComentario($id_comentario) {
    global $mysqli;

    $query = "SELECT
                t.*,
                u.nombre as nombre_usuario,
                u.email as email_usuario,
                u.id_media_header as avatar_usuario
              FROM conversaciones_internas_tags t
              LEFT JOIN usuarios u ON t.id_usuario = u.id
              WHERE t.id_comentario = '".addslashes($id_comentario)."'";

    $result = $mysqli->query($query);

    if(!$result) return array();

    $tags = array();
    while($row = mysqli_fetch_assoc($result)) {
      $tags[] = $row;
    }

    return $tags;
  }

  public function actualizarFechaModificacion($id_usuario = null) {
    if($id_usuario) {
      $this->id_actualizado_por = $id_usuario;
    }
    $this->fecha_actualizacion = date('Y-m-d H:i:s');
    $this->update();
  }

  public function obtenerCompleta() {
    return array(
      'conversacion' => array(
        'id' => $this->id,
        'nombre_vista' => $this->nombre_vista,
        'id_entidad' => $this->id_entidad,
        'estado' => $this->estado,
        'id_propietario' => $this->id_propietario,
        'id_creado_por' => $this->id_creado_por,
        'id_actualizado_por' => $this->id_actualizado_por,
        'fecha_creacion' => $this->fecha_creacion,
        'fecha_actualizacion' => $this->fecha_actualizacion
      ),
      'comentarios' => $this->obtenerComentarios()
    );
  }
}

?>
