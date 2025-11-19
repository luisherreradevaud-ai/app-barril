<?php

  class Pago extends Base {

    public $buy_order = '';
    public $session_id = 0;
    public $amount = 0;
    public $token = '';
    public $creada;
    public $ids_entregas = 0;
    public $id_clientes = 0;
    public $codigo_transaccion = '';
    public $facturas = '';
    public $forma_de_pago = '';
    public $id_usuarios = 0;
    public $restante = 0;
    public $id_documentos = 0;

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
