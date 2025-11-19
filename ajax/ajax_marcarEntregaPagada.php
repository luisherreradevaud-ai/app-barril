<?php

  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $entrega = new Entrega($_POST['id']);
  $entrega->estado = "Pagada";
  $entrega->save();

  $cliente = new Cliente($entrega->id_clientes);
  $obj = $cliente;

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $obj;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
