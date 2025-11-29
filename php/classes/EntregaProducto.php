<?php

    class EntregaProducto extends Base {

    public $tipo;
    public $cantidad;
    public $tipos_cerveza;
    public $id_entregas;
    public $codigo;
    public $id_barriles;
    public $id_cajas_de_envases = 0;
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

    /**
     * Obtener la caja de envases asociada
     * @return CajaDeEnvases|null
     */
    public function getCajaDeEnvases() {
      if($this->id_cajas_de_envases > 0) {
        return new CajaDeEnvases($this->id_cajas_de_envases);
      }
      return null;
    }
  }

 ?>
