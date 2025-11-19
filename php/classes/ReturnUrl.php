<?php

  class ReturnUrl extends Base {

    public $hash = 0;
    public $return_url = '';
    public $creada;

    public function __construct($id = null) {
      $this->tableName("return_urls");
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
