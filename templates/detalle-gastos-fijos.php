<?php

  if(!validaIdExists($_GET,'id')) {
      die();
  }

  $msg = 0;
  if(isset($_GET['msg'])) {
  $msg = $_GET['msg'];
  }

  $mes = date('m');
  if(validaIdExists($_GET,'mes')) {
    $mes = $_GET['mes'];
  }

  $ano = date('Y');
  if(validaIdExists($_GET,'ano')) {
    $ano = $_GET['ano'];
  }

  $usuario = $GLOBALS['usuario'];

  $obj = new GastoFijo($_GET['id']);
  $obj->getGastosMes($mes,$ano);

  $gastos_fijos_mes = GastoFijoMes::getAll("WHERE id_gastos_fijos='".$obj->id."' ORDER BY mes asc, ano asc");
  $tipos_de_gastos = TipoDeGasto::getAll();
  $lineas_de_negocio = LineaDeNegocio::getAll("ORDER BY nombre asc");
  $obj_lineas_de_negocio = GastoLineaDeNegocio::getAll("WHERE id_gastos_fijos='".$obj->id."'");

?>
<style>
.tr-gastos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Detalle Gasto Fijo
      </b>
    </h1>
  </div>
  <div>
    <div>
      <?php $usuario->printReturnBtn(); ?>
    </div>
  </div>
</div>
<hr />
<?php 
  Msg::show(1,'Gasto Fijo guardado con &eacute;xito','primary');
?>
<form id="gastos-fijos-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="gastos-fijos">
  <div class="row mb-5">
    <div class="col-md-6">
      <div class="row">
      <div class="col-6 mb-1">
        Item
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="item">
      </div>
      <div class="col-6 mb-1">
        Tipo de Gasto
      </div>
      <div class="col-6 mb-1">
        <select name="tipo_de_gasto" class="form-control">
        <?php
                foreach($tipos_de_gastos as $tipo) {
                    print "<option value='".$tipo->id."'>".$tipo->nombre."</option>";
                }
            ?>
        </select>
      </div>
      <div class="col-12 mb-1">
          Comentarios:
        </div>
        <div class="col-12 mb-1">
          <textarea name="comentarios" class="form-control"></textarea>
        </div>
    </div>
  </div>
  <div class="col-md-6">
      <div class="d-flex justify-content-between mb-4">
        <h3>Lineas de Negocio</h3>
        <button class="btn btn-sm btn-primary" id="agregar-lineas-de-negocio-btn"><i class="fas fa-fw fa-plus"></i> Agregar</button>
      </div>
      <table class="table table-sm table-striped">
        <thead>
          <tr>
            <th class="px-3">
              Nombre
            </th>
            <th>
              Porcentaje
            </th>
            <th>
            </th>
        </thead>
        <tbody>
          <?php
            foreach($obj_lineas_de_negocio as $oldn) {
                $ldn = new LineaDeNegocio($oldn->id_lineas_de_negocio);
              ?>
              <tr>
                <td  class="px-3" style="vertical-align: middle">
                  <a href="./?s=detalle-lineas-de-negocio&id=<?= $ldn->id; ?>"><?= $ldn->nombre; ?></a>
                </td>
                <td style="vertical-align: middle">
                  <?= $oldn->porcentaje; ?>%
                </td>
                <td class="text-center p-o">
                  <button class="btn btn-sm btn-default m-0 editar-tdgldn" data-id="<?= $oldn->id; ?>"><i class="fas fa-fw fa-pen"></i></button>
                  <button class="btn btn-sm btn-default m-0 eliminar-tdgldn" data-id="<?= $oldn->id; ?>"><i class="fas fa-fw fa-trash"></i></button>
                </td>
              </tr>
              <?php
            }
          ?>
        </tbody>
      </table>
  </div>
</div>

<div class="d-flex justify-content-between">
  <button class="btn btn-sm btn-danger" id="eliminar-obj-btn"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
  <div>
    <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
  </div>
