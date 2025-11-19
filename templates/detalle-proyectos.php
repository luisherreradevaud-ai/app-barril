<?php

    if(!validaIdExists($_GET,'id')) {
        die();
    }

    $obj = new Proyecto($_GET['id']);


    $ingresos = ProyectoIngreso::getAll("WHERE id_proyectos='".$obj->id."' ORDER BY id desc");
    $ingresos_liquidos_total = 0;
    $ingresos_neto_total = 0;
    $ingresos_iva_total = 0;
    $ingresos_ila_total = 0;
    $ingresos_comision_tarjeta_total = 0;
    foreach($ingresos as $ingreso) {

      $ingresos_liquidos_total += $ingreso->monto;

      if($ingreso->forma_de_pago != "Tarjeta") {
        if($ingreso->impuestos == "Sin impuestos") {
          $ingresos_neto_total += $ingreso->monto;
        } else
        if($ingreso->impuestos == "IVA") {
          $ingresos_neto_total += round($ingreso->monto/1.19);
          $ingresos_iva_total += $ingreso->monto * 0.19; 
        }
        else
        if($ingreso->impuestos == "IVA + ILA") {
          $ingresos_neto_total += round($ingreso->monto/1.19);
          $ingresos_iva_total += $ingreso->monto * 0.19; 
          $ingresos_ila_total += $ingreso->monto/1.205;
        }

      } else {
        $ingresos_comision_tarjeta_total += round($ingreso->monto*0.0345);
        $monto = $ingreso->monto - round($ingreso->monto*0.0345);
        if($ingreso->impuestos == "IVA") {
          $ingresos_neto_total += round($monto/1.19);
          $ingresos_iva_total += $monto * 0.19; 
        }
        else
        if($ingreso->impuestos == "IVA + ILA") {
          $ingresos_neto_total += round($monto/1.19);
          $ingresos_iva_total += $monto * 0.19; 
          $ingresos_ila_total += $ingreso->monto/1.205;
        }
      }
      

    }

    $usuario = $GLOBALS['usuario'];
    if($usuario->nivel == "Administrador")  {
        $tipos_de_gasto = TipoDeGasto::getAll("ORDER BY nombre asc");
    } else {
        $tipos_de_gasto = TipoDeGasto::getAll("WHERE nombre='Gas' OR nombre='Caja Chica' OR nombre='Combustible' OR nombre='Envios' ORDER BY nombre asc");
    }

    $ids_gastos = $obj->getRelations("gastos");
    $gastos = array();
    $gastos_total = 0;
    foreach($ids_gastos as $id_gastos) {
      $gasto = new Gasto($id_gastos);
      $gastos_total += $gasto->monto;
      $gastos[] = $gasto;
    }

    $tipos_barril = ['20L','30L','50L'];
    $tipos_caja = $GLOBALS['tipos_caja'];
    $tipos_caja_cerveza = $GLOBALS['tipos_caja_cerveza'];

    foreach($tipos_barril as $tb) {
      $productos['Barril'][$tb] = Producto::getAll("WHERE tipo='Barril' AND cantidad='".$tb."'");
    }

    foreach($tipos_caja as $tc) {
      $productos['Caja'][$tc] = Producto::getAll("WHERE tipo='Caja' AND cantidad='".$tc."'");
    }

    $barriles_enplanta_co2  = Barril::getAll("WHERE clasificacion='CO2' AND estado='En planta'");
    $barriles_enplanta_cerveza  = Barril::getAll("WHERE clasificacion='Cerveza' AND estado='En planta'");

    $balance_total = $ingresos_neto_total - $gastos_total;

    $proyecto_productos = ProyectoProducto::getAll("WHERE id_proyectos='".$obj->id."' ORDER BY id desc");

 ?>
<style>
.tr-gastos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-plus"></i> <b>Detalle Proyecto</b></h1>
  </div>
  <div>
    <div>
      <?php $usuario->printReturnBtn(); ?>
    </div>
  </div>
</div>
<hr />
<?php 
  Msg::show(1,'Proyecto guardado con &eacute;xito','primary');
