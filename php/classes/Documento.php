<?php

  class Documento extends Base {

    public $id_usuarios = 0;
    public $folio = "";
    public $monto = 0;
    public $estado = "Por Aprobar";
    public $id_clientes = 0;
    public $creada;
    public $datetime_aprobado;
    public $id_pagos = 0;

    public function __construct($id = null) {
      $this->tableName("documentos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
        $this->datetime_aprobado = date('Y-m-d H:i:s');
      }
    }

    public function setSpecifics($values) {
      $this->id_usuarios = $GLOBALS['usuario']->id;
    }
  }

 ?>
