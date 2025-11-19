<?php

  class Notificacion extends Base {

    public $id_usuarios = 0;
    public $link = "";
    public $texto;
    public $vista = 0;
    public $vista_datetime = "0000-00-00 00:00:00";
    public $creada;

    public function __construct($id = null) {
      $this->tableName("notificaciones");
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
