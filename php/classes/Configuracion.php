<?php

  class Configuracion extends Base {

    public $id_media_header = 0;
    public $nombre_empresa = "";
    public $duracion_notificaciones;
    public $duracion_historial;
    public $login_color_fondo = "";
    public $login_color_texto = "";
    public $email_header = "";
    public $email_footer = "";
    public $email_empresa = "";
    public $telefono_empresa	= "";
    public $direccion_empresa = "";
    public $representante_empresa = "";
    public $rut_empresa = "";
    public $giro_empresa = "";

    public function __construct($id = null) {
      $this->tableName("configuraciones");
      $this->tableFields($this->table_name);
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }
  }

 ?>