</div>
  
<hr/>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Periodos
      </b>
    </h1>
  </div>
  <div>
  </div>
</div>

<div class="mt-5">

      <table class="table table-bordered table-sm table-striped mt-3">
      <thead>
          <tr>
              <th>
                  <b>Mes</b>
              </th>
              <th>
                  <b>Año</b>
              </th>
              <th>
                  <b>Proy. Neto</b>
              </th>
              <th>
                  <b>Proy. Impuesto</b>
              </th>
              <th>
                  <b>Proy. Bruto</b>
              </th>
              <th>
                  <b>Real Neto</b>
              </th>
              <th>
                  <b>Real Impuesto</b>
              </th>
              <th>
                  <b>Real Bruto</b>
              </th>
              <th>
              </th>
          </tr>
      </thead>
      <tbody>
      <?php
      $totales['proyectado_neto'] = 0;
      $totales['proyectado_impuesto'] = 0;
      $totales['proyectado_bruto'] = 0;
      $totales['real_neto'] = 0;
      $totales['real_impuesto'] = 0;
      $totales['real_bruto'] = 0;

      foreach($gastos_fijos_mes as $gfm) {
        $totales['proyectado_neto'] += $gfm->proyectado_neto;
        $totales['proyectado_impuesto'] += $gfm->proyectado_impuesto;
        $totales['proyectado_bruto'] += $gfm->proyectado_bruto;
        $totales['real_neto'] += $gfm->real_neto;
        $totales['real_impuesto'] += $gfm->real_impuesto;
        $totales['real_bruto'] += $gfm->real_bruto;
      ?>
      <tr style="vertical-align: middle">
            <td>
              <a href="./s=detalle-gastos-fijos-mes&id=<?= $gfm->id; ?>">
                <?= int2mes($gfm->mes); ?>
              </a>
            </td>
            <td>
              <a href="./s=detalle-gastos-fijos-mes&id=<?= $gfm->id; ?>">
                <?= $gfm->ano; ?>
              </a>
            </td>
            <td>
              <input type="number" class="form-control acero" name="<?= $gfm->id; ?>_proyectado_neto" value="<?= $gfm->proyectado_neto; ?>" data-idgastosfijosmes="<?= $gfm->mes; ?>">
            </td>
            <td>
              <input type="number" class="form-control acero" name="<?= $gfm->id; ?>_proyectado_impuesto" value="<?= $gfm->proyectado_impuesto; ?>" data-idgastosfijosmes="<?= $gfm->mes; ?>">
            </td>
            <td>
              <input type="number" class="form-control acero" name="<?= $gfm->id; ?>_proyectado_bruto" value="<?= $gfm->proyectado_bruto; ?>" data-idgastosfijosmes="<?= $gfm->mes; ?>">
            </td>
            <td>
              <input type="number" class="form-control acero" name="<?= $gfm->id; ?>_real_neto" value="<?= $gfm->real_neto; ?>" data-idgastosfijosmes="<?= $gfm->mes; ?>">
            </td>
            <td>
              <input type="number" class="form-control acero" name="<?= $gfm->id; ?>_real_impuesto" value="<?= $gfm->real_impuesto; ?>" data-idgastosfijosmes="<?= $gfm->mes; ?>">
            </td>
            <td>
              <input type="number" class="form-control acero" name="<?= $gfm->id; ?>_real_bruto" value="<?= $gfm->real_bruto; ?>" data-idgastosfijosmes="<?= $gfm->mes; ?>">
            </td>
            <td>
              <button class="btn btn-default btn-sm eliminar-gastos-fijos-mes-btn" data-idgastosfijosmes="<?= $gfm->id; ?>">x</button>
            </td>
        </tr>
        <tr>
            <td colspan="9">
                Detalle:
            </td>
        </tr>
        <?php
        }
        ?>
      </tbody>
      <tfoot>
          <tr>
              <th colspan="2">
                  <b>TOTALES</b>
              </th>
              <th>
                  <b>$<?= number_format($totales['proyectado_neto']); ?></b>
              </th>
              <th>
                  <b>$<?= number_format($totales['proyectado_impuesto']); ?></b>
              </th>
              <th>
                  <b>$<?= number_format($totales['proyectado_bruto']); ?></b>
              </th>
              <th>
                  <b>$<?= number_format($totales['real_neto']); ?></b>
              </th>
              <th>
                  <b>$<?= number_format($totales['real_impuesto']); ?></b>
              </th>
              <th>
                  <b>$<?= number_format($totales['real_bruto']); ?></b>
              </th>
              <th>
              </th>
          </tr>
      </tfoot>
      </table>
      <div class="d-flex justify-content-between">
        &nbsp;
        <button class="btn btn-sm btn-primary" id="agregar-mes-btn"><i class="fas fa-fw fa-plus"></i> Agregar Mes</button>
      </div>


  </div>
