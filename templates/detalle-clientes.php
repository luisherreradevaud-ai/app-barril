<?php

$id = "";

if(validaIdExists($_GET,'id')) {
  $id = $_GET['id'];
}

$msg = 0;

if(isset($_GET['msg'])) {
  $msg = $_GET['msg'];
}

$obj = new Cliente($id);

$tipos_barril = $GLOBALS['tipos_barril'];
$tipos_barril_cerveza = $GLOBALS['tipos_barril_cerveza'];
$vendedores = Usuario::getAll("WHERE nivel='Vendedor' AND estado='Activo'");
$barriles = Barril::getAll("WHERE estado='En terreno' AND  id_clientes='".$obj->id."'");
$usuario = $GLOBALS['usuario'];

?>
<style>
.tr-clientes {
  cursor: pointer;
}
.tr-barriles {
  cursor: pointer;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b>
        <?php
        if($obj->id=="") {
          print '<i class="fas fa-fw fa-plus"></i> Nuevo';
        } else {
          print '<i class="fas fa-fw fa-handshake"></i> Detalle';
        }
        ?> Cliente
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
if($msg == 1) {
?>
<div class="alert alert-info" role="alert" >Cliente guardado con &eacute;xito.</div>
<?php
} if($msg == 3) {
?>
<div class="alert alert-info" role="alert" >Precios guardados con &eacute;xito.</div>
<?php
}
?>
<form id="clientes-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="clientes">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-1">
        Nombre:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="nombre">
      </div>
      <div class="col-6 mb-1">
        Email:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="email">
      </div>
      <div class="col-6 mb-1">
        Telefono:
      </div>
      <div class="col-6 mb-1">
        <input type="text" class="form-control" name="telefono">
      </div>
      <div class="col-6 mb-1">
        Estado:
      </div>
      <div class="col-6 mb-1">
        <select name="estado" class="form-control">
          <option>Activo</option>
          <option>Bloqueado</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        Criterio de cobro:
      </div>
      <div class="col-6 mb-1">
        <select name="criterio" class="form-control">
          <option>Contra entrega</option>
          <option>Martes siguiente</option>
          <option>Martes sub-siguiente</option>
          <option>&Uacute;ltimo dia del mes</option>
        </select>
      </div>
      <div class="col-6 mb-1">
        Meta barriles mensuales:
      </div>
      <div class="col-6 mb-1">
        <input type="number" min="0" value="0" name="meta_barriles_mensuales" class="form-control acero">
      </div>
      <div class="col-6 mb-1">
        Meta cajas mensuales:
      </div>
      <div class="col-6 mb-1">
        <input type="number" min="0" value="0" name="meta_cajas_mensuales" class="form-control acero">
      </div>
      <div class="col-6 mb-1">
        Salidas habilitadas:
      </div>
      <div class="col-6 mb-1">
        <input type="number" min="0" value="0" name="salidas_habilitadas" class="form-control acero">
      </div>
      <div class="col-6 mb-1">
        Vendedor:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="id_usuarios_vendedor">
          <option value="0">Sin Vendedor</option>
          <?php
            foreach($vendedores as $vendedor) {
              print "<option value='".$vendedor->id."'>".$vendedor->nombre."</option>";
            }
          ?>
        </select>
      </div>
      <div class="col-6 mb-1">
        Emite factura:
      </div>
      <div class="col-6 mb-1">
        <select class="form-control" name="emite_factura">
          <option value="0">No</option>
          <option value="1">Si</option>
        </select>
      </div>
      <div class="col-6 mb-1 emite-factura">
        RUT:
      </div>
      <div class="col-6 mb-1 emite-factura">
        <input type="text" name="RUT" class="form-control">
      </div>

      <div class="col-6 mb-1 emite-factura">
        Raz&oacute;n Social:
      </div>
      <div class="col-6 mb-1 emite-factura">
        <input type="text" name="RznSoc" class="form-control">
      </div>

      <div class="col-6 mb-1 emite-factura">
        Giro:
      </div>
      <div class="col-6 mb-1 emite-factura">
        <input type="text" name="Giro" class="form-control">
      </div>

      <div class="col-6 mb-1 emite-factura">
        Direcci&oacute;n:
      </div>
      <div class="col-6 mb-1 emite-factura">
        <input type="text" name="Dir" class="form-control">
      </div>

      <div class="col-6 mb-1 emite-factura">
        Comuna:
      </div>
      <div class="col-6 mb-1 emite-factura">
        <input type="text" name="Cmna" class="form-control">
      </div>

      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <?php
        if($obj->id != "" && $usuario->nivel == 'Administrador') {
          ?>
          <button class="btn btn-sm btn-danger eliminar-obj-btn">Eliminar</button>
          <?php
        } else {
          print "&nbsp;";
        }
        ?>
        <button class="btn btn-sm btn-primary" id="guardar-btn">Guardar</button>
      </div>
    </div>
  </div>
</form>
<?php
if(false){//if($obj->id != "" ) {
  ?>
<form action="./php/guardarPrecios.php" method="post">
  <input type="hidden" name="id" value="<?= $obj->id; ?>">
  <table class="table table-striped mt-5">
    <thead class="thead">
      <tr>
        <th>
          Tipo Barril
        </th>
        <th>
          Tipo Cerveza
        </th>
        <th>
          Precio
        </th>
      </tr>
    </thead>
    <?php
    foreach($tipos_barril as $tb) {
      foreach($tipos_barril_cerveza as $tbc) {
        $precio = 0;
        $precios = Precio::getAll("WHERE tipo_barril='".$tb."' AND tipo_cerveza='".$tbc."' AND id_clientes='".$obj->id."'");
        if(count($precios)>0) {
          $precio = $precios[0]->precio;
        }
        ?>
      <tr>
        <td>
          <?= $tb; ?>
        </td>
        <td>
          <?= $tbc; ?>
        </td>
        <td>
          <input type="number" min="0" name="<?= $tb."-$-".$tbc; ?>" class="form-control acero" value="<?= $precio; ?>">
        </td>
      </tr>
        <?php
      }
    }
    ?>
  </table>
  <div class="w-100 mt-3 mb-1 text-right">
    <input type="submit" class="btn btn-sm btn-primary" value="Guardar Precios">
  </div>
</form>
<?php
  }
?>
<div class="mt-5">
    <h1 class="h4 mb-0 text-gray-800">
      <b>
        Precios de Productos
      </b>
    </h1>
  </div>

<?php
if($obj->id != "" ) {
  $productos = Producto::getAll("ORDER BY tipo asc, cantidad asc, nombre asc");
  $precios = ClienteProductoPrecio::getAll("WHERE id_clientes='".$obj->id."'");
  ?>
<form action="./php/guardarPreciosProductos.php" method="post">
  <input type="hidden" name="id" value="<?= $obj->id; ?>">
  <input type="hidden" name="return_url" value="./?s=detalle-clientes&id=<?= $obj->id; ?>">
  <table class="table table-striped mt-3">
    <thead class="thead">
      <tr>
        <th>
          Tipo
        </th>
        <th>
          Cantidad
        </th>
        <th>
          Nombre
        </th>
        <th>
          Precio
        </th>
      </tr>
    </thead>
    <?php
    foreach($productos as $producto) {
      $precio = $producto->monto;
      foreach($precios as $p) {
        if($producto->id == $p->id_productos) {
          $precio = $p->precio;
        }
      }
        ?>
      <tr>
        <td>
          <?= $producto->tipo; ?>
        </td>
        <td>
          <?= $producto->cantidad; ?>
        </td>
        <td>
          <?= $producto->nombre; ?>
        <td>
          <input type="number" min="0" name="precio[<?= $producto->id; ?>]" class="form-control acero" value="<?= $precio; ?>">
        </td>
      </tr>
        <?php
    }
    ?>
  </table>
  <div class="w-100 mt-3 mb-1 text-right">
    <input type="submit" class="btn btn-sm btn-primary" value="Guardar Precios">
  </div>
</form>





<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800"><i class="fas fa-fw fa-coins"></i> <b>Barriles en Terreno</b> (<?= count($barriles); ?>)</h1>
  </div>
</div>
<table class="table table-hover table-striped table-sm mb-5">
  <thead class="thead-dark">
    <tr>
      <th>
          C&oacute;digo
      </th>
      <th>
          Tipo Barril

      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      foreach($barriles as $barril) {
    ?>
    <tr class="tr-barriles" data-idbarriles="<?= $barril->id; ?>">
      <td>
        <?= $barril->codigo; ?>
      </td>
      <td>
        <?= $barril->tipo_barril; ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>
<?php
}
?>

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-cliente-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Cliente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este cliente?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-cliente-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

<script>

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="nombre"]').val().length < 2) {
    alert("El nombre debe tener mas de 2 caracteres.");
    return false;
  }

  if(!$('input[name="email"]').val().includes('@')) {
    alert("Ingrese un correo valido.");
    return false;
  }

  if(!$('input[name="email"]').val().includes('.')) {
    alert("Ingrese un correo valido.");
    return false;
  }

  if($('input[name="email"]').val().length < 5) {
    alert("El email debe tener mas de 5 caracteres.");
    return false;
  }

  if($('input[name="telefono"]').val().length < 5) {
    alert("El telefono debe tener mas de 5 caracteres.");
    return false;
  }

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("clientes");

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-clientes&id=" + response.obj.id + "&msg=1";
    }
  },'json').fail(function(){
    alert("No funciono");
  });
});

