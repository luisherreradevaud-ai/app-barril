<?php

  class TipoDeNotificacion extends Base {

    public $nombre = "";
    public $texto = "";

    public function __construct($id = null) {
      $this->tableName("tipos_de_notificaciones");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }

  }
?>