<br/>
<hr>
<br/>

<div class="d-flex justify-content-between">
  &nbsp;
  <div>
    <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
  </div>
</div>

</form>

  <div class="modal fade" tabindex="-1" role="dialog" id="ingresar-gastos-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Ingresar Gasto</h5>
          <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
            <i class="ki-duotone ki-cross fs-1">
              <span class="path1"></span>
              <span class="path2"></span>
            </i>
          </div>
        </div>
        <div class="modal-body">
          <form action="./php/ingresar-gastos.php" method="POST" enctype="multipart/form-data" id="ingresar-gastos-form">
          <input type="hidden" name="id_gastos_fijos" value="<?= $obj->id; ?>">
          <input type="hidden" name="return_url" value="../?s=detalle-gastos-fijos&id=<?= $obj->id; ?>">
          <input type="hidden" name="tipo_de_gasto" value="<?= $obj->tipo_de_gasto; ?>">
          <input type="hidden" name="item" value="<?= $obj->item; ?>">
          <div class="row">
            <div class="col-6 mb-1">
              Fecha:
            </div>
            <div class="col-6 mb-1">
              <input type="date" class="form-control" value="<?= date('Y-m-d'); ?>" name="date">
            </div>
            <div class="col-6 mb-1">
              Monto Neto:
            </div>
            <div class="col-6 mb-1">
              <div class="input-group">
                <span class="input-group-text" id="basic-addon1" style="border-radius: 10px 0px 0px 10px">$</span>
                <input type="number" class="form-control acero modifica-bruto-variable" value="0" name="monto_neto" id="ingresar-gastos-monto_neto-input">
              </div>
            </div>
            <div class="col-6 mb-1">
              Monto Impuesto:
            </div>
            <div class="col-6 mb-1">
              <div class="input-group">
                <span class="input-group-text" id="basic-addon1" style="border-radius: 10px 0px 0px 10px">$</span>
                <input type="number" class="form-control acero modifica-bruto-variable" value="0" name="monto_impuesto" id="ingresar-gastos-monto_impuesto-input">
              </div>
            </div>
            <div class="col-6 mb-1">
              Monto:
            </div>
            <div class="col-6 mb-1">
              <div class="input-group">
                <span class="input-group-text" id="basic-addon1" style="border-radius: 10px 0px 0px 10px">$</span>
                <input type="number" class="form-control acero modifica-bruto-variable" value="0" name="monto_bruto" id="ingresar-gastos-monto_bruto-input">
              </div>
            </div>
            <div class="col-6 mb-1">
              Estado:
            </div>
            <div class="col-6 mb-1">
              <select class="form-control" name="estado">
                <option>Pagado</option>
                <option>Por Pagar</option>
              </select>
            </div>
            <div class="col-6 mb-1">
              Forma de pago:
            </div>
            <div class="col-6 mb-1">
              <select class="form-control" name="forma_de_pago">
                <option>Transferencia</option>
                <option>Credito</option>
                <option>Paypal</option>
                <option>Western Union</option>
                <option>Aplicacion</option>
              </select>
            </div>
            <div class="col-12 mb-3">
              Comprobante:
            </div>
            <div class="col-12 mb-3">
              <input type="file" class="form-control" name="comprobante-img">
            </div>
            <div class="col-12 mb-3">
              Observaciones:
            </div>
            <div class="col-12 mb-1">
              <textarea class="form-control" name="observaciones"></textarea>
            </div>
          </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="ingresar-gastos-aceptar-btn">Ingresar</button>
        </div>
      </div>
    </div>
  </div>



  <div class="modal fade" tabindex="-1" role="dialog" id="agregar-mes-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Mes</h5>
          <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
            <i class="ki-duotone ki-cross fs-1">
              <span class="path1"></span>
              <span class="path2"></span>
            </i>
          </div>
        </div>
        <div class="modal-body">
          <form id="agregar-mes-form">
          <input type="hidden" name="id" value="">
          <input type="hidden" name="entidad" value="gastos_fijos_mes">
          <input type="hidden" name="id_gastos_fijos" value="<?= $obj->id; ?>">
          <div class="row">
            <div class="col-6 mb-1">
              Mes:
            </div>
            <div class="col-6 mb-1">
              <select class="form-control" name="mes">
                <option value="1">Enero</option>
                <option value="2">Febrero</option>
                <option value="3">Marzo</option>
                <option value="4">Abril</option>
                <option value="5">Mayo</option>
                <option value="6">Junio</option>
                <option value="7">Julio</option>
                <option value="8">Agosto</option>
                <option value="9">Septiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
              </select>
            </div>
            <div class="col-6 mb-1">
              Año:
            </div>
            <div class="col-6 mb-1">
              <select class="form-control" name="ano">
                <option>2024</option>
                <option>2025</option>
              </select>
            </div>
          </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="agregar-mes-aceptar-btn">Agregar</button>
        </div>
      </div>
    </div>
  </div>


  <div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Gasto Fijo</h5>
          <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
            <i class="ki-duotone ki-cross fs-1">
              <span class="path1"></span>
              <span class="path2"></span>
            </i>
          </div>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este Gasto Fijo?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-obj-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>


