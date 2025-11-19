<?php

  class Producto extends Base {

    public $nombre = "";
    public $id_recetas = 0;
    public $productos_items;
    public $tipo = "";
    public $clasificacion = "";
    public $cantidad = "";
    public $monto = 0;
    public $codigo_de_barra = "";
    public $total_bruto = 0;

    public function __construct($id = null) {
      $this->tableName("productos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
        $this->productos_items = ProductoItem::getAll("WHERE id_productos='".$this->id."'");
        foreach($this->productos_items as $pi) {
          $this->total_bruto += $pi->monto_bruto;
        }
      } else {
        $this->productos_items = [];
      }
    }

    public function setSpecifics($post) {
        if($this->id == "" || $this->id == 0) {
            $this->save();
        }
        foreach($this->productos_items as $pi) {
            $pi->delete();
        }
        if(isset($post['items'])) {
            if(is_array($post['items'])) {
                foreach($post['items'] as $item) {
                    $pi = new ProductoItem;
                    $pi->setPropertiesNoId($item);
                    $pi->id_productos = $this->id;
                    $pi->save();
                }
            }
        }
    }

    public function deleteSpecifics($post) {
        foreach($this->productos_items as $pi) {
            $pi->delete();
        }
    }

    public function getClienteProductoPrecio($id_clientes) {

      $query = "WHERE id_productos='".$this->id."' AND id_clientes='".$id_clientes."'";
      $cpp = ClienteProductoPrecio::getAll($query);

      if(count($cpp)>0) {
        return $cpp[0]->precio;
      } else {
        return $this->monto;
      }

    }

  }

 ?>
