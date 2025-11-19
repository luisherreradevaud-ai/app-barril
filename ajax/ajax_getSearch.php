<?php

  require_once("../php/app.php");

  $term = $_GET['term'];

  $clientes = Cliente::getAll("WHERE nombre LIKE '%".$term."%' OR email LIKE '%".$term."%'");
  $activos = Activo::getAll("WHERE nombre LIKE '%".$term."%' OR marca LIKE '%".$term."%' OR modelo LIKE '%".$term."%' OR codigo LIKE '%".$term."%'");
  $usuarios = Usuario::getAll("WHERE nombre LIKE '%".$term."%' OR email LIKE '%".$term."%'");
  $barriles = Barril::getAll("WHERE codigo LIKE'%".$term."%'");

  $response = array();

  foreach($clientes as $r) {
    $response[] = array(
      "titulo" => "Cliente: <b>".$r->nombre."</b>",
      "link" => "./?s=resumen-cliente&id=".$r->id
    );
  }

  foreach($activos as $r) {
    $response[] = array(
      "titulo" => "Activo: <b>".$r->nombre." ".$r->marca." ".$r->modelo." ".$r->codigo."</b>",
      "link" => "./?s=detalle-activos&id=".$r->id
    );
  }

  foreach($usuarios as $r) {
    $response[] = array(
      "titulo" => "Usuario: <b>".$r->nombre."</b>",
      "link" => "./?s=detalle-usuarios&id=".$r->id
    );
  }

  foreach($barriles as $r) {
    $response[] = array(
      "titulo" => "Barril: <b>".$r->codigo."</b>",
      "link" => "./?s=detalle-barriles&id=".$r->id
    );
  }


  print json_encode($response,JSON_PRETTY_PRINT);

?>
