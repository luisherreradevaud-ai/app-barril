<?php

  require_once("./app.php");

  $usuario = new Usuario;
  $_SESSION = $usuario->logout();

  header("Location: ../login.php");
?>
