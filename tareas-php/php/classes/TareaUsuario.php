<?php

class TareaUsuario extends Base {

  public $id_tareas;
  public $id_usuarios;

  public function __construct($id = null) {
    $this->tableName("tareas_usuarios");
    if($id) {
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    }
  }
}

?>
