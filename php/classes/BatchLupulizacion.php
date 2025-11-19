<?php

  class BatchLupulizacion extends Base {

    public $id_batches = 0;
    public $seq_index = 0;
    public $tipo = '';
    public $creada;
    public $date;
    public $hora = '';

    public function __construct($id = null) {
      $this->tableName("batches_lupulizacion");
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
