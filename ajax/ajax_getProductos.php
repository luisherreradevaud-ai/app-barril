<?php

  require_once("../../php/app.php");

  $productos = Producto::getAll();
  print json_encode($productos,JSON_PRETTY_PRINT);

?>
