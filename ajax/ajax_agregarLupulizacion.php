    <?php

    if($_POST == array()) {
        die();
    }

    require_once("./../php/app.php");

    $usuario = new Usuario;
    session_start();
    $usuario->checkSession($_SESSION);

    if(!isset($_POST['entidad'])) {
        print "error";
        die();
    }

    $entidad = $_POST['entidad'];

    if(validaIdExists($_POST,'id')) {
        $accion = "modificado";
    } else {
        $accion = "creado";
    }

    $obj = createObjFromTableName($entidad,$_POST['id']);
    $obj->setPropertiesNoId($_POST);

    $batches_insumos = BatchInsumo::getAll("WHERE id_batches='".$obj->id."' AND etapa='lupulizacion'");
    foreach($batches_insumos as $bi) {
        $insumo = new Insumo($bi->id_insumos);
        $insumo->bodega += $bi->cantidad;
        $insumo->save();
        $bi->delete();
    }

    $batches_lupulizaciones = BatchLupulizacion::getAll("WHERE id_batches='".$obj->id."'");
    foreach($batches_lupulizaciones as $bl) {
        $bl->delete();
    }

    if(isset($_POST['insumos']) && is_array($_POST['insumos'])) {
        foreach($_POST['insumos'] as $etapa_key => $etapa) {
            foreach($etapa as $insumo) {

                $batch_insumo = new BatchInsumo;
                $batch_insumo->id_batches = $obj->id;
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

    if(isset($_POST['lupulizaciones']) && is_array($_POST['lupulizaciones'])) {
        foreach($_POST['lupulizaciones'] as $l_key => $lupulizacion) {

            $batch_lupulizacion = new BatchLupulizacion;
            $batch_lupulizacion->id_batches = $obj->id;
            $batch_lupulizacion->seq_index = $l_key;
            $batch_lupulizacion->tipo = $lupulizacion['tipo'];
            $batch_lupulizacion->date = $lupulizacion['date'];
            $batch_lupulizacion->hora = $lupulizacion['hora'];
            $batch_lupulizacion->save();
            //print_r($batch_lupulizacion);
        }
      }
    
    $obj->save();

    Historial::guardarAccion(get_class($obj)." #".$obj->id." ".$accion.".",$GLOBALS['usuario']);



    $response["status"] = "OK";
    $response["mensaje"] = "OK";
    $response["obj"] = $obj;

    print json_encode($response,JSON_PRETTY_PRINT);

    ?>
