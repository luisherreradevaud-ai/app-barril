<?php

    class CompraDeInsumoInsumo extends Base {

    public $date;
    public $creada;
    public $cantidad = 0;
    public $ubicacion = "";
    public $monto = 0;
    public $id_proveedores = 0;
    public $id_tipos_de_insumos = 0;
    public $id_insumos = 0;
    public $id_compras_de_insumos = 0;

    public function __construct($id = null) {
      $this->tableName("compras_de_insumos_insumos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }
  }

 ?>
