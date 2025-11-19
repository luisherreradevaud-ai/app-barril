<?php

  class Vendedor extends Base {

    public $nombre;
    public $email;
    public $telefono;
    public $creada;
    public $estado;
    public $meta_barriles_mensuales = 0;
    public $meta_cajas_mensuales = 0;

    public function __construct($id = null) {
      $this->tableName("vendedores");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }
  }

 ?>
