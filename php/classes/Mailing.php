<?php

  class Mailing extends Base {

    public $nombre = '';
    public $asunto = '';
    public $mensaje = '';
    public $categoria = '';
    public $creada;

    public function __construct($id = null) {
      $this->tableName("mailing");
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
