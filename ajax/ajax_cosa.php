<?php

    require_once "../php/app.php";

    $reparto = new Reparto(20);
    $cliente = new Cliente($reparto->id_clientes);

    $clientes = Cliente::getAll("WHERE visible=1");


?>