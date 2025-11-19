<?php

  class ProductoItem extends Base {

    public $nombre = "";
    public $id_productos = 0;
    public $monto_bruto = 0;
    public $impuesto = "";

    public function __construct($id = null) {
      $this->tableName("productos_items");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }
  }

 ?>
