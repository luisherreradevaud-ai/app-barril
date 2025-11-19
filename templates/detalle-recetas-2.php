<?php

    $activos = Activo::getAll("order by nombre asc");
    $proveedores = Proveedor::getAll();
    $tipos_de_insumos = TipoDeInsumo::getAll();

    if(validaIdExists($_GET,'id')) {
        $batch = new Batch($_GET['id']);
    } else {
        $batch = new Batch;
    }

?>

<form id="batch-form">
<input type="hidden" name="id" value="">
<input type="hidden" name="entidad" value="batches">
<div class="row">
    <div class="col-md-2 mb-4">
        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link active w-100" id="pills-batch-tab" data-bs-toggle="pill" data-bs-target="#pills-batch" type="button" role="tab" aria-controls="pills-batch" aria-selected="true"><i class="bi bi-check"></i> Batch</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100" id="pills-licor-tab" data-bs-toggle="pill" data-bs-target="#pills-licor" type="button" role="tab" aria-controls="pills-licor" aria-selected="true"><i class="bi bi-dash-circle"></i> Licor</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100" id="pills-maceracion-tab" data-bs-toggle="pill" data-bs-target="#pills-maceracion" type="button" role="tab" aria-controls="pills-maceracion" aria-selected="false"><i class="bi bi-dash-circle"></i> Maceración</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100" id="pills-lavado-de-granos-tab" data-bs-toggle="pill" data-bs-target="#pills-lavado-de-granos" type="button" role="tab" aria-controls="pills-lavado-de-granos" aria-selected="false"><i class="bi bi-dash-circle"></i> Lavado</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100" id="pills-coccion-tab" data-bs-toggle="pill" data-bs-target="#pills-coccion" type="button" role="tab" aria-controls="pills-coccion" aria-selected="false"><i class="bi bi-dash-circle"></i> Cocción</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100" id="pills-combustible-tab" data-bs-toggle="pill" data-bs-target="#pills-combustible" type="button" role="tab" aria-controls="pills-combustible" aria-selected="false"><i class="bi bi-dash-circle"></i> Combustible</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100" id="pills-lupulizacion-tab" data-bs-toggle="pill" data-bs-target="#pills-lupulizacion" type="button" role="tab" aria-controls="pills-lupulizacion" aria-selected="false"><i class="bi bi-dash-circle"></i> Lupulización</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100" id="pills-enfriado-tab" data-bs-toggle="pill" data-bs-target="#pills-enfriado" type="button" role="tab" aria-controls="pills-enfriado" aria-selected="false"><i class="bi bi-dash-circle"></i> Enfriado</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100" id="pills-inoculacion-tab" data-bs-toggle="pill" data-bs-target="#pills-inoculacion" type="button" role="tab" aria-controls="pills-inoculacion" aria-selected="false"><i class="bi bi-dash-circle"></i> Inoculación</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100" id="pills-fermentacion-tab" data-bs-toggle="pill" data-bs-target="#pills-fermentacion" type="button" role="tab" aria-controls="pills-fermentacion" aria-selected="false"><i class="bi bi-dash-circle"></i> Fermentación</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100" id="pills-traspasos-tab" data-bs-toggle="pill" data-bs-target="#pills-traspasos" type="button" role="tab" aria-controls="pills-traspasos" aria-selected="false"><i class="bi bi-dash-circle"></i> Traspasos</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100" id="pills-maduracion-tab" data-bs-toggle="pill" data-bs-target="#pills-maduracion" type="button" role="tab" aria-controls="pills-maduracion" aria-selected="false"><i class="bi bi-dash-circle"></i> Maduración</button>
            </li>
            <li class="nav-item d-md-block w-100" role="presentation">
                <button class="nav-link w-100" id="pills-finalizacion-tab" data-bs-toggle="pill" data-bs-target="#pills-finalizacion" type="button" role="tab" aria-controls="pills-finalizacion" aria-selected="false"><i class="bi bi-dash-circle"></i> Finalización</button>
            </li>
        </ul>
    </div>
    <div class="col-md-10">
        
        <div class="tab-content" id="pills-tabContent">
            
            <!-- BATCH -->
            <div class="tab-pane fade show active" id="pills-batch" role="tabpanel" aria-labelledby="pills-batch-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Batch</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Fecha de Creación:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="date" class="form-control" name="batch_date" value="<?= date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                Cocinero:
                            </div>
                            <div class="col-md-6 mb-2">
                                <select class="form-control" name="batch_id_usuarios_cocinero">
                                    <option>Cocinero</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                Receta:
                            </div>
                            <div class="col-md-6 mb-2">
                                <select class="form-control" name="batch_id_recetas">
                                    <option>Custom</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /BATCH -->

            <!-- LICOR -->
            <div class="tab-pane fade show" id="pills-licor" role="tabpanel" aria-labelledby="pills-licor-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Licor</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Temperatura:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control acero-float" name="licor_temperatura" value="0" step="0.1" min="0">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">°C</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                PH:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="number" class="form-control" name="licor_ph" value="0" step="0.1" min="0" max="10">
                            </div>
                            <div class="col-md-6 mb-2">
                                Litros:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control" name="licor_litros" value="0" step="0.1" min="0">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">&nbsp;&nbsp;L</span>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>
                                                Insumos
                                            </th>
                                            <th>
                                                Cantidad
                                            </th>
                                    </thead>
                                    <tbody id="licor-insumos-table">
                                    </tbody>
                                </table>
                                <button class="btn btn-primary btn-sm agregar-insumos-btn" data-etapa="licor">+ Agregar Insumo</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /LICOR -->

            <!-- MACERACION -->

            <div class="tab-pane fade" id="pills-maceracion" role="tabpanel" aria-labelledby="pills-maceracion-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Maceración</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Hora de inicio:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="time" class="form-control" name="maceracion_hora_inicio">
                            </div>
                            <div class="col-md-6 mb-2">
                                Temperatura:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control" name="maceracion_temperatura">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">°C</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                Litros:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control" name="maceracion_litros">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">&nbsp;&nbsp;L</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                PH macerado:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="number" class="form-control" name="maceracion_ph">
                            </div>
                            <div class="col-md-6 mb-2">
                                Hora de finalización:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="time" class="form-control" name="maceracion_hora_finalizacion">
                            </div>
                            <div class="col-12 mb-2">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>
                                                Insumos
                                            </th>
                                            <th>
                                                Cantidad
                                            </th>
                                    </thead>
                                    <tbody id="maceracion-insumos-table">
                                    </tbody>
                                </table>
                                <button class="btn btn-primary btn-sm agregar-insumos-btn" data-etapa="maceracion">+ Agregar Insumo</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- /MACERACION -->

            <!-- LAVADO DE GRANOS -->

            <div class="tab-pane fade" id="pills-lavado-de-granos" role="tabpanel" aria-labelledby="pills-lavado-de-granos-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Lavado de Granos</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Hora de inicio:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="time" class="form-control" name="lavado_de_granos_hora_inicio">
                            </div>
                            <div class="col-md-6 mb-2">
                                Mosto:
                            </div>
                            <div class="col-md-6 mb-2">
                                
                                <div class="input-group">
                                    <input type="number" class="form-control" name="lavado_de_granos_mosto">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">&nbsp;&nbsp;L</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                Densidad:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="number" class="form-control" name="lavado_de_granos_densidad">
                            </div>
                            <div class="col-md-6 mb-2">
                                Tipo de densidad:
                            </div>
                            <div class="col-md-6 mb-2">
                                <select class="form-control" name="lavado_de_granos_tipo_de_densidad">
                                    <option>Pre-hervor</option>
                                    <option>Inicial</option>
                                    <option>Final</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                Hora de termino:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="time" class="form-control" name="lavado_de_granos_hora_termino">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-coccion" role="tabpanel" aria-labelledby="pills-coccion-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Cocción</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                PH inicial:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="number" class="form-control" name="coccion_ph_inicial">
                            </div>
                            <div class="col-md-6 mb-2">
                                PH final:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="number" class="form-control" name="coccion_ph_final">
                            </div>
                            <div class="col-md-6 mb-2">
                                Reciclar Bagazo:
                            </div>
                            <div class="col-md-6 mb-2">
                                
                                <div class="input-group">
                                    <input type="number" class="form-control" name="coccion_recilar">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">Kg</span>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>
                                                Insumos
                                            </th>
                                            <th>
                                                Cantidad
                                            </th>
                                    </thead>
                                    <tbody id="coccion-insumos-table">
                                    </tbody>
                                </table>
                                <button class="btn btn-primary btn-sm agregar-insumos-btn" data-etapa="coccion">+ Agregar Insumo</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-combustible" role="tabpanel" aria-labelledby="pills-combustible-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Combustible</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Gas:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control" name="combustible_gas">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">Kg</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-lupulizacion" role="tabpanel" aria-labelledby="pills-lupulizacion-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Lupulización</h3>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-1">
                                <button class="btn btn-primary btn-sm">+ Agregar Lupulización</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-enfriado" role="tabpanel" aria-labelledby="pills-enfriado-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Enfriado</h3>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-1">
                                <button class="btn btn-primary btn-sm">+ Agregar Enfriado</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-inoculacion" role="tabpanel" aria-labelledby="pills-inoculacion-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Inoculación</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Temperatura de mosto:
                            </div>
                            <div class="col-md-6 mb-2">
                                
                                <div class="input-group">
                                    <input type="number" class="form-control" name="inoculacion_temperatura">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">°C</span>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>
                                                Insumos
                                            </th>
                                            <th>
                                                Cantidad
                                            </th>
                                    </thead>
                                    <tbody id="inoculacion-insumos-table">
                                    </tbody>
                                </table>
                                <button class="btn btn-primary btn-sm agregar-insumos-btn" data-etapa="inoculacion">+ Agregar Insumo</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-fermentacion" role="tabpanel" aria-labelledby="pills-fermentacion-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Fermentación</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Fecha:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="date" class="form-control" name="fermentacion_date">
                            </div>
                            <div class="col-md-6 mb-2">
                                Hora de inicio:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="time" class="form-control" name="fermentacion_hora_inicio">
                            </div>
                            <div class="col-md-6 mb-2">
                                Temperatura Inicio:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control" name="inoculacion_temperatura_inicio">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">°C</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                Fermentador:
                            </div>
                            <div class="col-md-6 mb-2">
                                <select class="form-control" name="fermentacion_id_activos">
                                    <?php
                                    foreach($activos as $activo) {
                                    ?>
                                    <option value="<?= $activo->id; ?>"><?= $activo->nombre; ?></option>
                                    <?php
                                    }

                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                Temperatura fermentación:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control" name="fermentacion_temperatura">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">°C</span>
                                </div>
                                
                            </div>
                            <div class="col-md-6 mb-2">
                                Hora de finalización:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="time" class="form-control" name="fermentacion_hora_finalizacion">
                            </div>
                            <div class="col-md-6 mb-2">
                                PH:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="number" class="form-control" name="fermentacion_ph">
                            </div>
                            <div class="col-md-6 mb-2">
                                Densidad:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="number" class="form-control" name="fermentacion_densidad">
                            </div>
                            <div class="col-md-6 mb-2">
                                Tipo de densidad:
                            </div>
                            <div class="col-md-6 mb-2">
                                <select class="form-control" name="fermentacion_tipo_de_densidad">
                                    <option>Pre-hervor</option>
                                    <option>Inicial</option>
                                    <option>Final</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-traspasos" role="tabpanel" aria-labelledby="pills-traspasos-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Traspaso</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Fecha y hora:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="datetime-local" class="form-control" name="traspaso_datetime">
                            </div>
                            <div class="col-12 mb-2">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>
                                                Activo
                                            </th>
                                            <th>
                                                Cantidad
                                            </th>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                                <button class="btn btn-primary btn-sm">+ Agregar Traspaso</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-maduracion" role="tabpanel" aria-labelledby="pills-maduracion-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Maduración</h3>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                Fecha:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="date" class="form-control" name="maduracion_date">
                            </div>
                            <div class="col-md-6 mb-2">
                                Temperatura inicio:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control" name="maduracion_temperatura_inicio">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">°C</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                Hora inicio:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="time" class="form-control" name="maduracion_hora_inicio">
                            </div>
                            <div class="col-md-6 mb-2">
                                Temperatura finalización:
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="number" class="form-control" name="maduracion_temperatura_finalizacion">
                                    <span class="input-group-text" id="basic-addon1" style="border-radius: 0px 10px 10px 0px">°C</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                Hora finalización:
                            </div>
                            <div class="col-md-6 mb-2">
                                <input type="time" class="form-control" name="maduracion_hora_finalizacion">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="pills-finalizacion" role="tabpanel" aria-labelledby="pills-finalizacion-tab">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="card-title mb-3">
                            <h3>Finalización</h3>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-2">
                                <button class="btn btn-primary btn-sm" id="guardar-btn"><i class="bi bi-check"></i> Finalizar Batch</button>
                            </div>
                        </div>
                    </div>
                </div>
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
                            <input type="number" class="form-control acero-float" name="cantidad" value="0">
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

