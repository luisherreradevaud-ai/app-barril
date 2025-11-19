<?php

  class TareaComentario extends Base {

    public $id_usuarios = 0;
    public $id_tareas = 0;
    public $comentario = "";
    public $creada;

    public function __construct($id = null) {
      $this->tableName("tareas_comentarios");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }

    public function setSpecifics($values) {

      if($this->id == '') {
        $this->save();
      }

      NotificacionControl::trigger('Nuevo Comentario de Tarea',$this);

    }

    public function deleteSpecifics($values) {
      
    }

  }

 ?>
