<?php

  class BatchInsumo extends Base {

    public $id_batches = 0;
    public $id_insumos = 0;
    public $cantidad = 0;
    public $etapa = '';
    public $etapa_index = 0;
    public $tipo = '';
    public $creada;
    public $date;
    public $insumo;

    public function __construct($id = null) {
      $this->tableName("batches_insumos");
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

    public function getInsumo() {
      $this->insumo = new Insumo($this->id_insumos);
    }
  }

 ?>
