<?php

  if($_GET == array()) {
    die();
  }

  require_once("../php/app.php");

  $activo = new Activo($_GET['id_activos']);

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $activo;
  if($activo->id_clientes_ubicacion !=0 && $activo->ubicacion == "En terreno") {
    $cliente = new Cliente($activo->id_clientes_ubicacion);
    $response["id_clientes_ubicacion"] = $cliente->id;
    $response['ubicacion'] = $cliente->nombre;
  } else {
    $response['id_clientes_ubicacion'] = 0;
    $response['ubicacion'] = "Planta";
  }
  
  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
