    <?php

    if($_POST == array()) {
        die();
    }

    require_once("./../php/app.php");

    $usuario = new Usuario;
    session_start();
    $usuario->checkSession($_SESSION);

    if(!validaIdExistsVarios($_POST,['cantidad_a_cargar','id_batches_activos','id_barriles'])) {
        die();
    }

    $batch_activo = new BatchActivo($_POST['id_batches_activos']);
    $activo = new Activo($batch_activo->id_activos);
    $barril = new Barril($_POST['id_barriles']);

    $batch_activo->litraje -= $_POST['cantidad_a_cargar'];
    $barril->litros_cargados += $_POST['cantidad_a_cargar'];
    $barril->id_batches = $batch_activo->id_batches;
    $barril->id_activos = $batch_activo->id_activos;
    $barril->id_batches_activos = $batch_activo->id;

    $batch_activo->save();
    $barril->save();

    $batch = new Batch($batch_activo->id_batches);
    Historial::guardarAccion("Barril ".$barril->codigo." cargado con ".$_POST['cantidad_a_cargar']." litros de ".$activo->codigo." para Batch #".$batch->batch_nombre.".",$GLOBALS['usuario']);

    $cdd = new CentralDeDespacho;
    $response["status"] = "OK";
    $response["mensaje"] = "OK";
    $response["obj"] = $cdd->getDataPage();
    $response['msg_content'] = "Barril ".$barril->codigo." cargado con ".$_POST['cantidad_a_cargar']." litros de ".$activo->codigo.".";

    print json_encode($response,JSON_PRETTY_PRINT);

    ?>
