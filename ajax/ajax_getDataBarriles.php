<?php

  if($_GET == array()) {
    die();
  }

  require_once("../php/app.php");

  $barril = new Barril($_GET['id_barriles']);

  $response["status"] = "OK";
  $response["mensaje"] = "OK";
  $response["obj"] = $barril;

  print json_encode($response,JSON_PRETTY_PRINT);

 ?>
