<pre><?php

    /*
    include("app.php");


    $usuarios = Usuario::getAll("WHERE nivel='Cliente'");
    foreach($usuarios as $usuario) {
        $cliente = new Cliente($usuario->id_clientes);
        $usuario->createRelation($cliente);
    }



    for($i = 1; $i <=52; $i++) {
        $activo = new Activo;
        if($i > 9) {
            $nombre_activo = '0'.$i;
        } else {
            $nombre_activo = '00'.$i;
        }
        $activo->nombre = 'Bidón '.$nombre_activo;
        $activo->codigo = 'BD '.$nombre_activo;
        $activo->ubicacion = 'En planta';
        $activo->activo = 'Fermentador';
        $activo->capacidad = '50L';
        $activo->clasificacion = 'Crítico';
        $activo->estado = 'Activo';
        $activo->propietario = 'CC SPA.';
        $activo->adquisicion_date = date('Y-m-d');
        $activo->valorizacion = '15000';
        $activo->save();
        print_r($activo);
    }



die();


include("libredte.php");

$response = array();


for($i = 644; $i <= 649; $i++) {
    $obj = new Entrega($i);
    $cliente = new Cliente($obj->id_clientes);
      if( $cliente->emite_factura == 1 ) {
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

        LIBREDTE_enviarCorreo($dte->folio,$cliente->email);
        LIBREDTE_enviarCorreo($dte->folio,"matiashmecanico@gmail.com");

        }
        catch (Exception $e) {

        }
        
    }
}

print "<pre>";
print_r(json_encode($response));


*/

?>