<?php

  class Errores extends Base {

    public $descripcion;
    public $hash;
    public $url;

    public function __construct($id = null) {
      $this->tableName("errores");
      $this->tableFields($this->table_name);
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }
  }



 ?>
