<?php

    class Pedido extends Base {

    public $id_clientes;
    public $estado;
    public $creada;

    public function __construct($id = null) {
      $this->tableName("pedidos");
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
