<?php

    if($_POST == array()) {
    die();
    }



    require_once("../php/app.php");

    $usuario = new Usuario;
    session_start();
    $usuario->checkSession($_SESSION);

    $barril_reemplazo = new BarrilReemplazo($_POST['id']);
    $barril = new Barril($barril_reemplazo->id_barriles_reemplazo);
  
    $entregas_productos = EntregaProducto::getAll("WHERE id_barriles='".$barril->id."' ORDER BY id desc LIMIT 1");

    if(count($entregas_productos)>0) {

        $barril_cambiar = new Barril($barril_reemplazo->id_barriles_devuelto);
        $barril_cambiar->id_clientes = $barril->id_clientes;
        $barril_cambiar->estado = "En terreno";
        $barril_cambiar->registrarCambioDeEstado();
        $barril_cambiar->save();

        $ep = $entregas_productos[0];
        $ep->codigo = $barril_cambiar->codigo;
        $ep->id_barriles = $barril_cambiar->id;
        $ep->save();

        Historial::guardarAccion("Reemplazo de Barriles #".$barril_reemplazo->id." revertido.",$GLOBALS['usuario']);

        $barril_reemplazo->save();
        $barril->estado = "En planta";
        $barril->id_clientes = 0;
        $barril->id_batches = 0;
        $barril->registrarCambioDeEstado();
        $barril->save();

    }

    $barril_reemplazo->delete();
    
    $response["status"] = "OK";
    $response["mensaje"] = "OK";
    $response["obj"] = $barril;

    print json_encode($response,JSON_PRETTY_PRINT);

 ?>
