<?php

    class PedidoProducto extends Base {

    public $tipo;
    public $cantidad;
    public $tipos_cerveza;
    public $id_pedidos;
    public $estado;
    public $id_productos = 0;

    public function __construct($id = null) {
      $this->tableName("pedidos_productos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }
  }

 ?>
