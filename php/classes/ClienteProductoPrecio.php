<?php

  class ClienteProductoPrecio extends Base {

    public $id_clientes;
    public $id_productos;
    public $precio = 0;

    public function __construct($id = null) {
      $this->tableName("clientes_productos_precios");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }

  }
?>
