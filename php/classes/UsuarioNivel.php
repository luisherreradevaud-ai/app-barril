<?php

  class UsuarioNivel extends Base {

    public $nombre;
    public $editable = 1;
    public $comentarios = "";
    public $creada;

    public function __construct($id = null) {
      $this->tableName("usuarios_niveles");
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
