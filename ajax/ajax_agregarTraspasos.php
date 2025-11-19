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

    $batches_traspasos = BatchTraspaso::getAll("WHERE id_batches='".$obj->id."'");
    foreach($batches_traspasos as $bt) {
        $bt->delete();
    }

    if(isset($_POST['traspasos']) && is_array($_POST['traspasos'])) {
        foreach($_POST['traspasos'] as $l_key => $traspaso) {

          $batch_traspaso = new BatchTraspaso;
          $batch_traspaso->id_batches = $obj->id;
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
    
    $obj->save();

    Historial::guardarAccion(get_class($obj)." #".$obj->id." ".$accion.".",$GLOBALS['usuario']);



    $response["status"] = "OK";
    $response["mensaje"] = "OK";
    $response["obj"] = $obj;

    print json_encode($response,JSON_PRETTY_PRINT);

    ?>
