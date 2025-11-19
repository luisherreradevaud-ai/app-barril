<?php

  $usuario = $GLOBALS['usuario'];
  $cliente = new Cliente($usuario->id_clientes);
  $usuario = $GLOBALS['usuario'];

?>
<style>
.tr-usuarios {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        Nueva Sugerencia
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
<form id="sugerencias-form">
  <input type="hidden" name="id_clientes" value="<?= $cliente->id; ?>">
  <input type="hidden" name="id_usuarios" value="<?= $usuario->id; ?>">
  <div class="row">
    <div class="col-md-8 row">
      <div class="col-12 mb-1">
        <select name="tipo" class="form-control">
          <option>Sugerencia</option>
          <option>Comentario</option>
          <option>Reclamo</option>
        </select>
      </div>
      <div class="col-12 mb-1">
        <textarea class="form-control" name="contenido" style="height: 100px"></textarea>
      </div>

      <div class="col-12 mt-3 mb-1 text-right">
        <button class="btn btn-primary" id="guardar-btn">Guardar</button>
      </div>
    </div>
  </div>
</form>



<script>
$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();


  var url = "./ajax/ajax_nuevaSugerencia.php";
  var data = getDataForm("sugerencias");

  $.post(url,data,function(raw){
    console.log(raw);
    var response = JSON.parse(raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo: " + response.mensaje);
      alert();
      return false;
    } else {
      window.location.href = "./?s=central-clientes-completa&msg=2#sugerencias";
    }
  }).fail(function(){
    alert("No funciono");
  });
});



</script>
