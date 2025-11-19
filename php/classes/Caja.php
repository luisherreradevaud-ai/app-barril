<?php

  class Caja extends Base {

    public $id_productos = 0;
    public $creada;
    public $estado;
    public $codigo;

    public function __construct($id = null) {
      $this->tableName("cajas");
      $this->tableFields($this->table_name);
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }

    public function setSpecifics($values) {
      if($this->id == "" && $this->id_productos!=0) {
        $producto = new Producto($this->id_productos);
        $botellas = new Insumo(20);
        if( $producto->cantidad=="Tripack" ) {
          $botellas->bodega -= 3;
        } else
        if( $producto->cantidad=="24" ) {
          $botellas->bodega -= 24;
        }
        $botellas->save();
      }
    }
  }

 ?>
