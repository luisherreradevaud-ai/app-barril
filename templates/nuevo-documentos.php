<?php
  $clientes = Cliente::getAll("WHERE estado='Activo'");
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
        <i class="fas fa-fw fa-plus"></i> Nuevo Documento
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
  Msg::show(1,'Documento guardado con &eacute;xito','primary');
?>

<form id="documentos-form" action="./php/procesar.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="documentos">
  <input type="hidden" name="modo" value="nuevo-entidad-con-media">
  <input type="hidden" name="redirect" value="">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-1">
        Cliente
      </div>
      <div class="col-6 mb-1">
        <select name="id_clientes" class="form-control">
        <option></option>
        <?php
                foreach($clientes as $cliente) {
                    print "<option value='".$cliente->id."'>".$cliente->nombre."</option>";
                }
            ?>
        </select>
      </div>
      <div class="col-6 mb-1">
        Folio
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="folio">
      </div>
      <div class="col-6 mb-1">
        Monto
      </div>
      <div class="col-6 mb-1">
        <div class="input-group">
          <span class="input-group-text" id="basic-addon1" style="border-radius: 10px 0px 0px 10px">$</span>
          <input type="text" class="form-control acero" name="monto" value="0">
        </div>
      </div>
      <div class="col-12 mb-1">
        Imagen:
      </div>
      <div class="col-12 mb-1">
        <input type="file" name="file" class="form-control">
      </div>

      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        &nbsp;
        <div>
          <button class="btn btn-sm btn-primary guardar-btn" data-redirect="detalle-documentos"><i class="fas fa-fw fa-save"></i> Guardar</button>
          <button class="btn btn-sm btn-primary guardar-btn" data-redirect="nuevo-documentos"><i class="fas fa-fw fa-save"></i> Guardar y agregar Nuevo</button>
        </div>
      </div>
    </div>
  </div>
</form>

<script>

$(document).on('click','.guardar-btn',function(e){

  e.preventDefault();

  if($('select[name="id_clientes"]').val() == "") {
    alert("Debe ingresar un Cliente");
    return false;
  }

  if($('input[name="monto"]').val() == "") {
    $('input[name="monto"]').val(0);
  }

  if($('input[name="monto"]').val() < 1) {
    alert("El monto debe ser mayor a 0.");
    return false;
  }

  $('input[name="redirect"]').val($(e.currentTarget).data('redirect'));

  $('#documentos-form').submit();

});


$(document).on('keyup','.acero',function(){
  $(this).val($(this).val().replace(/\D/g,''));
  if($(this).val() == "") {
    $(this).val(0);
  }
  $(this).val(parseInt($(this).val()));
});

</script>
