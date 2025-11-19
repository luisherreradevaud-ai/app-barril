<?php

  class ProyectoProducto extends Base {

    public $id_proyectos = 0;
    public $id_relation = 0;
    public $id_productos = 0;
    public $id_gastos = 0;
    public $monto = 0;
    public $formato = "";
    public $creada;


    public function __construct($id = null) {
      $this->tableName("proyectos_productos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d- H:i:s');
      }
    }

    public function setSpecifics($post) {

        $proyecto = new Proyecto($this->id_proyectos);
        $producto = new Producto($this->id_productos);

        $gasto = new Gasto;
        $gasto->monto = $this->monto;
        $gasto->item = $proyecto->nombre.": ".$producto->tipo." ".$producto->cantidad." ".$producto->clasificacion." ".$producto->nombre;
        $gasto->tipo_de_gasto = "Proyectos";
        $gasto->estado = "Pagado";
        $gasto->date_vencimiento = date('Y-m-d');
        $gasto->save();

        $this->id_gastos = $gasto->id;

        $proyecto->createRelation($gasto);

        if($producto->tipo == "Barril" && $this->id_relation != 0) {
            $barril = new Barril($this->id_relation);
            $barril->estado = "En proyecto";
            $barril->save();
        }

    }

    public function deleteSpecifics($post) {

        $proyecto = new Proyecto($this->id_proyectos);
        $gasto = new Gasto($this->id_gastos);

        $proyecto->deleteRelation($gasto);
        $gasto->delete();

        if($this->formato == "Barril") {
            $barril = new Barril($this->id_relation);
            $barril->estado = "En planta";
            $barril->save();
        }

    }

  }

 ?>