<script>

var obj = <?= json_encode($batch,JSON_PRETTY_PRINT); ?>;
var tipos_de_insumos = <?= json_encode($tipos_de_insumos,JSON_PRETTY_PRINT); ?>;
var tipo_de_insumo = {};
var insumos = [];
var insumo = {};


var lista_selected = false;

var etapas = ['licor','maceracion','coccion','inoculacion'];

/*
var lista = [];
lista['licor'] = [];
lista['maceracion'] = [];
lista['coccion'] = [];
lista['inoculacion'] = [];
*/

var lista = {
    'licor': new Array(),
    'maceracion': new Array(),
    'coccion': new Array(),
    'inoculacion': new Array()
};



$(document).ready(function(){

    $.each(obj,function(key,value){
        //console.log(key);
        if(key!="table_name"&&key!="table_fields"){
            $('input[name="'+key+'"]').val(value);
            $('textarea[name="'+key+'"]').val(value);
            $('select[name="'+key+'"]').val(value);
        }
    });

    renderLista();
    armarTiposDeInsumosSelect();
    changeTiposDeInsumosSelect();
    changeInsumosSelect();

});

$(document).on('change','select[name="id_tipos_de_insumos"]',function(){
    changeTiposDeInsumosSelect();
    changeInsumosSelect();
});

