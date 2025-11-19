<?php

    class Despacho extends Base {

    public $id_usuarios_repartidor;
    public $tipo_de_entrega;
    public $estado = "En despacho";
    public $creada;
    public $id_pedidos = 0;

    public function __construct($id = null) {
      $this->tableName("despachos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }

    public function deleteSpecifics($values) {
      if($this->id=='') {
        return false;
      }
      $despachos_productos = DespachoProducto::getAll("WHERE id_despachos='".$this->id."'");
      foreach($despachos_productos as $dp) {
        if($dp->tipo=="Barril") {
          $barril = new Barril($dp->id_barriles);
          $barril->estado = "En planta";
          $barril->id_clientes = 0;
          $barril->id_batches = 0;
          $barril->registrarCambioDeEstado();
          $barril->save();
        }
        $dp->delete();
      }
    }
  }

 ?>
