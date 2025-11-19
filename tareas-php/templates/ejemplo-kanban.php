<?php
/**
 * EJEMPLO DE USO DEL SISTEMA KANBAN
 *
 * Este archivo muestra cómo integrar el tablero Kanban en cualquier página.
 * Puedes copiar y adaptar este código a tus necesidades.
 */

// Incluir el sistema base de Barril.cl
require_once(__DIR__ . "/../../php/app.php");

// Verificar sesión de usuario
$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);

// EJEMPLO 1: Kanban para un Batch
// Descomenta para usar con batches
// $batch = new Batch($_GET['id']);
// $entity_id = 'batch_' . $batch->id;
// $entity_nombre = 'Batch #' . $batch->numero;

// EJEMPLO 2: Kanban para un Cliente
// Descomenta para usar con clientes
// $cliente = new Cliente($_GET['id']);
// $entity_id = 'cliente_' . $cliente->id;
// $entity_nombre = $cliente->nombre;

// EJEMPLO 3: Kanban para un Proyecto
// Descomenta para usar con proyectos
// $proyecto = new Proyecto($_GET['id']);
// $entity_id = 'proyecto_' . $proyecto->id;
// $entity_nombre = $proyecto->nombre;

// EJEMPLO 4: Kanban para cualquier entidad personalizada
// Para este ejemplo, usaremos un ID de prueba
$entity_id = isset($_GET['entity_id']) ? $_GET['entity_id'] : 'demo_1';
$entity_nombre = isset($_GET['entity_nombre']) ? $_GET['entity_nombre'] : 'Tablero de Demostración';

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tablero Kanban - <?php echo $entity_nombre; ?></title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

  <style>
    body {
      background-color: #f5f5f5;
      padding: 20px;
    }
    .page-header {
      background: white;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .breadcrumb {
      background: none;
      padding: 0;
      margin: 0;
    }
  </style>
</head>
<body>

  <!-- Header de la página -->
  <div class="page-header">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-2">
        <li class="breadcrumb-item"><a href="/">Inicio</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo $entity_nombre; ?></li>
      </ol>
    </nav>
    <h1 class="h3 mb-0">
      <i class="bi bi-kanban"></i>
      Tablero Kanban - <?php echo $entity_nombre; ?>
    </h1>
    <p class="text-muted mb-0 mt-2">
      Gestiona tus tareas con el sistema de tablero Kanban. Arrastra las tareas entre columnas para cambiar su estado.
    </p>
  </div>

  <!--
    ============================================
    AQUÍ SE INCLUYE EL TABLERO KANBAN
    ============================================
  -->
  <?php
    // Pasar el entity_id al template
    $_GET['entity_id'] = $entity_id;

    // Incluir el template del tablero Kanban
    include(__DIR__ . '/tablero-kanban.php');
  ?>

  <!-- Scripts requeridos -->

  <!-- jQuery (requerido) -->
  <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>

  <!-- jQuery UI (IMPORTANTE para drag & drop) -->
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

  <!-- Bootstrap 5 -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Scripts del Kanban -->
  <script src="../js/kanban.js"></script>
  <script src="../js/kanban-task-functions.js"></script>

  <!-- Definir el entityId para JavaScript -->
  <script>
    // IMPORTANTE: Definir el entityId para que el sistema funcione
    var entityId = '<?php echo $entity_id; ?>';

    // Inicializar el tablero cuando la página esté lista
    $(document).ready(function() {
      console.log('Tablero Kanban inicializado para entity:', entityId);
    });
  </script>

</body>
</html>

<?php
/*
============================================
INSTRUCCIONES DE USO
============================================

1. INTEGRACIÓN BÁSICA EN TU TEMPLATE EXISTENTE:

   <?php
     // En cualquier template (batch-detalle.php, cliente-detalle.php, etc.)
     $entity_id = 'batch_' . $batch->id;
     $_GET['entity_id'] = $entity_id;
     include(__DIR__ . '/tablero-kanban.php');
   ?>

   <script>
     var entityId = '<?php echo $entity_id; ?>';
   </script>


2. INTEGRACIÓN CON TABS (Bootstrap):

   <ul class="nav nav-tabs">
     <li class="nav-item">
       <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#info">
         Información
       </button>
     </li>
     <li class="nav-item">
       <button class="nav-link" data-bs-toggle="tab" data-bs-target="#kanban">
         <i class="bi bi-kanban"></i> Tareas
       </button>
     </li>
   </ul>

   <div class="tab-content">
     <div class="tab-pane fade show active" id="info">
       <!-- Tu contenido actual -->
     </div>

     <div class="tab-pane fade" id="kanban">
       <?php
         $_GET['entity_id'] = 'batch_' . $batch->id;
         include(__DIR__ . '/tablero-kanban.php');
       ?>
     </div>
   </div>

   <script>
     var entityId = 'batch_<?php echo $batch->id; ?>';
   </script>


3. MÚLTIPLES TABLEROS EN LA MISMA APLICACIÓN:

   // Batch
   entityId = 'batch_123'

   // Cliente
   entityId = 'cliente_456'

   // Proyecto
   entityId = 'proyecto_789'

   // Departamento
   entityId = 'departamento_ventas'

   Cada entityId tendrá su propio tablero independiente.


4. SCRIPTS REQUERIDOS (agregar en tu layout principal):

   <!-- jQuery -->
   <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>

   <!-- jQuery UI (IMPORTANTE para drag & drop) -->
   <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

   <!-- Bootstrap 5 -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

   <!-- Bootstrap Icons -->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

   <!-- Scripts del Kanban -->
   <script src="js/kanban.js"></script>
   <script src="js/kanban-task-functions.js"></script>


5. PRUEBA RÁPIDA:

   Accede a: http://localhost/tareas-php/templates/ejemplo-kanban.php

   O con parámetros:
   http://localhost/tareas-php/templates/ejemplo-kanban.php?entity_id=batch_123&entity_nombre=Batch%20123


============================================
CARACTERÍSTICAS DEL SISTEMA
============================================

✅ Drag & Drop entre columnas
✅ Auto-guardado (1 segundo de debounce)
✅ Múltiples usuarios por tarea
✅ Etiquetas con colores
✅ Checklists con progreso
✅ Fechas de vencimiento
✅ Enlaces externos
✅ Archivos adjuntos
✅ Columnas personalizables
✅ Colores por columna
✅ Contador de tareas
✅ Indicador de tareas vencidas


============================================
SOPORTE Y PERSONALIZACIÓN
============================================

Para personalizar colores, tamaños, o comportamiento:
- Ver documentación en README.md
- Modificar CSS en tablero-kanban.php
- Ajustar comportamiento en kanban.js

*/
?>
