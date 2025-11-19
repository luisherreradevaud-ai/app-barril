<?php

  class Locacion extends Base {

    public $nombre = "";
    public $creada = "";

    public function __construct($id = null) {
      $this->tableName("locaciones");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }
  }

 ?>
