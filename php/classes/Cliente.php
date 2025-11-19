<?php

  class Cliente extends Base {

    public $nombre = '';
    public $email = '';
    public $telefono = '';
    public $creada;
    public $estado = 'Activo';
    public $criterio = '';
    public $precio_tripack = 0;
    public $precio_24 = 0;
    public $emite_factura = 0;
    public $RUT = '';
    public $RznSoc = '';
    public $Giro = '';
    public $Dir = '';
    public $Cmna = '';
    public $meta_barriles_mensuales = 0;
    public $meta_cajas_mensuales = 0;
    public $id_usuarios_vendedor = 0;
    public $salidas_habilitadas = 0;

    public function __construct($id = null) {
      $this->tableName("clientes");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }
  }

 ?>
