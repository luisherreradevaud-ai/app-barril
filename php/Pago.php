<?php

  class Pago extends Base {

    public $buy_order;
    public $session_id;
    public $amount;
    public $token;
    public $creada;
    public $ids_entregas;
    public $id_clientes;
    public $codigo_transaccion;
    public $facturas;
    public $forma_de_pago;

    public function __construct($id = null) {
      $this->tableName("pagos");
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
