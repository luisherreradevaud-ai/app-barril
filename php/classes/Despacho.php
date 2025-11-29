<?php

    class Despacho extends Base {

    public $id_usuarios_repartidor;
    public $id_clientes = 0;
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
          // No limpiar id_batches para mantener trazabilidad (el barril aún tiene cerveza)
          $barril->registrarCambioDeEstado();
          $barril->save();
        }
        // Revertir cajas de envases a estado "En planta"
        if($dp->tipo=="CajaEnvases" && $dp->id_cajas_de_envases > 0) {
          $caja = new CajaDeEnvases($dp->id_cajas_de_envases);
          if($caja->id) {
            $caja->estado = "En planta";
            $caja->save();
          }
        }
        $dp->delete();
      }
    }
  }

 ?>
