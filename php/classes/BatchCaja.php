<?php

  class BatchCaja extends Base {

    public $id_batches = 0;
    public $cantidad = 0;

    public function __construct($id = null) {
      $this->tableName("batches_cajas");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }
    
  }

 ?>
