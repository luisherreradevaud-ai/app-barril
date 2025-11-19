<?php

  class Fermentador extends Base {

    public $tipo;
    public $codigo = '';
    public $id_batches = 0;
    public $clasificacion = '';
    public $activo = 1;
    public $creada;

    public function __construct($id = null) {
      $this->tableName("fermentadores");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }

  }

 ?>
