<?php

  class Permiso extends Base {

    public $id_secciones = 0;
    public $id_usuarios_niveles = 0;
    public $acceso = 0;

    public function __construct($id = null) {
      $this->tableName("permisos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }
  }

 ?>
