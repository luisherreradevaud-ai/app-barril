<?php

    class Gasto extends Base {

    public $id_usuarios;
    public $estado;
    public $creada;
    public $monto = 0;
    public $item = "";
    public $tipo_de_gasto = "";
    public $comentarios = "";
    public $id_media_header = 0;
    public $date_vencimiento;
    public $aprobado = 0;

    public function __construct($id = null) {
      $this->tableName("gastos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
        $this->date_vencimiento = date('Y-m-d');
      }
    }

    public function setSpecifics($values) {

      if(!is_array($values)) {
        return false;
      }

      if(!isset($values['estado'])) {
        return false;
      }

      if($this->tipo_de_gasto == "Insumos" && $this->id != '') {
        $cdi = new CompraDeInsumo;
        $cdi->getFromDatabase('id_gastos',$this->id);

        if($cdi->id == '') {
          return false;
        }

        $cdi->estado = $values['estado'];
        $cdi->save();

        return true;
      }

      if($this->id == "") {
        $this->save();
        NotificacionControl::trigger('Nuevo Gasto',$this);
      }

      

    }

    public function deleteSpecifics($values) {
      $this->deleteAllMedia();
      $ids_proyectos = $this->getRelations("proyectos");
      if(count($ids_proyectos)>0) {
        foreach($ids_proyectos as $id_proyectos) {
          $proyecto = new Proyecto($id_proyectos);
          $this->deleteRelation($proyecto);
          $proyectos_productos = ProyectoProducto::getAll("WHERE id_gastos='".$this->id."'");
          foreach($proyectos_productos as $pp) {
            $pp->delete();
          }
        }
      }
      
      
    }

  }

 ?>
