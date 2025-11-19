<?php

    require_once "app.php";
    require_once "libredte.php";

    if(!validaIdExists($_GET,'folio')) {
        die();
    }

    $entrega = new Entrega;
    $entrega->getFromDatabase('factura',$_GET['folio']);

    if($entrega->id == "") {
        die();
    }

    $cliente = new Cliente($entrega->id_clientes);

    $filename = 'Factura N°'.$entrega->factura.' Entrega #'.$entrega->id.' | Cerveza Cocholgue a '.$cliente->nombre;

    header('Content-type: application/pdf');
    header('Content-Disposition: inline; filename='.$filename.'.pdf');

    print LIBREDTE_getPDF($_GET['folio']);


?>