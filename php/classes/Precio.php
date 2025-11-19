<?php

  class Precio extends Base {

    public $id_clientes;
    public $tipo_barril;
    public $tipo_cerveza;
    public $precio;

    public function __construct($id = null) {
      $this->tableName("precios");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }

  }
?>
