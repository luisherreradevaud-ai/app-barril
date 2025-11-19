<?php

require_once("Base.php");

class Visita extends Base {

  protected $cantidad = "";
  protected $fecha = "";

  public function __construct($nombre = null) {
    $this->tableName("visitas");
    $this->tableFields($this->table_name);
    $this->fecha = date('Y-m-d');
    $info = $this->getInfoDatabase('fecha');
    if($info) {
      $this->setProperties($info);
      $this->cantidad += 1;
    } else {
      $this->cantidad = 1;
    }
    $this->save();
  }

  public static function getVisitas($date = null) {
    if(!$date) {
      $date = date('Y-m-d');
    }
    $query = "SELECT
              cantidad
              FROM visitas
              WHERE fecha='".$date."'";

    $mysqli = $GLOBALS['mysqli'];
    $data = $mysqli->query($query);
    $respuesta = mysqli_fetch_array($data,MYSQLI_ASSOC);

    if(!isset($respuesta['cantidad'])) {
      return "0";
    }

    return $respuesta['cantidad'];
  }
}

 ?>
