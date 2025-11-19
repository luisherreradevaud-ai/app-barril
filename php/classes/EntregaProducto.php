<?php

    class EntregaProducto extends Base {

    public $tipo;
    public $cantidad;
    public $tipos_cerveza;
    public $id_entregas;
    public $codigo;
    public $id_barriles;
    public $monto;
    public $creada;
    public $QtyItem = 1;
    public $id_productos = 0;
    public $id_pedidos_productos = 0;
    public $id_despachos_productos = 0;

    public function __construct($id = null) {
      $this->tableName("entregas_productos");
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
