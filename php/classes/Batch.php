<?php

  class Batch extends Base {

    public $batch_date;
    public $batch_id_usuarios_cocinero = 0;
    public $id_recetas = 0;
    public $batch_nombre = '';
    public $batch_litros = 0;

    public $licor_temperatura = 0;
    public $licor_ph = 0;
    public $licor_litros = 0;

    public $maceracion_hora_inicio;
    public $maceracion_temperatura = 0;
    public $maceracion_litros = 0;
    public $maceracion_ph = 0;
    public $maceracion_hora_finalizacion;

    public $lavado_de_granos_hora_inicio;
    public $lavado_de_granos_mosto = 0;
    public $lavado_de_granos_densidad = 0;
    public $lavado_de_granos_tipo_de_densidad = '';
    public $lavado_de_granos_hora_termino;

    public $coccion_ph_inicial = 0;
    public $coccion_ph_final = 0;
    public $coccion_recilar = 0;

    public $combustible_gas = 0;

    public $inoculacion_temperatura = 0;

    public $fermentacion_date;
    public $fermentacion_hora_inicio;
    public $inoculacion_temperatura_inicio = 0;

    public $fermentacion_id_activos = 0;
    public $fermentacion_temperatura = 0;
    public $fermentacion_hora_finalizacion;
    public $fermentacion_ph = 0;
    public $fermentacion_densidad = 0;
    public $fermentacion_tipo_de_densidad = '';
    public $fermentacion_finalizada = 0;
    public $fermentacion_finalizada_datetime;

    public $traspaso_datetime;

    public $maduracion_date;
    public $maduracion_temperatura_inicio = 0;
    public $maduracion_hora_inicio;
    public $maduracion_temperatura_finalizacion = 0;
    public $maduracion_hora_finalizacion;

    public $datetime_finalizacion;
    
    public $observaciones = "";
    public $creada;
    public $etapa_seleccionada = 'batch';
    public $tipo = 'Batch';

    public $finalizacion_date = '';

    public function __construct($id = null) {
      $this->tableName("batches");
      if($id) {
        $this->id = $id;
        $info = $this->getInfoDatabase('id');
        $this->setProperties($info);
      } else {
        $this->creada = date("Y-m-d H:i:s");
        $this->batch_date = date('Y-m-d'); 
      }
    }

    public function setSpecifics($post) {

      if($this->id == "") {
        $this->save();
        //NotificacionControl::trigger('Nuevo Batch',$obj);
      } else {
        $batches_insumos = BatchInsumo::getAll("WHERE id_batches='".$this->id."'");
        foreach($batches_insumos as $bi) {
          $insumo = new Insumo($bi->id_insumos);
          $insumo->bodega += $bi->cantidad;
          $insumo->save();
          $bi->delete();
        }
        $batches_lupulizaciones = BatchLupulizacion::getAll("WHERE id_batches='".$this->id."'");
        foreach($batches_lupulizaciones as $bl) {
          $bl->delete();
        }
        $batches_enfriados = BatchEnfriado::getAll("WHERE id_batches='".$this->id."'");
        foreach($batches_enfriados as $be) {
          $be->delete();
        }
        $batches_traspasos = BatchTraspaso::getAll("WHERE id_batches='".$this->id."'");
        foreach($batches_traspasos as $bt) {
          $bt->delete();
        }

        $batches_activos = BatchActivo::getAll("WHERE id_batches='".$this->id."'");
        foreach($batches_activos as $bt) {
          $fermentador = new Activo($bt->id_activos);
          $fermentador->id_batches = 0;
          $fermentador->save();
          $bt->delete();
        }
      }

      if(isset($post['insumos']) && is_array($post['insumos'])) {
        foreach($post['insumos'] as $etapa_key => $etapa) {
          foreach($etapa as $insumo) {

            $batch_insumo = new BatchInsumo;
            $batch_insumo->id_batches = $this->id;
            $batch_insumo->id_insumos = $insumo['id'];
            $batch_insumo->cantidad = $insumo['cantidad'];
            $batch_insumo->tipo = "Receta";
            $batch_insumo->etapa = $etapa_key;
            $batch_insumo->etapa_index = $insumo['etapa_index'];
            $batch_insumo->date = date('Y-m-d');
            $batch_insumo->save();

            $insumo = new Insumo($batch_insumo->id_insumos);
            $insumo->bodega -= $batch_insumo->cantidad;
            $insumo->save();

          }
        }
      }

      if(isset($post['lupulizaciones']) && is_array($post['lupulizaciones'])) {
        foreach($post['lupulizaciones'] as $l_key => $lupulizacion) {

          $batch_lupulizacion = new BatchLupulizacion;
          $batch_lupulizacion->id_batches = $this->id;
          $batch_lupulizacion->seq_index = $l_key;
          $batch_lupulizacion->tipo = $lupulizacion['tipo'];
          $batch_lupulizacion->date = $lupulizacion['date'];
          $batch_lupulizacion->hora = $lupulizacion['hora'];
          $batch_lupulizacion->save();
        }
      }


      if(isset($post['enfriados']) && is_array($post['enfriados'])) {
        foreach($post['enfriados'] as $l_key => $enfriado) {

          $batch_enfriado = new BatchEnfriado;
          $batch_enfriado->id_batches = $this->id;
          $batch_enfriado->seq_index = $l_key;
          $batch_enfriado->temperatura_inicio = $enfriado['temperatura_inicio'];
          $batch_enfriado->ph = $enfriado['ph'];
          $batch_enfriado->densidad = $enfriado['densidad'];
          $batch_enfriado->ph_enfriado = $enfriado['ph_enfriado'];
          $batch_enfriado->date = $enfriado['date'];
          $batch_enfriado->hora_inicio = $enfriado['hora_inicio'];
          $batch_enfriado->save();

        }
      }

      if(isset($post['traspasos']) && is_array($post['traspasos'])) {
        foreach($post['traspasos'] as $l_key => $traspaso) {



          $batch_traspaso = new BatchTraspaso;
          $batch_traspaso->id_batches = $this->id;
          $batch_traspaso->seq_index = $l_key;
          $batch_traspaso->id_fermentadores_inicio = $traspaso['id_fermentadores_inicio'];
          $batch_traspaso->id_fermentadores_final = $traspaso['id_fermentadores_final'];
          $batch_traspaso->cantidad = $traspaso['cantidad'];
          $batch_traspaso->date = $traspaso['date'];
          $batch_traspaso->hora = $traspaso['hora'];
          $batch_traspaso->save();
          //print_r($batch_traspaso);


        }
      }

      if(isset($post['fermentacion_fermentadores']) && is_array($post['fermentacion_fermentadores'])) {
        foreach($post['fermentacion_fermentadores'] as $l_key => $traspaso) {
          $batch_traspaso = new BatchActivo;
          $batch_traspaso->setPropertiesNoId($traspaso);
          $batch_traspaso->save();
          $fermentador = new Activo($batch_traspaso->id_activos);
          $fermentador->id_batches = $this->id;
          $fermentador->save();
        }
      }
      

      /*$receta = new Receta($this->id_recetas);
      foreach($receta->insumos_arr as $ri) {

        $insumo = new Insumo($ri->id_insumos);
        $insumo->bodega -= $ri->cantidad;
        $insumo->save();

        $batch_insumo = new BatchInsumo;
        $batch_insumo->id_batches = $this->id;
        $batch_insumo->id_insumos = $ri->id_insumos;
        $batch_insumo->cantidad = $ri->cantidad;
        $batch_insumo->tipo = "Receta";
        $batch_insumo->save();

      }*/

      /*if(isset($post['dryhop'])) {
        if(is_array($post['dryhop'])) {
          foreach($post['dryhop'] as $dh) {
            $insumo = new Insumo($dh['id']);
            $insumo->bodega -= $dh['cantidad'];
            $insumo->save();
            $batch_insumo = new BatchInsumo;
            $batch_insumo->id_batches = $this->id;
            $batch_insumo->id_insumos = $dh['id'];
            $batch_insumo->cantidad = $dh['cantidad'];
            $batch_insumo->tipo = "Dryhop";
            $batch_insumo->date = $dh['date'];
            $batch_insumo->save();
          }
        }
      }*/

      /*$batches_barriles_anteriores = $this->getRelations("barriles");
      foreach($batches_barriles_anteriores as $bba) {
        $barril = new Barril($bba);
        $barril->id_batches = 0;
        $barril->estado = "En planta";
        $barril->save();
        $this->deleteRelation($barril);
      }

      $batches_cajas_anteriores = BatchCaja::getAll("WHERE id_batches='".$this->id."'");
      foreach($batches_cajas_anteriores as $bca) {
        $bca->delete();
      }

      if(!isset($post['productos'])) {
        return false;
      }

      if(!is_array($post['productos'])) {
        return false;
      }

      if(count($post['productos']) == 0) {
        return false;
      }

      foreach($post['productos'] as $producto) {
        if($producto['tipo'] == "Barril") {
          $barril = new Barril($producto['id_barriles']);
          $barril->id_batches = $this->id;
          $barril->estado = "En sala de frio";
          $barril->save();
          $this->createRelation($barril);
        } else
        if($producto['tipo'] == "Caja") {
          $batch_caja = new BatchCaja;
          $batch_caja->id_batches = $this->id;
          $batch_caja->cantidad = $producto['cantidad'];
          $batch_caja->save();
        }
      }

      $recetas = Recetas::getAll();
      foreach($recetas as $receta) {
        foreach($receta->insumos_arr as $ri) {

          $insumo = new Insumo($ri->id_insumos);
          if($insumo->bodega < $ri->cantidad) {
            NotificacionControl::trigger('Insumos insuficientes para Batches',$receta);
            break;
          }

        }

      }*/

    }

    public function deleteSpecifics($values) {

      $batches_insumos = BatchInsumo::getAll("WHERE id_batches='".$this->id."'");
      foreach($batches_insumos as $bi) {
        $insumo = new Insumo($bi->id_insumos);
        $insumo->bodega += $bi->cantidad;
        $insumo->save();
        $bi->delete();
      }
      
      $batches_lupulizaciones = BatchLupulizacion::getAll("WHERE id_batches='".$this->id."'");
      foreach($batches_lupulizaciones as $bl) {
        $bl->delete();
      }
      $batches_enfriados = BatchEnfriado::getAll("WHERE id_batches='".$this->id."'");
      foreach($batches_enfriados as $be) {
        $be->delete();
      }
      $batches_traspasos = BatchTraspaso::getAll("WHERE id_batches='".$this->id."'");
      foreach($batches_traspasos as $bt) {
        $bt->delete();
      }

    }

    public function agregarActivo($data) {

      $fermentador = new Activo($data['id_activos']);

      $batch_activo = new BatchActivo;
      $batch_activo->setProperties($data);
      $batch_activo->id_batches = $this->id;
      $batch_activo->litraje = $fermentador->litraje;
      $batch_activo->save();

      $fermentador->id_batches = $this->id;
      $fermentador->save();

      return $batch_activo;

    }

    public function editarActivo($data) {

      if(empty($data['id'])) return;
      
      $batch_activo = new BatchActivo($data['id']);

      if($data['id_activos'] != $batch_activo->id_activos) {
        $fermentador_anterior = new Activo($batch_activo->id_activos);
        $fermentador_anterior->id_batches = 0;
        $fermentador_anterior->save();
      }

      $fermentador = new Activo($data['id_activos']);

      $batch_activo->setProperties($data);
      $batch_activo->id_batches = $this->id;
      $batch_activo->litraje = $fermentador->litraje;
      $batch_activo->save();
      
      $fermentador->id_batches = $this->id;
      $fermentador->save();

    }

    public function eliminarActivo($data) {

      if(!isset($data['id_batches_activos'])) {
        return false;
      }

      $batch_activo = new BatchActivo($data['id_batches_activos']);

      $fermentador_anterior = new Activo($batch_activo->id_activos);
      $fermentador_anterior->id_batches = 0;
      $fermentador_anterior->save();

      $batch_activo->delete();

      
    }

  }

 ?>