<div class="modal fade" tabindex="-1" role="dialog" id="agregar-lineas-de-negocio-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Agregar Linea de Negocio</h5>
          <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
            <i class="ki-duotone ki-cross fs-1">
              <span class="path1"></span>
              <span class="path2"></span>
            </i>
          </div>
        </div>
        <div class="modal-body">
          <form id="gastos_lineas_de_negocio-form">
            <input type="hidden" name="id" value="">
            <input type="hidden" name="id_gastos_fijos" value="<?= $obj->id; ?>">
            <input type="hidden" name="entidad" value="gastos_lineas_de_negocio">
          <div class="row">
            <div class="col-6">
              Linea de Negocio:
            </div>
            <div class="col-6">
              <select class="form-control" name="id_lineas_de_negocio">
                <?php
                  foreach($lineas_de_negocio as $linea_de_negocio) {
                    print "<option value='".$linea_de_negocio->id."'>".$linea_de_negocio->nombre."</option>";
                  }
                ?>
              </select>
            </div>
            <div class="col-6">
              Porcentaje:
            </div>
            <div class="col-6">
              <div class="input-group">
                <input type="number"  value="0" name="porcentaje" class="form-control">
                <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">%</span>
              </div>
            </div>
          </div>
        </div>
        </form>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="agregar-lineas-de-negocio-aceptar-btn" data-bs-dismiss="modal"><i class="fas fa-fw fa-save"></i> Agregar</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" tabindex="-1" role="dialog" id="eliminar-lineas-de-negocio-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Linea de Negocio</h5>
          <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
            <i class="ki-duotone ki-cross fs-1">
              <span class="path1"></span>
              <span class="path2"></span>
            </i>
          </div>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar esta Linea de Negocio de este Gasto Fijo?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-lineas-de-negocio-aceptar-btn" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>


