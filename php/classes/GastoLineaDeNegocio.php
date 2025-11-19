<?php

    class GastoLineaDeNegocio extends Base {

    public $id_gastos = 0;
    public $id_gastos_fijos = 0;
    public $id_lineas_de_negocio = 0;
    public $porcentaje = 0;

    public function __construct($id = null) {
      $this->tableName("gastos_lineas_de_negocio");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
        }
      }
    

    public function setSpecifics($values) {

    }
  }

 ?>
