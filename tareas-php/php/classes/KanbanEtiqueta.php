<?php

class KanbanEtiqueta extends Base {

  public $nombre;
  public $codigo_hex = "#6A1693";

  public function __construct($id = null) {
    $this->tableName("kanban_etiquetas");
    if($id) {
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    }
  }

  public static function getTodasLasEtiquetas() {
    return KanbanEtiqueta::getAll("ORDER BY nombre ASC");
  }
}

?>
