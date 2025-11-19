<?php

  class BarrilReemplazo extends Base {

    public $id_barriles_devuelto = 0;
    public $id_barriles_reemplazo = 0;
    public $creada;
    public $motivo = "";
    public $id_entregas_productos = 0;
    public $id_entregas = 0;
    public $id_clientes = 0;
    public $id_usuarios = 0;

    public function __construct($id = null) {
      $this->tableName("barriles_reemplazos");
      $this->cliente = new Cliente;
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
        $this->id_usuarios = $GLOBALS['usuario']->id;
      }
    }

  }

 ?>
