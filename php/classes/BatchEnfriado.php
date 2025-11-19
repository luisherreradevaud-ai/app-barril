<?php

  class BatchEnfriado extends Base {

    public $id_batches = 0;
    public $date;
    public $temperatura_inicio = 0;
    public $hora_inicio = '';
    public $ph = 0;
    public $densidad = '';
    public $ph_enfriado = 0;
    public $seq_index = 0;
    public $tipo = '';
    public $creada;

    public function __construct($id = null) {
      $this->tableName("batches_enfriado");
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
