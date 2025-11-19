<?php

class Etiqueta extends Base {

  public $nombre;
  public $codigo_hex = "#6A1693";

  public function __construct($id = null) {
    $this->tableName("etiquetas");
    if($id) {
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    }
  }

  public static function getTodasLasEtiquetas() {
    return Etiqueta::getAll("ORDER BY nombre ASC");
  }
}

?>
