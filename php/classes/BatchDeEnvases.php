<?php

  class BatchDeEnvases extends Base {

    public $id = "";
    public $tipo = "Lata";
    public $id_batches = 0;
    public $id_activos = 0;
    public $id_barriles = 0;
    public $id_batches_activos = 0;
    public $id_formatos_de_envases = 0;
    public $id_recetas = 0;
    public $cantidad_de_envases = 0;
    public $volumen_origen_ml = 0;
    public $rendimiento_ml = 0;
    public $merma_ml = 0;
    public $id_usuarios = 0;
    public $estado = "Cargado en planta";
    public $creada;
    public $actualizada;

    // Propiedades calculadas
    public $envases_disponibles = 0;

    public $table_name = "batches_de_envases";
    public $table_fields = array();

    public function __construct($id = null) {
      $this->tableName("batches_de_envases");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
        $this->calcularEnvasesDisponibles();
      } else {
        $this->creada = date('Y-m-d H:i:s');
        $this->actualizada = date('Y-m-d H:i:s');
      }
    }

    /**
     * Calcular cuántos envases están disponibles (no asignados a cajas)
     */
    public function calcularEnvasesDisponibles() {
      $this->envases_disponibles = Envase::contarDisponiblesPorBatch($this->id);
    }

    /**
     * Obtener el batch de cerveza asociado
     * @return Batch|null
     */
    public function getBatch() {
      if($this->id_batches > 0) {
        return new Batch($this->id_batches);
      }
      return null;
    }

    /**
     * Obtener la receta asociada
     * @return Receta|null
     */
    public function getReceta() {
      if($this->id_recetas > 0) {
        return new Receta($this->id_recetas);
      }
      return null;
    }

    /**
     * Obtener el formato de envase
     * @return FormatoDeEnvases|null
     */
    public function getFormatoDeEnvases() {
      if($this->id_formatos_de_envases > 0) {
        return new FormatoDeEnvases($this->id_formatos_de_envases);
      }
      return null;
    }

    /**
     * Obtener el activo (fermentador) de origen
     * @return Activo|null
     */
    public function getActivo() {
      if($this->id_activos > 0) {
        return new Activo($this->id_activos);
      }
      return null;
    }

    /**
     * Obtener el barril de origen
     * @return Barril|null
     */
    public function getBarril() {
      if($this->id_barriles > 0) {
        return new Barril($this->id_barriles);
      }
      return null;
    }

    /**
     * Obtener el usuario que realizó el envasado
     * @return Usuario|null
     */
    public function getUsuario() {
      if($this->id_usuarios > 0) {
        return new Usuario($this->id_usuarios);
      }
      return null;
    }

    /**
     * Obtener todos los envases de este batch
     * @return array
     */
    public function getEnvases() {
      return Envase::getAll("WHERE id_batches_de_envases='" . $this->id . "'");
    }

    /**
     * Obtener todos los batches que tienen envases disponibles por tipo
     * @param string $tipo 'Lata' o 'Botella'
     * @return array
     */
    public static function getAllConDisponiblesByTipo($tipo) {
      $all = self::getAll("WHERE tipo='" . addslashes($tipo) . "' AND estado!='eliminado' ORDER BY creada DESC");
      $result = array();
      foreach($all as $batch) {
        $batch->calcularEnvasesDisponibles();
        if($batch->envases_disponibles > 0) {
          $result[] = $batch;
        }
      }
      return $result;
    }

    /**
     * Obtener todos los batches que tienen envases disponibles
     * @return array
     */
    public static function getAllConDisponibles() {
      $all = self::getAll("WHERE estado!='eliminado' ORDER BY creada DESC");
      $result = array();
      foreach($all as $batch) {
        $batch->calcularEnvasesDisponibles();
        if($batch->envases_disponibles > 0) {
          $result[] = $batch;
        }
      }
      return $result;
    }

    /**
     * Obtener el label del tipo para mostrar en UI
     * @return string
     */
    public function getTipoLabel() {
      $labels = array(
        'Lata' => 'Lata',
        'Botella' => 'Botella'
      );
      return isset($labels[$this->tipo]) ? $labels[$this->tipo] : $this->tipo;
    }

    /**
     * Obtener el verbo de acción según el tipo
     * @return string
     */
    public function getAccionVerbo() {
      $verbos = array(
        'Lata' => 'Enlatado',
        'Botella' => 'Embotellado'
      );
      return isset($verbos[$this->tipo]) ? $verbos[$this->tipo] : 'Envasado';
    }

  }

?>
