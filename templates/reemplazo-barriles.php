<?php

  //checkAutorizacion(["Jefe de Planta","Administrador","Repartidor"]);

  $msg = 0;
  if(isset($_GET['msg'])){
    $msg = $_GET['msg'];
  }

  $barriles = Barril::getAll("WHERE clasificacion='Cerveza' AND estado!='En planta'");
  $barriles_en_planta = Barril::getAll("WHERE clasificacion='Cerveza' AND estado='En planta'");
  $barriles_reemplazos = BarrilReemplazo::getAll("ORDER BY id desc");

?>
<style>
.tr-barriles {
  cursor: pointer;
}
.tr-barriles_reemplazos {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Reemplazo de Barriles
      </b>
    </h1>
  </div>
  <div>
  </div>
</div>
<hr />

<form id="devolucion-barriles-form">
<input type="hidden" name="accion" value="Reemplazar">
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
      <div class="col-6 mb-1">
        Barril a cambiar:
      </div>
      <div class="col-6 mb-1">
        <select name="id_barriles_cambiar" class="form-control">
        <option></option>
          <?php
          foreach($barriles_en_planta as $barril_ep) {
            print "<option value='".$barril_ep->id."'>".$barril_ep->codigo."</option>";
          }
          ?>
        </select>
      </div>
      <div class="col-6 mb-1">
        Motivo de reemplazo:
      </div>
      <div class="col-6 mb-1">
        <textarea name="motivo" class="form-control"></textarea>
      </div>
      <div class="col-12 mt-3 mb-1 text-right">
        <button class="btn btn-primary btn-sm" id="guardar-btn"><i class="fas fa-fw fa-download"></i> Devolver a Planta</button>
      </div>
    </div>
  </div>
</form>

<hr />
<?php 
    Msg::show(1,'Reemplazo de Barril generado con &eacute;xito','success');
    Msg::show(2,'Reemplazo de Barril revertido y eliminado con &eacute;xito','danger');
?>
<table class="table table-hover table-striped table-sm mt-3">
  <thead class="thead-dark">
    <tr>
      <th>
        Fecha
      </th>
      <th>
        Cliente
      </th>
      <th>
        Barril Devuelto
      </th>
      <th>
        Barril Reemplazo
      </th>
      <th>
        Usuario
      </th>
      <th>
        Motivo
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($barriles_reemplazos as $reemplazo) {
        $barril_devuelto = new Barril($reemplazo->id_barriles_devuelto);
        $barril_reemplazo = new Barril($reemplazo->id_barriles_reemplazo);
        $cliente = new Cliente($reemplazo->id_clientes);
        $usuario_2 = new Usuario($reemplazo->id_usuarios);
    ?>
    <tr class="tr-barriles_reemplazos" data-idbarrilesreemplazos="<?= $reemplazo->id; ?>">
      <td>
        <?= $reemplazo->creada; ?>
      </td>
      <td>
        <?= $cliente->nombre; ?>
      </td>
      <td>
        <?= $barril_devuelto->codigo; ?>
      </td>
      <td>
        <?= $barril_reemplazo->codigo; ?>
      </td>
      <td>
        <?= $usuario_2->nombre; ?>
      </td>
      <td>
        <?= $reemplazo->motivo; ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>


<script>

$(document).on('click','.tr-barriles_reemplazos',function(e) {
    window.location.href = "./?s=detalle-barriles_reemplazos&id=" + $(e.currentTarget).data('idbarrilesreemplazos');
});

$(document).ready(function() {
  $('#guardar-btn').attr('disabled',true);
  $('.barriles-datos-estado').hide();
  $('.barriles-datos-cliente').hide();
});

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  console.log($('select[name="accion"]').val());
  console.log($('select[name="id_barriles_cambiar"]').val());
  

  if($('select[name="id_barriles_cambiar"]').val() == "") {
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
      window.location.href = "./?s=reemplazo-barriles&msg=1&id=" + $('select[name="id_barriles"]').val();
    }
  }).fail(function(){
    alert("No funciono");
  });
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
