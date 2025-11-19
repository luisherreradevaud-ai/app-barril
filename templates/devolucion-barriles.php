<?php

  //checkAutorizacion(["Jefe de Planta","Administrador","Repartidor"]);

  $msg = 0;
  if(isset($_GET['msg'])){
    $msg = $_GET['msg'];
  }

  $barriles = Barril::getAll("WHERE clasificacion='Cerveza' AND estado!='En planta'");
  $barriles_en_planta = Barril::getAll("WHERE clasificacion='Cerveza' AND estado='En planta'");

?>
<style>
.tr-barriles {
  cursor: pointer;
}
</style>
<?php
if($msg == 1) {
  $barril = new Barril($_GET['id']);
?>
<div class="alert alert-info" role="alert" >Barril <b><?= $barril->tipo_barril."-".$barril->codigo; ?></b> ha sido marcado como "<b>En planta</b>".</div>
<?php
}
?>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Devoluci&oacute;n de Barriles
      </b>
    </h1>
  </div>
  <div>
  </div>
</div>
<hr />

<form id="devolucion-barriles-form">
<input type="hidden" name="accion" value="Devolver">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-1">
        Barril:
      </div>
      <div class="col-6 mb-1">
        <select name="id_barriles" class="form-control">
        <option></option>
          <?php
          foreach($barriles as $barril) {
            print "<option value='".$barril->id."'>".$barril->codigo."</option>";
          }
          ?>
        </select>
      </div>
      <div class="col-6 mb-1 barriles-datos-estado">
        Estado
      </div>
      <div class="col-6 mb-1 barriles-datos-estado">
        <input type="text" id="barriles-estado" class="form-control" DISABLED>
      </div>
      <div class="col-6 mb-1 barriles-datos-cliente">
        Cliente
      </div>
      <div class="col-6 mb-1 barriles-datos-cliente">
        <input type="text" id="barriles-cliente-nombre" class="form-control" DISABLED>
      </div>
      <div class="col-6 mb-1 barriles-cambiar">
        Barril a cambiar:
      </div>
      <div class="col-6 mb-1 barriles-cambiar">
        <select name="id_barriles_cambiar" class="form-control">
        <option></option>
          <?php
          foreach($barriles_en_planta as $barril_ep) {
            print "<option value='".$barril_ep->id."'>".$barril_ep->codigo."</option>";
          }
          ?>
        </select>
      </div>
      <div class="col-6 mb-1 barriles-cambiar">
        Motivo de reemplazo:
      </div>
      <div class="col-6 mb-1 barriles-cambiar">
        <textarea name="motivo" class="form-control"></textarea>
      </div>
      <div class="col-12 mt-3 mb-1 text-right">
        <button class="btn btn-primary btn-sm" id="guardar-btn"><i class="fas fa-fw fa-download"></i> Devolver a Planta</button>
      </div>
    </div>
  </div>
</form>


<script>

$(document).ready(function() {
  $('#guardar-btn').attr('disabled',true);
  $('.barriles-datos-estado').hide();
  $('.barriles-datos-cliente').hide();
  $('.barriles-cambiar').hide();
});

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  console.log($('select[name="accion"]').val());
  console.log($('select[name="id_barriles_cambiar"]').val());
  

  if($('select[name="accion"]').val() == "Reemplazar" && $('select[name="id_barriles_cambiar"]').val() == "") {
    alert("Elegir barril a cambiar");
    return false;
  } 

  var url = "./ajax/ajax_devolverBarril.php";
  var data = getDataForm("devolucion-barriles");

  console.log(data);

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=devolucion-barriles&msg=1&id=" + $('select[name="id_barriles"]').val();
    }
  }).fail(function(){
    alert("No funciono");
  });
});

$(document).on('change','select[name="accion"]',function(e) {
  if($(e.currentTarget).val() == "Devolver") {
    $('.barriles-cambiar').hide(200);
  } else {
    $('.barriles-cambiar').show(200);
  }
});


$('select[name="id_barriles"]').on('change',function(e){

  e.preventDefault();

  if($('select[name="id_barriles"]').val()=='') {
    $('#guardar-btn').attr('disabled',true);
    $('.barriles-datos-estado').hide(200);
    $('.barriles-datos-cliente').hide(200);
    return false;
  }

  var url = "./ajax/ajax_getDataBarriles.php";
  var data = getDataForm("devolucion-barriles");

  $.get(url,data,function(response_raw){
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      $('#guardar-btn').attr('disabled',false);
      
       $('#barriles-estado').val(response.obj.estado);
       if(response.obj.cliente.nombre!=null) {
        $('#barriles-cliente-nombre').val(response.obj.cliente.nombre);
        $('.barriles-datos-cliente').show(200);
        $('.barriles-datos-estado').show(200);
       } else {
        console.log('no tiene');
        $('.barriles-datos-cliente').hide(200);
        $('.barriles-datos-estado').show(200);
       }
       
    }
  }).fail(function(){
    alert("No funciono");
  });

  

});

</script>
