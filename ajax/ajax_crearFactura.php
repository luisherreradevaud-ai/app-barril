<?php

    include("../php/app.php");
    include("../php/libredte.php");

    $response = array();

    if(!validaIdExists($_POST,'id')) {
        die();
    }


    $obj = new Entrega($_POST['id']);
    $cliente = new Cliente($obj->id_clientes);
    if( $cliente->emite_factura == 1 && ($obj->factura == 0 || $obj->factura == '')) {
        $entrega_productos = EntregaProducto::getAll("WHERE id_entregas='".$obj->id."'");

        $dte = new DTE;
        $data = dataEntrada2json($cliente,$obj,$entrega_productos);

        try {
        $body = LIBREDTE_emison($data);
        $response_string = LIBREDTE_generar($body);
        $response_arr = json_decode($response_string);

        $dte->setProperties($response_arr);
        $dte->id_entregas = $obj->id;

        if($dte->folio != 0 && $dte->folio != "") {
            $dte->save();
        }
        
        $obj->factura = $dte->folio;
        $obj->save();

        LIBREDTE_enviarCorreo($dte->folio,$cliente->email);
        LIBREDTE_enviarCorreo($dte->folio,"matiashmecanico@gmail.com");

    }
    catch (Exception $e) {

    }
    
}

$response['mensaje'] = "OK";

print json_encode($response,JSON_PRETTY_PRINT);

?>