<?php

  class ProyectoIngreso extends Base {

    public $id_proyectos = 0;
    public $monto = 0;
    public $item = "";
    public $forma_de_pago = "";
    public $impuestos = "";
    public $creada;


    public function __construct($id = null) {
      $this->tableName("proyectos_ingresos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }

    public function setSpecifics($post) {

    }

    public function deleteSpecifics($post) {

    }

  }

 ?>
