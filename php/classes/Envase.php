<?php

  class Envase extends Base {

    public $id = "";
    public $id_formatos_de_envases = 0;
    public $volumen_ml = 0;
    public $id_batches_de_envases = 0;
    public $id_batches = 0;
    public $id_barriles = 0;
    public $id_activos = 0;
    public $id_cajas_de_envases = 0;
    public $estado = "Envasado";
    public $creada;
    public $actualizada;

    public $table_name = "envases";
    public $table_fields = array();

    public function __construct($id = null) {
      $this->tableName("envases");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
        $this->actualizada = date('Y-m-d H:i:s');
      }
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
     * Obtener el batch de envases al que pertenece
     * @return BatchDeEnvases|null
     */
    public function getBatchDeEnvases() {
      if($this->id_batches_de_envases > 0) {
        return new BatchDeEnvases($this->id_batches_de_envases);
      }
      return null;
    }

    /**
     * Obtener la caja a la que pertenece
     * @return CajaDeEnvases|null
     */
    public function getCajaDeEnvases() {
      if($this->id_cajas_de_envases > 0) {
        return new CajaDeEnvases($this->id_cajas_de_envases);
      }
      return null;
    }

    /**
     * Verificar si el envase está disponible (no asignado a una caja)
     * @return bool
     */
    public function estaDisponible() {
      return ($this->id_cajas_de_envases == 0 || empty($this->id_cajas_de_envases));
    }

    /**
     * Contar envases disponibles por batch
     * @param int $id_batch_de_envases
     * @return int
     */
    public static function contarDisponiblesPorBatch($id_batch_de_envases) {
      $mysqli = $GLOBALS['mysqli'];
      $query = "SELECT COUNT(*) as total FROM envases WHERE id_batches_de_envases='" . intval($id_batch_de_envases) . "' AND (id_cajas_de_envases=0 OR id_cajas_de_envases='') AND estado!='eliminado'";
      $result = $mysqli->query($query);
      $row = mysqli_fetch_assoc($result);
      return intval($row['total']);
    }

    /**
     * Obtener envases disponibles por batch
     * @param int $id_batch_de_envases
     * @param int $limit
     * @return array
     */
    public static function getDisponiblesPorBatch($id_batch_de_envases, $limit = 0) {
      $where = "WHERE id_batches_de_envases='" . intval($id_batch_de_envases) . "' AND (id_cajas_de_envases=0 OR id_cajas_de_envases='') AND estado!='eliminado'";
      if($limit > 0) {
        $where .= " LIMIT " . intval($limit);
      }
      return self::getAll($where);
    }

  }

?>
