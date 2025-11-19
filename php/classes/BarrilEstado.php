<?php

class BarrilEstado extends Base {

  public $id_barriles = 0;
  public $id_clientes = 0;
  public $inicio_date;
  public $finalizacion_date;
  public $tiempo_transcurrido = 0;
  public $estado = '';
  public $id_usuarios = 0;
  public $creada;

  public function __construct($id = null) {
    $this->tableName("barriles_estados");
    if($id){
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    } else {
      $this->creada = date('Y-m-d H:i:s');
      $this->inicio_date = date('Y-m-d H:i:s');
      $this->finalizacion_date = '0000-00-00 00:00:00'; 
    }
  }

  public function setTiempoTranscurrido() {
      $inicio = new DateTime($this->inicio_date);
      $final = new DateTime($this->finalizacion_date);
      $diferencia = $inicio->diff($final);
      $dias = $diferencia->days;
      $horas = $diferencia->h + ($diferencia->i > 0 ? 1 : 0);
      if ($dias > 1) {
          $this->tiempo_transcurrido =  $dias." días";
      } elseif ($dias == 1) {
          $this->tiempo_transcurrido = "1 día";
      } else {
          $this->tiempo_transcurrido = max($horas, 1) . " hora" . ($horas > 1 ? "s" : "");
      }
  }

}
