<?php

    if(!validaIdExists($_GET,'id')) {
        die();
    }

    $obj = new CompraDeInsumo($_GET['id']);
    $cdii = CompraDeInsumoInsumo::getAll("WHERE id_compras_de_insumos='".$obj->id."'");

    $proveedor = new Proveedor($obj->id_proveedores);

    $proveedores = Proveedor::getAll();
    $tipos_de_insumos = TipoDeInsumo::getAll();

    $usuario = $GLOBALS['usuario'];

?>
<style>
.tr-gasto {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Detalle Compra de Insumos #<?= $obj->id; ?>
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
<form id="compras-de-insumos-form">
  <input type="hidden" name="id_usuarios" value="<?= $GLOBALS['usuario']->id; ?>">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="compras_de_insumos">
  <div class="row">
    <div class="col-md-8 row">
    <div class="col-6 mb-1">
        Fecha:
      </div>
      <div class="col-6 mb-1">
        <input type="date" class="form-control" name="date">
      </div>
      <div class="col-6 mb-1">
        Proveedor:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" value="<?= $proveedor->nombre; ?>" DISABLED>
      </div>
      <div class="col-6 mb-1">
        Factura:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="factura" value="">
      </div>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <select name="estado" class="form-control">
            <option>Pagado</option>
            <option>Por Pagar</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        Total:
      </div>
      <div class="col-6 mb-1">
      <div class="input-group">
              <span class="input-group-text" style="border-radius: 10px 0px 0px 10px">$</span>
              <input type="number" class="form-control acero" name="monto" value="0">
            </div>
      </div>
      <div class="col-12">
        <table class="table table-striped mt-4" id="insumos-table">
          <?php
            foreach($cdii as $cdinsumo) {
              $insumo = new Insumo($cdinsumo->id_insumos);
              ?>
              <tr class="insumos-tr">
                <td><b><?= $insumo->nombre; ?></b></td>
                <td><b><?= $cdinsumo->cantidad." ".$insumo->unidad_de_medida; ?></b></td>
                <td><b>$<?= number_format($cdinsumo->monto); ?></b></td>
              </tr>
              <?php
            }
          ?>
        </table>
      </div>
      <div class="col-12 mb-1">
        Comentarios:
      </div>
      <div class="col-12 mb-1">
        <textarea name="comentarios" class="form-control"></textarea>
      </div>
      
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        &nbsp;
        <button class="btn btn-sm btn-primary" id="guardar-btn">Guardar</button>
      </div>
    </div>
  </div>
  </form>

<script>

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
$(document).ready(function(){

  if(obj.id!="") {
    $.each(obj,function(key,value){
      if(key!="table_name"&&key!="table_fields"){
        $('input[name="'+key+'"]').val(value);
        $('textarea[name="'+key+'"]').val(value);
        $('select[name="'+key+'"]').val(value);
      }
    });
  }

});

$(document).on('click','#guardar-btn',function(e){

e.preventDefault();

var url = "./ajax/ajax_guardarEntidad.php";
var data = getDataForm("compras-de-insumos");

$.post(url,data,function(raw){
  console.log(raw);
  var response = JSON.parse(raw);
  if(response.mensaje!="OK") {
    alert("Algo fallo");
    return false;
  } else {
    window.location.href = "./?s=detalle-compras_de_insumos&id=" + response.obj.id + "&msg=1";
  }
}).fail(function(){
  alert("No funciono");
});
});

</script>
