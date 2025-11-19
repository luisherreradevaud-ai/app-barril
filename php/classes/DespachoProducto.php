<?php

    class DespachoProducto extends Base {

    public $tipo;
    public $cantidad;
    public $tipos_cerveza;
    public $id_despachos;
    public $codigo;
    public $id_barriles = 0;
    public $id_productos = 0;
    public $creada;
    public $clasificacion = "";
    public $id_pedidos_productos = 0;
    public $id_pedidos = 0;

    public function __construct($id = null) {
      $this->tableName("despachos_productos");
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
