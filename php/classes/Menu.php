<?php

  class Menu extends Base {

    public $nombre = "";
    public $icon = "";
    public $link;
    public $secciones;
    public $estado = "";

    public function __construct($id = null) {

      $this->tableName("menus");
      $this->secciones = array();

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
