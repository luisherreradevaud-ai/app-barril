<?php
  $barriles = Barril::getAll("WHERE clasificacion='Cerveza' ORDER BY codigo asc");
  $fermentadores = Activo::getAll("WHERE clase='Fermentador' ORDER BY codigo asc");
?>
<div class="container my-4">
  <h2>Reporte Diario</h2>
  <div class="row mt-3">
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <h3>Barriles en Planta</h3>
            <button 
              type="button" 
              class="btn btn-primary btn-sm mb-3" 
              data-type="barril" 
              data-bs-toggle="modal" 
              data-bs-target="#itemModal"
            >+ Agregar</button>
          </div>
          <table class="table table-striped table-hover" id="barrilesTable">
            <thead>
              <tr>
                <th>Código</th>
                <th class="text-end">Acción</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <h3>Fermentadores</h3>
            <button 
              type="button" 
              class="btn btn-primary btn-sm mb-3" 
              data-type="fermentador" 
              data-bs-toggle="modal" 
              data-bs-target="#itemModal"
            >+ Agregar</button>
          </div>
          <table class="table table-striped table-hover" id="fermentadoresTable">
            <thead>
              <tr>
                <th>Código</th>
                <th>Estado</th>
                <th class="text-end">Acción</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="d-flex justify-content-end mt-3">
    <button type="button" class="btn btn-secondary" id="cerrarReporteBtn">Cerrar Reporte</button>
  </div>
</div>

<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="itemModalLabel">Selecciona un ítem</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <select class="form-select" id="itemSelect">
            <option value="">-- Elige un ítem --</option>
          </select>
        </div>
        <div class="mb-3 d-none" id="statusGroup">
          <label for="itemStatus" class="form-label">Estado</label>
          <select class="form-select" id="itemStatus">
            <option value="Fermentación">Fermentación</option>
            <option value="Maduración">Maduración</option>
            <option value="Vacío">Vacío</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="modalAccept" data-bs-dismiss="modal">Aceptar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const barriles = <?= json_encode($barriles, JSON_PRETTY_PRINT); ?>;
  const fermentadores = <?= json_encode($fermentadores, JSON_PRETTY_PRINT); ?>;
  const dataMap = { 
    barril: barriles, 
    fermentador: fermentadores 
  };
  const selected = { 
    barril: [], 
    fermentador: [] 
  };
  let currentType = null;

  function updateSelect(type) {
    const $sel = $('#itemSelect').empty().append('<option value="">-- Elige un ítem --</option>');
    dataMap[type].forEach(item => {
      if (!selected[type].some(s => s.id === item.id)) {
        $sel.append(`<option value="${item.id}">${item.codigo}</option>`);
      }
    });
    if (type === 'fermentador') {
      $('#statusGroup').removeClass('d-none');
    } else {
      $('#statusGroup').addClass('d-none');
    }
  }

  function updateTable(type) {
    const $tbody = $(`#${type}esTable tbody`).empty();
    selected[type].forEach((item, i) => {
      if (type === 'barril') {
        $tbody.append(`
          <tr data-index="${i}">
            <td>${item.codigo}</td>
            <td class="text-end">
              <button class="btn btn-danger btn-sm removeItem" data-type="${type}" data-index="${i}">Eliminar</button>
            </td>
          </tr>
        `);
      } else {
        $tbody.append(`
          <tr data-index="${i}">
            <td>${item.codigo}</td>
            <td>${item.status}</td>
            <td class="text-end">
              <button class="btn btn-danger btn-sm removeItem" data-type="${type}" data-index="${i}">Eliminar</button>
            </td>
          </tr>
        `);
      }
    });
    updateSelect(type);
  }

  $(document).on('click', 'button[data-bs-target="#itemModal"]', function(){
    currentType = $(this).data('type');
    $('#itemModalLabel').text(
      currentType === 'barril' ? 'Selecciona un Barril' : 'Selecciona un Fermentador'
    );
    updateSelect(currentType);
  });

  $('#modalAccept').click(function(){
    const id = $('#itemSelect').val();
    if (!id) return;
    const item = dataMap[currentType].find(x => x.id == id);
    if (item) {
      if (currentType === 'fermentador') {
        const status = $('#itemStatus').val();
        selected[currentType].push({ ...item, status });
      } else {
        selected[currentType].push(item);
      }
      updateTable(currentType);
    }
  });

  $(document).on('click', '.removeItem', function(){
    const type = $(this).data('type');
    const idx = $(this).data('index');
    selected[type].splice(idx, 1);
    updateTable(type);
  });

  $('#cerrarReporteBtn').click(function(){
    $.post(
      './ajax/ajax_guardarReporteDiario.php',
      { barriles: selected.barril, fermentadores: selected.fermentador },
      function(response){
        console.log(response);
      }
    ).fail(function(){
      alert('Error al guardar reporte');
    });
  });
</script>
