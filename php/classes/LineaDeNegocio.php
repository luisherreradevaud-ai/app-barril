<?php

    class LineaDeNegocio extends Base {

    public $nombre = '';

    public function __construct($id = null) {
      $this->tableName("lineas_de_negocio");
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
