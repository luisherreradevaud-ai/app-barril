<?php

  //checkAutorizacion(["Cliente"]);

  $msg = 0;
  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  $usuario = $GLOBALS['usuario'];
  $cliente = new Cliente($usuario->id_clientes);
  $pedidos = Pedido::getAll("WHERE id_clientes='".$cliente->id."' ORDER BY id desc");
  $entregas_facturacion = Entrega::getAll("WHERE id_clientes='".$cliente->id."' AND estado!='Pagada'");
  $sugerencias = Sugerencia::getAll("WHERE id_clientes='".$cliente->id."' ORDER BY id desc");

  $tipos_barriles = $GLOBALS['tipos_barril_cerveza'];

  $msg = 0;

  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  function getSemanas($date) {

    $semanas = array();

      $time = strtotime($date);
      $year = date('Y', $time);
      $month = date('m', $time);

      for($day = 1; $day <= 31; $day++)
      {
          $time = mktime(0, 0, 0, $month, $day, $year);
          if (date('N', $time) == 1)
          {
            $semana = new stdClass;
            $semana->lunes = date('Y-m-d', $time);
            $semana->domingo = date('Y-m-d',strtotime($semana->lunes.' +6 days'));
            $semanas[] = $semana;

          }
      }

      return $semanas;
  }

  $ano = date('Y');
  if(validaIdExists($_GET,'ano')) {
    $ano = $_GET['ano'];
  }

  $mes = intval(date('m'));
  if(validaIdExists($_GET,'mes')) {
    $mes = $_GET['mes'];
  }

  if(validaIdExists($_GET,'trimestre')) {
    $trimestre = $_GET['trimestre'];
  } else {
    $trimestre = ceil($mes/3);
  }

  if(validaIdExists($_GET,'semestre')) {
    $semestre = $_GET['semestre'];
  } else {
    $semestre = ceil($mes/6);
  }

  $order_by = "id";
  if(isset($_GET['order_by'])) {
    if($_GET['order_by'] == "clientes") {
      $order_by = "clientes";
    }
    if($_GET['order_by'] == "id") {
      $order_by = "id";
    }
    if($_GET['order_by'] == "monto") {
      $order_by = "monto";
    }
    if($_GET['order_by'] == "factura") {
      $order_by = "factura";
    }
    if($_GET['order_by'] == "estado") {
      $order_by = "estado";
    }
    if($_GET['order_by'] == "creada") {
      $order_by = "creada";
    }
  }

  $order = "asc";
  if(isset($_GET['order'])) {
    if($_GET['order'] == "asc") {
      $order = "asc";
    } else 
    if($_GET['order'] == "desc") {
      $order = "desc";
    }
  }

  if($mes == intval(date('m')) && $ano == date('Y')) {
    $date = date($ano."-".$mes.'-d');
  } else {
    $date = date($ano."-".$mes.'-1');
  }

  if(isset($_GET['lunes'])) {
    $date = $_GET['lunes'];
  }

  $modo = "Semanal";
  if(isset($_GET['modo'])) {
    $modo = $_GET['modo'];
  }

  $datetime = new DateTime($date);

  if(intval($datetime->format('m')) != $mes || intval($datetime->format('Y')) != $ano ) {
    $date = $ano."-".$mes."-1";
    $datetime = new DateTime($date);
  }

  //print $datetime->format('Y-m-d');
  
  $esta_semana = new stdClass;

  if($datetime->format('N') == 1){
    $esta_semana->lunes = $datetime->format('Y-m-d');
  } else {
    $esta_semana->lunes = $datetime->modify('last monday')->format('Y-m-d');
  }

  //$esta_semana->lunes = $datetime->format('Y-m-d');
  $lunes = $esta_semana->lunes;

  //print $lunes;

  $esta_semana->domingo = date('Y-m-d',strtotime($esta_semana->lunes.' +6 days'));

  $semanas = getSemanas($date);

  if($modo == "Semanal") {
    $date_inicio = $esta_semana->lunes;
    $date_final = $esta_semana->domingo;
  } else 
  if($modo == "Mensual") {
    $date_inicio = $ano."-".$mes."-01";
    $date_final = $ano."-".$mes."-31";
  } else 
  if($modo == "Trimestral") {
    if($trimestre == 1) {
      $date_inicio = $ano."-01-01";
      $date_final = $ano."-03-31";
    } else
    if($trimestre == 2) {
      $date_inicio = $ano."-04-01";
      $date_final = $ano."-06-31";
    } else
    if($trimestre == 3) {
      $date_inicio = $ano."-07-01";
      $date_final = $ano."-09-31";
    } else
    if($trimestre == 4) {
      $date_inicio = $ano."-10-01";
      $date_final = $ano."-12-31";
    }
  } else 
  if($modo == "Semestral") {
    if($semestre == 1) {
      $date_inicio = $ano."-01-01";
      $date_final = $ano."-06-31";
    } else
    if($semestre == 2) {
      $date_inicio = $ano."-07-01";
      $date_final = $ano."-12-31";
    }
  } else 
  if($modo == "Anual") {
    $date_inicio = $ano."-01-01";
    $date_final = $ano."-12-31";
  } else 
  if($modo == "Historico") {
    $date_inicio = "22-01-01";
    $date_final = date('Y-m-d');
  }

  //print $date_inicio." ".$date_final;

  $query = "";

  if($order_by == "clientes") {
    $query = "INNER JOIN clientes ON entregas.id_clientes=clientes.id";
  }

  $query .= " WHERE entregas.id_clientes='".$cliente->id."' AND entregas.creada BETWEEN '".$date_inicio." 00:00:00' AND '".$date_final." 23:59:59'";

  $id_clientes = 0;
  if(validaIdExists($_GET,'id_clientes')) {
    $query .= " AND id_clientes='".$_GET['id_clientes']."'";
  }

  if($order_by != "clientes") {
    $query .= " ORDER BY ".$order_by." ".$order;
  } else {
    $query .= " ORDER BY clientes.nombre ".$order;
  }

  $entregas = Entrega::getAll($query);
  $entregas_anteriores_sin_pagar = Entrega::getAll("WHERE id_clientes='".$cliente->id."'  AND creada < '".$date_inicio." 00:00:00' AND estado!='Pagada' ORDER BY id asc");


