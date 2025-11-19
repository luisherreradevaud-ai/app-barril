<?php

    $usuario = $GLOBALS['usuario'];
    $despachos = Despacho::getAll("WHERE id_usuarios_repartidor='".$usuario->id."' ORDER BY id desc");
    $clientes = Cliente::getAll("ORDER BY nombre");

    $rand_int = rand(0,2147483640);

    $barriles_obj = new Barril;
    $clientes = Cliente::getAll("ORDER BY nombre asc");
    $clientes_barriles = $barriles_obj->getClientesBarriles();

?>

<h1>Entregar Productos</h1>

<div class="row mt-3">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label class="fw-bold">
                        Cliente:
                    </label>
                    <select class="form-control mt-2" id="id_clientes-select">
                        <option value="0">-</option>
                        <?php
                            foreach($clientes as $cb) {
                            ?>
                            <option value="<?= $cb['obj']->id; ?>"><?= $cb['obj']->nombre; ?></option>
                            <?php
                            }
                        ?>
                    </select>
                </div>
                <div class="fw-bold mt-4 fs-5">
                    Barriles:
                </div>
                <form id="barriles-form">
                    <table class="table table-sm table-striped mt-2">
                        <thead>
                            <tr>
                                <th>
                                    Código
                                </th>
                                <th>
                                    Estado
                                </th>
                            </tr>
                        </thead>
                        <tbody id="barriles-table-tbody">
                        </tbody>
                    </table>
                </form>
                <div class="text-center text-warning mt-3" id="clientes_barriles-warning-div">Debes seleccionar el cliente y actualizar el estado de los barriles en el cliente para hacer la entrega.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="fw-bold">
                    Entrega:
                </div>
                <table class="table table-striped table-sm mt-2">
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
                            foreach($despachos as $despacho) {
                                $despacho_productos = DespachoProducto::getAll("WHERE id_despachos='".$despacho->id."'");
                                foreach($despacho_productos as $dp) {
                            ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="despacho-checkbox" data-id="<?= $dp->id; ?>" data-tipo="<?= $dp->tipo; ?>" DISABLED>
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
                    <div class="mt-3">
                        <b class="total">0</b> despachos seleccionados
                    </div>
                    <button class="btn btn-primary shadow-sm w-100 mt-3 mb-2" disabled="true" id="entregar-cliente-btn" data-bs-toggle="modal" data-bs-target="#entregarModal">Entregar a Cliente <i class="fas fa-fw fa-forward"></i></button>

                </div>
            </div>
    </div>
</div>

<div class="modal fade" id="entregarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Entregar a Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

    var clientes_barriles = <?= json_encode($clientes_barriles,JSON_PRETTY_PRINT); ?>;
    var paso_2_disabled = true;

    console.log(clientes_barriles); 

    $(document).on('change','#id_clientes-select', function(e){

        console.log($(e.currentTarget).val());

        if($(e.currentTarget).val() == 0) {
            paso_2_disabled = true;
            $('#clientes_barriles-warning-div').show(200);
            $('#entregar-cliente-btn').attr('disabled', true);
            $('#barriles-table-tbody').empty();
            $('.despacho-checkbox').attr('checked',false);
            $('.despacho-checkbox').attr('disabled',true);
            return false;
        }

        var cliente_barril = clientes_barriles.find( (cb) => cb.obj.id == $(e.currentTarget).val());
        console.log(cliente_barril);
        return false;
        var barriles = cliente_barril.barriles;

        var html = '';
        barriles.forEach(function(barril){
            html += '<tr>';
            html += '<td>' + barril.codigo + '</td>';
            html += '<td>';
            html += '<select class="form-control barril-estado" name="' + barril.id + '">';
            html += '<option value="0">-</option>';
            html += '<option>En terreno</option>';
            html += '<option>Pinchado</option>';
            html += '<option>Perdido desde el bar</option>';
            html += '</select>';
            html += '</td>';
        });

        $('#barriles-table-tbody').html(html);

        checkBarrilesEstado();

    });

    function checkBarrilesEstado() {

        var barriles_estado = getDataForm('barriles');

        var disabled = false;
        for (const [key, be] of Object.entries(barriles_estado)) {
            if(be == 0) {
                disabled = true;
            }
        }

        paso_2_disabled = disabled;
        paso2Disable();

    }

    function paso2Disable() {

        $('.despacho-checkbox').attr('disabled', paso_2_disabled);
        //$('#entregar-cliente-btn').attr('disabled', paso_2_disabled);

        if(!paso_2_disabled) {
            $('#clientes_barriles-warning-div').hide(200);
        } else {
            $('#clientes_barriles-warning-div').show(200);
            $('#entregar-cliente-btn').attr('disabled', true);
            $('.despacho-checkbox').attr('checked',false);
        }
    }


    $(document).on('change','.barril-estado',checkBarrilesEstado);

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
        'id_clientes': $('#id_clientes-select').val(),
        'id_usuarios_repartidor': <?= $usuario->id; ?>,
        'cantidad_vasos': $('#cantidad-vasos-select').val(),
        'receptor_nombre': $('#receptor-input').val(),
        'rand_int': <?= $rand_int; ?>,
        'barriles_estado': getDataForm('barriles')
    };

    $.post(url,data,function(response_raw){
        console.log(response_raw);
        var response = JSON.parse(response_raw);
        if(response.mensaje!="OK") {
            alert("Algo fallo");
            return false;
        } else {
        var obj = response.obj;
            window.location.href = "./?s=repartidor&msg=1&id_entregas=" + obj.id;
        }
    }).fail(function(){
        alert("No funciono");
    });

    }

</script>