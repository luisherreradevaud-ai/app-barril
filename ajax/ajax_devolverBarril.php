<?php

  if($_POST == array()) {
    die();
  }

  

  require_once("../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  $barril = new Barril($_POST['id_barriles']);
  
  if($_POST['accion'] == "Reemplazar" && validaIdExists($_POST,'id_barriles_cambiar') && $barril->id_clientes != 0 && $barril->estado != "En planta") {

    $entregas_productos = EntregaProducto::getAll("WHERE id_barriles='".$barril->id."' ORDER BY id desc LIMIT 1");

    if(count($entregas_productos)>0) {

      $barril_cambiar = new Barril($_POST['id_barriles_cambiar']);
      $barril_cambiar->id_clientes = $barril->id_clientes;
      $barril_cambiar->estado = "En terreno";
      $barril_cambiar->save();

      $ep = $entregas_productos[0];
      $ep->codigo = $barril_cambiar->codigo;
      $ep->id_barriles = $barril_cambiar->id;
      $ep->save();

      $entrega = new Entrega($ep->id_entregas);

      Historial::guardarAccion("Barril #".$barril->id." Codigo '".$barril->codigo."' devuelto a Planta y cambiado por Barril #".$barril_cambiar->id." Codigo '".$barril_cambiar->codigo."'.<br/>Justificaci&oacute;n: ".$_POST['motivo'].".",$GLOBALS['usuario']);

      $barril_reemplazo = new BarrilReemplazo;
      $barril_reemplazo->id_barriles_devuelto = $barril->id;
      $barril_reemplazo->id_barriles_reemplazo = $barril_cambiar->id;
      $barril_reemplazo->motivo = $_POST['motivo'];
      $barril_reemplazo->id_entregas_productos = $ep->id;
      $barril_reemplazo->id_entregas = $entrega->id;
      $barril_reemplazo->id_clientes = $entrega->id_clientes;
      $barril_reemplazo->save();

    }
    
  }

  if($barril->estado == "En despacho") {
    $dp = new DespachoProducto;
    $dp->getFromDatabase('id_barriles',$barril->id);
    if($dp->id!="") {
      if($dp->id_pedidos_productos == 0) {
        $pedido_producto = new PedidoProducto($dp->id_pedidos_productos);
        $pedido_producto->estado = 'Solicitado';
        $pedido_producto->save();
      }
      $dp->delete();
    }
  } else {
    $barril->id_batches = 0;
    $barril->litros_cargados = 0;
  }

  $barril->estado = "En planta";
  $barril->id_clientes = 0;
  $barril->registrarCambioDeEstado();
  $barril->save();

  Historial::guardarAccion("Barril #".$barril->id." Codigo '".$barril->codigo."' devuelto a Planta.",$GLOBALS['usuario']);

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $barril;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
