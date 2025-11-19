<?php

class Tablero extends Base {

  public $nombre;
  public $descripcion;
  public $id_entidad;
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
    return Tablero::getAll("WHERE id_entidad='".$id_entidad."' ORDER BY orden ASC, id ASC");
  }

  public function getUsuarios() {
    if($this->id == "" || $this->id == 0) {
      return array();
    }

    $ids_usuarios = $this->getRelations('usuarios');
    $usuarios = array();

    foreach($ids_usuarios as $id_usuario) {
      $usuarios[] = new Usuario($id_usuario);
    }

    return $usuarios;
  }

  public function agregarUsuario($usuario) {
    return $this->createRelation($usuario);
  }

  public function quitarUsuario($usuario) {
    return $this->deleteRelation($usuario);
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
      'orden' => $this->orden,
      'columnas' => $columnas,
      'creada' => $this->creada,
      'actualizada' => $this->actualizada
    );
  }
}

?>
