<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if(!isset($_POST['id'])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "error";
    print json_encode($response,JSON_PRETTY_PRINT);
    die();
  }

  $obj = new PedidoProducto($_POST['id']);
  $obj->estado = $_POST['estado'];
  $obj->save();

  $pedidos_productos_no_entregados = PedidoProducto::getAll("WHERE id_pedidos='".$obj->id_pedidos."' AND estado!='Entregado'");

  if(count($pedidos_productos_no_entregados) == 0) {
    $pedido = new Pedido($obj->id_pedidos);
    $pedido->estado = "Entregado";
    $pedido->save();
  }

  //Historial::guardarAccion("Barril #".$obj->id." marcado como Perdido.",$GLOBALS['usuario']);

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $obj;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
