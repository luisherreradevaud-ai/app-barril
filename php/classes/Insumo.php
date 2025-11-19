<?php

    class Insumo extends Base {

    public $nombre = "";
    public $id_tipos_de_insumos = 0;
    public $comentarios = "";
    public $unidad_de_medida = "";
    public $despacho = 0;
    public $bodega = 0;
    public $creada;
    public $last_modified;
    public $last_modified_mensaje = '';

    public function __construct($id = null) {
      $this->tableName("insumos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
        $this->last_modified = $this->creada;
      }
    }
  }

 ?>
