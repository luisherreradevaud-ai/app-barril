<?php

  require_once("../../php/app.php");
  $date = $_GET['date'];

  $caja = Venta::getAll("WHERE fecha BETWEEN '".$date." 00:00:00' AND '".$date." 23:59:59'");
  print json_encode($caja,JSON_PRETTY_PRINT);

?>
