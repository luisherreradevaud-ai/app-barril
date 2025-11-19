<?php


    $usuario = $GLOBALS['usuario'];

    $recetas = Receta::getALL("ORDER BY nombre asc");

    $cocineros = Usuario::getAll("WHERE nivel='Jefe de Cocina'");

    $activos = Activo::getAll("order by nombre asc");
    $proveedores = Proveedor::getAll();
    $tipos_de_insumos = TipoDeInsumo::getAll();
    $fermentadores = Fermentador::getAll("WHERE activo='1'");

    $insumos_arr = array(
        'licor' => array(),
        'maceracion' => array(),
        'coccion' => array(),
        'inoculacion' => array(),
        'lupulizacion' => array(),
        'enfriado' => array()
    );

    if(validaIdExists($_GET,'id')) {
        $batch = new Batch($_GET['id']);
        $batch_insumos = BatchInsumo::getAll("WHERE id_batches='".$batch->id."' AND etapa='lupulizacion'");
        foreach($batch_insumos as $bi) {
            if(isset($insumos_arr[$bi->etapa])) {
                $insumo = new Insumo($bi->id_insumos);
                $insumo->cantidad = $bi->cantidad;
                $insumo->etapa_index = $bi->etapa_index;
                $insumos_arr[$bi->etapa][] = $insumo;
            }
        }
    } else {
        die();
    }

    $lupulizaciones = BatchLupulizacion::getAll("WHERE id_batches='".$batch->id."'");
    $enfriados = BatchEnfriado::getAll("WHERE id_batches='".$batch->id."'");
    $traspasos = BatchTraspaso::getAll("WHERE id_batches='".$batch->id."'");

    $ultimo_batch = Batch::getAll("ORDER BY id desc LIMIT 1");

?>

<form id="batch-form">
<input type="hidden" name="id" value="">
<input type="hidden" name="entidad" value="batches">
<div class="row">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-body d-flex justify-content-between">
                <h1>Lupulización Batch <?= ($batch->id != '') ? '#'.$batch->id : ''; ?></h1>
                <?php $usuario->printReturnBtn(); ?>
            </div>
        </div>
        


        <div>
            <div class="card shadow">
                <div class="card-body">
                    <div class="mt-1 w-100" id="lupulizaciones-div"></div>
                    <div class="row">
                        <div class="col-12 mb-1">
                            <button class="btn btn-primary btn-sm" id="nuevo-lupulizacion-btn">+ Agregar Lupulización</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            

        <div class="card shadow">
            <div class="card-body d-flex justify-content-between">
                &nbsp;
                <button class="btn btn-primary ms-3 guardar-btn" data-tipo="Batch"><i class="bi bi-save"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>
</form>

<!-- // MODALS -->
  

