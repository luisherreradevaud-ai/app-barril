<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if(!validaIdExists($_POST,'id')) {
    die();
  }

  $pago = new Pago($_POST['id']);

  if($pago->amount == 0) {
    die();
  }

  $cliente = new Cliente($pago->id_clientes);
  $entregas = Entrega::getAll("WHERE (estado='Pagada' OR estado='Abonada') AND id_clientes='".$cliente->id."' ORDER BY id desc");

  //$usable = $pago->amount - $pago->restante;
  $usable = $pago->amount;

  foreach($entregas as $entrega) {
    if($usable == 0) {
        break;
    }
    if($entrega->abonado > $usable) {
        $entrega->abonado -= $usable;
        $entrega->estado = "Abonada";
        $usable = 0;
    } else
    if($entrega->abonado == $usable) {
        $entrega->abonado = 0;
        $entrega->estado = "Entregada";
        $usable = 0;
    } else
    if($entrega->abonado < $usable) {
        $usable -= $entrega->abonado;
        $entrega->abonado = 0;
        $entrega->estado = "Entregada";
    }
    $entrega->save();
  }

  if($pago->id_documentos != 0 && $pago->forma_de_pago == "Documento") {
    $documento = new Documento($pago->id_documentos);
    $documento->estado = "Por Aprobar";
    $documento->id_pagos = 0;
    $documento->save();
  }

  $pago->delete();

  
  

  Historial::guardarAccion("Pago #".$pago->id." ($".number_format($pago->amount).") de cliente ".$cliente->nombre." eliminado y revertido.",$GLOBALS['usuario']);

  $response["status"] = "OK";
  $response["mensaje"] = "DELETE OK";
  $response["obj"] = $pago;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
