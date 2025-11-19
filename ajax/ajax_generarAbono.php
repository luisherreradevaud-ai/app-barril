<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  $cliente = new Cliente($_POST['id']);
  $pago = new Pago;
  $pago->creada = date('Y-m-d H:i:s');

  if($_POST['tipopago'] == "abono") {
    $pago->total = $_POST['monto'];
  }

  $pago->amount = $pago->total;
  $pago->id_clientes = $cliente->id;
  $pago->forma_de_pago = "Transferencia";
  $pago->id_usuarios = $_POST['id_usuarios'];

  $entregas_sin_pagar = Entrega::getAll("WHERE id_clientes='".$cliente->id."'  AND estado!='Pagada' ORDER BY id asc");

  $pago->restante = $pago->total;

  foreach($entregas_sin_pagar as $entrega) {

    $monto_entrega_restante = $entrega->monto - $entrega->abonado;

    if($pago->restante >= $monto_entrega_restante) {

      $entrega->estado = "Pagada";
      $entrega->abonado = $entrega->monto;
      $entrega->datetime_abonado = date('Y-m-d H:i:s');
      $entrega->save();

      $pago->restante -= $monto_entrega_restante;

    } else {

      $entrega->estado = "Abonada";
      $entrega->abonado = $entrega->abonado + $pago->restante;
      
      if($entrega->abonado == $entrega->monto) {
        $entrega->estado = "Pagada";
      }
      $entrega->datetime_abonado = date('Y-m-d H:i:s');
      $entrega->save();

      $pago->restante = 0;
      break;

    }

  }

  $pago->save();

  //Historial::guardarAccion("Pago #".$pago->id." ($".number_format($pago->amount).") de cliente ".$cliente->nombre." ingresado.",$GLOBALS['usuario']);

  $obj = $cliente;

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $obj;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
