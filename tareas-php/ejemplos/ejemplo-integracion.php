<!--
  EJEMPLO DE INTEGRACIÓN DEL GESTOR DE TAREAS

  Este archivo muestra cómo integrar el gestor de tareas en diferentes contextos
  dentro de la aplicación Barril.cl
-->

<!-- ============================================ -->
<!-- EJEMPLO 1: En una página de Batch           -->
<!-- ============================================ -->
<?php
  // En templates/batch-detalle.php o similar

  // Obtener el batch actual
  $batch = new Batch($_GET['id']);
?>

<div class="container">
  <h2>Batch #<?php echo $batch->id; ?> - <?php echo $batch->nombre; ?></h2>

  <!-- Información del batch -->
  <div class="batch-info">
    <!-- ... información del batch ... -->
  </div>

  <!-- Sección de Tareas -->
  <div class="mt-5">
    <h3>Tareas del Batch</h3>
    <?php
      // Definir el ID de la entidad
      $_GET['entity_id'] = 'batch_' . $batch->id;

      // Incluir el template de tareas
      incluir_template("gestor-de-tareas");
    ?>
  </div>
</div>

<script>
  // Asegurarse de que entityId está definido
  entityId = 'batch_<?php echo $batch->id; ?>';
</script>


<!-- ============================================ -->
<!-- EJEMPLO 2: En una página de Cliente         -->
<!-- ============================================ -->
<?php
  // En templates/cliente-detalle.php o similar

  $cliente = new Cliente($_GET['id']);
?>

<div class="container">
  <h2>Cliente: <?php echo $cliente->nombre; ?></h2>

  <!-- Tabs de navegación -->
  <ul class="nav nav-tabs" id="clienteTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#info">
        Información
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tareas">
        Tareas
      </button>
    </li>
  </ul>

  <!-- Contenido de tabs -->
  <div class="tab-content" id="clienteTabContent">
    <div class="tab-pane fade show active" id="info" role="tabpanel">
      <!-- Información del cliente -->
    </div>

    <div class="tab-pane fade" id="tareas" role="tabpanel">
      <?php
        $_GET['entity_id'] = 'cliente_' . $cliente->id;
        incluir_template("gestor-de-tareas");
      ?>
    </div>
  </div>
</div>

<script>
  entityId = 'cliente_<?php echo $cliente->id; ?>';
</script>


<!-- ============================================ -->
<!-- EJEMPLO 3: En una página de Proyecto        -->
<!-- ============================================ -->
<?php
  // En templates/proyecto-detalle.php o similar

  $proyecto = new Proyecto($_GET['id']);
?>

<div class="container">
  <div class="row">
    <!-- Columna principal -->
    <div class="col-lg-8">
      <h2>Proyecto: <?php echo $proyecto->nombre; ?></h2>

      <!-- Detalles del proyecto -->
      <div class="proyecto-info mb-4">
        <!-- ... -->
      </div>

      <!-- Tareas del proyecto -->
      <div class="proyecto-tareas">
        <h3>Tareas del Proyecto</h3>
        <?php
          $_GET['entity_id'] = 'proyecto_' . $proyecto->id;
          incluir_template("gestor-de-tareas");
        ?>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
      <!-- Información adicional del proyecto -->
    </div>
  </div>
</div>

<script>
  entityId = 'proyecto_<?php echo $proyecto->id; ?>';
</script>


<!-- ============================================ -->
<!-- EJEMPLO 4: Como sección colapsable          -->
<!-- ============================================ -->
<?php
  $pedido = new Pedido($_GET['id']);
?>

<div class="container">
  <h2>Pedido #<?php echo $pedido->id; ?></h2>

  <!-- Información del pedido -->
  <div class="pedido-info mb-4">
    <!-- ... -->
  </div>

  <!-- Accordion para secciones adicionales -->
  <div class="accordion" id="pedidoAccordion">
    <!-- Productos -->
    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#productos">
          Productos
        </button>
      </h2>
      <div id="productos" class="accordion-collapse collapse show">
        <div class="accordion-body">
          <!-- Lista de productos -->
        </div>
      </div>
    </div>

    <!-- Tareas -->
    <div class="accordion-item">
      <h2 class="accordion-header">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tareas">
          Tareas
        </button>
      </h2>
      <div id="tareas" class="accordion-collapse collapse">
        <div class="accordion-body">
          <?php
            $_GET['entity_id'] = 'pedido_' . $pedido->id;
            incluir_template("gestor-de-tareas");
          ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  entityId = 'pedido_<?php echo $pedido->id; ?>';
</script>


<!-- ============================================ -->
<!-- EJEMPLO 5: Modal flotante                    -->
<!-- ============================================ -->
<?php
  // En cualquier página donde se quiera mostrar tareas en un modal
?>

<div class="container">
  <h2>Mi Página</h2>

  <!-- Botón para abrir modal de tareas -->
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tareasModal">
    <i class="bi bi-check-square"></i> Ver Tareas
  </button>
</div>

<!-- Modal de Tareas -->
<div class="modal fade" id="tareasModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tareas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php
          $_GET['entity_id'] = 'general_' . $usuario->id;
          incluir_template("gestor-de-tareas");
        ?>
      </div>
    </div>
  </div>
</div>

<script>
  entityId = 'general_<?php echo $usuario->id; ?>';
</script>


<!-- ============================================ -->
<!-- EJEMPLO 6: Widget en Dashboard               -->
<!-- ============================================ -->
<?php
  // En templates/inicio.php (dashboard)
?>

<div class="container-fluid">
  <h2>Dashboard</h2>

  <div class="row">
    <!-- Estadísticas -->
    <div class="col-lg-8">
      <div class="row">
        <div class="col-md-4">
          <!-- Card de estadística -->
        </div>
        <div class="col-md-4">
          <!-- Card de estadística -->
        </div>
        <div class="col-md-4">
          <!-- Card de estadística -->
        </div>
      </div>

      <!-- Gráficos -->
      <div class="charts mt-4">
        <!-- ... -->
      </div>
    </div>

    <!-- Sidebar con tareas -->
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">
          <h5>Mis Tareas Pendientes</h5>
        </div>
        <div class="card-body" style="max-height: 600px; overflow-y: auto;">
          <?php
            $_GET['entity_id'] = 'usuario_' . $usuario->id;
            incluir_template("gestor-de-tareas");
          ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  entityId = 'usuario_<?php echo $usuario->id; ?>';
</script>


<!-- ============================================ -->
<!-- NOTAS IMPORTANTES                            -->
<!-- ============================================ -->

<!--
  1. ID DE ENTIDAD:
     - Debe ser único para cada contexto
     - Formato recomendado: "tipo_id" (ej: "batch_123", "cliente_456")
     - Usar el mismo formato en PHP y JavaScript

  2. MÚLTIPLES INSTANCIAS:
     - Si hay múltiples gestores de tareas en la misma página,
       usar IDs de entidad diferentes

  3. CARGA DINÁMICA:
     - El gestor se carga automáticamente cuando se define entityId
     - Se puede recargar llamando a loadTasks() desde JavaScript

  4. ESTILOS:
     - Los estilos están incluidos en el template
     - Se pueden sobrescribir en un archivo CSS personalizado

  5. DEPENDENCIAS:
     - Requiere Bootstrap 5
     - Requiere jQuery 1.12.4+
     - Requiere Bootstrap Icons
-->
