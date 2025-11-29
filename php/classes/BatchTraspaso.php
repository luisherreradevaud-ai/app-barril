<?php

  class BatchTraspaso extends Base {

    public $id_batches = 0;
    public $cantidad = 0;
    public $merma_litros = 0;
    public $id_fermentadores_inicio = 0;
    public $id_fermentadores_final = 0;
    public $creada;
    public $date;
    public $hora;

    public function __construct($id = null) {
      $this->tableName("batches_traspasos");
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
