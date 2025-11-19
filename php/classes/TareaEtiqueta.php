<?php

class TareaEtiqueta extends Base {

  public $id_tareas;
  public $id_etiquetas;

  public function __construct($id = null) {
    $this->tableName("tareas_etiquetas");
    if($id) {
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    }
  }
}

?>
