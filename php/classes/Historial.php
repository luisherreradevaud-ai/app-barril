<?php

  class Historial extends Base {

    public $accion = "";
    public $id_usuarios = 0;
    public $creada;

    public function __construct($id = null) {
      $this->tableName("historial");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }

    public static function guardarAccion($accion,$usuario) {
        $historial = new Historial;
        $historial->id_usuarios = $usuario->id;
        $historial->accion = $accion;
        $historial->save();
    }
  }

 ?>