<div class="modal fade" tabindex="-1" role="dialog" id="editar-lineas-de-negocio-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar Linea de Negocio</h5>
          <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
            <i class="ki-duotone ki-cross fs-1">
              <span class="path1"></span>
              <span class="path2"></span>
            </i>
          </div>
        </div>
        <div class="modal-body">
          <form id="editar-gastos_lineas_de_negocio-form">
            <input type="hidden" name="id" value="">
            <input type="hidden" name="id_gastos_fijos" value="<?= $obj->id; ?>">
            <input type="hidden" name="entidad" value="gastos_lineas_de_negocio">
          <div class="row">
            <div class="col-6">
              Linea de Negocio:
            </div>
            <div class="col-6">
              <select class="form-control" name="id_lineas_de_negocio">
                <?php
                  foreach($lineas_de_negocio as $linea_de_negocio) {
                    print "<option value='".$linea_de_negocio->id."'>".$linea_de_negocio->nombre."</option>";
                  }
                ?>
              </select>
            </div>
            <div class="col-6">
              Porcentaje:
            </div>
            <div class="col-6">
              <div class="input-group">
                <input type="number"  value="0" name="porcentaje" class="form-control">
                <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">%</span>
              </div>
            </div>
          </div>
        </div>
        </form>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="editar-lineas-de-negocio-aceptar-btn" data-bs-dismiss="modal"><i class="fas fa-fw fa-save"></i> Guardar Modificación</button>
        </div>
      </div>
    </div>
  </div>


<script>

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
var mes = '<?= intval($mes); ?>';
var ano = '<?= intval($ano); ?>';
var gastos_fijos_mes = <?= json_encode($gastos_fijos_mes,JSON_PRETTY_PRINT); ?>;
var obj_lineas_de_negocio = <?= json_encode($obj_lineas_de_negocio,JSON_PRETTY_PRINT); ?>;

$(document).ready(function() {

  $('#mes-select').val(mes);
  $('#ano-select').val(ano);
  
  $.each(obj,function(key,value){
    if(key!="table_name"&&key!="table_fields"){
      $('#gastos-fijos-form input[name="'+key+'"]').val(value);
      $('#gastos-fijos-form textarea[name="'+key+'"]').val(value);
      $('#gastos-fijos-form select[name="'+key+'"]').val(value);
    }
  });
});

$(document).on('click','#agregar-mes-aceptar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("agregar-mes");
  console.log(data);

  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-gastos-fijos&id=" + obj.id + "&msg=5";
    }
  }).fail(function(){
    alert("No funciono");
  });
});



$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("gastos-fijos");
  var nuevos_gastos_fijos_mes = [];

  gastos_fijos_mes.forEach(function(gfm){
    gfm.proyectado_neto = $('input[name="' + gfm.id + '_proyectado_neto"]').val();
    gfm.proyectado_impuesto = $('input[name="' + gfm.id + '_proyectado_impuesto"]').val();
    gfm.proyectado_bruto = $('input[name="' + gfm.id + '_proyectado_bruto"]').val();
    gfm.real_neto = $('input[name="' + gfm.id + '_real_neto"]').val();
    gfm.real_impuesto = $('input[name="' + gfm.id + '_real_impuesto"]').val();
    gfm.real_bruto = $('input[name="' + gfm.id + '_real_bruto"]').val();
  });

  data['gastos_fijos_mes'] = gastos_fijos_mes;

  console.log(data);

  $.post(url,data,function(raw){
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-gastos-fijos&id=" + obj.id + "&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});





$(document).on('click','#guardar-y-agregar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("gastos-fijos");

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=nuevo-gastos-fijos&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});

$(document).on('keyup','.acero',function(){
  $(this).val($(this).val().replace(/\D/g,''));
  if($(this).val() == "") {
    $(this).val(0);
  }
  $(this).val(parseInt($(this).val()));
});

