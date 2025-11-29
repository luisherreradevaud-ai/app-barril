<?php

    $usuario = $GLOBALS['usuario'];
    $despachos = Despacho::getAll("WHERE id_usuarios_repartidor='".$usuario->id."' AND estado='En despacho' ORDER BY id desc");

    $rand_int = rand(0,2147483640);

    $barriles_obj = new Barril;
    $clientes_barriles = $barriles_obj->getClientesBarriles();
    $clientes = Cliente::getAll("WHERE estado='Activo' ORDER BY nombre asc");

    // Preparar despachos con sus productos agrupados por cliente
    $despachos_por_cliente = array();
    foreach($despachos as $despacho) {
        $id_cliente = $despacho->id_clientes;
        if(!isset($despachos_por_cliente[$id_cliente])) {
            $despachos_por_cliente[$id_cliente] = array();
        }

        $productos_despacho = DespachoProducto::getAll("WHERE id_despachos='".$despacho->id."'");
        $productos_arr = array();

        foreach($productos_despacho as $dp) {
            $caja_envases = null;
            $es_mixta = false;
            $codigo_mostrar = $dp->codigo;
            $contenido_resumen = '';

            if($dp->tipo == "CajaEnvases" && $dp->id_cajas_de_envases > 0) {
                $caja_envases = new CajaDeEnvases($dp->id_cajas_de_envases);
                $es_mixta = $caja_envases->esMixta();
                $codigo_mostrar = $caja_envases->codigo;
                if($es_mixta) {
                    $contenido_resumen = $caja_envases->getContenidoResumen();
                }
            }

            $productos_arr[] = array(
                'id' => $dp->id,
                'tipo' => $dp->tipo,
                'cantidad' => $dp->cantidad,
                'tipos_cerveza' => $dp->tipos_cerveza,
                'codigo' => $codigo_mostrar,
                'id_cajas_de_envases' => $dp->id_cajas_de_envases,
                'es_mixta' => $es_mixta,
                'contenido_resumen' => $contenido_resumen
            );
        }

        $despachos_por_cliente[$id_cliente][] = array(
            'id' => $despacho->id,
            'creada' => $despacho->creada,
            'productos' => $productos_arr
        );
    }

