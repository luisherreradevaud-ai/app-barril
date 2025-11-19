<?php

  $token_bhexpress = "0ade65d3ed5aac21fdd03f9e06506015addfef1d";
  $rut_emisor = "76485421-7";

  $httpheader = array(
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Basic '.base64_encode('rdwHeaDpJCERrBQvbSkglZcFq4rbGrUF:X')
  );

  function dataEntrada2json($cliente,$entrega,$entrega_productos) {

    $rut_emisor = $GLOBALS['rut_emisor'];

    $detalles = array();

    $entrega_productos_2 = array();

    foreach($entrega_productos as $ep) {
      $exists = 0;
      foreach($entrega_productos_2 as $ep_2) {
        if(!is_object($ep_2)) {
          continue;
        }
        if($ep_2->id_productos == $ep->id_productos) {
          $ep_2->QtyItem += 1;
          $exists = 1;
        }
      }
      if(!$exists) {
        $entrega_productos_2[] = $ep;
      }
    }

    foreach($entrega_productos_2 as $ep) {

      $producto = new Producto($ep->id_productos);
      $precio = $producto->getClienteProductoPrecio($cliente->id);

      foreach($producto->productos_items as $pi) {

        $monto = $pi->monto_bruto;

        if($pi->impuesto == "IVA + ILA") {

          if($producto->monto != $precio) {
            $monto = round($pi->monto_bruto - (($producto->monto - $precio) / 1.395),1);
          }

          if($monto < 1) {
              $monto = 1;
          }

          $detalles[] = '{
            "IndExe": false,
            "NmbItem": "'.$pi->nombre.'",
            "DscItem": "'.$producto->tipo.' '.$producto->cantidad.' '.$producto->nombre.'",
            "QtyItem": "'.$ep->QtyItem.'",
            "PrcItem": "'.$monto.'",
            "CodImpAdic": "26"
          }';
        } else {
          $detalles[] = '{
            "IndExe": false,
            "NmbItem": "'.$pi->nombre.'",
            "QtyItem": "'.$ep->QtyItem.'",
            "PrcItem": "'.$monto.'"
          }';

        }

      }

      

    

    }

    






    /*
    foreach($entrega_productos as $ep) {
      $detalles[] = '{
          "IndExe": false,
          "NmbItem": "'.$ep->tipo.' '.$ep->tipos_cerveza.'",
          "QtyItem": '.$ep->cantidad.',
          "PrcItem": '.$ep->monto.'
      }';
    }
    */

    $detalle = implode(",",$detalles);

    $json_postfields = '{
        "Encabezado": {
            "IdDoc": {
                "TipoDTE": 33
            },
            "Emisor": {
                "RUTEmisor": "'.$rut_emisor.'"
            },
            "Receptor": {
                "RUTRecep": "'.$cliente->RUT.'",
                "RznSocRecep": "'.$cliente->RznSoc.'",
                "GiroRecep": "'.$cliente->Giro.'",
                "DirRecep": "'.$cliente->Dir.'",
                "CmnaRecep": "'.$cliente->Cmna.'"
            }
        },
        "Detalle": [
            '.$detalle.'
        ]
    }
    ';

    return $json_postfields;

  }

  function LIBREDTE_emison($data) {

    $httpheader = $GLOBALS['httpheader'];

    $curl = curl_init();

    $json_postfields = $data;//dataEntrada2json($data_entrada);

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://libredte.cl/api/dte/documentos/emitir?normalizar=1&formato=json&links=0&email=0',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $json_postfields,
      CURLOPT_HTTPHEADER => $httpheader
    ));

    $response = curl_exec($curl);
    //print gettype($response);

    curl_close($curl);
    return $response;

  }

  function LIBREDTE_generar($data) {

    $httpheader = $GLOBALS['httpheader'];

    $curl = curl_init();

    $json_postfields = $data;

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://libredte.cl/api/dte/documentos/generar?getXML=0&links=0&email=0&retry=10&gzip=0',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $json_postfields,
      CURLOPT_HTTPHEADER => $httpheader
    ));

    $response = curl_exec($curl);
    //print gettype($response);

    curl_close($curl);
    return $response;

  }

  function LIBREDTE_getDatos() {

    $httpheader = $GLOBALS['httpheader'];

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://libredte.cl/api/dte/contribuyentes/config/76192083-9',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => $httpheader
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
  }

  function LIBREDTE_getXML($folio) {

    $httpheader = $GLOBALS['httpheader'];
    $rut_emisor = $GLOBALS['rut_emisor'];

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://libredte.cl/api/dte/dte_emitidos/xml/33/'.$folio.'/'.$rut_emisor,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => $httpheader
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
  }

  function LIBREDTE_enviarCorreo($folio,$email) {

    $httpheader = $GLOBALS['httpheader'];
    $rut_emisor = $GLOBALS['rut_emisor'];
    $postfields = '{
      "emails": "'.$email.'",
      "asunto": "Factura '.$folio.' Cerveza Cocholgue",
      "mensaje": null,
      "pdf": true,
      "cedible": false,
      "papelContinuo": 0
    }';

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://libredte.cl/api/dte/dte_emitidos/enviar_email/33/'.$folio.'/'.$rut_emisor,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $postfields,
      CURLOPT_HTTPHEADER => $httpheader
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
  }


  function LIBREDTE_enviarCorreo2($folio) {

    $httpheader = $GLOBALS['httpheader'];
    $rut_emisor = $GLOBALS['rut_emisor'];
    $postfields = '{
      "emails": "luisherreradevaud@gmail.com",
      "asunto": "Factura '.$folio.' Cerveza Cocholgue",
      "mensaje": null,
      "pdf": true,
      "cedible": false,
      "papelContinuo": 0
    }';

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://libredte.cl/api/dte/dte_emitidos/enviar_email/33/'.$folio.'/'.$rut_emisor,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => $postfields,
      CURLOPT_HTTPHEADER => $httpheader
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
  }

