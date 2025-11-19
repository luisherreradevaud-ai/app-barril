<?php

  class ApiResponse extends Base {

    public 

    public function __construct($id = null) {
      $this->tableName("api_response");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }
  }

 ?>
