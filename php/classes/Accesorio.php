<?php

class Accesorio extends Base {

  public $id;
  public $id_activos;
  public $nombre;
  public $marca;
  public $creada;
  public $observaciones;
  public $ultimo_cambio;
  public $accesorios_renovados;

  public function __construct($id = null) {
    $this->tableName("accesorios");
    if($id){
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    } else {
      $this->creada = date('Y-m-d H:i:s');
      $this->ultimo_cambio = date('Y-m-d H:i:s');
    }
  }

}

?>