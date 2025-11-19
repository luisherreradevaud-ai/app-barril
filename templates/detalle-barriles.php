<?php

$id = '';

if(validaIdExists($_GET,'id')) {
  $id = $_GET['id'];
}

$obj = new Barril($id);

$msg = 0;

if(isset($_GET['msg'])) {
  $msg = $_GET['msg'];
}

$repartidores = Usuario::getAll("WHERE nivel='repartidor'");
$clientes = Cliente::getAll();
$tipos_barril = $GLOBALS['tipos_barril'];

$ids_batches = $obj->getRelations("batches");
$entregas_productos = EntregaProducto::getAll("WHERE id_barriles='".$obj->id."' ORDER BY id desc");
$usuario = $GLOBALS['usuario'];

$barriles_estados = BarrilEstado::getAll('WHERE id_barriles="'.$obj->id.'" ORDER BY id desc');

?>
<style>
.tr-barriles {
  cursor: pointer;
}
.tr-batches {
  cursor: pointer;
}
.tr-entregas {
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
          print '<i class="fas fa-fw fa-coins"></i> Detalle';
        }
        ?> Barril
      </b>
    </h1>
  </div>
  <div>
    <div>
      <?php $usuario->printReturnBtn(); ?>
    </div>
  </div>
</div>
<hr/>
<?php
  Msg::show(1,'Barril guardado con &eacute;xito.','info');
  Msg::show(6,'Barril marcado como <b>Perdido</b>.','info');
?>
<form id="barriles-form">
  <input type="hidden" name="id" value="">
  <input type="hidden" name="entidad" value="barriles">
  <input type="hidden" name="clasificacion" value="Cerveza">
  <div class="row">
    <div class="col-md-6 row">
      <div class="col-6 mb-1">
        Tipo de Barril:
      </div>
      <div class="col-6 mb-1">
        <select name="tipo_barril" class="form-control">
          <?php
          foreach($tipos_barril as $tipo_barril) {
            print "<option>".$tipo_barril."</option>";
          }
          ?>
        </select>
      </div>
      <div class="col-6 mb-2">
        Código de barril:
      </div>
      <div class="col-6 mb-2">
        <input type="text" class="form-control" name="codigo">
      </div>
        <div class="col-6 mb-2">
          Estado:
        </div>
      <?php
      if($obj->id!='') {
        ?>
        <div class="col-6 mb-2">
          <input type="text" class="form-control" name="estado" READONLY>
        </div>
        <?php
      } else {
        ?>
        <div class="col-6 mb-2">
          <select class="form-control" name="estado">
            <option>En planta</option>
            <option>Perdido</option>
          </select>
        </div>
        <?php
      }
      ?>
      <?php
      if($obj->id_clientes != 0) {
        $cliente = new Cliente($obj->id_clientes);
      ?>
      <div class="col-6 mb-2">
        Cliente:
      </div>
      <div class="col-6 mb-2">
        <input type="text" class="form-control" value="<?= $cliente->nombre; ?>" READONLY>
      </div>

      <?php
      }
      ?>
      <?php
      if($obj->id_batches != 0) {
        $batch = new Batch($obj->id_batches);
        $receta = new Receta($batch->id_recetas);
      ?>
      <div class="col-6 mb-2">
        Batch actual:
      </div>
      <div class="col-6 mb-2">
       <button class="btn btn-secondary btn-sm" href="./?s=detalle-batches&id=<?= $obj->id_batches; ?>">#<?= $obj->id_batches." ".$receta->nombre; ?></a>
      </div>
      <?php
      }
      ?>
      <div class="col-12 mt-3 mb-1 d-flex justify-content-between">
        <?php
        if($obj->id != "") {
          ?>
          <button class="btn btn-sm btn-danger eliminar-obj-btn"><i class="fas fa-fw fa-trash"></i> Eliminar</button>
          <?php
        } else {
          print "&nbsp;";
        }
        ?>
        <div>
          <?php
          if($usuario->nivel == 'Administrador' && $obj->id != "") {
          ?>
          <button class="btn btn-sm btn-warning ms-2" id="marcar-como-perdido-btn">Marcar como Perdido</button>
          <?php
          }
          ?>
          <button class="btn btn-sm btn-primary" id="guardar-btn"><i class="fas fa-fw fa-save"></i> Guardar</button>
        </div>
      </div>
    </div>
  </div>
