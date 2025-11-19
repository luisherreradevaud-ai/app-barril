<?php

  require_once "../php/app.php";

  $clientes = Cliente::getAll();
  
  foreach($clientes as $cliente) {
    $email = new Email;
    $email->enviarCorreoEstadoCuenta($cliente);
    $email->enviarCorreoInformeMorosidad($cliente);
  }

?>
