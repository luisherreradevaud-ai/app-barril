<?php

if ($_POST == array()) {
    die();
}

require_once("./../php/app.php");
require_once("./../php/libredte.php");

$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

// Procesar barriles devueltos a planta
if (isset($_POST['barriles_estado']) && is_array($_POST['barriles_estado'])) {
    foreach ($_POST['barriles_estado'] as $id_barriles => $estado) {
        $barril = new Barril($id_barriles);
        if ($estado === "Devuelto a planta") {
            $barril->estado = "En planta";
            $barril->id_clientes = 0;
            $barril->id_batches = 0;
            $barril->litros_cargados = 0;
            $barril->registrarCambioDeEstado();
            $barril->save();
            Historial::guardarAccion("Barril #" . $barril->id . " Código '".$barril->codigo."' devuelto a Planta.", $GLOBALS['usuario']);
        } else {
            $barril->estado = $estado;
            $barril->registrarCambioDeEstado();
            $barril->save();
            Historial::guardarAccion("Barril #" . $barril->id . " Código '".$barril->codigo."' marcado como ".$barril->estado.".", $GLOBALS['usuario']);
        }
    }
}

if (validaIdExists($_POST, 'rand_int')) {
    $entregas_anteriores = Entrega::getAll("WHERE rand_int='" . $_POST['rand_int'] . "'");
    if (count($entregas_anteriores) != 0) {
        die();
    }
}

$obj = new Entrega;
$obj->setProperties($_POST);
$obj->creada = date('Y-m-d H:i:s');
$obj->estado = "Entregada";

$cliente = new Cliente($obj->id_clientes);
$fecha = new DateTime();
if ($cliente->criterio == "Martes siguiente") {
    $fecha->modify("next tuesday");
    $obj->fecha_vencimiento = $fecha->format('Y-m-d');
} elseif ($cliente->criterio == "Martes sub-siguiente") {
    $fecha->modify("next tuesday");
    $fecha->modify("next tuesday");
    $obj->fecha_vencimiento = $fecha->format('Y-m-d');
} else {
    $obj->fecha_vencimiento = $fecha->format('Y-m-t');
}

if ($cliente->id_usuarios_vendedor != 0) {
    $obj->id_usuarios_vendedor = $cliente->id_usuarios_vendedor;
}

$obj->save();

$monto_total = 0;

foreach ($_POST['ids_despachos_productos'] as $id_despacho_producto) {
    $dp = new DespachoProducto($id_despacho_producto);
    $id_despachos = $dp->id_despachos;

    $producto = new Producto($dp->id_productos);
    $precio = $producto->getClienteProductoPrecio($cliente->id);

    $ep = new EntregaProducto;
    $ep->setPropertiesNoId($dp);
    $ep->id_entregas = $obj->id;
    $ep->estado = "Entregada";
    $ep->monto = $precio;
    $ep->creada = date('Y-m-d H:i:s');
    $ep->save();
    $dp->delete();

    if ($ep->tipo == "Barril") {
        $barril = new Barril($ep->id_barriles);
        $barril->estado = "En terreno";
        $barril->id_clientes = $obj->id_clientes;
        $barril->registrarCambioDeEstado();
        $barril->save();
    } elseif ($ep->tipo == "Vasos") {
        $ep->cantidad = $_POST['cantidad_vasos'];
        $ep->save();
    }

    $despachos_productos = DespachoProducto::getAll("WHERE id_despachos='" . $id_despachos . "'");
    if (count($despachos_productos) == 0) {
        $despacho = new Despacho($id_despachos);
        $despacho->delete();
    }

    $monto_total += $precio;
    unset($producto);
}

$obj->monto = $monto_total;
$obj->save();

if ($cliente->emite_factura == 1) {
    $entrega_productos = EntregaProducto::getAll("WHERE id_entregas='" . $obj->id . "'");

    $dte = new DTE;
    $data = dataEntrada2json($cliente, $obj, $entrega_productos);

    try {
        $body = LIBREDTE_emison($data);
        $response_string = LIBREDTE_generar($body);
        $response_arr = json_decode($response_string);

        $dte->setProperties($response_arr);
        $dte->id_entregas = $obj->id;

        if ($dte->folio != 0 && $dte->folio != "") {
            $dte->save();
        }

        $obj->factura = $dte->folio;

        $correos = explode(',', $cliente->email);
        foreach ($correos as $correo) {
            LIBREDTE_enviarCorreo($dte->folio, $correo);
        }

        LIBREDTE_enviarCorreo($dte->folio, "matiashmecanico@gmail.com");
    } catch (Exception $e) {
    }
}

$obj->save();

/*$pagos_con_restante = Pago::getAll("WHERE restante!='0' AND id_clientes='" . $cliente->id . "' ORDER BY id asc");

foreach ($pagos_con_restante as $pago) {

    if (($obj->monto - $obj->abonado) == $pago->restante) {
        $obj->abonado = $obj->monto;
        $obj->estado = "Pagada";
        $pago->restante = 0;
    } elseif (($obj->monto - $obj->abonado) > $pago->restante) {
        $obj->abonado += $pago->restante;
        $obj->estado = "Abonada";
        $pago->restante = 0;
    } elseif (($obj->monto - $obj->abonado) < $pago->restante) {
        $pago->restante -= ($obj->monto - $obj->abonado);
        $obj->abonado = $obj->monto;
        $obj->estado = "Pagada";
    }

    $pago->save();
    $obj->save();
}
*/

Historial::guardarAccion("Entrega #" . $obj->id . " entregada a cliente " . $cliente->nombre . ".", $GLOBALS['usuario']);
NotificacionControl::trigger('Nueva Entrega', $obj);

$response["status"] = "OK";
$response["mensaje"] = "OK";
$response["obj"] = $obj;

print json_encode($response, JSON_PRETTY_PRINT);

?>