?>
<form id="proyectos-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="proyectos">
  <div class="row">
    <div class="col-md-6">
    <div class="row">
    <div class="col-6 mb-1">
        Nombre:
      </div>
      <div class="col-6 mb-1">
        <input type="text" name="nombre" class="form-control">
      </div>
      <div class="col-6 mb-1">
        Clasificaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="clasificacion">
            <option>Feria</option>
            <option>Otro</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="estado">
            <option>Activo</option>
            <option>Finalizado con pendientes</option>
            <option>Finalizado y cerrado</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        Fecha de inicio:
      </div>
      <div class="col-6 mb-1">
        <input name="date_inicio" class="form-control" type="date">
      </div>
      <div class="col-6 mb-1">
        Fecha de finalizaci&oacute;n:
      </div>
      <div class="col-6 mb-1">
        <input name="date_finalizacion" class="form-control" type="date">
      </div>
      <div class="col-12 mb-1 mt-3 d-flex justify-content-between">
      <button class="btn btn-danger btn-sm eliminar-obj-btn"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
      <button class="btn btn-primary btn-sm" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
      </div>
      </div>
    </div>
    <div class="col-md-6" style="vertical-align: top">
    <div class="row">
      <div class="col-12 mb-1">
        <table class="table table-sm table-striped" style="border: 1px solid #5a5c68">
          <thead class="table-dark">
            <th>
              Resumen Totales
            </th>
            <th>
            </th>
          </thead>
          <tbody>
          <tr>
          <td>Total Ingresos Liquidos $</td>
          <td id="totales-ingresos-liquidos">$<?= number_format($ingresos_liquidos_total); ?></td>
          </tr>
          <tr>
          <td>Comisi&oacute;n Tarjetas (3.45%) $</td>
          <td id="totales-iva">$<?= number_format($ingresos_comision_tarjeta_total); ?></td>
          </tr>
          <tr>
          <td>IVA (19%) $</td>
          <td id="totales-iva">$<?= number_format($ingresos_iva_total); ?></td>
          </tr>
          <tr>
          <td>Cervezas y Bebidas Alcoh. (20.5%) $</td>
          <td id="totales-ila">$<?= number_format($ingresos_ila_total); ?></td>
          </tr>
          <tr>
          <td>Ingreso Neto $</td>
          <td id="totales-neto">$<?= number_format($ingresos_neto_total); ?></td>
          </tr>
          <tr>
          <td>Total Gastos $</td>
          <td id="totales-gastos">$<?= number_format($gastos_total); ?></td>
          </tr>
          <tr>
          <td><b>Balance Total</b> $</td>
          <td id="totales-total">$<?= number_format($balance_total); ?></td>
          </tr>
          </tbody>
        </table>
        
      </div>
      
      </div>
    </div>
    
  </div>
</form>

<div class="modal" tabindex="-1" role="dialog" id="ingresosModal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Ingresos</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
        <form id="proyectos_ingresos-form">
        <input type="hidden" name="id" value="">
        <input type="hidden" name="id_proyectos" value="<?= $obj->id; ?>">
        <input type="hidden" name="entidad" value="proyectos_ingresos">
          <div class="row">
            <div class="col-6 mb-2">
              &Iacute;tem
            </div>
            <div class="col-6 mb-2">
              <input type="text" name="item" class="form-control">
            </div>
            <div class="col-6 mb-2">
              Monto
            </div>
            <div class="col-6 mb-2">
              <div class="input-group">
                <span class="input-group-text" style="border-radius: 10px 0px 0px 10px">$</span>
                <input type="number" class="form-control acero" name="monto">
              </div>
            </div>
            <div class="col-6 mb-2">
              Forma de Pago
            </div>
            <div class="col-6 mb-2">
              <select class="form-control" name="forma_de_pago">
                <option>Tarjeta</option>
                <option>Efectivo</option>
                <option>Transferencia</option>
              </select>
            </div>
            <div class="col-6 mb-2">
              Impuesto
            </div>
            <div class="col-6 mb-2">
              <select class="form-control" name="impuestos">
                <option>IVA</option>
                <option>IVA + ILA</option>
                <option>Sin impuestos</option>
              </select>
            </div>
          </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="ingresos-agregar-aceptar-btn" data-bs-dismiss="modal">Agregar</button>
        </div>
      </div>
    </div>
  </div>