<div class="modal modal-fade" tabindex="-1" role="dialog" id="agregar-insumos-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Agregar Insumo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-1">
                        Tipo de Insumo:
                    </div>
                    <div class="col-6 mb-1">
                        <select name="id_tipos_de_insumos" class="form-control">
                        </select>
                    </div>
                    <div class="col-6 mb-1">
                        Insumo:
                    </div>
                    <div class="col-6 mb-1">
                        <select name="id_insumos" class="form-control">
                        </select>
                    </div>
                    <div class="col-6 mb-1">
                        Cantidad:
                    </div>
                    <div class="col-6 mb-1">
                        <div class="input-group">
                            <input type="number" class="form-control acero-float" name="cantidad"  value="0" step="0.1" min="0">
                            <span class="input-group-text" style="border-radius: 0px 10px 10px 0px" id="agregar-insumos-unidad-de-medida">ml</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="agregar-insumos-aceptar" data-bs-dismiss="modal">Agregar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal modal-fade" tabindex="-1" role="dialog" id="nuevo-lupulizacion-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Agregar Lupulización
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-1">
                        Fecha:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="date" name="nuevo-lupulizacion-date" id="nuevo-lupulizacion-date" class="form-control">
                    </div>
                    <div class="col-6 mb-1">
                        Hora:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="time" name="nuevo-lupulizacion-hora" id="nuevo-lupulizacion-hora" class="form-control">
                    </div>
                    <div class="col-6 mb-1">
                        Tipo:
                    </div>
                    <div class="col-6 mb-1">
                        <select class="form-control" name="nuevo-lupulizacion-tipo">
                            <option>Hervor</option>
                            <option>Hopstand</option>
                            <option>Dry-hop</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="nuevo-lupulizacion-aceptar-btn" data-bs-dismiss="modal">Agregar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal modal-fade" tabindex="-1" role="dialog" id="nuevo-enfriado-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Agregar Enfriado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-1">
                        Fecha:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="date" name="nuevo-enfriado-date" id="nuevo-enfriado-date" class="form-control">
                    </div>
                    <div class="col-6 mb-1">
                        Hora inicio:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="time" name="nuevo-enfriado-hora" id="nuevo-enfriado-hora" class="form-control">
                    </div>
                    <div class="col-6 mb-1">
                        Temperatura de enfriado:
                    </div>
                    <div class="col-6 mb-1">
                        <div class="input-group">
                            <input type="number" class="form-control" name="nuevo-enfriado-temperatura-inicio" id="nuevo-enfriado-temperatura-inicio">
                            <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">°C</span>
                        </div>
                    </div>
                    <div class="col-6 mb-1">
                        pH:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="number" class="form-control" name="nuevo-enfriado-ph" id="nuevo-enfriado-ph">
                    </div>
                    <div class="col-6 mb-1">
                        Densidad:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="number" class="form-control" name="nuevo-enfriado-densidad" id="nuevo-enfriado-densidad">
                    </div>
                    <div class="col-6 mb-1">
                        pH enfriado:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="number" class="form-control" name="nuevo-enfriado-ph-enfriado" id="nuevo-enfriado-ph-enfriado">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="nuevo-enfriado-aceptar-btn" data-bs-dismiss="modal">Agregar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal modal-fade" tabindex="-1" role="dialog" id="nuevo-traspasos-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Agregar Traspaso
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-1">
                        Cantidad:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="number" name="cantidad" id="nuevo-traspasos-cantidad-input" class="form-control">
                    </div>
                    <div class="col-6 mb-1">
                        Fermentador Inicio:
                    </div>
                    <div class="col-6 mb-1">
                        <select class="form-control" name="id_fermentadores_inicio" id="nuevo-traspasos-id_fermentadores_inicio-select">
                            <?php
                            foreach($fermentadores as $fermentador) {
                            ?>
                            <option value="<?= $fermentador->id; ?>"><?= $fermentador->codigo; ?></option>
                            <?php
                            }

                            ?>
                        </select>
                    </div>
                    <div class="col-6 mb-1">
                        Fermentador Final:
                    </div>
                    <div class="col-6 mb-1">
                        <select class="form-control" name="id_fermentadores_final" id="nuevo-traspasos-id_fermentadores_final-select">
                            <?php
                            foreach($fermentadores as $fermentador) {
                            ?>
                            <option value="<?= $fermentador->id; ?>"><?= $fermentador->codigo; ?></option>
                            <?php
                            }

                            ?>
                        </select>
                    </div>
                    <div class="col-6 mb-1">
                        Fecha:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="date" name="date" id="nuevo-traspasos-date-input" class="form-control">
                    </div>
                    <div class="col-6 mb-1">
                        Hora:
                    </div>
                    <div class="col-6 mb-1">
                        <input type="time" name="hora" id="nuevo-traspasos-hora-input" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="agregar-traspasos-agregar-btn" data-bs-dismiss="modal">Agregar</button>
            </div>
        </div>
    </div>
</div>

  <div class="modal fade" tabindex="-1" role="dialog" id="eliminar-obj-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Batch</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>¿Desea <b>eliminar</b> este Batch?<br/>Este paso no es reversible.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" id="eliminar-obj-aceptar" data-bs-dismiss="modal">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="finalizar-modal">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirmación de Finalización</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <center><h5>¿Confirma la <b>finalización</b> este Batch?<br/>Este paso no es reversible y posteriormente no podrá realizar cambios.</h5></center>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="finalizar-aceptar-btn" data-bs-dismiss="modal">
            <i class="bi bi-check"></i>
            Finalizar
        </button>
        </div>
      </div>
    </div>
  </div>

<script>

var obj = <?= json_encode($batch,JSON_PRETTY_PRINT); ?>;
var tipos_de_insumos = <?= json_encode($tipos_de_insumos,JSON_PRETTY_PRINT); ?>;
var tipo_de_insumo = {};
var insumos = [];
var insumo = {};
var etapa_seleccionada = '<?= $batch->etapa_seleccionada; ?>';
var index_seleccionado = 0;
var lupulizaciones = <?= json_encode($lupulizaciones,JSON_PRETTY_PRINT); ?>;
var enfriados = <?= json_encode($enfriados,JSON_PRETTY_PRINT); ?>;
var traspasos = <?= json_encode($traspasos,JSON_PRETTY_PRINT); ?>;
var fermentadores = <?= json_encode($fermentadores,JSON_PRETTY_PRINT); ?>;

