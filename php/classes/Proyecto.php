<?php

  class Proyecto extends Base {

    public $nombre = "";
    public $clasificacion;
    public $estado = "Activo";
    public $date_inicio;
    public $date_finalizacion;

    public function __construct($id = null) {
      $this->tableName("proyectos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }

    public function setSpecifics($post) {

    }

    public function deleteSpecifics($post) {

      $proyectos_productos = ProyectoProducto::getAll("WHERE id_proyectos='".$this->id."'");
      foreach($proyectos_productos as $pp) {
        $pp->deleteSpecifics($post);
        $pp->delete();
      }

      $proyectos_ingresos = ProyectoIngreso::getAll("WHERE id_proyectos='".$this->id."'");
      foreach($proyectos_ingresos as $pi) {
        $pi->deleteSpecifics($post);
        $pi->delete();
      }

    }

  }

 ?>