<div class="modal" tabindex="-1" role="dialog" id="gastosModal">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Gasto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="gastos-form" action="./php/procesar.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="">
            <input type="hidden" name="id_proyectos" value="<?= $obj->id; ?>">
            <input type="hidden" name="modo" value="nuevo-gastos-en-proyectos">
            <input type="hidden" name="tipo_de_gasto" value="Proyectos">
  <div class="row">
    <div class="col-6 mb-1">
        Fecha:
      </div>
      <div class="col-6 mb-1">
        <input type="date" value="<?= date('Y-m-d'); ?>" class="form-control" name="creada">
      </div>
      <div class="col-6 mb-1">
        &Iacute;tem:
      </div>
      <div class="col-6 mb-1">
        <div class="input-group">
            <span class="input-group-text" id="basic-addon1" style="border-radius: 10px 0px 0px 10px"><?= $obj->nombre; ?>:</span>
            <input type="text" name="item" class="form-control">
        </div>
      </div>
      <div class="col-6 mb-1">
        Monto:
      </div>
      <div class="col-6 mb-1">
        <div class="input-group">
          <span class="input-group-text" id="basic-addon1" style="border-radius: 10px 0px 0px 10px">$</span>
          <input type="text" class="form-control acero" name="monto" value="0">
        </div>
      </div>
      <?php
      if($usuario->nivel=="Administrador") {
        ?>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <select name="estado" class="form-control">
            <option>Pagado</option>
            <option>Por Pagar</option>
        </select>
      </div>
      <div class="col-6 mb-1 date_vencimiento">
        Vencimiento:
      </div>
      <div class="col-6 mb-1 date_vencimiento">
        <input type="date" name="date_vencimiento" class="form-control" value="<?= date('Y-m-d'); ?>">
      </div>
      <?php
      } else 
      {
      ?>
      <input type="hidden" name="estado" value="Por Pagar">
      <div class="col-6 mb-1">
        Vencimiento:
      </div>
      <div class="col-6 mb-1">
        <input type="date" name="date_vencimiento" class="form-control" value="<?= date('Y-m-d'); ?>">
      </div>
      <?php
      }
      ?>
      <div class="col-12 mb-1">
        Imagen:
      </div>
      <div class="col-12 mb-1">
        <input type="file" name="file" class="form-control">
      </div>
      <div class="col-12 mb-1">
        Comentarios:
      </div>
      <div class="col-12 mb-1">
        <textarea name="comentarios" class="form-control"></textarea>
      </div>
    
        </div>
        </form>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal" id="agregar-gastos-aceptar-btn">Agregar</button>
        </form>
        </div>
      </div>
    </div>
  </div>
</div>


<div class="d-sm-flex align-items-center justify-content-between mt-5">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-dollar-sign"></i> <b>Ingresos</b></h1>
</div>
<button class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mt-3" id="nuevo-ingresos-btn"><i class="fas fa-fw fa-plus"></i> Agregar Ingreso</button>
<hr />
<?php 
  Msg::show(2,'Ingreso agregado con &eacute;xito','primary');
  Msg::show(5,'Ingreso eliminado con &eacute;xito','danger');
?>


<table class="table table-hover table-striped table-sm">
  <thead class="thead-dark">
    <tr>
      <th>
        <a href="#" class="sort" data-sortorderby="item">&Iacute;tem</a>
      </th>
       <th>
        <a href="#" class="sort" data-sortorderby="item">Fecha y hora</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="tipo_de_gasto">Forma de Pago</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="fecha">Impuestos</a>
      </th>
      <th>
        <a href="#" class="sort" data-sortorderby="id_usuarios">Monto</a>
      </th>
      <th>
      </th>
    </tr>
  </thead>
  <tbody>
  <?php
    foreach($ingresos as $ingreso) {
      ?>
      <tr>
        <td>
          <?= $ingreso->item; ?>
        </td>
        <td>
          <?= datetime2fechayhora($ingreso->creada); ?>
        </td>
        <td>
          <?= $ingreso->forma_de_pago; ?>
        </td>
        <td>
          <?= $ingreso->impuestos; ?>
        </td>
        <td>
          $<?= number_format($ingreso->monto); ?>
        </td>
        <td>
        <?php
          if($GLOBALS['usuario']->nivel == "Administrador") {
            ?>
            <button class="btn btn-danger btn-sm eliminar-ingreso-btn" style="float: right" data-idproyectosingresos="<?= $ingreso->id; ?>">Eliminar Ingreso</button>
            <?php
          }
          ?>
        </td>

      <?php
    }
  ?>
  </tbody>
