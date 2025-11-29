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
    public $id_formatos_de_envases = 0;
    public $cantidad_de_envases = 0;
    public $tipo_envase = "Lata";
    public $es_mixto = 0;

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

    /**
     * Obtiene el formato de envases asociado
     * @return FormatoDeEnvases|null
     */
    public function getFormatoDeEnvases() {
      if($this->id_formatos_de_envases > 0) {
        return new FormatoDeEnvases($this->id_formatos_de_envases);
      }
      return null;
    }

    /**
     * Verifica si este producto es de tipo envases (latas o botellas)
     * @return bool
     */
    public function esProductoDeEnvases() {
      return $this->id_formatos_de_envases > 0 && $this->cantidad_de_envases > 0;
    }

    /**
     * Obtiene productos configurados para envases
     * @param string|null $tipo 'Lata', 'Botella' o null para todos
     * @return array
     */
    public static function getProductosDeEnvases($tipo = null) {
      $where = "WHERE id_formatos_de_envases > 0 AND cantidad_de_envases > 0";
      if($tipo) {
        $where .= " AND tipo_envase='" . addslashes($tipo) . "'";
      }
      $where .= " ORDER BY nombre ASC";
      return self::getAll($where);
    }

    /**
     * Obtiene el label del tipo de envase para UI
     * @return string
     */
    public function getTipoEnvaseLabel() {
      $labels = array(
        'Lata' => 'Lata',
        'Botella' => 'Botella'
      );
      return isset($labels[$this->tipo_envase]) ? $labels[$this->tipo_envase] : $this->tipo_envase;
    }

    /**
     * Verifica si este producto es mixto (acepta múltiples recetas)
     * @return bool
     */
    public function esMixto() {
      return $this->es_mixto == 1;
    }

    /**
     * Obtiene productos mixtos configurados para envases
     * @param string|null $tipo 'Lata', 'Botella' o null para todos
     * @return array
     */
    public static function getProductosMixtos($tipo = null) {
      $where = "WHERE es_mixto = 1 AND id_formatos_de_envases > 0 AND cantidad_de_envases > 0";
      if($tipo) {
        $where .= " AND tipo_envase='" . addslashes($tipo) . "'";
      }
      $where .= " ORDER BY nombre ASC";
      return self::getAll($where);
    }

  }

 ?>
