<?php

    require_once "../php/app.php";
    require_once "../php/libredte.php";

    if(!validaIdExists($_GET,'folio')) {
        die();
    }

    $usuario = new Usuario;
    session_start();

    $usuario->checkSession($_SESSION);
    $_SESSION['login'] = "cocholgue";

    if(!validaIdExists($_SESSION,'id')) {
        header("Location: ../login.php");
    }

    $entrega = new Entrega;
    $entrega->getFromDatabase('factura',$_GET['folio']);

    /*if($entrega->id == "" || $usuario->id_clientes != $entrega->id_clientes) {
        die();
    }*/

    $cliente = new Cliente($entrega->id_clientes);

    $filename = 'Factura N°'.$entrega->factura.' Entrega #'.$entrega->id.' | Cerveza Cocholgue a '.$cliente->nombre;

    header('Content-type: application/pdf');
    header('Content-Disposition: inline; filename='.$filename.'.pdf');

    print LIBREDTE_getPDF($_GET['folio']);


?>