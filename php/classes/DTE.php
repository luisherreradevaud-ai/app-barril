<?php

  class DTE extends Base {

    public $folio = 0;
    public $emisor = 0;
    public $telefono = 0;
    public $receptor = 0;
    public $dte = 0;
    public $certificacion = 0;
    public $tasa = 0;
    public $fecha;
    public $neto = 0;
    public $iva = 0;
    public $total = 0;
    public $usuario = 0;
    public $track_id = 0;
    public $fecha_hora_creacion;
    public $id_entregas = 0;

    public function __construct($id = null) {
      $this->tableName("dte");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }
  }

 ?>
