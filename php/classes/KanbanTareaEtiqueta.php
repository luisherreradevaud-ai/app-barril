<?php

class KanbanTareaEtiqueta extends Base {

  public $id_kanban_tareas;
  public $id_kanban_etiquetas;

  public function __construct($id = null) {
    $this->tableName("kanban_tareas_etiquetas");
    if($id) {
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    }
  }
}

?>