$(document).on('click','#ingresar-gastos-btn',function(e){
  e.preventDefault();
  $('#ingresar-gastos-monto-input').val('0');
  $('#ingresar-gastos-modal').modal('toggle');
});

$(document).on('click','#ingresar-gastos-aceptar-btn',function(e){
  $('#ingresar-gastos-form').submit();
});

$(document).on('click','.tr-gastos',function(e){
  window.location.href = "./?s=detalle-gastos&id=" + $(e.currentTarget).data('idgastos');
})

$(document).on('change','.modifica-bruto',function(e){
  var monto_bruto = 0;
  monto_bruto = parseInt($('input[name="monto_neto"]').val()) + parseInt($('input[name="monto_impuesto"]').val());
  $('input[name="monto_bruto"]').val(monto_bruto);
});

$(document).on('change','.modifica-bruto-variable',function(e){
  var monto_bruto = 0;
  monto_bruto = parseInt($('#ingresar-gastos-monto_neto-input').val()) + parseInt($('#ingresar-gastos-monto_impuesto-input').val());
  $('#ingresar-gastos-monto_bruto-input').val(monto_bruto);
});

$(document).on('change','.date-select', function(e) {
  window.location.href = "./?s=detalle-gastos-fijos&id=" + obj.id + "&mes=" + $('#mes-select').val() + "&ano=" + $('#ano-select').val();
});

$(document).on('click','#agregar-mes-btn',function(e){
  e.preventDefault();
  $('#agregar-mes-modal').modal('toggle');
});





$(document).on('click','#eliminar-obj-btn',function(e){
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
    console.log(response);
    response = JSON.parse(response);
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=gastos-fijos-vista-anual&msg=2";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


$(document).on('click','.eliminar-gastos-fijos-mes-btn',function(e){

  e.preventDefault();

  var data = {
    'id': $(e.currentTarget).data('idgastosfijosmes'),
    'modo': 'gastos_fijos_mes'
  }
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.reload();
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});



$(document).on('click','#agregar-lineas-de-negocio-aceptar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("gastos_lineas_de_negocio");
  console.log(data);

  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-gastos-fijos&id=" + obj.id + "&msg=2";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


$(document).on('click','#agregar-lineas-de-negocio-btn',function(e){
  e.preventDefault();
  $('#agregar-lineas-de-negocio-modal').modal('toggle');
});


$(document).on('click','.eliminar-tdgldn',function(e){
  e.preventDefault();
  lineas_de_negocio_selected = $(e.currentTarget).data('id');
  $('#eliminar-lineas-de-negocio-modal').modal('toggle');
});

$(document).on('click','#eliminar-lineas-de-negocio-aceptar-btn',function(e){

  e.preventDefault();

  var data = {
    'id': lineas_de_negocio_selected,
    'modo': 'gastos_lineas_de_negocio'
  }
  var url = './ajax/ajax_eliminarEntidad.php';
  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-gastos-fijos&id=" + obj.id + "&msg=4";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','.editar-tdgldn',function(e){
  e.preventDefault();
  $('#editar-lineas-de-negocio-modal').modal('toggle');
  console.log($(e.currentTarget).data('id'));
  var linea_de_negocio = obj_lineas_de_negocio.find((l) => l.id == $(e.currentTarget).data('id'));
  console.log(linea_de_negocio);

  $('#editar-gastos_lineas_de_negocio-form input[name="id"]').val(linea_de_negocio.id);
  $('#editar-gastos_lineas_de_negocio-form input[name="porcentaje"]').val(linea_de_negocio.porcentaje);
  $('#editar-gastos_lineas_de_negocio-form select[name="id_lineas_de_negocio"]').val(linea_de_negocio.id_lineas_de_negocio);

});

$(document).on('click','#editar-lineas-de-negocio-aceptar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("editar-gastos_lineas_de_negocio");
  console.log(data);

  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-gastos-fijos&id=" + obj.id + "&msg=2";
    }
  }).fail(function(){
    alert("No funciono");
  });
});

</script>
