<?php

  class Activo extends Base {

    public $nombre = "";
    public $marca = "";
    public $modelo = "";
    public $codigo = "";
    public $capacidad = "";
    public $clasificacion = "";
    public $estado = "";

    public $propietario = "";
    public $adquisicion_date = "0000-00-00";
    public $valorizacion = "";

    public $id_usuarios_control = 0;

    public $ultima_inspeccion = "0000-00-00";
    public $proxima_inspeccion = "0000-00-00";
    public $inspeccion_procedimiento = "";
    public $inspeccion_periodicidad = "";

    public $ultima_mantencion = "0000-00-00";
    public $proxima_mantencion = "0000-00-00";
    public $mantencion_procedimiento = "";
    public $mantencion_periodicidad = "";
    
    public $creada = "";
    public $id_media_header = 0;

    public $ubicacion = "En planta";
    public $id_clientes_ubicacion = 0;

    public $clase = '';
    public $id_locaciones = 0;

    public $accesorios = array();

    public $id_batches = 0;
    public $litraje = 0;

    public function __construct($id = null) {
      $this->tableName("activos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }

    public static function getClases() {

      $clases = [
        'Agitador',
        'Equipo de Cocción',
        'Equipo de Refrigeración',
        'EPP y Equipo de Seguridad',
        'Fermentador',
        'Maquinaria General',
        'Máquina Schopera',
        'Mueble',
        'Vehículo'
      ];

      return $clases;

    }

    public function getAccesorios() {
      $this->accesorios = Accesorio::getAll("WHERE id_activos='".$this->id."'");
      return $this->accesorios;
    }
  }

 ?>