var lista_selected = false;

var etapas = ['licor','maceracion','coccion','inoculacion','lupulizacion','enfriado'];

/*
var lista = [];
lista['licor'] = [];
lista['maceracion'] = [];
lista['coccion'] = [];
lista['inoculacion'] = [];
*/

/*var lista = {
    'licor': new Array(),
    'maceracion': new Array(),
    'coccion': new Array(),
    'inoculacion': new Array()
};*/

var lista = <?= json_encode($insumos_arr,JSON_PRETTY_PRINT); ?>;



$(document).ready(function(){

    $.each(obj,function(key,value){
        if(key!="table_name"&&key!="table_fields"){
        $('input[name="'+key+'"]').val(value);
        $('textarea[name="'+key+'"]').val(value);
        $('select[name="'+key+'"]').val(value);
        }
    });

    renderListaLupulizacion();
    renderListaEnfriado();
    renderListaTraspasos();
    renderLista();
    armarTiposDeInsumosSelect();
    changeTiposDeInsumosSelect();
    changeInsumosSelect();

});

$(document).on('change','select[name="id_tipos_de_insumos"]',function(){
    changeTiposDeInsumosSelect();
    changeInsumosSelect();
});

function armarTiposDeInsumosSelect(tipos_de_insumos_array = false) {

    var tipos_de_insumos_filtrada = [];

    if(!tipos_de_insumos_array) {
        tipos_de_insumos_filtrada = tipos_de_insumos;
    } else
    if(Array.isArray(tipos_de_insumos_array)) {
        tipos_de_insumos_filtrada = tipos_de_insumos.filter( function(ti) {
            return tipos_de_insumos_array.includes(ti.nombre);
        });
    }
    $('select[name="id_tipos_de_insumos"]').empty();
    tipos_de_insumos_filtrada.forEach(function(tdi) {
        $('select[name="id_tipos_de_insumos"]').append("<option value='" + tdi.id + "'>" + tdi.nombre + "</option>");
    });
    changeTiposDeInsumosSelect();
    
}

function changeTiposDeInsumosSelect() {

  $('select[name="id_insumos"]').empty();

  var id_tipos_de_insumos = $('select[name="id_tipos_de_insumos"]').val();
  if(id_tipos_de_insumos == null) {
    return false;
  }

  tipo_de_insumo = tipos_de_insumos.find((tdi) => tdi.id == id_tipos_de_insumos);
  insumos = tipo_de_insumo.insumos;
  insumos.forEach(function(insumo) {
    $('select[name="id_insumos"]').append("<option value='" + insumo.id + "'>" + insumo.nombre + "</option>");
  });
  
}

function changeInsumosSelect() {

  var id_insumos = $('select[name="id_insumos"]').val();
  if(id_insumos == null) {
    return false;
  }

  var insumo = insumos.find((i) => i.id == id_insumos);
  $('#agregar-insumos-unidad-de-medida').html(insumo.unidad_de_medida);
  $('input[name="cantidad"]').val('0');
  $('input[name="monto"]').val('0');

}


$(document).on('change','select[name="id_tipos_de_insumos"]',function(){
    changeTiposDeInsumosSelect();
    changeInsumosSelect();
});

$(document).on('change','select[name="id_insumos"]',function(){
    changeInsumosSelect();
});


$(document).on('click','.guardar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_agregarLupulizacion.php";
  var data = getDataForm("batch");
  data['insumos'] = lista;
  data['lupulizaciones'] = lupulizaciones;
  data['tipo'] = $(e.currentTarget).data('tipo');

  $.post(url,data,function(response_raw){
  console.log(response_raw);
  //return false;
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = './?s=agregar-lupulizacion&id=' + response.obj.id;
    }
  }).fail(function(){
    alert("No funciono");
  });
});


