<?php

    class TipoDeInsumo extends Base {

    public $nombre = "";
    public $comentarios = "";
    public $visible = 0;
    public $insumos = array();

    public function __construct($id = null) {
      $this->tableName("tipos_de_insumos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
        $this->insumos = Insumo::getAll("WHERE id_tipos_de_insumos='".$this->id."'");
      }
    }
  }

 ?>