var obj = <?= json_encode($obj,JSON_PRETTY_PRINT); ?>;
$(document).ready(function(){

  if(obj.id!="") {
    $.each(obj,function(key,value){
      console.log(key);
      if(key!="table_name"&&key!="table_fields"){
        $('input[name="'+key+'"]').val(value);
        $('textarea[name="'+key+'"]').val(value);
        $('select[name="'+key+'"]').val(value);
      }
    });
  }

  if(obj.emite_factura == 0) {
    $('.emite-factura').hide();
  }
});

$(document).on('click','.eliminar-obj-btn',function(e){
  e.preventDefault();
  $('#eliminar-cliente-modal').modal('toggle');
})

$(document).on('click','#eliminar-cliente-aceptar',function(e){

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

$(document).on('keyup','.acero',function(){
  $(this).val($(this).val().replace(/\D/g,''));
});

$(document).on('change','.acero',function(){
  if($(this).val() == "") {
    $(this).val(0);
  }
  $(this).val(parseInt($(this).val()));
});

$(document).on('change','select[name="emite_factura"]',function(e){
  if($(e.currentTarget).val() == 1) {
    $('.emite-factura').show(200);
  } else {
    $('.emite-factura').hide(200);
  }
});

$(document).on('keyup','input[name="RUT"]',function(e){
  var input = $(e.currentTarget).val();
  input = input.split('.').join('');
  //input = input.replace(/a-j/,'').replace('l-z','');
  $(e.currentTarget).val(input);
});

$(document).on('click','.tr-barriles',function(e){
  window.location.href = "./?s=detalle-barriles&id=" + $(e.currentTarget).data('idbarriles') + "&volver=barriles-en-terreno";
});

</script>
