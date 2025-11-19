<?php

    class TipoDeGasto extends Base {

    public $nombre;
    public $total = 0;
    public $total_vencido = 0;
    public $total_por_vencer = 0;
    public $total_pagado = 0;

    public function __construct($id = null) {
      $this->tableName("tipos_de_gastos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }
  }

 ?>
