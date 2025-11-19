<?php

    class GastoFijo extends Base {

    public $creada;
    public $item = "";
    public $tipo_de_gasto = "";
    public $comentarios = "";
    public $visible = 0;
    public $montos;
    public $gastos_fijos_mes;


    public function __construct($id = null) {
      $this->tableName("gastos_fijos");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date('Y-m-d H:i:s');
      }
    }

    public function setSpecifics($values) {

      if(isset($values['gastos_fijos_mes'])) {
        if(is_array($values['gastos_fijos_mes'])) {
          foreach($values['gastos_fijos_mes'] as $gfm) {
            $gasto_fijo_mes = new GastoFijoMes($gfm['id']);
            $gasto_fijo_mes->setPropertiesNoId($gfm);
            $gasto_fijo_mes->save();
          }
        }
      }


      

    }

    public function deleteSpecifics($values) {
      $this->deleteAllMedia();
    }

    public function getGastosMes($mes,$ano) {
      $inicio_date = $ano."-".$mes."-01";
      $termino_date = $ano."-".$mes."-".$this->cantidadDiasMes($mes,$ano);
      $this->gastos_mes = Gasto::getAll("WHERE id_gastos_fijos='".$this->id."' AND (date BETWEEN '".$inicio_date."' AND '".$termino_date."')");
    }

    public function getTotalMes($mes,$ano) {
      $query = "WHERE id_gastos_fijos='".$this->id."' AND mes='".$mes."' AND ano='".$ano."' LIMIT 1";
      $gfm = GastoFijoMes::getAll($query);
      if(count($gfm)>0) {
        $this->montos = $gfm[0];
        $this->visible = 1;
      } else {
        $this->montos = null;
        $this->visible = 0;
      }
    }

    public function cantidadDiasMes($mes,$ano) {
      $mes = intval($mes);
      if($mes<1||$mes>12) {
        return 0;
      }
      $cantidad_dias_mes[1] = 31;
      $cantidad_dias_mes[2] = 28;
      $cantidad_dias_mes[3] = 31;
      $cantidad_dias_mes[4] = 30;
      $cantidad_dias_mes[5] = 31;
      $cantidad_dias_mes[6] = 30;
      $cantidad_dias_mes[7] = 31;
      $cantidad_dias_mes[8] = 31;
      $cantidad_dias_mes[9] = 30;
      $cantidad_dias_mes[10] = 31;
      $cantidad_dias_mes[11] = 30;
      $cantidad_dias_mes[12] = 31;
      if($ano%4==0) {
        $cantidad_dias_mes[2] = 29;
      }
      return $cantidad_dias_mes[$mes];
    }

  }

 ?>
