<?php

  class Receta extends Base {

    public $nombre = "";
    public $codigo = "";
    public $clasificacion = "";
    public $observaciones = "";
    public $litros = 0;
    public $creada = "";
    public $insumos_arr;

    public function __construct($id = null) {
      $this->tableName("recetas");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
        $this->insumos_arr = RecetaInsumo::getAll("WHERE id_recetas='".$this->id."'");
      }
    }

    public function setSpecifics($post) {

      $this->save();

      $recetas_insumos = RecetaInsumo::getAll("WHERE id_recetas='".$this->id."'");
      foreach($recetas_insumos as $ri) {
        $ri->delete();
      }

      if(isset($post['insumos'])) {
    
        foreach($post['insumos'] as $insumo) {

          $ri = new RecetaInsumo;
          $ri->setPropertiesNoId($insumo);
          $ri->id_insumos = $insumo['id'];
          $ri->id_recetas = $this->id;
          $ri->save();
    
        }
    
      }
    }
  }

 ?>