?>
<style>
.tr-entregas {
  cursor: pointer;
}
.despacho-card {
  cursor: pointer;
  border-radius: 12px;
  transition: all 0.2s ease;
  border: 2px solid transparent;
}
.despacho-card:hover {
  background-color: rgba(0,0,0,0.02);
}
.despacho-card.selected {
  border-color: #0d6efd;
  box-shadow: 0 0.5rem 1rem rgba(13, 110, 253, 0.25);
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-3">
  <div class="mb-2">
    <h1 class="h3 mb-0 text-gray-800">
      <b><i class="fas fa-fw fa-truck"></i> Entregar Productos</b>
    </h1>
  </div>
  <div>
    <?php $usuario->printReturnBtn(); ?>
  </div>
</div>
<hr />

<?php
  Msg::show(1, '<i class="fas fa-check-circle me-2"></i> Entrega realizada con exito.', 'success');
?>

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
                            foreach($clientes as $cliente) {
                            ?>
                            <option value="<?= $cliente->id; ?>"><?= $cliente->nombre; ?></option>
                            <?php
                            }
                        ?>
                    </select>
                </div>
                <div id="barriles-table-container">
                    <div class="fw-bold mt-4 fs-5">
                        Barriles:
                    </div>
                    <form id="barriles-form">
                        <table class="table table-sm table-striped mt-2" id="barriles-table">
                            <thead>
                                <tr>
                                    <th>
                                        Codigo
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
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="fw-bold mb-3">
                    Despachos para entregar:
                </div>

                <div id="despachos-container">
                    <div class="text-center text-muted py-4" id="despachos-placeholder">
                        <i class="fas fa-hand-pointer fa-2x mb-2"></i>
                        <p>Selecciona un cliente para ver sus despachos</p>
                    </div>
                </div>

                <div class="mt-3">
                    <b class="total">0</b> despacho(s) seleccionado(s)
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

    var clientes_barriles = <?= json_encode($clientes_barriles, JSON_PRETTY_PRINT); ?>;
    var despachos_por_cliente = <?= json_encode($despachos_por_cliente, JSON_PRETTY_PRINT); ?>;
    var estados_barril_repartidor = <?= json_encode($estados_barril_repartidor, JSON_PRETTY_PRINT); ?>;
    var paso_2_disabled = true;

    console.log('clientes_barriles:', clientes_barriles);
    console.log('despachos_por_cliente:', despachos_por_cliente);

    $(document).on('change','#id_clientes-select', function(e){

        var id_cliente = $(e.currentTarget).val();
        console.log('Cliente seleccionado:', id_cliente);

        // Reset selecciones
        ids_despachos_productos = [];
        ids_cajas_envases = [];
        despachos_seleccionados = [];
        actualizarContador();

        if(id_cliente == 0) {
            paso_2_disabled = true;
            $('#clientes_barriles-warning-div').show(200);
            $('#entregar-cliente-btn').attr('disabled', true);
            $('#barriles-table-tbody').empty();
            $('#despachos-container').html('<div class="text-center text-muted py-4" id="despachos-placeholder"><i class="fas fa-hand-pointer fa-2x mb-2"></i><p>Selecciona un cliente para ver sus despachos</p></div>');
            return false;
        }

        // Renderizar despachos del cliente
        renderizarDespachos(id_cliente);

        // Manejar barriles del cliente
        cliente_barril = undefined;
        var cliente_barril = clientes_barriles.find( (cb) => cb.obj.id == id_cliente);
        if(cliente_barril === undefined) {
            $('#barriles-table-container').hide(200);
        } else {
            var barriles = cliente_barril.barriles;
            var html = '';
            barriles.forEach(function(barril){
                html += '<tr>';
                html += '<td>' + barril.codigo + '</td>';
                html += '<td>';
                html += '<select class="form-control barril-estado" name="' + barril.id + '">';
                html += '<option value="0">-</option>';
                estados_barril_repartidor.forEach(function(estado){
                    if(estado !== 'En despacho') {
                        html += '<option>' + estado + '</option>';
                    }
                });
                html += '</select>';
                html += '</td>';
            });
            $('#barriles-table-tbody').html(html);
            $('#barriles-table-container').show(200);
        }

        checkBarrilesEstado();

    });

    function renderizarDespachos(id_cliente) {
        var despachos = despachos_por_cliente[id_cliente] || [];
        console.log('Despachos encontrados para cliente ' + id_cliente + ':', despachos);

        if(despachos.length === 0) {
            $('#despachos-container').html('<div class="text-center text-muted py-4"><i class="fas fa-box-open fa-2x mb-2"></i><p>No hay despachos pendientes para este cliente</p></div>');
            return;
        }

        var html = '';
        despachos.forEach(function(despacho) {
            // Preparar data de productos para el data attribute
            var productos_ids = despacho.productos.map(function(p) { return p.id; });
            var cajas_envases_ids = despacho.productos.filter(function(p) {
                return p.tipo === 'CajaEnvases' && p.id_cajas_de_envases && p.id_cajas_de_envases != '0';
            }).map(function(p) { return p.id_cajas_de_envases; });

            var tiene_vasos = despacho.productos.some(function(p) { return p.tipo === 'Vasos'; });

            html += '<div class="card despacho-card mb-3" data-id-despacho="' + despacho.id + '" data-productos-ids=\'' + JSON.stringify(productos_ids) + '\' data-cajas-envases-ids=\'' + JSON.stringify(cajas_envases_ids) + '\' data-tiene-vasos="' + (tiene_vasos ? '1' : '0') + '">';
            html += '<div class="card-body">';
            html += '<div class="d-flex justify-content-between align-items-center mb-2">';
            html += '<h6 class="card-title mb-0"><i class="fas fa-truck me-2"></i>Despacho #' + despacho.id + '</h6>';
            html += '<small class="text-muted">' + despacho.creada + '</small>';
            html += '</div>';

            // Tabla de productos
            html += '<table class="table table-sm table-bordered mb-0">';
            html += '<thead class="table-light"><tr><th>Tipo</th><th>Cant.</th><th>Cerveza</th><th>Codigo</th></tr></thead>';
            html += '<tbody>';

            despacho.productos.forEach(function(producto) {
                html += '<tr>';

                // Tipo con icono
                if(producto.tipo === 'CajaEnvases') {
                    html += '<td><i class="fas fa-box text-success me-1"></i> Caja Envases';
                    if(producto.es_mixta) {
                        html += ' <span class="badge bg-warning text-dark">MIXTO</span>';
                    }
                    html += '</td>';
                } else if(producto.tipo === 'Barril') {
                    html += '<td><i class="fas fa-beer text-secondary me-1"></i> ' + producto.tipo + '</td>';
                } else if(producto.tipo === 'Caja') {
                    html += '<td><i class="fas fa-box text-info me-1"></i> ' + producto.tipo + '</td>';
                } else {
                    html += '<td>' + producto.tipo + '</td>';
                }

                html += '<td>' + producto.cantidad + '</td>';

                // Cerveza
                if(producto.es_mixta && producto.contenido_resumen) {
                    html += '<td>' + producto.contenido_resumen + '</td>';
                } else {
                    html += '<td>' + (producto.tipos_cerveza || '-') + '</td>';
                }

                html += '<td>' + (producto.codigo || '-') + '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            html += '</div></div>';
        });

        $('#despachos-container').html(html);
    }

    function checkBarrilesEstado() {

        var barriles_estado = getDataForm('barriles');

        var disabled = false;
        for (const [key, be] of Object.entries(barriles_estado)) {
            if(be == 0) {
                disabled = true;
            }
        }

        console.log('Barriles estado disabled:', disabled);

        paso_2_disabled = disabled;
        paso2Disable();

    }

    function paso2Disable() {
        if(!paso_2_disabled) {
            $('#clientes_barriles-warning-div').hide(200);
        } else {
            $('#clientes_barriles-warning-div').show(200);
            $('#entregar-cliente-btn').attr('disabled', true);
            // Deseleccionar todas las cards
            $('.despacho-card').removeClass('selected');
            ids_despachos_productos = [];
            ids_cajas_envases = [];
            despachos_seleccionados = [];
            actualizarContador();
        }
    }


    $(document).on('change','.barril-estado',checkBarrilesEstado);

    var ids_despachos_productos = [];
    var ids_cajas_envases = [];
    var despachos_seleccionados = [];
    var vasos = false;

    $(document).on('click','.tr-entregas',function(e){
        window.location.href = "./?s=detalle-entregas&id=" + $(e.currentTarget).data('identregas');
    });

    // Click en card de despacho
    $(document).on('click', '.despacho-card', function(e) {
        if(paso_2_disabled) {
            console.log('Click bloqueado - paso_2_disabled es true');
            return false;
        }

        var $card = $(this);
        var id_despacho = $card.data('id-despacho');
        var productos_ids = $card.data('productos-ids');
        var cajas_ids = $card.data('cajas-envases-ids');
        var tiene_vasos = $card.data('tiene-vasos') === '1';

        console.log('Despacho clickeado:', id_despacho);
        console.log('Productos IDs:', productos_ids);
        console.log('Cajas envases IDs:', cajas_ids);

        if($card.hasClass('selected')) {
            // Deseleccionar
            $card.removeClass('selected');

            // Remover del array de seleccionados
            despachos_seleccionados = despachos_seleccionados.filter(function(id) { return id !== id_despacho; });

            // Remover productos
            productos_ids.forEach(function(pid) {
                ids_despachos_productos = ids_despachos_productos.filter(function(id) { return id !== pid; });
            });

            // Remover cajas envases
            if(cajas_ids && cajas_ids.length > 0) {
                cajas_ids.forEach(function(cid) {
                    ids_cajas_envases = ids_cajas_envases.filter(function(id) { return id !== cid; });
                });
            }

            console.log('Despacho DESELECCIONADO:', id_despacho);
        } else {
            // Seleccionar
            $card.addClass('selected');

            // Agregar al array de seleccionados
            despachos_seleccionados.push(id_despacho);

            // Agregar productos
            productos_ids.forEach(function(pid) {
                if(ids_despachos_productos.indexOf(pid) === -1) {
                    ids_despachos_productos.push(pid);
                }
            });

            // Agregar cajas envases
            if(cajas_ids && cajas_ids.length > 0) {
                cajas_ids.forEach(function(cid) {
                    if(ids_cajas_envases.indexOf(cid) === -1) {
                        ids_cajas_envases.push(cid);
                    }
                });
            }

            if(tiene_vasos) {
                vasos = true;
            }

            console.log('Despacho SELECCIONADO:', id_despacho);
        }

        console.log('Despachos seleccionados:', despachos_seleccionados);
        console.log('IDs productos seleccionados:', ids_despachos_productos);
        console.log('IDs cajas envases:', ids_cajas_envases);

        actualizarContador();
    });

    function actualizarContador() {
        var total = despachos_seleccionados.length;
        $('.total').html(total);

        if(total === 0 || paso_2_disabled) {
            $('#entregar-cliente-btn').attr('disabled', true);
        } else {
            $('#entregar-cliente-btn').attr('disabled', false);
        }

        // Verificar si hay vasos en alguno de los despachos seleccionados
        vasos = false;
        $('.despacho-card.selected').each(function() {
            if($(this).data('tiene-vasos') === '1') {
                vasos = true;
            }
        });
    }


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
        'ids_cajas_envases': ids_cajas_envases,
        'id_clientes': $('#id_clientes-select').val(),
        'id_usuarios_repartidor': <?= $usuario->id; ?>,
        'cantidad_vasos': $('#cantidad-vasos-select').val(),
        'receptor_nombre': $('#receptor-input').val(),
        'rand_int': <?= $rand_int; ?>,
        'barriles_estado': getDataForm('barriles')
    };

    console.log('=== ENVIANDO ENTREGA ===');
    console.log('URL:', url);
    console.log('Payload:', data);
    console.log('========================');

    $.post(url,data,function(response_raw){
        console.log('Respuesta raw:', response_raw);
        var response = JSON.parse(response_raw);
        if(response.mensaje!="OK") {
            console.error('Error en respuesta:', response);
            alert("Algo fallo");
            return false;
        } else {
            console.log('Entrega exitosa:', response);
            var obj = response.obj;
            window.location.href = "./?s=repartidor&msg=1&id_entregas=" + obj.id;
        }
    }).fail(function(xhr, status, error){
        console.error('Error AJAX:', status, error);
        console.error('Response:', xhr.responseText);
        alert("No funciono");
    });

    }

</script>
