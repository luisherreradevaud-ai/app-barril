    <?php

    if($_POST == array()) {
        die();
    }

    require_once("./../php/app.php");

    $usuario = new Usuario;
    session_start();
    $usuario->checkSession($_SESSION);

    $batches_traspasos_anteriores = BatchTraspaso::getAll("WHERE id_batches='".$_POST['id_batches']."' ORDER BY seq_index DESC LIMTI 1");

    if(count($batches_traspasos_anteriores) > 0) {
        $seq_index = $batches_traspasos_anteriores[0]->seq_index;
    } else {
        $seq_index = 0;
    }

    $activo_1 = new Activo($_POST['id_fermentadores_inicio']);
    $activo_2 = new Activo($_POST['id_fermentadores_final']);

    $batch_activos = BatchActivo::getAll("WHERE id_batches='".$_POST['id_batches']."' AND id_activos='".$activo_1->id."' AND litraje!=0 AND estado='Fermentación'");
    
    $nuevo_batch_activo = new BatchActivo;
    $nuevo_batch_activo->id_activos = $activo_2->id;
    $nuevo_batch_activo->id_batches = $batch_activos[0]->id_batches;
    $nuevo_batch_activo->litraje = $batch_activos[0]->litraje;
    $nuevo_batch_activo->estado = 'Maduración';
    $nuevo_batch_activo->save();

    $batch_activos[0]->litraje = 0;
    $batch_activos[0]->save();

    $activo_1->id_batches = 0;
    $activo_1->save();

    $obj = new BatchTraspaso;
    $obj->id_batches = $_POST['id_batches'];
    $obj->seq_index = $seq_index;
    $obj->id_fermentadores_inicio = $_POST['id_fermentadores_inicio'];
    $obj->id_fermentadores_final = $_POST['id_fermentadores_final'];
    $obj->cantidad = $activo_1->litraje;
    $obj->date = $_POST['date'];
    $obj->hora = $_POST['hora'];
    $obj->save();

    $batch = new Batch($_POST['id_batches']);
    Historial::guardarAccion("Traspaso #".$obj->id." creado de ".$activo_1->codigo." a ".$activo_2->codigo." para Batch #".$batch->batch_nombre.".",$GLOBALS['usuario']);



    $response["status"] = "OK";
    $response["mensaje"] = "OK";
    $response["obj"] = $obj;

    print json_encode($response,JSON_PRETTY_PRINT);

    ?>
