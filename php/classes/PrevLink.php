<?php

  class PrevLink extends Base {

    public $id_usuarios = 0;
    public $url = "";
    public $datetime;
    public $count = 0;
    public $id_secciones = 0;

    public function __construct($id = null) {
      $this->tableName("prevlinks");
      if($id) {
        $this->getFromDatabase('id',$id);
      }
    }

    public function deleteAnteriores() {
      $this->runQuery("DELETE FROM prevlinks WHERE datetime < DATE_SUB(SYSDATE(), INTERVAL 1 DAY)");
    }

    public static function create() {
      $prevlink = new PrevLink;
      return $prevlink;
    }

    

  }
?>
