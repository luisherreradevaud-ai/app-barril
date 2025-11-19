<?php

  class Entrega extends Base {

    public $id_usuarios_repartidor = 0;
    public $id_clientes = 0;
    public $tipo_de_entrega = '';
    public $estado = '';
    public $monto = 0;
    public $creada;
    public $factura = '';
    public $fecha_vencimiento;
    public $abonado = 0;
    public $datetime_abonado = '0000-00-00';
    public $rand_int = 0;
    public $receptor_nombre = '';
    public $id_usuarios_vendedor = 0;
    public $observaciones = '';
    public $id_despachos = 0;
    public $entregas_productos = array();

    public function __construct($id = null) {
      $this->tableName("entregas");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }

    public function getEntregasProductos() {
      $this->entregas_productos = EntregaProducto::getAll("WHERE id_entregas='".$this->id."'");
      return $this->entregas_productos;
    }

    public function deleteSpecifics($values) {

      if($this->id == "") {
        return false;
      }

      foreach($this->getEntregasProductos() as $ep) {
        $ep->delete();
      }
      
    }
  }

 ?>
