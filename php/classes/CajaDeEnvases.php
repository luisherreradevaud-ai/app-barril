<?php

  class CajaDeEnvases extends Base {

    public $id = "";
    public $codigo = "";
    public $id_productos = 0;
    public $cantidad_envases = 0;
    public $id_usuarios = 0;
    public $estado = "En planta";
    public $creada;
    public $actualizada;

    // Propiedades calculadas
    public $envases = array();

    public $table_name = "cajas_de_envases";
    public $table_fields = array();

    public function __construct($id = null) {
      $this->tableName("cajas_de_envases");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
        $this->envases = $this->getEnvases();
      } else {
        $this->creada = date('Y-m-d H:i:s');
        $this->actualizada = date('Y-m-d H:i:s');
      }
    }

    /**
     * Generar código único para la caja
     */
    public function generarCodigo() {
      $prefijo = "CAJA";
      $fecha = date('ymd');
      $random = strtoupper(substr(uniqid(), -4));
      $this->codigo = $prefijo . "-" . $fecha . "-" . $random;
    }

    /**
     * Obtener el producto asociado
     * @return Producto|null
     */
    public function getProducto() {
      if($this->id_productos > 0) {
        return new Producto($this->id_productos);
      }
      return null;
    }

    /**
     * Obtener el usuario que creó la caja
     * @return Usuario|null
     */
    public function getUsuario() {
      if($this->id_usuarios > 0) {
        return new Usuario($this->id_usuarios);
      }
      return null;
    }

    /**
     * Obtener todos los envases de esta caja
     * @return array
     */
    public function getEnvases() {
      return Envase::getAll("WHERE id_cajas_de_envases='" . $this->id . "'");
    }

    /**
     * Asignar envases a esta caja desde múltiples batches
     * @param array $asignaciones [id_batch => cantidad]
     */
    public function asignarEnvases($asignaciones) {
      foreach($asignaciones as $id_batch => $cantidad) {
        if($cantidad <= 0) continue;

        $envases = Envase::getDisponiblesPorBatch($id_batch, $cantidad);
        foreach($envases as $envase) {
          $envase->id_cajas_de_envases = $this->id;
          $envase->estado = "En caja";
          $envase->save();
        }
      }
    }

    /**
     * Liberar todos los envases de esta caja
     */
    public function liberarEnvases() {
      $envases = $this->getEnvases();
      foreach($envases as $envase) {
        $envase->id_cajas_de_envases = 0;
        $envase->estado = "Envasado";
        $envase->save();
      }
    }

    /**
     * Obtener todas las cajas en planta
     * @return array
     */
    public static function getCajasEnPlanta() {
      return self::getAll("WHERE estado='En planta' ORDER BY creada DESC");
    }

    /**
     * Obtener cajas en planta por tipo de envase
     * @param string $tipo 'Lata' o 'Botella'
     * @return array
     */
    public static function getCajasEnPlantaByTipo($tipo) {
      $mysqli = $GLOBALS['mysqli'];
      $query = "SELECT DISTINCT c.* FROM cajas_de_envases c
        INNER JOIN productos p ON c.id_productos = p.id
        WHERE c.estado='En planta' AND p.tipo_envase='" . addslashes($tipo) . "'
        ORDER BY c.creada DESC";
      $result = $mysqli->query($query);
      $cajas = array();
      while($row = mysqli_fetch_assoc($result)) {
        $caja = new CajaDeEnvases();
        $caja->setProperties($row);
        $caja->envases = $caja->getEnvases();
        $cajas[] = $caja;
      }
      return $cajas;
    }

    /**
     * Verifica si esta caja es de un producto mixto
     * @return bool
     */
    public function esMixta() {
      $producto = $this->getProducto();
      return $producto ? $producto->esMixto() : false;
    }

    /**
     * Obtener resumen de recetas contenidas en la caja
     * @return array ['nombre_receta' => cantidad]
     */
    public function getResumenRecetas() {
      $envases = $this->getEnvases();
      $resumen = array();
      foreach($envases as $envase) {
        $batch = $envase->getBatchDeEnvases();
        if($batch) {
          $receta = $batch->getReceta();
          $key = $receta ? $receta->nombre : 'Sin receta';
          if(!isset($resumen[$key])) {
            $resumen[$key] = 0;
          }
          $resumen[$key]++;
        }
      }
      return $resumen;
    }

    /**
     * Obtener string con resumen de contenido para UI
     * @return string Ej: "IPA x8, Amber x8, Pale x8"
     */
    public function getContenidoResumen() {
      $resumen = $this->getResumenRecetas();
      $partes = array();
      foreach($resumen as $receta => $cantidad) {
        $partes[] = $receta . ' x' . $cantidad;
      }
      return implode(', ', $partes);
    }

  }

?>
