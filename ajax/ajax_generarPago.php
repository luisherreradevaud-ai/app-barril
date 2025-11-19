<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $entrega = new Entrega($_POST['id']);
  $pago = new Pago;
  $pago->creada = date('Y-m-d H:i:s');

  if($_POST['tipopago'] == "abono") {
    $pago->total = $_POST['monto'];
  } else {
    $entrega->estado = "Pagada";
    $pago->total = $entrega->monto;
  }

  $pago->amount = $pago->total;
  $pago->facturas = $entrega->factura;
  $pago->id_clientes = $entrega->id_clientes;
  $pago->ids_entregas = $entrega->id;
  $pago->forma_de_pago = "Transferencia";

  $entrega->save();
  $pago->save();

  $obj = $entrega;

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $obj;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
