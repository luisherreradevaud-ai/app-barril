<?php

  class Transaccion extends Base {

    public $buy_order;
    public $session_id;
    public $amount;
    public $token;
    public $creada;
    public $ids_entregas;
    public $id_clientes;

    public function __construct($id = null) {
      $this->tableName("transacciones");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }

  }
?>
