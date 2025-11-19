<?php

class ConversacionInternaTag extends Base {

  public $id_comentario = "";
  public $id_usuario = "";
  public $id_creado_por = "";
  public $fecha_creacion;

  public function __construct($id = null) {
    $this->tableName("conversaciones_internas_tags");
    if($id) {
      $this->id = intval($id);
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    } else {
      $this->fecha_creacion = date('Y-m-d H:i:s');
    }
  }
}

?>