$(document).on('click','.agregar-insumos-btn',function(e){
  e.preventDefault();
  lista_selected = $(e.currentTarget).data('etapa');
  filter_array = false;
  if(lista_selected == 'licor' || lista_selected == 'maceracion' || lista_selected == 'coccion') {
    filter_array = ['Quimicos'];
  } else 
  if(lista_selected == 'lupulizacion') {
    filter_array = ['Lupulos'];
  }
  armarTiposDeInsumosSelect(filter_array);
  index_seleccionado = $(e.currentTarget).data('index');
  $('#agregar-insumos-modal').modal('toggle');
});

$(document).on('click','#agregar-insumos-aceptar',function(e){

  e.preventDefault();

  var id_insumos = $('select[name="id_insumos"]').val();
  var insumo = insumos.find((ins) => ins.id == id_insumos);
  var insumo_new = JSON.parse(JSON.stringify(insumo));
  insumo_new.cantidad = $('input[name="cantidad"').val();
  insumo_new.etapa_index = index_seleccionado;

  lista[lista_selected].push(insumo_new);
  renderLista();

});

function renderLista() {

  

  etapas.forEach(function(et,et_index){


    var lista_index = new Array();
    var indexes = new Array();


    lista_index[0] = '';
    indexes[0] = 0;

    if(et == 'lupulizacion') {
        lupulizaciones.forEach((lup,lup_index) => {
            lista_index[lup_index] = '';
            indexes[lup_index] = 0;
        });
    }

    if(et == 'enfriado') {
        enfriados.forEach((lup,lup_index) => {
            lista_index[lup_index] = '';
            indexes[lup_index] = 0;
        });
    }

    
    lista[et].forEach(function(ins,index){
        
        if(lista_index[ins.etapa_index] == undefined) {
            indexes[ins.etapa_index] = 0;
            lista_index[ins.etapa_index] = '';
        }

        lista_index[ins.etapa_index] += '<tr class="insumos-tr" data-index="' + index +'"><td><b>' + ins.nombre;
        lista_index[ins.etapa_index] += '</b></td><td><b>' + ins.cantidad + " " + ins.unidad_de_medida;
        lista_index[ins.etapa_index] += '</b></td><td><b><button class="btn btn-sm item-eliminar-btn" data-index="' + index + '" + data-lista="' + et + '" data-etapaindex="' + ins.etapa_index + '">x</button>';
        lista_index[ins.etapa_index] += '</b></td></tr>';

        indexes[ins.etapa_index] += 1;

    });

    lista_index.forEach(function(html,index) {
        $('#' + et + '-' + index + '-insumos-table').html(html);
    });

    
  });


}

$(document).on('click','.item-eliminar-btn',function(e){

  e.preventDefault();
  var index = $(e.currentTarget).data('index');
  var etapa = $(e.currentTarget).data('lista');
  var etapa_index = $(e.currentTarget).data('etapaindex');

  lista[etapa].splice(index,1);

  renderLista();

});

