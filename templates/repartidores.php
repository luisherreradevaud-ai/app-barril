<?php

  //checkAutorizacion(["Administrador","Jefe de Planta"]);

  $msg = 0;

  if(isset($_GET['msg'])) {
    $msg = $_GET['msg'];
  }

  $usuario = $GLOBALS['usuario'];

  $repartidores = Usuario::getAll("WHERE nivel='Repartidor'");
  $clientes = Cliente::getAll("ORDER BY nombre");

  $rand_int = rand(0,2147483640);

?>
<style>
.tr-entregas {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-truck"></i> <b>Repartidores</b></h1>
  </div>
</div>
<hr />
<?php
if($msg == 1) {
?>
<div class="alert alert-info" role="alert" >Entrega guardada con &eacute;xito.</div>
<?php
}
?>


<?php

    foreach($repartidores as $repartidor) {
        $despachos = Despacho::getAll("WHERE id_usuarios_repartidor='".$repartidor->id."' ORDER BY id desc");

?>

<div class="d-sm-flex align-items-center justify-content-between mb-3 mt-4">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800"><b><?= $repartidor->nombre; ?></b></h1>
  </div>
</div>


<table class="table table-striped table-sm">
  <thead class="thead-dark">
    <tr>
      <th>
      </th>
      <th>
        Tipo
      </th>
      <th>
        Cantidad
      </th>
      <th>
        Tipo Cerveza
      </th>
      <th>
        Codigo
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      $total = 0;
      foreach($despachos as $despacho) {

        $despacho_productos = DespachoProducto::getAll("WHERE id_despachos='".$despacho->id."'");
        foreach($despacho_productos as $dp) {
          $total += 1;
    ?>
    <tr>
      <td>
        <input type="checkbox" class="d-none despacho-checkbox" data-id="<?= $dp->id; ?>" data-tipo="<?= $dp->tipo; ?>">
      </td>
      <td>
        <?= $dp->tipo; ?>
      </td>
      <td>
        <?= $dp->cantidad; ?>
      </td>
      <td>
        <?= $dp->tipos_cerveza; ?>
      </td>
      <td>
        <?= $dp->codigo; ?>
      </td>
    </tr>
    <?php
        }
      }
    ?>
  </tbody>
</table>
<div class="mt-3 d-flex justify-content-between">
  <div>
    <b class="total"><?= $total; ?></b> productos por despachar
  </div>
  <!--<button class="d-sm-inline-block btn btn-sm btn-primary shadow-sm mb-2" disabled="true" id="entregar-cliente-btn" data-bs-toggle="modal" data-bs-target="#entregarModal">Entregar a Cliente <i class="fas fa-fw fa-forward"></i></button>-->
</div>

<?php
    }
?>

<div class="modal fade" id="entregarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Entregar a Cliente</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <select class="form-control" id="clientes-select">
          <?php
          foreach($clientes as $cliente) {
            print "<option value='".$cliente->id."'>".$cliente->nombre."</option>";
          }
          ?>
        </select>
        <input type="text" class="form-control" placeholder="Nombre de quien recibe" id="receptor-input">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-primary" id="guardar-btn">Entregar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="cantidad-vasos-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Vasos Entregados</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Ingrese la cantidad de vasos entregados:
        <br/>
        <br />
        <select id="cantidad-vasos-select" class="form-control">
          <option value="0" SELECTED>----</option>
          <?php
          for($i = 1; $i<100; $i++) {
            print "<option>".$i."</option>";
          }
          ?>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-primary" id="cantidad-vasos-btn">Entregar</button>
      </div>
    </div>
  </div>
</div>

<script>

var ids_despachos_productos = [];
var vasos = false;

$(document).on('click','.tr-entregas',function(e){
  window.location.href = "./?s=detalle-entregas&id=" + $(e.currentTarget).data('identregas');
});

$(document).on('change','.despacho-checkbox',function() {

  ids_despachos_productos = [];
  total = 0;
  vasos = false;

  $('.despacho-checkbox').each(function(){
    if($(this).is(':checked')){
      total += 1;
      ids_despachos_productos.push($(this).data('id'));
      if($(this).data('tipo') == "Vasos") {
        vasos = true;
      }
    }
  })
  $('.total').html(total);
  if(total == 0) {
    $('#entregar-cliente-btn').attr('disabled',true);
  } else {
    $('#entregar-cliente-btn').attr('disabled',false);
  }

});


$(document).on('change','#cantidad-vasos-select',function(){
  if($('#cantidad-vasos-select').val() != 0) {
    $('#cantidad-vasos-btn').attr('disabled',false);
  } else {
    $('#cantidad-vasos-btn').attr('disabled',true);
  }
});

$(document).on('click','#cantidad-vasos-btn',entregar);
$(document).on('click','#guardar-btn',entregar);


function entregar(){

  if($('#cantidad-vasos-select').val() == 0 && vasos) {
    $('#cantidad-vasos-modal').modal('toggle');
    $('#cantidad-vasos-btn').attr('disabled',true);
    return false;
  }

  var url = "./ajax/ajax_guardarEntrega.php";
  var data = {
    'ids_despachos_productos': ids_despachos_productos,
    'id_clientes': $('#clientes-select').val(),
    'id_usuarios_repartidor': <?= $usuario->id; ?>,
    'cantidad_vasos': $('#cantidad-vasos-select').val(),
    'receptor_nombre': $('#receptor-input').val(),
    'rand_int': <?= $rand_int; ?>
  };

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=repartidor&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });

}

</script>