?>
<style>
.tr-pedidos {
  cursor: pointer;
}
.tr-entregas {
  cursor: pointer;
}
.tr-entregas-facturacion {
  cursor: pointer;
}
.tr-sugerencias {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800"><b>Mis Entregas</b></h1>
  </div>
  <div>
        <a href="./?s=central-clientes-nuevo-pedido" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm">
          <i class="fas fa-fw fa-plus"></i> Nuevo Pedido
        </a>
        <a href="./?s=nuevo-sugerencias" class="btn btn-sm btn-primary shadow">
          <i class="fas fa-fw fa-plus"></i> Nueva Sugerencia o Reclamo
        </a>
  </div>
</div>
<hr />
<div class="d-sm-flex mb-3">
  <div>
    <select class="form-control me-1 vista-select"  data-select="lunes">
      <?php
      foreach($semanas as $semana) {
        print "<option value='".$semana->lunes."'";
        if($esta_semana->lunes == $semana->lunes) {
          print " SELECTED";
        }
        print ">".date2fecha($semana->lunes)." al ".date2fecha($semana->domingo)."</option>";
      }
      ?>
    </select>
  </div>
  <div>
    <select class="form-control me-1 vista-select"  data-select="mes">
      <?php
      for($i = 1; $i<=12; $i++) {
        print "<option value='".$i."'>".int2mes($i)."</option>";
      }
      ?>
    </select>
  </div>

  <div>
    <select class="form-control me-1 vista-select" data-select="trimestre">
      <option value="1">Enero - Marzo</option>
      <option value="2">Abril - Junio</option>
      <option value="3">Julio - Septiembre</option>
      <option value="4">Octubre - Diciembre</option>
    </select>
  </div>

  <div>
    <select class="form-control me-1 vista-select"  data-select="semestre">
      <option value="1">Enero - Junio</option>
      <option value="2">Julio - Diciembre</option>
    </select>
  </div>

  <div>
    <select class="form-control vista-select"  data-select="ano">
      <?php
      for($i = 2023; $i<=date('Y'); $i++) {
        print "<option>".$i."</option>";
      }
      ?>
    </select>
  </div>
</div>
<div class="mb-3">
  <select class="form-control vista-select" style="max-width: 300px" data-select="modo">
    <option>Semanal</option>
    <option>Mensual</option>
    <option>Trimestral</option>
    <option>Semestral</option>
    <option>Anual</option>
    <option value="Historico">Hist&oacute;rico</option>
  </select>
</div>
<hr />
<table class="table table-striped table-sm">
  <thead class="thead-dark">
    <tr>
      <th>
      </th>
      <th>
        ID
      </th>
      <th>
        Fecha de entrega
      </th>
      <th>
        Factura
      </th>
      <th>
        Vencimiento
      </th>
      <th>
        Estado
      </th>
      <th>
        Monto Total
      </th>
      <th>
        Adeudado
      </th>
    </tr>
  </thead>
  <tbody>
    <?php

    $total = 0;
    $adeudado = 0;

      foreach($entregas_anteriores_sin_pagar as $entrega) {

        if($entrega->monto == 0) {
          continue;
        }

        $adeudado += $entrega->monto - $entrega->abonado;
    ?>
    <tr class="table-danger tr-entregas" data-identregas="<?= $entrega->id; ?>">
    <td>
            <input type="checkbox" data-monto="<?= ($entrega->monto - $entrega->abonado); ?>" data-id="<?= $entrega->id; ?>" class="checkbox-monto">

    </td>
      <td>
        <b>#<?= $entrega->id; ?></b>
      </td>
      <td>
        <b><?= datetime2fechayhora($entrega->creada); ?></b>
      </td>
      <td>
        <a href="./clientes/dte.php?folio=<?= $entrega->factura; ?>"><?= $entrega->factura; ?></a>
      </td>
      <td>
        <?= date2fecha($entrega->fecha_vencimiento); ?>
      </td>
      <td>
        <?= $entrega->estado; ?>
      </td>
      <td>
        <b>$<?= number_format($entrega->monto); ?></b>
      </td>
      <td>
        <b>$<?= number_format($entrega->monto - $entrega->abonado); ?></b>
      </td>
    </tr>

    <?php
      }
    ?>



    <?php
      foreach($entregas as $entrega) {
        $barriles = Barril::getAll("WHERE id_entregas='".$entrega->id."'");
        $cajas = Caja::getAll("WHERE id_entregas='".$entrega->id."'");
        $entregas_productos = EntregaProducto::getAll("WHERE id_entregas='".$entrega->id."'");
    ?>
    <tr data-identregas="<?= $entrega->id; ?>" class="tr-entregas-facturacion">
      <td>
        <?php
        if($entrega->estado != "Pagada") {
         ?>
        <input type="checkbox" data-monto="<?= ($entrega->monto - $entrega->abonado); ?>" data-id="<?= $entrega->id; ?>" class="checkbox-monto">
        <?php
        }
        ?>
      </td>
      <td>
        #<?= $entrega->id; ?>
      </td>
      <td>
        <?= datetime2fecha($entrega->creada); ?>
      </td>
      <td>
        <a href="./clientes/dte.php?folio=<?= $entrega->factura; ?>"><?= $entrega->factura; ?></a>
      </td>
      <td>
        <?= date2fecha($entrega->fecha_vencimiento); ?>
      </td>
      <td>
        <?= $entrega->estado; ?>
      </td>
      <td>
        <b>$<?= number_format($entrega->monto); ?></b>
      </td>
      <td>
        <b>$<?= number_format($entrega->monto - $entrega->abonado); ?></b>
      </td>
    </tr>
    <tr id="tr-desplegable-<?= $entrega->id; ?>" style="display: none; border: 1px solid #BBB">
      <td colspan="7" class="tr-desplegable" style="padding: 10px">
        <b>Detalle Entrega #<?= $entrega->id; ?>:</b>
        <br />
        <br />
        <table class="table">
          <?php
          foreach($entregas_productos as $ep) {
            ?>
            <tr>
              <td>
                <?= $ep->tipo; ?>
              </td>
              <td>
                <?= $ep->cantidad; ?>
              </td>
              <td>
                <?= $ep->tipos_cerveza; ?>
              </td>
              <td>
                <b>$<?= number_format($ep->monto); ?></b>
              </td>
            </tr>
            <?php
          }
          ?>

        </table>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>

<table class="table w-100" style="border: 1px solid black">
  <tr>
    <td class="d-flex justify-content-between">
      <span class="h5 text-gray-800 mt-2">
        TOTAL: <b>$<span class="total">0</span></b>
      </span>
      <button class="d-sm-inline-block btn btn-primary shadow-sm" id="pagar-btn"><i class="fas fa-fw fa-dollar-sign"></i> Pagar</button>
    </td>
  </tr>
</table>
<form id="pay" method="POST" action="">
  <input id="token_ws" type="hidden" name="token_ws" value="">
</form>

<!--
<div class="d-sm-flex align-items-center justify-content-between mt-5 mb-3">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800"><b>Entregas</b></h1>
  </div>
  <div>
    <div>

    </div>
  </div>
</div>

<?php
if($msg == 6) {
?>
<div class="alert alert-info" role="alert" >Entrega guardada con &eacute;xito.</div>
<?php
}
?>
<table class="table table-hover table-striped table-sm" id="table-entregas">
  <thead class="thead-dark">
    <tr>
      <th>
        <a href="#table-entregas" class="sort" data-orderby="id">#</a>
      </th>
      <th>
        <a href="#table-entregas" class="sort" data-orderby="clientes">Recibida por</a>
      </th>
      <th>
        <a href="#table-entregas" class="sort" data-orderby="creada">Fecha</a>
      </th>
      <th>
        <a href="#table-entregas" class="sort" data-orderby="factura">Factura</a>
      </th>
      <th>
        <a href="#table-entregas" class="sort" data-orderby="monto">Monto</a>
      </th>
      <th>
        <a href="#table-entregas" class="sort" data-orderby="estado">Estado</a>
      </th>
      <?php
        foreach($tipos_barriles as $tipo_barril) {
          print "<th style='font-size: 0.9em'>".$tipo_barril."</th>";
        }
      ?>
      <th>
        Cajas
      </th>
    </tr>
  </thead>
  <tbody>
    <?php

      $totales = array();
      $totales['monto'] = 0;
      foreach($tipos_barriles as $tipo_barril) {
        $totales[$tipo_barril] = 0;
      }
      $totales['Caja'] = 0;

      foreach($entregas as $entrega) {
        $cliente = new Cliente($entrega->id_clientes);

        $barriles_arr = array();
        foreach($tipos_barriles as $tipo_barril) {
          $barriles_arr[$tipo_barril] = 0;
        }
        $cajas = 0;

        $entrega_productos = EntregaProducto::getAll("WHERE id_entregas='".$entrega->id."'");

        foreach($entrega_productos as $entrega_producto) {
          if($entrega_producto->tipo == "Barril") {
            $barriles_arr[$entrega_producto->tipos_cerveza] += 1;
            $totales[$entrega_producto->tipos_cerveza] += 1;
          } else
          if($entrega_producto->tipo == "Caja") {
            $cajas += 1;
            $totales['Caja'] += 1;
          }
        }

        $totales['monto'] += $entrega->monto;

    ?>
    <tr>
      <td>
        <?= $entrega->id; ?>
      </td>
      <td>
        <?= $entrega->receptor_nombre; ?>
      </td>
      <td>
        <?= datetime2fecha($entrega->creada); ?>
      </td>
      <td>
        <a href="./clientes/dte.php?folio=<?= $entrega->factura; ?>"><?= $entrega->factura; ?></a>
      </td>

      <td>
        $<?= number_format($entrega->monto); ?>
      </td>
      <td>
        <?= $entrega->estado; ?>
      </td>
      <?php
        foreach($tipos_barriles as $tipo_barril) {
          print "<td style='font-size: 0.9em'>".$barriles_arr[$tipo_barril]."</td>";
        }
      ?>
      <td>
        <?= $cajas; ?>
      </td>
    </tr>
    <?php
      }
    ?>
    <tfooter>
      <tr style="border: 1px solid black; background-color: white">
        <td colspan="4">
          Totales
        </td>
        <td colspan="2">
          <b>$<?= number_format($totales['monto']); ?></b>
        </td>
        <?php
          foreach($tipos_barriles as $tipo_barril) {
            print "<td><b>".$totales[$tipo_barril]."</b></td>";
          }
        ?>
        <td>
          <b><?= $totales['Caja']; ?></b>
        </td>
      </tr>
    </tfooter>
  </tbody>
</table>



<hr style="border: 2px solid black;" class="mt-5">

-->

<div class="d-sm-flex align-items-center justify-content-between mt-5 mb-3">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800" id="pedidos"><b>Mis Pedidos</b></h1>
  </div>
  <div>
    <div>
    </div>
  </div>
</div>
<hr />
<?php
if($msg == 1) {
?>
  <div class="alert alert-info" role="alert" >Pedido ingresado con &eacute;xito.</div>
<?php
}
  foreach($pedidos as $pedido) {
    $productos = PedidoProducto::getAll("WHERE id_pedidos='".$pedido->id."'");
?>
<div class="card w-100 shadow mb-5">
  <div class="card-body">
    <h5 class="card-title mb-3 h5"><i class="fas fa-fw fa-truck"></i> PEDIDO #<?= $pedido->id; ?></h5>
    <table>
      <tr>
        <td>
          Creado:
        </td>
        <td>
          <b>
            <?= datetime2fechayhora($pedido->creada); ?>
          </b>
        </td>
      </tr>
      <tr>
        <td>
          Estado:
        </td>
        <td>
          <b>
            <?= $pedido->estado; ?>
          </b>
        </td>
      </tr>
    </table>
    <br />
    <table class="table table-striped mt-3">
      <?php
      foreach($productos as $pp) {
        ?>
        <tr>
          <td>
            <?= $pp->tipo; ?>
          </td>
          <td>
            <?= $pp->cantidad; ?>
          </td>
          <td>
            <?= $pp->tipos_cerveza; ?>
          </td>
        <?php

      }
       ?>
    </table>
  </div>
</div>
<?php
  }
?>

<?php
    $barriles = Barril::getAll("WHERE estado='En terreno' AND id_clientes='".$cliente->id."'");
?>
<style>
.tr-barriles {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mt-5 mb-3">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800"><b>Mis Barriles Asignados</b></h1>
  </div>
</div>
<hr />

    <table class="table table-hover table-striped table-sm mb-5">
      <thead class="thead-dark">
        <tr>
          <th>
              C&oacute;digo
          </th>
          <th>
              Tipo
          </th>
          <th>
              Entrega
          </th>
          <th>
              Fecha Entrega
          </th>
        </tr>
      </thead>
      <tbody>
        <?php
          foreach($barriles as $barril) {
            $ep = EntregaProducto::getAll("WHERE id_barriles='".$barril->id."' ORDER BY id desc LIMIT 1");
            if(count($ep)>0) {
              $entrega = new Entrega($ep[0]->id_entregas);
              $datetime_entrega = datetime2fecha($entrega->creada);
            } else {
              $datetime_entrega = "-";
            }
        ?>
        <tr class="tr-barriles" data-idbarriles="<?= $barril->id; ?>">
          <td>
            <?= $barril->codigo; ?>
          </td>
          <td>
            <?= $barril->tipo_barril; ?>
          </td>
          <td>
            <?= $entrega->id; ?>
          </td>
          <td>
            <?= $datetime_entrega; ?>
          </td>
        </tr>
        <?php
          }
        ?>
      </tbody>
    </table>
<?php
  $query = "WHERE mantenciones.id_clientes_ubicacion='".$cliente->id."' AND mantenciones.date BETWEEN '".$ano."-".$mes."-01' AND '".$ano."-".$mes."-31'";
  $mantenciones = Mantencion::getAll($query);

?>
<style>
.tr-mantenciones {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mt-5 mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><b>Mis Mantenciones</b></h1>
  </div>
</div>
<hr />
<table class="table table-hover table-striped table-sm" id="insumos-table">
  <thead class="thead-dark">
    <tr>
        <th>
      <a href="#" class="sort" data-show="insumos" data-sort="nombre">ID</a>
      </th>
      <th>
      <a href="#" class="sort" data-show="insumos" data-sort="nombre">Fecha</a>
      </th>
      <th>
      <a href="#" class="sort" data-show="insumos" data-sort="nombre">Tarea</a>
      </th>
      <th>
      <a href="#" class="sort" data-show="insumos" data-sort="nombre">Nombre</a>
      </th>
      <th>
      <a href="#" class="sort" data-show="insumos" data-sort="tipos_de_insumos">Marca</a>
      </th>
      <th>
      <a href="#" class="sort" data-show="insumos" data-sort="tipos_de_insumos">Modelo</a>
      </th>
      <th>
      <a href="#" class="sort" data-show="insumos" data-sort="cantidad">C&oacute;digo</a>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      foreach($mantenciones as $mantencion) {
        $activo = new Activo($mantencion->id_activos);
    ?>
    <tr>
        <td>
            #<?= $mantencion->id; ?>
      </td>
      <td>
        <?= date2fecha($mantencion->date); ?>
      </td>
      <td>
        <?= $mantencion->tarea; ?>
      </td>
      <td>
        <?= $activo->nombre; ?>
      </td>
      <td>
        <?= $activo->marca; ?>
      </td>
      <td>
        <?= $activo->modelo; ?>
      </td>
      <td>
        <?= $activo->codigo; ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<div class="d-sm-flex align-items-center justify-content-between mt-5 mb-3" id="sugerencias">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><b>Mis Sugerencias</b></h1>
  </div>
  <div>
    
  </div>
</div>
<hr />
<?php
if($msg == 2) {
?>
<div class="alert alert-info" role="alert" >Sugerencia o reclamo enviada con &eacute;xito.</div>
<?php
}
  foreach($sugerencias as $sugerencia) {
    $usuario_2 = new Usuario($sugerencia->id_usuarios);
?>
<div class="card w-100 shadow mb-5">
  <div class="card-body">
    <h5 class="card-title mb-3 h5" style="text-transform: uppercase"><?= $sugerencia->tipo; ?></h5>
    <table>
      <tr>
        <td>
          Cliente:
        </td>
        <td>
          <b>
            <?= $cliente->nombre; ?>
          </b>
        </td>
      </tr>
      <tr>
        <td>
          Usuario:
        </td>
        <td>
          <b>
            <?= $usuario_2->nombre; ?>
          </b>
        </td>
      </tr>
      <tr>
        <td>
          Creado:
        </td>
        <td>
          <b>
            <?= datetime2fechayhora($sugerencia->creada); ?>
          </b>
        </td>
      </tr>
    </table>
    <br />
    Contenido:<br/><b><?= $sugerencia->contenido; ?></b>
    <br />
    <br />
  </div>
</div>
<?php
  }
?>



<script>

var entregas = <?= json_encode($entregas,JSON_PRETTY_PRINT); ?>;
var total = 0;
var ids_entregas = [];

$(document).ready(function(){
  calcularTotal();
})

$(document).on('click','.checkbox-monto',function(e){
  e.stopPropagation();
  calcularTotal();
});

function calcularTotal() {
  total = 0;
  ids_entregas = []
  $('.checkbox-monto').each(function(){
    if($(this).is(':checked')){
      total += $(this).data('monto');
      ids_entregas.push($(this).data('id'));
    }
  })
  $('.total').html(parseInt(total).toLocaleString('en-US'));
  ids_entregas = ids_entregas.join(',');
  if(total == 0) {
    $('#pagar-btn').attr('disabled',true);
  } else {
    $('#pagar-btn').attr('disabled',false);
  }
}

$(document).on('click','.tr-despachos',function(e){
  window.location.href = "./?s=detalle-despachos&id=" + $(e.currentTarget).data('iddespachos');
});

$(document).on('click','#pagar-btn',function() {
  var url = "./ajax/ajax_getTransbankResponse.php";
  var data = {
    'total': total,
    'ids_entregas': ids_entregas,
    'id_clientes': <?= $cliente->id; ?>
  };
  console.log(data);

  $.ajax({
    url: url,
    data: data,
    method: 'POST',
    dataType: 'JSON',
    success: function(response){
      console.log(response);
      if(response.mensaje=="OK") {
        submitToWebpay(response.tokens);
      }
    }
  });

});

function submitToWebpay(tokens) {
  $('#pay').attr('action',tokens.url);
  $('#token_ws').val(tokens.token_ws);
  $('#pay').submit();
}

$(document).on('click','.tr-entregas-facturacion',function(e){

  var id_entregas = $(e.currentTarget).data('identregas');
  var id = '#tr-desplegable-' + id_entregas;

  $(id).toggle(200);
});

$(document).on('click','.tr-pedidos',function(e){
  window.location.href = "./?s=detalle-pedidos&id=" + $(e.currentTarget).data('idpedidos');
})


var lunes = '<?= $esta_semana->lunes; ?>';
var mes = '<?= $mes; ?>';
var ano = '<?= $ano; ?>';
var id_clientes = '<?= $id_clientes; ?>';
var order_by = '<?= $order_by; ?>';
var order = '<?= $order; ?>';
var modo = '<?= $modo; ?>';
var trimestre = '<?= $trimestre; ?>';
var semestre = '<?= $semestre; ?>';


$(document).ready(function(){

  $('.vista-select[data-select="mes"').val(mes);
  $('.vista-select[data-select="ano"').val(ano);
  $('.vista-select[data-select="modo"').val(modo);
  $('.vista-select[data-select="lunes"').val(lunes);
  $('.vista-select[data-select="trimestre"').val(trimestre);
  $('.vista-select[data-select="semestre"').val(semestre);

  $('.vista-select').hide();
  $('.vista-select[data-select="modo"').show();
  $('.vista-select[data-select="id_clientes"').show();

  if(modo == "Semanal") {
    $('.vista-select[data-select="lunes"').show();
    $('.vista-select[data-select="mes"').show();
    $('.vista-select[data-select="ano"').show();
  }
  if(modo == "Mensual") {
    $('.vista-select[data-select="mes"').show();
    $('.vista-select[data-select="ano"').show();
  }
  if(modo == "Trimestral") {
    $('.vista-select[data-select="trimestre"').show();
    $('.vista-select[data-select="ano"').show();
  }
  if(modo == "Semestral") {
    $('.vista-select[data-select="semestre"').show();
    $('.vista-select[data-select="ano"').show();
  }
  if(modo == "Anual") {
    $('.vista-select[data-select="ano"').show();
  }


  $('#mes-select').val(<?= $mes; ?>);
  $('#ano-select').val(<?= $ano; ?>);

});

$(document).on('change','.vista-select', function(e) {

  var select = $(e.currentTarget).data('select');
  var value = $(e.currentTarget).val();

  if(select == 'lunes') {
    lunes = value;
  } else
  if(select == 'mes') {
    mes = value;
  } else
  if(select == 'ano') {
    ano = value;
  } else
  if(select == 'trimestre') {
    trimestre = value;
  } else
  if(select == 'semestre') {
    semestre = value;
  } else
  if(select == 'id_clientes') {
    id_clientes = value;
  } else
  if(select == 'modo') {
    modo = value;
  }

  cambiarVista();

});

$(document).on('click','.sort', function(e) {

  if($(e.currentTarget).data('orderby') == order_by) {
    if(order == "asc") {
      order = "desc";
    } else {
      order = "asc";
    }
  } else {
    order = "asc";
  }

  order_by = $(e.currentTarget).data('orderby');
  cambiarVista();

});

function cambiarVista() {

  if(modo == 'Semanal') {
    window.location.href = "./?s=central-clientes-completa&lunes=" + lunes + "&id_clientes=" + id_clientes + "&mes=" + mes + "&ano=" + ano + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order + "#entregas";
  }
  if(modo == 'Mensual') {
    window.location.href = "./?s=central-clientes-completa&mes=" + mes + "&ano=" + ano + "&id_clientes=" + id_clientes + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order + "#entregas";
  }
  if(modo == 'Trimestral') {
    window.location.href = "./?s=central-clientes-completa&trimestre=" + trimestre + "&ano=" + ano + "&id_clientes=" + id_clientes + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order + "#entregas";
  }
  if(modo == 'Semestral') {
    window.location.href = "./?s=central-clientes-completa&semestre=" + semestre + "&ano=" + ano + "&id_clientes=" + id_clientes + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order + "#entregas";
  }
  if(modo == 'Historico') {
    window.location.href = "./?s=central-clientes-completa&id_clientes=" + id_clientes + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order + "#entregas";
  }
  if(modo == 'Anual') {
    window.location.href = "./?s=central-clientes-completa&ano=" + ano + "&id_clientes=" + id_clientes + "&modo=" + modo + "&order_by=" + order_by + "&order=" + order + "#entregas";
  }

}

$(document).on('click','.tr-sugerencias',function(e){
  window.location.href = "./?s=detalle-sugerencias&id=" + $(e.currentTarget).data('idsugerencias');
});

</script>