<?php

  class RecetaInsumo extends Base {

    public $id_recetas = 0;
    public $id_insumos = 0;
    public $cantidad = 0;

    public function __construct($id = null) {
      $this->tableName("recetas_insumos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }
  }

 ?>