/*
  function LIBREDTE_getPDF($boleta) {
    $httpheader = $GLOBALS['httpheader'];
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://bhexpress.cl/api/v1/bhe/pdf/'.$boleta,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => $httpheader
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    header('Content-type: application/pdf');
    header('Content-Disposition: inline; filename=bhe.pdf');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . strlen($response));
    header('Accept-Ranges: bytes');
    print $response;
  }

  function LIBREDTE_datosBoleta($boleta) {
    $httpheader = $GLOBALS['httpheader'];
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://bhexpress.cl/api/v1/bhe/boletas/'.$boleta,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => $httpheader
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
  }

  function LIBREDTE_emitidas() {
    $httpheader = $GLOBALS['httpheader'];
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://bhexpress.cl/api/v1/bhe/boletas',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => $httpheader
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
  }

  function LIBREDTE_anular($boleta) {
    $httpheader = $GLOBALS['httpheader'];
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://bhexpress.cl/api/v1/bhe/anular/'.$boleta,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "causa": 3
    }
    ',
      CURLOPT_HTTPHEADER => $httpheader
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
  }

  function LIBREDTE_email($boleta,$email) {
    $httpheader = $GLOBALS['httpheader'];
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://bhexpress.cl/api/v1/bhe/email/'.$boleta,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "destinatario": {
            "email": "'.$email.'"
        }
    }
    ',
      CURLOPT_HTTPHEADER => $httpheader,
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
  }



*/
  /*$data_entrada = array(
    "fecha_emision"=> "2021-06-14",
    "rut_receptor"=> str_replace(".","","19.855.319-0"),
    "nombre_receptor"=> "Luis Herrera",
    "correo_receptor"=> "luisherreradevaud@gmail.com",
    "ciudad_receptor"=> "Los Ángeles",
    "comuna_receptor"=> "Los Ángeles",
    "monto_total"=> "2000",
    "examen"=> "INGRESO 0: Prueba"
  );*/

  function response2array($object) {
    $respuesta = json_decode(json_encode($object), true);
    $sRetorno = (array)simplexml_load_string($respuesta_xml);
    return $sRetorno;
  }

  function LIBREDTE_getPDF($folio) {

    $httpheader = $GLOBALS['httpheader'];
    $rut_emisor = $GLOBALS['rut_emisor'];

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://libredte.cl/api/dte/dte_emitidos/pdf/33/'.$folio.'/'.$rut_emisor.'?formato=general&papelContinuo=0&copias_tributarias=1&copias_cedibles=1&cedible=0&compress=0&base64=0',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => $httpheader
    ));

    $response = curl_exec($curl);
    //print gettype($response);


    curl_close($curl);
    return $response;

  }

  function LIBREDTE_getDataDTE($dte) {

    $httpheader = array(
      'Content-Type: application/json',
      'Accept: application/json',
      'Authorization: Basic '.base64_encode('rdwHeaDpJCERrBQvbSkglZcFq4rbGrUF:X')
    );

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://libredte.cl/api/dte/dte_emitidos/info/33/'.$dte.'/76485421?getXML=0&getDetalle=0&getDatosDte=0&getTed=0&getResolucion=0&getEmailEnviados=0&getLinks=0&getReceptor=0&getSucursal=0&getUsuario=0',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => $httpheader
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;

  }


?>
