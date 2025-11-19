<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if(!isset($_POST['id_clientes'])) {
    $response["status"] = "ERROR";
    $response["mensaje"] = "error";
    print json_encode($response,JSON_PRETTY_PRINT);
    die();
  }

  //print_r($_POST);

  $obj = new Pedido;
  $obj->setProperties($_POST);
  $obj->creada = date('Y-m-d H:i:s');
  $obj->estado = "Solicitado";
  $obj->save();

  foreach($_POST['pedido'] as $producto) {
    for($i=0;$i<$producto['cantidad_items'];$i++) {
      $pp = new PedidoProducto;
      $pp->setProperties($producto);
      $pp->id_pedidos = $obj->id;
      $pp->estado = "Solicitado";
      $pp->save();
    }
  }

  /*$email = new Email;
  $email->enviarCorreosPedido($obj);
  $email->enviarCorreosPedidoRespaldo($obj);*/

  $cliente = new Cliente($_POST['id_clientes']);

  Historial::guardarAccion("Pedido #".$obj->id." creado para cliente ".$cliente->nombre.".",$GLOBALS['usuario']);
  NotificacionControl::trigger('Nuevo Pedido',$obj);

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $obj;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
