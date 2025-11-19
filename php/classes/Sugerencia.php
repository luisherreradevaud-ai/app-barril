<?php

    class Sugerencia extends Base {

    public $id_clientes;
    public $id_usuarios;
    public $tipo;
    public $contenido;
    public $creada;

    public function __construct($id = null) {
      $this->tableName("sugerencias");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }

    public function setSpecifics($values) {

    }
  }

 ?>
