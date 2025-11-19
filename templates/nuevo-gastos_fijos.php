<?php

    $tipos_de_gastos = TipoDeGasto::getAll();

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
        Nuevo Gasto Fijo
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
<form id="gastos_fijos-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="gastos_fijos">
  <input type="hidden" name="id_usuarios" value="<?= $GLOBALS['usuario']->id; ?>">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-1">
        Nombre
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="item">
      </div>
      <div class="col-6 mb-1">
        Tipo de Gasto
      </div>
      <div class="col-6 mb-1">
        <select name="id_tipos_de_gastos" class="form-control">
        <?php
                foreach($tipos_de_gastos as $tipo) {
                    print "<option value='".$tipo->id."'>".$tipo->nombre."</option>";
                }
            ?>
        </select>
      </div>


      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        &nbsp;
        <div>
          <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
          <button class="btn btn-sm btn-primary" id="guardar-y-agregar-btn"><i class="fas fa-fw fa-save"></i> Guardar y agregar Nuevo</button>
        </div>
      </div>
    </div>
  </div>
</form>

<script>
$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("gastos_fijos");

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=gastos_fijos&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#guardar-y-agregar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("gastos_fijos");

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=nuevo-gastos_fijos&msg=1";
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

</script>