function armarTiposDeInsumosSelect() {
    tipos_de_insumos.forEach(function(tdi) {
        $('select[name="id_tipos_de_insumos"]').append("<option value='" + tdi.id + "'>" + tdi.nombre + "</option>");
    });
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


$(document).on('click','#guardar-btn',function(e){

  e.preventDefault();

  var url = "./ajax/ajax_guardarEntidad.php";
  var data = getDataForm("batch");
  console.log(lista);
  data['insumos'] = lista;


  //console.log(data);

  $.post(url,data,function(response_raw){
    console.log(response_raw);
    var response = JSON.parse(response_raw);
    if(response.mensaje!="OK") {
      alert("Algo fallo");
      return false;
    } else {
      //window.location.href = "./?s=detalle-batches&id=" + response.obj.id + "&msg=1";
    }
  }).fail(function(){
    alert("No funciono");
  });
});


$(document).on('click','.agregar-insumos-btn',function(e){
  e.preventDefault();
  lista_selected = $(e.currentTarget).data('etapa');
  $('#agregar-insumos-modal').modal('toggle');
});

$(document).on('click','#agregar-insumos-aceptar',function(e){

  e.preventDefault();

  var id_insumos = $('select[name="id_insumos"]').val();
  var insumo = insumos.find((ins) => ins.id == id_insumos);
  insumo.cantidad = $('input[name="cantidad"').val();

  lista[lista_selected].push(insumo);
  renderLista();

});

function renderLista() {

  //console.log(lista);
  

  etapas.forEach(function(et,et_index){
    var html = '';
    lista[et].forEach(function(ins,index){
        //console.log(ins);
        html += '<tr class="insumos-tr" data-index="' + index +'"><td><b>' + ins.nombre;
        html += '</b></td><td><b>' + ins.cantidad + " " + ins.unidad_de_medida;
        html += '</b></td><td><b><button class="btn btn-sm item-eliminar-btn" data-index="' + index + '" + data-lista="' + et + '">x</button>';
        html += '</b></td></tr>';
    });
    $('#' + et + '-insumos-table').html(html);
  });


}

$(document).on('click','.item-eliminar-btn',function(e){
  e.preventDefault();
  var index = $(e.currentTarget).data('index');
  lista.splice(index,1);
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



</script>