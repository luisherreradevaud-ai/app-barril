<?php

class KanbanTareaUsuario extends Base {

  public $id_kanban_tareas;
  public $id_usuarios;

  public function __construct($id = null) {
    $this->tableName("kanban_tareas_usuarios");
    if($id) {
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    }
  }
}

?>
