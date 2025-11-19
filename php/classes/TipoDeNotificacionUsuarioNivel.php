<?php

  class TipoDeNotificacionUsuarioNivel extends Base {

    public $id_usuarios_niveles = 0;
    public $id_tipos_de_notificaciones = 0;
    public $app = 0;
    public $email = 0;

    public function __construct($id = null) {
      $this->tableName("notificaciones_usuarios_niveles");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }

  }
?>
