<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if($usuario->nivel  != "Administrador" || !validaIdExists($_POST,'id') || !isset($_POST['accion'])) {
    $response["status"] = "Error";
    $response["mensaje"] = "Error";
    print json_encode($response,JSON_PRETTY_PRINT);
    die();
  }


  if($_POST['accion'] == "Aprobado") {

    $obj = new Documento($_POST['id']);
    $obj->estado = "Aprobado";
    $obj->datetime_aprobado = date('Y-m-d H:i:s');
    $obj->save();

    $pago = new Pago;
    $pago->amount = $obj->monto;
    $pago->total = $obj->monto;
    $pago->id_clientes = $obj->id_clientes;
    $pago->forma_de_pago = "Documento";
    $pago->id_usuarios = $usuario->id;
    $pago->id_documentos = $obj->id;
    $pago->save();

    $obj->id_pagos = $pago->id;
    $obj->save();

    $entregas_sin_pagar = Entrega::getAll("WHERE id_clientes='".$obj->id_clientes."'  AND estado!='Pagada' ORDER BY id asc");

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

    $cliente = new Cliente($pago->id_clientes);

    Historial::guardarAccion(get_class($obj)." #".$obj->id." aprobado. Se ha generado un pago de $".number_format($pago->amount)." al cliente ".$cliente->nombre.".",$GLOBALS['usuario']);

    $response["status"] = "OK";
    $response["mensaje"] = "OK";
    $response["obj"] = $obj;

    print json_encode($response,JSON_PRETTY_PRINT);

  } else

  if($_POST['accion'] == "Rechazado") {

    $obj = new Documento($_POST['id']);
    $obj->estado = "Rechazado";
    $obj->datetime_aprobado = date('Y-m-d H:i:s');
    $obj->save();

    Historial::guardarAccion(get_class($obj)." #".$obj->id." rechazado.",$GLOBALS['usuario']);

    $response["status"] = "OK";
    $response["mensaje"] = "OK";
    $response["obj"] = $obj;
    print json_encode($response,JSON_PRETTY_PRINT);

  } else {
    print_r($_POST);

  }

  

 ?>