</table>



<div class="d-sm-flex align-items-center justify-content-between mt-5">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-dollar-sign"></i> <b>Gastos</b></h1>
</div>
<button class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mt-3" id="nuevo-gastos-btn"><i class="fas fa-fw fa-plus"></i> Agregar Gasto</button>
<button class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mt-3" id="agregar-barriles"><i class="fas fa-fw fa-plus"></i> Agregar Barriles</button>
<button class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mt-3" id="agregar-cajas"><i class="fas fa-fw fa-plus"></i> Agregar Cajas</button>


<hr />
<?php 
  Msg::show(3,'Gasto agregado con &eacute;xito','primary');
?>

<table class="table table-hover table-striped table-sm">
  <thead class="thead-dark">
    <tr>
      <th>
      <a href="#" class="sort" data-sortorderby="item">&Iacute;tem</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="tipo_de_gasto">Tipo de Gasto</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="fecha">Fecha</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="id_usuarios">Ingresado por</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="por_pagar">Por Vencer</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="vencido">Vencido</a>
      </th>
      <th>
      <a href="#" class="sort" data-sortorderby="pagado">Pagado</a>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php

        $total_vencido = 0;
        $total_pagado = 0;
        $total_por_pagar = 0;
        $total = 0;
      
      foreach($gastos as $gasto) {

        $contable = false;

        $fecha_1 = date("Y-m")."-01 00:00:00";
        $fecha_2 = date("Y-m")."-31 23:59:59";
        $vencimi = $gasto->date_vencimiento." 00:00:00";
        $hoy = date('Y-m-d 00:00:00');
        $ts_fecha_1 = strtotime($fecha_1);
        $ts_fecha_2 = strtotime($fecha_2);
        $ts_vencimi = strtotime($vencimi);
        $ts_hoy = strtotime($hoy);

        if( $ts_fecha_1 <= $ts_vencimi && $ts_fecha_2 >= $ts_vencimi ) {
          $contable = true;
          $total += $gasto->monto;
        }
        
        $por_pagar = 0;
        $pagado = 0;
        $estado = $gasto->estado;
        if($gasto->estado == "Por Pagar") {
          $por_pagar = $gasto->monto;
          if($contable) {
            $total_por_pagar += $gasto->monto;
          }
          if($ts_vencimi < $ts_hoy) {
            $estado = "Vencido";
          } else {
            $estado = "Por Vencer";
          }
        }
        if($gasto->estado == "Pagado") {
          $pagado = $gasto->monto;
          if($contable) {
            $total_pagado += $gasto->monto;
          }
        }
        
        $usuario_creador = new Usuario($gasto->id_usuarios);
    ?>
    <tr class="tr-gastos" data-idgastos="<?= $gasto->id; ?>">
      <td>
        <?= $gasto->item; ?>
      </td>
      <td>
        <?= $gasto->tipo_de_gasto; ?>
      </td>
      <td>
        <?= date2fecha($gasto->date_vencimiento); ?>
      </td>
      <td>
        <?= $usuario_creador->nombre; ?>
      </td>
      <td>
        $<?= number_format($por_pagar); ?>
      </td>
      <td>
        $0
      </td>
      <td>
        $<?= number_format($pagado); ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
  <tfooter>
    <tr style="background-color: white; border: 1px solid black">
      <td colspan="3">
      </td>
      <td>
        Total:
    </td>
    <td><b>$<?= number_format($total_por_pagar); ?></td>
    <td><b>$<?= number_format($total_vencido); ?></td>
    <td><b>$<?= number_format($total_pagado); ?></td>
    </tr>
  </tfooter>
</table>

<div class="d-sm-flex align-items-center justify-content-between mb-3 mt-5">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800"><i class="fas fa-fw fa-coins"></i> <b>Productos Asignados al Proyecto</b></h1>
  </div>
</div>
<?php 
  Msg::show(4,'Producto asignado al Proyecto con &eacute;xito','primary');
  Msg::show(6,'Producto eliminado del Proyecto con &eacute;xito','danger');
?>
<table class="table table-striped table-sm mb-4">
  <thead class="thead-dark">
    <tr>
      <th>
        Nombre
      </th>
      <th>
        Formato
      </th>
      <th>
        Clasificaci&oacute;n
      </th>
      <th>
        Cantidad
      </th>
      <th>
        C&oacute;digo
      </th>
      <th>
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($proyecto_productos as $pp) {
        $p = new Producto($pp->id_productos);
        $codigo = "";
        if($pp->formato == "Barril") {
          $barril =  new Barril($pp->id_relation);
          $codigo = $barril->codigo;
        }
    ?>
    <tr>
     
      <td>
        <?= $p->nombre; ?>
      </td>
      <td>
        <?= $p->tipo; ?>
      </td>
      <td>
        <?= $p->clasificacion; ?>
      </td>

      <td>
        <?= $p->cantidad; ?>
      </td>
      <td>
        <?= $codigo; ?>
      </td>
      <td>
        <button class="btn btn-danger btn-sm eliminar-pp-btn" style="float: right" data-idproyectosproductos="<?= $pp->id; ?>">Eliminar de Proyecto</button>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>



  <div class="modal" tabindex="-1" role="dialog" id="barrilesModal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Barril</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-6 mb-3">
              Cantidad
            </div>
            <div class="col-6 mb-3">
              <select class="form-control" id="tipos_barril_select">
              </select>
            </div>
            <div class="col-6 mb-3">
              Tipo de Cerveza
            </div>
            <div class="col-6 mb-3">
              <select class="form-control" id="id_productos">
              </select>
            </div>
            <div class="col-6 mb-3">
              C&oacute;digo
            </div>
            <div class="col-6 mb-3">
              <select class="form-control" id="codigo_barril">
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="agregar-barriles-aceptar" data-bs-dismiss="modal">Agregar</button>
        </div>
      </div>
    </div>
  </div>




  <div class="modal" tabindex="-1" role="dialog" id="cajasModal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Caja</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-6 mb-3">
              Cantidad
            </div>
            <div class="col-6 mb-3">
              <select class="form-control" id="tipos_caja_select">
              </select>
            </div>
            <div class="col-6 mb-3">
              Tipo de Cerveza
            </div>
            <div class="col-6 mb-3">
              <select class="form-control" id="cajas_id_productos">
                <?php
                foreach($productos['Caja'] as $producto_caja) {
                  print "<option value='".$producto->id."'>".$producto_caja->nombre."</option>";
                }
                ?>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="agregar-cajas-aceptar" data-bs-dismiss="modal">Agregar</button>
        </div>
      </div>
    </div>
  </div>


  <div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Proyecto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este Proyecto?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-obj-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="eliminar-ingreso-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Ingreso</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este Ingreso del Proyecto?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-ingreso-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

<script>

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
var tipos_barril = <?= json_encode($tipos_barril,JSON_PRETTY_PRINT); ?>;
var tipos_caja = <?= json_encode($tipos_caja,JSON_PRETTY_PRINT); ?>;
var barriles_enplanta_cerveza  = <?= json_encode($barriles_enplanta_cerveza,JSON_PRETTY_PRINT); ?>;
var productos = <?= json_encode($productos,JSON_PRETTY_PRINT); ?>;
var eliminar_proyectos_ingresos = 0;

$(function() {
  armarTiposBarriles();
  armarProductosBarriles();
  armarCodigosBarriles();
  armarTiposCajas();
  armarProductosCajas();

  $.each(obj,function(key,value){
    if(key!="table_name"&&key!="table_fields"){
      $('#proyectos-form input[name="'+key+'"]').val(value);
      $('#proyectos-form textarea[name="'+key+'"]').val(value);
      $('#proyectos-form select[name="'+key+'"]').val(value);
    }
  });

  console.log(productos);

});

$(document).on('click','#nuevo-gastos-btn',function(e) {
    e.preventDefault();
    $('#gastosModal').modal('toggle');
})


$(document).on('click','#items-agregar-aceptar-btn',function() {
  items.push({
    'nombre': $('input[name="items_nombre"]').val(),
    'monto_bruto': $('input[name="items_monto_bruto"]').val(),
    'impuesto': $('select[name="items_impuesto"]').val()
  });
  renderTable();
});

function renderTable() {

  var neto = 0;
  var iva = 0;
  var ila = 0;
  var total = 0;
  var html = '';

  items.forEach(function(item,index){
    neto += parseInt(item.monto_bruto);
    if(item.impuesto == 'IVA + ILA') {
      ila += parseInt(item.monto_bruto) * 0.205;
    }
    html += '<tr class="productos-tr" data-index="' + index +'"><td>' + item.nombre;
    html += '</td><td>' + item.monto_bruto;
    html += '</td><td>' + item.impuesto;
    html += '</td><td style="width:10px"><button class="btn btn-sm item-eliminar-btn" data-index="' + index + '">x</button>';
    html += '</td></tr>';
  });

  iva = neto * 0.19;
  total = Math.round(neto + iva + ila);

  $('#totales-neto').html(neto);
  $('#totales-iva').html(iva);
  $('#totales-ila').html(ila);
  $('#totales-total').html(total);
  $('#items-table').html(html);
  $('input[name="monto"]').val(total);

}

$(document).on('click','.item-eliminar-btn',function(e){
  var index = $(e.currentTarget).data('index');
  items.splice(index,1);
  renderTable();
});

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("proyectos");
  //data['items'] = items;

  console.log(data);


  $.post(url,data,function(raw){
    console.log(raw);
    //return false;
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-proyectos&id=" + response.obj.id + "&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#guardar-y-agregar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("proyectos");
  data['items'] = items;

  console.log(data);


  $.post(url,data,function(raw){
    console.log(raw);
    //return false;
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=nuevo-proyectos&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


$(document).on('click','#guardar-gastos-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("gastos");

  console.log(data);


  $.post(url,data,function(raw){
    console.log(raw);
    //return false;
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-proyectos&id=" + response.obj.id + "&msg=3#agregar-gastos-btn";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


$(document).on('click','#nuevo-ingresos-btn',function(e){
  e.preventDefault();
  $('#proyectos_ingresos-form input[name="item"]').val('Venta');
  $('#proyectos_ingresos-form input[name="monto"]').val('0');
  $('#proyectos_ingresos-form select[name="impuesto"]').val('IVA');
  $('#proyectos_ingresos-form select[name="forma_de_pago"]').val('Tarjeta');
  $('#ingresosModal').modal('toggle');
});


function armarTiposBarriles() {

  $('#tipos_barril_select').empty();
  var html = '';

  tipos_barril.forEach(function(tb) {
    html += '<option>' + tb + '</option>';
  });

  $('#tipos_barril_select').html(html);

}

function armarProductosBarriles() {

  $('#id_productos').empty();
  var html = '';

  productos_barril = productos['Barril'][$('#tipos_barril_select').val()];

  productos_barril.forEach(function(pb) {
    html += '<option value="' + pb.id + '">' + pb.nombre + '</option>';
  });

  $('#id_productos').html(html);

}

function armarCodigosBarriles() {

  $('#codigo_barril').empty();

  var html = '';

  var barriles_cerveza = barriles_enplanta_cerveza.filter((b) => b.tipo_barril == $('#tipos_barril_select').val());
  console.log(barriles_cerveza);
  barriles_cerveza.forEach(function(barril) {
    html += '<option value="' + barril.id + '">' + barril.codigo + '</option>';
  });

  $('#codigo_barril').html(html);

  if(html == '') {
    $('#agregar-barriles-aceptar').attr('DISABLED',true);
  } else {
    $('#agregar-barriles-aceptar').attr('DISABLED',false);
  }

}


function armarTiposCajas() {

  $('#tipos_caja_select').empty();
  var html = '';

  tipos_caja.forEach(function(tb) {
    html += '<option>' + tb + '</option>';
  });

  $('#tipos_caja_select').html(html);

}

function armarProductosCajas() {

  $('#cajas_id_productos').empty();
  var html = '';

  productos_cajas = productos['Caja'][$('#tipos_caja_select').val()];

  productos_cajas.forEach(function(p) {
    html += '<option value="' + p.id + '">' + p.nombre + '</option>';
  });

  $('#cajas_id_productos').html(html);

}


$(document).on('click','#agregar-barriles-aceptar',function() {

  var prod_arr = productos['Barril'][$('#tipos_barril_select').val()];
  var prod = prod_arr.find((p) => p.id == $('#id_productos').val());
  
  var data = {
    'formato': 'Barril',
    'id_proyectos': '<?= $obj->id; ?>',
    'id_productos': prod.id,
    'monto': prod.total_bruto,
    'id_relation': $('#codigo_barril').val(),
    'entidad': 'proyectos_productos',
    'id': ''
  };

  var url = "./ajax/ajax_guardarEntidad.php";

  console.log(data);


  $.post(url,data,function(raw){
    console.log(raw);
    //return false;
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-proyectos&id=<?= $obj->id; ?>&msg=4#agregar-barriles-btn";
    }
  }).fail(function(){
    alert("No funciono");
  });

});


$(document).on('click','#agregar-cajas-aceptar',function() {

  console.log($('#tipos_caja_select').val());

  var prod_arr = productos['Caja'][$('#tipos_caja_select').val()];
  var prod = prod_arr.find((p) => p.id == $('#cajas_id_productos').val());
  
  var data = {
    'formato': 'Caja',
    'id_proyectos': '<?= $obj->id; ?>',
    'id_productos': prod.id,
    'monto': prod.total_bruto,
    'id_relation': $('#codigo_barril').val(),
    'entidad': 'proyectos_productos',
    'id': ''
  };

  var url = "./ajax/ajax_guardarEntidad.php";

  console.log(data);


  $.post(url,data,function(raw){
    console.log(raw);
    //return false;
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-proyectos&id=<?= $obj->id; ?>&msg=4#agregar-barriles-btn";
    }
  }).fail(function(){
    alert("No funciono");
  });

});








$(document).on('click','#agregar-barriles',function() {
  $('#barrilesModal').modal('toggle');
});

$(document).on('click','#agregar-cajas',function(e) {
  e.preventDefault();
  $('#cajasModal').modal('toggle');
});


$(document).on('change','#tipos_barril_select',function(e){
  e.preventDefault();
  armarProductosBarriles();
  armarCodigosBarriles();
});

$(document).on('change','#tipos_caja_select',function(e){
  e.preventDefault();
  armarProductosCajas();
});

$(document).on('click','#agregar-gastos-aceptar-btn',function(e){
  $('#gastos-form').submit();
});




$(document).on('click','#ingresos-agregar-aceptar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("proyectos_ingresos");

  console.log(data);


  $.post(url,data,function(raw){
    console.log(raw);
    //return false;
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-proyectos&id=" + response.obj.id_proyectos + "&msg=2&id_proyectos_ingresos=" + response.obj.id + "#agregar-ingresos-btn";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','.tr-gastos',function(e) {
    window.location.href = "./?s=detalle-gastos&id=" + $(e.currentTarget).data('idgastos');
});

$(document).on('click','.eliminar-pp-btn',function(e){

  e.preventDefault();

  var data = {
    'id': $(e.currentTarget).data('idproyectosproductos'),
    'modo': 'proyectos_productos'
  }
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-proyectos&id=<?= $obj->id; ?>&msg=6";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','.eliminar-obj-btn',function(e){
  e.preventDefault();
  $('#eliminar-obj-modal').modal('toggle');
})

$(document).on('click','#eliminar-obj-aceptar',function(e){

  e.preventDefault();

  var data = {
    'id': obj.id,
    'modo': obj.table_name
  }
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=" + response.table_name + "&msg=2";
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','.eliminar-ingreso-btn',function(e){
  e.preventDefault();
  eliminar_proyectos_ingresos = $(e.currentTarget).data('idproyectosingresos');
  $('#eliminar-ingreso-modal').modal('toggle');
})

$(document).on('click','#eliminar-ingreso-aceptar',function(e){

  e.preventDefault();

  var data = {
    'id': eliminar_proyectos_ingresos,
    'modo': 'proyectos_ingresos'
  }

  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-proyectos&id=<?= $obj->id; ?>&msg=5";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


</script>