</form>
<?php
  if($obj->id!="") {
?>



<div class="d-sm-flex align-items-center justify-content-between mt-5 mb-3">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800">
      <b>
        Historial
      </b>
    </h1>
  </div>
</div>

<table class="table table-hover table-striped table-sm" id="batches-table">
  <thead class="thead-dark">
    <tr>
      <th>
        Estado
      </th>
      <th>
        Desde
      </th>
      <th>
        Hasta
      </th>
      <th>
        Usuario
      </th>
      <th>
        Tiempo transcurrido
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
     
      foreach($barriles_estados as $barril_estado) {
        $usuario_ejecutor = new Usuario($barril_estado->id_usuarios);
        if($usuario_ejecutor->nombre != '') {
          $usuario_ejecutor_nombre = $usuario_ejecutor->nombre;
        } else {
          $usuario_ejecutor_nombre = '-';
        }
        
    ?>
    <tr>
      <td>
        <?php
        if($barril_estado->id_clientes != 0 ) {
          $cliente = new Cliente($barril_estado->id_clientes);
          $estado = $barril_estado->estado.": ".$cliente->nombre;
        } else {
          $estado = $barril_estado->estado;
        }
        print $estado;
        ?>

      </td>
      <td>
        <?= datetime2fechayhora($barril_estado->inicio_date); ?>
      </td>
      <td>
        <?php
        if($barril_estado->finalizacion_date != '0000-00-00 00:00:00') {
          print datetime2fechayhora($barril_estado->finalizacion_date);
        } else {
          print "<b>Actualidad</b>";
        }
        ?>
      </td>
      <td>
        <?= $usuario_ejecutor_nombre; ?>
      </td>
      <td>
        <?php
        if($barril_estado->tiempo_transcurrido != 0) {
          print $barril_estado->tiempo_transcurrido;
        } else {
          $inicio = new DateTime($barril_estado->inicio_date);
          $final = new DateTime(date('Y-m-d H:i:s'));
          $diferencia = $inicio->diff($final);
          $dias = $diferencia->days;
          $horas = $diferencia->h + ($diferencia->i > 0 ? 1 : 0);
          if ($dias > 1) {
              $tiempo_transcurrido =  $dias." días";
          } elseif ($dias == 1) {
              $tiempo_transcurrido = "1 día";
          } else {
              $tiempo_transcurrido = max($horas, 1) . " hora" . ($horas > 1 ? "s" : "");
          }
          print $tiempo_transcurrido;
        } ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>


<div class="d-sm-flex align-items-center justify-content-between mt-5 mb-3">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800">
      <b>
        Historial de Entregas
      </b>
    </h1>
  </div>
</div>

<table class="table table-hover table-striped table-sm" id="batches-table">
  <thead class="thead-dark">
    <tr>
        <th>
        #
      </th>
      <th>
        Cliente
      </th>
      <th>
        Fecha
      </th>
      <th>
        Estado
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
     
      foreach($entregas_productos as $ep) {
        $entrega = new Entrega($ep->id_entregas);
        $cliente = new Cliente($entrega->id_clientes);
    ?>
    <tr class="tr-entregas" data-identregas="<?= $entrega->id; ?>">
      <td>
        <?= $entrega->id; ?>
      </td>
      <td>
        <?= $cliente->nombre; ?>
      </td>
      <td>
        <?= datetime2fechayhora($entrega->creada); ?>
      </td>
      <td>
        <?= $entrega->estado; ?>
      </td>
    </tr>
    <?php
      }
    ?>
  </tbody>
</table>


<div class="d-sm-flex align-items-center justify-content-between mt-5 mb-3">
  <div class="mb-2">
    <h1 class="h4 mb-0 text-gray-800">
      <b>
        Historial de Batches
      </b>
    </h1>
  </div>
</div>

<table class="table table-hover table-striped table-sm" id="batches-table">
  <thead class="thead-dark">
    <tr>
        <th>
          #
      </th>
      <th>
        Receta
      </th>
      <th>
        Fecha
      </th>
      <th>
        Estado
      </th>
    </tr>
  </thead>
  <tbody>
    <?php
      arsort($ids_batches);
      foreach($ids_batches as $id_batches) {
        $batch = new Batch($id_batches);
        $receta = new Receta($batch->id_recetas);
    ?>
    <tr class="tr-batches" data-idbatches="<?= $batch->id; ?>">
      <td>
        #<?= $batch->id; ?>
      </td>
      <td>
        <?= $receta->nombre; ?>
      </td>
      <td>
        <?= datetime2fechayhora($batch->creada); ?>
      </td>
      <td>
        <?= $batch->estado; ?>
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

<div class="modal fade" tabindex="-1" role="dialog" id="eliminar-barril-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Barril</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>Desea eliminar este barril?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-barril-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

<script>

$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  if($('input[name="codigo"]').val().length < 1) {
    alert("El codigo de barril debe tener al menos 1 caracter.");
    return false;
  }


  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("barriles");

  if(data.id == '') {
    if(data.tipo_barril == '20L') {
      data.litraje = '20';
    } else
    if(data.tipo_barril == '30L') {
      data.litraje = '30';
    } else
    if(data.tipo_barril == '50L') {
      data.litraje = '50';
    }

  }

  console.log(data)

  $.post(url,data,function(response){
    console.log(response);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-barriles&id=" + response.obj.id + "&msg=1";
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
});

$(document).on('click','.eliminar-obj-btn',function(e){

  e.preventDefault();
  $('#eliminar-barril-modal').modal('toggle');

});

$(document).on('click','#eliminar-barril-aceptar',function(e){

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
      window.location.href = "./?s=barriles-en-terreno&msg=2";
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','#marcar-como-perdido-btn',function(e){

  e.preventDefault();

  var data = {
    'id': obj.id,
    'modo': obj.table_name
  }
  var url = './ajax/ajax_marcarBarrilesComoPerdido.php';
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=detalle-barriles&id=" + obj.id + "&msg=6";
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});

$(document).on('click','.tr-batches',function(e) {
    window.location.href = "./?s=detalle-batches&id=" + $(e.currentTarget).data('idbatches');
});

$(document).on('click','.tr-entregas',function(e) {
    window.location.href = "./?s=detalle-entregas&id=" + $(e.currentTarget).data('identregas');
});

</script>
