<?php

    class CompraDeInsumo extends Base {

    public $id_usuarios;
    public $date;
    public $creada;
    public $monto = 0;
    public $factura = "";
    public $estado = "";
    public $observaciones = "";
    public $id_proveedores = 0;
    public $id_gastos = 0;

    public function __construct($id = null) {
      $this->tableName("compras_de_insumos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }

    public function setSpecifics($values) {

      if(!is_array($values)) {
        return false;
      }

      if(!isset($values['estado'])) {
        return false;
      }

      $gasto = new Gasto($this->id_gastos);

      if($gasto->item == '') {
        return false;
      }

      $gasto->estado = $values['estado'];
      $gasto->save();

      return true;

    }
  }

 ?>
