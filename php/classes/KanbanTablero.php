<?php

class KanbanTablero extends Base {

  public $nombre;
  public $descripcion;
  public $id_entidad;
  public $id_usuario_creador;
  public $orden = 0;
  public $creada;
  public $actualizada;

  public function __construct($id = null) {
    $this->tableName("kanban_tableros");
    if($id) {
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    } else {
      $this->creada = date('Y-m-d H:i:s');
      $this->actualizada = date('Y-m-d H:i:s');

      // Assign current user as creator if session exists
      if(isset($GLOBALS['usuario']) && $GLOBALS['usuario']->id) {
        $this->id_usuario_creador = $GLOBALS['usuario']->id;
      }
    }
  }

  public function save() {
    $this->actualizada = date('Y-m-d H:i:s');
    return parent::save();
  }

  public function getColumnas() {
    return KanbanColumna::getAll("WHERE id_kanban_tableros='".$this->id."' ORDER BY orden ASC, id ASC");
  }

  public static function getTablerosPorEntidad($id_entidad) {
    return KanbanTablero::getAll("WHERE id_entidad='".$id_entidad."' ORDER BY orden ASC, id ASC");
  }

  public function addUsuario($id_usuario) {
    if(!$this->id || !$id_usuario) {
      return false;
    }
    $relacion = new TableroUsuario();
    $relacion->id_kanban_tableros = $this->id;
    $relacion->id_usuarios = $id_usuario;
    return $relacion->save();
  }

  public function getUsuarios() {
    return TableroUsuario::getAll("WHERE id_kanban_tableros = '".$this->id."'");
  }

  public function usuarioTieneAcceso($id_usuario) {
    if(!$this->id || !$id_usuario) {
      error_log("DEBUG usuarioTieneAcceso - ID tablero o usuario vacío");
      return false;
    }

    // Es creador?
    if($this->id_usuario_creador == $id_usuario) {
      error_log("DEBUG usuarioTieneAcceso - Es creador, acceso concedido");
      return true;
    }

    // Está asignado?
    $usuarios_asignados = $this->getUsuarios();
    error_log("DEBUG usuarioTieneAcceso - Usuarios asignados: " . count($usuarios_asignados));

    if(is_array($usuarios_asignados)) {
      foreach($usuarios_asignados as $rel) {
        error_log("DEBUG usuarioTieneAcceso - Revisando usuario asignado ID: " . ($rel ? $rel->id_usuarios : 'null'));
        if($rel && isset($rel->id_usuarios) && $rel->id_usuarios == $id_usuario) {
          error_log("DEBUG usuarioTieneAcceso - Está asignado, acceso concedido");
          return true;
        }
      }
    }

    error_log("DEBUG usuarioTieneAcceso - No es creador ni está asignado, acceso denegado");
    return false;
  }

  public function toArray() {
    $columnas_arr = $this->getColumnas();
    $columnas = array();
    foreach($columnas_arr as $columna) {
      $columnas[] = $columna->toArray();
    }

    return array(
      'id' => $this->id,
      'nombre' => $this->nombre,
      'descripcion' => $this->descripcion,
      'id_entidad' => $this->id_entidad,
      'id_usuario_creador' => $this->id_usuario_creador,
      'orden' => $this->orden,
      'columnas' => $columnas,
      'creada' => $this->creada,
      'actualizada' => $this->actualizada
    );
  }
}

?>
