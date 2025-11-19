<?php

  class Mantencion extends Base {

    public $date = "";
    public $id_activos = 0;
    public $tarea = "";
    public $ejecutor = "";
    public $observaciones = "";
    public $hora_inicio;
    public $hora_termino = '00:00:00';
    public $ubicacion = "";
    public $id_clientes_ubicacion = 0;
    public $accesorios_renovados = '[]';

    public function __construct($id = null) {
      $this->tableName("mantenciones");
      if($id) {
        $this->id = $id; 
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      }
    }

    public function setSpecifics($post) {

      $activo = new Activo($this->id_activos);

      $periodicidad = "";

      if($this->tarea == "Inpeccion") {
        $periodicidad = $activo->inspeccion_periodicidad;
      }
      if($this->tarea == "Mantencion") {
        $periodicidad = $activo->mantencion_periodicidad;
      }

      $final = date("Y-m-d", strtotime("+1 month", strtotime($this->date)));
      
      if($periodicidad == "Inmediata Tras Uso") {
        $final = date("Y-m-d", strtotime("+1 day", strtotime($this->date)));
      } else
      if($periodicidad == "Semanal") {
        $final = date("Y-m-d", strtotime("+14 day", strtotime($this->date)));
      } else
      if($periodicidad == "Quincenal") {
        $final = date("Y-m-d", strtotime("+14 day", strtotime($this->date)));
      } else
      if($periodicidad == "Mensual") {
        $final = date("Y-m-d", strtotime("+1 month", strtotime($this->date)));
      } else
      if($periodicidad == "Bimestral") {
        $final = date("Y-m-d", strtotime("+2 month", strtotime($this->date)));
      } else
      if($periodicidad == "Trimestral") {
        $final = date("Y-m-d", strtotime("+3 month", strtotime($this->date)));
      } else
      if($periodicidad == "Semestral") {
        $final = date("Y-m-d", strtotime("+6 month", strtotime($this->date)));
      } else
      if($periodicidad == "Anual") {
        $final = date("Y-m-d", strtotime("+12 month", strtotime($this->date)));
      }

      if($this->tarea == "Inpeccion") {
        $activo->proxima_inspeccion = $final;
      }
      if($this->tarea == "Mantencion") {
        $activo->proxima_mantencion = $final;
      }

      $activo->save();

      if($this->id == "") {
        $this->save();
        NotificacionControl::trigger('Nueva Mantencion',$this);
      }

      $accesorios_renovados_array = $this->getAccesoriosRenovados();

      if(isset($post['accesorios_renovados']) && is_array($post['accesorios_renovados'])) {
        foreach($post['accesorios_renovados'] as $id_accesorios => $value) {
          if( $value == 'true' && !in_array($id_accesorios,$accesorios_renovados_array) ) {
            $accesorio = new Accesorio($id_accesorios);
            $accesorio->ultimo_cambio = date('Y-m-d H:i:s');
            $accesorio->save();
            $accesorios_renovados_array[] = $id_accesorios;
          }
        }
      }

      $this->setAccesoriosRenovados($accesorios_renovados_array);

    }

    public function setAccesoriosRenovados($accesorios_renovados_arr) {
      $this->accesorios_renovados = json_encode($accesorios_renovados_arr);
    }

    public function getAccesoriosRenovados() {
      return json_decode($this->accesorios_renovados);
    }
  }

 ?>
