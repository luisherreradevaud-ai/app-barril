<?php

    class GastoFijoMes extends Base {

    public $id_gastos_fijos = 0;
    public $mes;
    public $ano;
    public $proyectado_neto = 0;
    public $proyectado_impuesto = 0;
    public $proyectado_bruto = 0;
    public $real_neto = 0;
    public $real_impuesto = 0;
    public $real_bruto = 0;
    public $creada;

    public function __construct($id = null) {
      $this->tableName("gastos_fijos_mes");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }

    public function setSpecifics($values) {
    }

    public function deleteSpecifics($values) {
    }

  }

 ?>
