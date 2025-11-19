<?php

  class Seccion extends Base {

    public $nombre = "";
    public $template_file = "";
    public $visible = 1;
    public $permisos_editables = 1;
    public $clasificacion = "";
    public $id_menus = 0;
    public $create_path = 0;
    public $estado = "";

    public function __construct($id = null) {
      $this->tableName("secciones");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }

    public function getMenu() {
      if($this->id_menus && $this->id_menus > 0) {
        return new Menu($this->id_menus);
      }
      return null;
    }
  }

 ?>
