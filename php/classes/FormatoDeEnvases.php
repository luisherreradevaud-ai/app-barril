<?php

  class FormatoDeEnvases extends Base {

    public $id = "";
    public $nombre = "";
    public $tipo = "Lata";
    public $volumen_ml = 0;
    public $estado = "activo";
    public $creada;
    public $actualizada;

    public $table_name = "formatos_de_envases";
    public $table_fields = array();

    public function __construct($id = null) {
      $this->tableName("formatos_de_envases");
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
     * Obtener todos los formatos activos por tipo
     * @param string $tipo 'Lata' o 'Botella'
     * @return array
     */
    public static function getAllByTipo($tipo) {
      return self::getAll("WHERE tipo='" . addslashes($tipo) . "' AND estado='activo' ORDER BY nombre ASC");
    }

    /**
     * Obtener todos los formatos activos
     * @return array
     */
    public static function getAllActivos() {
      return self::getAll("WHERE estado='activo' ORDER BY tipo ASC, nombre ASC");
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
     * Verificar si el formato está siendo usado
     * @return bool
     */
    public function estaEnUso() {
      $batches = BatchDeEnvases::getAll("WHERE id_formatos_de_envases='" . $this->id . "'");
      $productos = Producto::getAll("WHERE id_formatos_de_envases='" . $this->id . "'");
      return (count($batches) > 0 || count($productos) > 0);
    }

  }

?>