$(document).on('click','.eliminar-obj-btn',function(e){
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

$(document).on('click','.etapa-btn',function(e){
    var etapa = $(e.currentTarget).data('etapa');
    etapa_seleccionada = etapa;
});

$(document).on('click','#nuevo-lupulizacion-btn',function(e){

    e.preventDefault();

    const fecha = new Date();
    const anio = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0'); // Los meses van de 0 a 11
    const dia = String(fecha.getDate()).padStart(2, '0');
    const horas = String(fecha.getHours()).padStart(2, '0');
    const minutos = String(fecha.getMinutes()).padStart(2, '0');
    const segundos = String(fecha.getSeconds()).padStart(2, '0');

    $('#nuevo-lupulizacion-date').val(anio + '-' + mes + '-' + dia);
    $('#nuevo-lupulizacion-hora').val(horas + ':' + minutos + ':' + segundos);
    $('#nuevo-lupulizacion-modal').modal('toggle');

});


$(document).on('click','#nuevo-lupulizacion-aceptar-btn',function(e){

    e.preventDefault();



    var lupulizacion = {
        'id': '',
        'id_batches': '',
        'seq_index': '',
        'tipo': $('select[name="nuevo-lupulizacion-tipo"]').val(),
        'date': $('#nuevo-lupulizacion-date').val(),
        'hora': $('#nuevo-lupulizacion-hora').val()
    };

    lupulizaciones.push(lupulizacion);
    renderListaLupulizacion();

});


function renderListaLupulizacion() {
  
    var html = '';
    lupulizaciones.forEach(function(lupulizacion,index){
        html += '<div class="p-3 shadow mb-5">';
        html += '<div class="d-flex justify-content-between mb-1">';
        html += '<div><h4>Lupulización #' + (parseInt(index) + 1) + '</h4></div>';
        html += '<button class="btn btn-sm lupulizaciones-item-eliminar-btn" data-index="' + index + '">x</button>';
        html += '</div>';
        html += '<div class="mb-1">' + lupulizacion.date + ' ' + lupulizacion.hora + '</div>';
        html += '<div class="mb-1">Tipo: ' + lupulizacion.tipo + '</div>';
        html += '<table class="table table-striped">';
        html += '<thead>';
        html += '<tr>';
        html += '<th>Insumos</th>';
        html += '<th>Cantidad</th>';
        html += '</thead>';
        html += '<tbody id="lupulizacion-' + index + '-insumos-table">';
        html += '</tbody>';
        html += '</table>';
        html += '<button class="btn btn-primary btn-sm agregar-insumos-btn" data-etapa="lupulizacion" data-index="' + index + '">+ Agregar Insumo</button>';
        html += '</div>';
    });
    $('#lupulizaciones-div').html(html);
    renderLista();


}

$(document).on('click','.lupulizaciones-item-eliminar-btn',function(e){

  e.preventDefault();
  var index = $(e.currentTarget).data('index');
  lupulizaciones.splice(index,1);

  var lista_lupulizacion = JSON.parse(JSON.stringify(lista['lupulizacion'].filter((lup) => lup.etapa_index != index)));


  lista_lupulizacion.forEach((lup,lup_index) => {

    if(lup.etapa_index > index) {
        lup.etapa_index = lup.etapa_index - 1;
    }

  });

  lista['lupulizacion'] = JSON.parse(JSON.stringify(lista_lupulizacion));

  renderListaLupulizacion();

});


$(document).on('click','#nuevo-enfriado-btn',function(e){

    e.preventDefault();

    const fecha = new Date();
    const anio = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0'); // Los meses van de 0 a 11
    const dia = String(fecha.getDate()).padStart(2, '0');
    const horas = String(fecha.getHours()).padStart(2, '0');
    const minutos = String(fecha.getMinutes()).padStart(2, '0');
    const segundos = String(fecha.getSeconds()).padStart(2, '0');

    $('#nuevo-enfriado-date').val(anio + '-' + mes + '-' + dia);
    $('#nuevo-enfriado-hora').val(horas + ':' + minutos + ':' + segundos);
    $('#nuevo-enfriado-modal').modal('toggle');

});


$(document).on('click','#nuevo-enfriado-aceptar-btn',function(e){

    e.preventDefault();

    var enfriado = {
        'id': '',
        'id_batches': '',
        'seq_index': '',
        'temperatura_inicio': $('#nuevo-enfriado-temperatura-inicio').val(),
        'ph': $('#nuevo-enfriado-ph').val(),
        'densidad': $('#nuevo-enfriado-densidad').val(),
        'ph_enfriado': $('#nuevo-enfriado-ph-enfriado').val(),
        'date': $('#nuevo-enfriado-date').val(),
        'hora_inicio': $('#nuevo-enfriado-hora').val()
    };

    enfriados.push(enfriado);
    renderListaEnfriado();

});


function renderListaEnfriado() {
  
    var html = '';
    enfriados.forEach(function(enfriado,index){
        html += '<div class="p-3 shadow mb-5">';
        html += '<div class="d-flex justify-content-between mb-1">';
        html += '<div><h4>Enfriado #' + (parseInt(index) + 1) + '</h4></div>';
        html += '<button class="btn btn-sm enfriados-item-eliminar-btn" data-index="' + index + '">x</button>';
        html += '</div>';
        html += '<div class="mb-1">' + enfriado.date + ' ' + enfriado.hora_inicio + '</div>';
        html += '<div class="mb-1">Temperatura: ' + enfriado.temperatura_inicio + '°C</div>';
        html += '<div class="mb-1">pH: ' + enfriado.ph + '</div>';
        html += '<div class="mb-1">Densidad: ' + enfriado.densidad + '</div>';
        html += '<div class="mb-1">Densidad: ' + enfriado.ph_enfriado + '</div>';
        html += '<table class="table table-striped">';
        html += '<thead>';
        html += '<tr>';
        html += '<th>Insumos</th>';
        html += '<th>Cantidad</th>';
        html += '</thead>';
        html += '<tbody id="enfriado-' + index + '-insumos-table">';
        html += '</tbody>';
        html += '</table>';
        html += '<button class="btn btn-primary btn-sm agregar-insumos-btn" data-etapa="enfriado" data-index="' + index + '">+ Agregar Insumo</button>';
        html += '</div>';
    });
    $('#enfriados-div').html(html);
    renderLista();


}

$(document).on('click','.enfriados-item-eliminar-btn',function(e){

  e.preventDefault();
  var index = $(e.currentTarget).data('index');
  enfriados.splice(index,1);

  var lista_enfriado = JSON.parse(JSON.stringify(lista['enfriado'].filter((lup) => lup.etapa_index != index)));


  lista_enfriado.forEach((lup,lup_index) => {

    if(lup.etapa_index > index) {
        lup.etapa_index = lup.etapa_index - 1;
    }

  });

  lista['enfriado'] = JSON.parse(JSON.stringify(lista_enfriado));

  renderListaEnfriado();

});


$(document).on('click','#nuevo-traspasos-btn',function(e){

    e.preventDefault();

    const fecha = new Date();
    const anio = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, '0'); // Los meses van de 0 a 11
    const dia = String(fecha.getDate()).padStart(2, '0');
    const horas = String(fecha.getHours()).padStart(2, '0');
    const minutos = String(fecha.getMinutes()).padStart(2, '0');
    const segundos = String(fecha.getSeconds()).padStart(2, '0');

    $('#nuevo-traspasos-date-input').val(anio + '-' + mes + '-' + dia);
    $('#nuevo-traspasos-hora-input').val(horas + ':' + minutos + ':' + segundos);
    $('#nuevo-traspasos-modal').modal('toggle');

});

$(document).on('click','#agregar-traspasos-agregar-btn',function(e){

    e.preventDefault();

    var traspaso = {
        'id': '',
        'id_batches': obj.id,
        'id_fermentadores_inicio': $('#nuevo-traspasos-id_fermentadores_inicio-select').val(),
        'id_fermentadores_final': $('#nuevo-traspasos-id_fermentadores_final-select').val(),
        'cantidad': $('#nuevo-traspasos-cantidad-input').val(),
        'date': $('#nuevo-traspasos-date-input').val(),
        'hora': $('#nuevo-traspasos-hora-input').val()
    };

    traspasos.push(traspaso);

    renderListaTraspasos();

});

function renderListaTraspasos() {

  
    var html = '';
    traspasos.forEach(function(traspaso,index){

        var fermentador_inicio = fermentadores.find((f) => f.id == traspaso.id_fermentadores_inicio);
        var fermentador_final = fermentadores.find((f) => f.id == traspaso.id_fermentadores_final);
        html += '<div class="p-3 shadow mb-5">';
        html += '<div class="d-flex justify-content-between mb-1">';
        html += '<div><h4>Traspaso #' + (parseInt(index) + 1) + '</h4></div>';
        html += '<button class="btn btn-sm traspasos-item-eliminar-btn" data-index="' + index + '">x</button>';
        html += '</div>';
        html += '<div class="mb-1">' + traspaso.date + ' ' + traspaso.hora + '</div>';
        html += '<div class="mb-1">Cantidad: ' + traspaso.cantidad + '</div>';
        html += '<div class="mb-1">Fermentador inicio: ' + fermentador_inicio.codigo + '</div>';
        html += '<div class="mb-1">Fermentador final: ' + fermentador_final.codigo + '</div>';
        html += '</div>';

    });

    $('#traspasos-div').html(html);


}


$(document).on('click','.traspasos-item-eliminar-btn',function(e){
  e.preventDefault();
  var index = $(e.currentTarget).data('index');
  traspasos.splice(index,1);
  renderListaTraspasos();
});

$(document).on('click','.eliminar-obj-btn',function(e){
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

$(document).on('click','#finalizar-btn',function(e){
    e.preventDefault();
});

$(document).on('click','#finalizar-aceptar-btn',function(e){

  e.preventDefault();

  var data = {
    'id': obj.id,
    'entidad': obj.table_name,
    'finalizar_date': '<?= date('Y-m-d'); ?>'
  };
  var url = './ajax/ajax_guardarEntidad.php';
  $.post(url,data,function(response){
    if(response.status!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      window.location.href = "./?s=bitacora-batches&id=" + obj.id;
    }
  },"json").fail(function(){
    alert("No funciono");
  });
});

</script>