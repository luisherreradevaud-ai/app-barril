<?php

class TableroUsuario extends Base {

  public $id_kanban_tableros;
  public $id_usuarios;

  public function __construct($id = null) {
    $this->tableName("kanban_tableros_usuarios");
    if($id) {
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    }
  }
}

?>
