<?php

  class BatchActivo extends Base {

    public $id_batches = 0;
    public $id_activos = 0;
    public $estado = '';
    public $litraje = '';
    public $creada;

    public function __construct($id = null) {
      $this->tableName("batches_activos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date("Y-m-d H:i:s");
      }
    }

    public function setSpecifics($post) {

    }

    public function deleteSpecifics($values) {

    }
  }

 ?>
