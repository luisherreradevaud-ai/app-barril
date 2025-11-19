# Barril.cl - Coding Conventions

This document outlines the coding standards and conventions used throughout the Barril.cl codebase. Follow these conventions when contributing to the project.

---

## PHP Conventions

### General PHP Style

#### File Structure
```php
<?php
// Always use full PHP opening tag (never short tags <?)

  class MyClass extends Base {
    // Class body with 2-space indentation
  }

?>
```

#### Indentation
- **2 spaces** for indentation (not tabs)
- Consistent across PHP, HTML, and JavaScript

#### Naming Conventions
- **Classes**: PascalCase, singular
  ```php
  class Batch extends Base
  class Usuario extends Base
  class ConversacionInterna extends Base
  ```

- **Table names**: lowercase, plural
  ```php
  $this->tableName("batches");
  $this->tableName("usuarios");
  $this->tableName("conversaciones_internas");
  ```

- **Properties**: snake_case (for database fields)
  ```php
  public $batch_date;
  public $batch_id_usuarios_cocinero = 0;
  public $id_recetas = 0;
  ```

- **Methods**: camelCase
  ```php
  public function checkSession()
  public function setProperties($array)
  public function getInfoDatabase($field, $value)
  ```

- **Functions**: snake_case (utility functions in app.php)
  ```php
  function incluir_template($template)
  function validaIdExists($array, $index)
  function datetime2fechayhora($datetime)
  ```

#### Property Defaults
Always initialize properties with appropriate defaults:
```php
public $id = "";              // String IDs (UUID-like)
public $nombre = "";           // Strings
public $cantidad = 0;          // Integers
public $estado = "activo";     // Enums
public $fecha_creacion;        // Dates/timestamps (no default)
```

### Class Structure

#### Standard Entity Class Pattern
```php
<?php

class MyEntity extends Base {

  // Public properties (map to database fields)
  public $id = "";
  public $nombre = "";
  public $id_parent_entity = 0;
  public $estado = "activo";
  public $fecha_creacion;

  // Table name and fields
  public $table_name = "my_entities";
  public $table_fields = array();

  // Constructor
  public function __construct($id = null) {
    $this->tableName("my_entities");
    if($id) {
      $this->id = $id;
      $info = $this->getInfoDatabase('id');
      $this->setProperties($info);
    } else {
      $this->fecha_creacion = date('Y-m-d H:i:s');
    }
  }

  // Custom methods
  public function setSpecifics($post) {
    if($this->id == "") {
      $this->save();
    }
    // Custom logic here
  }
}

?>
```

#### Base Class Methods (Inherited by all entities)
- `save()` - Insert or update record
- `delete()` - Soft delete (sets estado='eliminado')
- `getAll($where = "")` - Fetch all records
- `getInfoDatabase($field, $value)` - Fetch single record
- `setProperties($array)` - Set multiple properties from array
- `setPropertiesNoId($array)` - Set properties excluding ID
- `tableFields()` - Auto-inspect table schema

### AJAX Endpoints

#### File Naming
`ajax_actionName.php` - Use snake_case with `ajax_` prefix

#### Standard AJAX Response Structure (SECURE - NEW STANDARD)
```php
<?php

require_once("./../php/app.php");

// Initialize AJAX security with comprehensive checks
$ajax = AjaxSecurity::init([
  'methods' => ['POST'],           // Allowed HTTP methods
  'csrf' => true,                  // Require CSRF token
  'auth' => true,                  // Require authentication
  'rate_limit' => true,            // Enable rate limiting
  'min_level' => 'Operario',       // Optional: minimum user level
  'required_params' => ['id'],     // Required parameters
  'input_rules' => [               // Sanitization rules
    'id' => 'string',
    'nombre' => 'string',
    'email' => 'email'
  ]
]);

try {
  // Get validated and sanitized input
  $id = $ajax->input('id');
  $nombre = $ajax->input('nombre');

  // Business logic here
  $obj = new MyClass($id);
  $obj->nombre = $nombre;
  $obj->save();

  // Send success response
  $ajax->success(['obj' => $obj], 'Guardado exitosamente');

} catch (Exception $e) {
  error_log("Error in ajax_actionName.php: " . $e->getMessage());
  $ajax->error($e->getMessage(), 'save_error', 500);
}

?>
```

#### Legacy Format (DEPRECATED - Do not use for new code)
```php
<?php
  // Old insecure pattern - DO NOT USE
  if($_POST == array()) { die(); }
  require_once("./../php/app.php");
  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);
  // ... rest of old code
?>
```

#### Response Format Standards
- Success: `{ "status": "OK", "mensaje": "...", "data": {...} }`
- Error: `{ "status": "ERROR", "mensaje": "...", "error_code": "..." }`
- Always use `JSON_PRETTY_PRINT` for debugging

#### AjaxSecurity Configuration Options
```php
$ajax = AjaxSecurity::init([
  'methods' => ['POST'],        // Default: ['POST']
  'csrf' => true,               // Default: true (validates CSRF token)
  'auth' => true,               // Default: true (requires login)
  'rate_limit' => true,         // Default: true (60 req/min)
  'ajax_only' => false,         // Default: false (require X-Requested-With header)
  'min_level' => null,          // Default: null (options: 'Operario', 'Administrador', etc.)
  'required_params' => [],      // Default: [] (array of required param names)
  'input_rules' => []           // Default: [] (field => sanitization type)
]);
```

#### Input Sanitization Types
- `'string'` - XSS-safe string (strip_tags + htmlspecialchars)
- `'int'` - Integer
- `'float'` - Float/decimal
- `'email'` - Valid email address
- `'url'` - Valid URL
- `'html'` - HTML content (htmlspecialchars only)
- `'sql'` - SQL-safe (addslashes)

#### Permission Levels (Hierarchy)
1. `'Visita'` - Lowest
2. `'Cliente'`
3. `'Repartidor'`
4. `'Vendedor'`
5. `'Operario'`
6. `'Jefe de Cocina'`
7. `'Jefe de Planta'`
8. `'Administrador'` - Highest

### SQL Conventions

#### Query Building
```php
// Multi-line SELECT for readability
$query = "SELECT
  COLUMN_NAME,
  DATA_TYPE
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_NAME = '".$this->table_name."'";

// UPDATE with concatenation
$query = "UPDATE "
  .$this->table_name
  ." SET "
  .implode(',',$insert_cols)
  ." WHERE id='"
  .$this->id
  ."'";

// INSERT with arrays
$query = "INSERT INTO "
  .$this->table_name
  ." ("
  .implode(",",$keys)
  .") VALUES ("
  .implode(",",$values)
  .")";
```

#### SQL Safety
- Use `addslashes()` for string escaping (handled in `Base::update()`)
- Use `validaIdExists()` to validate parameters before queries
- Use `mysqli_real_escape_string()` for user input in custom queries

#### Query Execution
```php
$mysqli = $GLOBALS['mysqli'];
$data = $mysqli->query($query);

// Fetch as associative array
$result = mysqli_fetch_all($data, MYSQLI_ASSOC);
// OR for single record
$result = mysqli_fetch_array($data, MYSQLI_ASSOC);
```

### Validation Functions

#### Common Validation Patterns
```php
// Check if array key exists and is not empty
if(validaIdExists($_POST, 'id')) {
  $action = "modificado";
} else {
  $action = "creado";
}

// Validate date format
if(validaFecha($date)) {
  // Process date
}

// Validate email
if(validaEmail($email)) {
  // Process email
}

// Validate numeric
if(validaNumerico($value)) {
  // Process number
}
```

---

## Template Conventions

### File Naming
- Use kebab-case: `detalle-batch.php`, `nuevo-producto.php`
- Prefix with action: `nuevo-`, `detalle-`, `editar-`

### Template Structure
```php
<?php
  // Data preparation at top
  $mes = date('m');
  if(validaIdExists($_GET,'mes')) {
    $mes = $_GET['mes'];
  }

  $entity = MyEntity::getAll("WHERE estado='activo'");
?>
<style>
/* Scoped component styles */
.my-component {
  cursor: pointer;
}
</style>

<!-- HTML structure -->
<div class="mb-4">
  <h1 class="h2 mb-0 text-gray-800"><b>Page Title</b></h1>
</div>

<?php Widget::printWidget("my-widget"); ?>

<!-- Data display -->
<table class="table table-hover table-striped table-sm" id="my-table">
  <thead class="thead-dark">
    <tr>
      <th>Column 1</th>
      <th>Column 2</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($entities as $entity) { ?>
    <tr class="tr-entity" data-id="<?= $entity->id; ?>">
      <td><?= $entity->nombre; ?></td>
      <td><?= $entity->descripcion; ?></td>
    </tr>
    <?php } ?>
  </tbody>
</table>

<script>
// Component-specific JavaScript at bottom
$(document).ready(function() {
  // Event handlers
  $('.tr-entity').click(function() {
    var id = $(this).data('id');
    window.location.href = '?s=detalle-entity&id=' + id;
  });
});
</script>
```

### PHP/HTML Mixing
```php
// Short echo tag for output
<?= $variable; ?>

// Full tags for logic
<?php if($condition) { ?>
  <div>Content</div>
<?php } ?>

// Loop pattern
<?php foreach($items as $item) { ?>
  <div><?= $item->name; ?></div>
<?php } ?>
```

### Bootstrap Classes
Use Bootstrap 5 utility classes consistently:
```html
<!-- Spacing -->
<div class="mb-4">         <!-- margin-bottom -->
<div class="mt-3">         <!-- margin-top -->
<div class="p-3">          <!-- padding -->

<!-- Display -->
<div class="d-flex justify-content-between">
<div class="text-center">
<div class="text-gray-800">

<!-- Components -->
<button class="btn btn-primary">
<div class="card">
<div class="modal fade">
```

---

## JavaScript Conventions

### Module Pattern (Modern)
```javascript
/**
 * Module Name - Description
 */

(function() {
  'use strict';

  // ===========================
  // GLOBAL VARIABLES
  // ===========================

  window.MyModule = window.MyModule || {};

  var config = window.MyModule.config || {};
  var myVar = null;

  // Cached selectors
  var $document = $(document);
  var $myElement;

  console.log('🎯 [INIT] Module loaded');

  // ===========================
  // INITIALIZATION
  // ===========================

  $(document).ready(function() {
    console.log('📄 [READY] Document ready');

    $myElement = $('#my-element');
    setupEventHandlers();
  });

  // ===========================
  // EVENT HANDLERS
  // ===========================

  function setupEventHandlers() {
    // Use event delegation for dynamic elements
    $document.on('click', '.dynamic-element', handleClick);

    // Direct binding for static elements
    $('#static-button').on('click', handleButtonClick);
  }

  function handleClick(e) {
    var $target = $(e.currentTarget);
    var id = $target.data('id');
    // Handle click
  }

  // ===========================
  // HELPER FUNCTIONS
  // ===========================

  function helperFunction(param) {
    // Implementation
  }

})();
```

### Legacy Pattern (Still in use)
```javascript
// Global functions
function myFunction(param) {
  // Implementation
}

// Document ready
$(document).ready(function() {
  // Event handlers
  $('#element').click(function() {
    // Handle click
  });
});
```

### Naming
- **Variables**: camelCase
  ```javascript
  var myVariable = "value";
  var currentUserId = 123;
  ```

- **Functions**: camelCase
  ```javascript
  function loadTableros() { }
  function handleTaskCardClick() { }
  ```

- **Constants**: UPPER_SNAKE_CASE (rare, usually in config)
  ```javascript
  var MAX_FILE_SIZE = 20 * 1024 * 1024;
  ```

- **jQuery objects**: Prefix with `$`
  ```javascript
  var $kanbanBoard = $('#kanban-board');
  var $modal = $('#myModal');
  ```

### AJAX Calls

#### Secure AJAX Call (With CSRF Token - NEW STANDARD)
```javascript
$.ajax({
  url: '/ajax/ajax_actionName.php',
  method: 'POST',
  data: {
    csrf_token: csrfToken,  // Include CSRF token
    param1: value1,
    param2: value2
  },
  success: function(response) {
    if(response.status === 'OK') {
      // Handle success
      console.log('✅ Success:', response.mensaje);
    } else {
      // Handle error
      console.error('❌ Error:', response.mensaje);
    }
  },
  error: function(xhr, status, error) {
    console.error('❌ AJAX error:', error);
  }
});
```

#### Global CSRF Token Setup
```javascript
// Set CSRF token globally in your main JavaScript file
// The token should be provided by the backend in the main template
var csrfToken = '<?php echo Security::getCSRFToken(); ?>';

// Option 1: Include in every AJAX call data
$.ajax({
  url: '/ajax/ajax_action.php',
  data: { csrf_token: csrfToken, ...otherData }
});

// Option 2: Set as default header for all AJAX requests
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': csrfToken
  }
});

// Option 3: Add to specific request header
$.ajax({
  url: '/ajax/ajax_action.php',
  headers: {
    'X-CSRF-TOKEN': csrfToken
  },
  data: { ...yourData }
});
```

#### Legacy AJAX Call (DEPRECATED)
```javascript
// Old pattern without CSRF - DO NOT USE FOR NEW CODE
$.ajax({
  url: '/ajax/ajax_actionName.php',
  method: 'POST',
  data: { param1: value1 }
});
```

### Console Logging
Use emoji prefixes for log categorization:
```javascript
console.log('🎯 [INIT] Initialization message');
console.log('📄 [READY] Document ready');
console.log('✅ [SUCCESS] Operation successful');
console.warn('⚠️ [WARNING] Warning message');
console.error('❌ [ERROR] Error message');
console.log('🎮 [HANDLERS] Event handlers');
```

---

## Database Conventions

### Table Naming
- **Lowercase, plural**: `batches`, `usuarios`, `productos`
- **Join tables**: Entity names in alphabetical order
  - `media_batches` (not `batches_media`)
  - `kanban_tareas_usuarios` (many-to-many)

### Field Naming
- **Primary key**: Always `id` (VARCHAR 36)
- **Foreign keys**: `id_parent_table` (singular)
  ```sql
  id_batches      -- References batches.id
  id_usuarios     -- References usuarios.id
  id_recetas      -- References recetas.id
  ```

### Data Types
```sql
-- IDs (UUIDs)
id VARCHAR(36) PRIMARY KEY

-- Foreign keys
id_parent_entity VARCHAR(36)

-- Strings
nombre VARCHAR(255)
descripcion TEXT
estado VARCHAR(50)

-- Numbers
cantidad INT
precio DECIMAL(10,2)

-- Dates
fecha_creacion DATE
datetime_actualizacion DATETIME

-- JSON data
metadata TEXT  -- Store JSON as TEXT
checklist TEXT -- Store JSON arrays
```

### Date/Time Standards
- **Date format**: `YYYY-MM-DD` (MySQL DATE)
- **DateTime format**: `YYYY-MM-DD HH:MM:SS` (MySQL DATETIME)
- **Timezone**: America/Santiago (set in app.php)
- **Empty dates**: `'0000-00-00'` or `NULL`

### Estado (Status) Field
Use consistent status values:
```php
// Common values
$entity->estado = "activo";
$entity->estado = "eliminado";  // Soft delete
$entity->estado = "bloqueado";
$entity->estado = "completado";
$entity->estado = "pendiente";
```

### Boolean Fields
Store as strings (for mysqli compatibility):
```php
$entity->is_active = "1";  // True
$entity->is_active = "0";  // False

// Check in PHP
if($entity->is_active == "1") { }
```

---

## File Organization

### Directory Structure
```
/php/classes/           # Entity classes (PascalCase.php)
  Base.php
  Batch.php
  Usuario.php

/templates/             # View templates (kebab-case.php)
  batches.php
  detalle-batch.php
  nuevo-producto.php
  components/           # Reusable components
    conversacion-interna.php

/ajax/                  # AJAX endpoints (ajax_actionName.php)
  ajax_guardarEntidad.php
  ajax_eliminarEntidad.php

/js/                    # JavaScript files (kebab-case.js)
  app.js
  tablero-kanban.js

/css/                   # Stylesheets (kebab-case.css)
  app.css
  conversacion-interna.css

/media/                 # Uploaded files
  batches/
  productos/
  conversaciones/
```

---

## Error Handling

### PHP Error Handling
```php
// Try-catch for critical operations
try {
  $obj->save();
  $response["status"] = "OK";
} catch (Exception $e) {
  $response["status"] = "ERROR";
  $response["mensaje"] = $e->getMessage();
  error_log("Error in file.php: " . $e->getMessage());
}

// Validation before processing
if(!isset($_POST['required'])) {
  print "error";
  die();
}
```

### JavaScript Error Handling
```javascript
try {
  // Risky operation
} catch(error) {
  console.error('❌ Error:', error);
}

// AJAX error handling
$.ajax({
  // ...
  error: function(xhr, status, error) {
    console.error('❌ AJAX error:', error);
    alert('Error: ' + error);
  }
});
```

---

## Comments and Documentation

### PHP Comments
```php
// Single line comment for brief explanations

/**
 * Multi-line comment for complex functions
 * @param string $param Description
 * @return bool Returns true on success
 */
public function myMethod($param) {
  // Implementation
}

// Database query explanation
// Get all active batches from current month
$query = "SELECT * FROM batches WHERE estado='activo'";
```

### JavaScript Comments
```javascript
// Single line for brief notes

/**
 * Function Name - Description
 * Longer explanation if needed
 */
function myFunction() {
  // Implementation
}

// Section headers
// ===========================
// SECTION NAME
// ===========================
```

### HTML Comments
```html
<!-- Section description -->
<div class="section">
  <!-- Component explanation -->
</div>
```

---

## Security Best Practices

### Input Validation
```php
// Always validate and sanitize input
$input = sanitize_input($_POST['field']);
$input = addslashes($input);
$input = htmlspecialchars($input);

// Validate before database operations
if(validaIdExists($_POST, 'id')) {
  // Process
}
```

### Authentication Checks
```php
// Every AJAX endpoint must check session
$usuario = new Usuario;
session_start();
$usuario->checkSession($_SESSION);
```

### SQL Injection Prevention
```php
// Use Base class methods (auto-escaping)
$obj->setProperties($_POST);
$obj->save();

// For custom queries, escape strings
$safe_value = addslashes($user_input);
```

---

## Chilean-Specific Conventions

### RUT (Chilean Tax ID)
```php
// Store without formatting
$rut = convertRutToInt("12.345.678-9"); // "123456789"

// Calculate verification digit
$dv = calcularDV("12345678"); // "9"
```

### Date Display
```php
// Convert to Chilean format (DD-MM-YYYY)
$fecha_chilena = convertToChileanDate("2024-03-15"); // "15-03-2024"

// Convert from Chilean format to MySQL
$fecha_mysql = convertToMySQLDate("15-03-2024"); // "2024-03-15"
```

### Currency
```php
// Always use Chilean Peso (CLP)
$precio = 15000; // No decimals for CLP
```

---

## Version Control (Not in use)

While not currently using Git, follow these conventions if implementing:

### Commit Messages
```
feat: Add new feature
fix: Fix bug
refactor: Code refactoring
docs: Documentation changes
style: Code style changes
```

### Branch Naming
```
feature/feature-name
bugfix/bug-description
hotfix/critical-fix
```

---

## Performance Guidelines

### Database Queries
- Use `WHERE` clauses to limit results
- Avoid `SELECT *` when possible
- Cache frequently accessed data

### JavaScript
- Cache jQuery selectors
- Use event delegation for dynamic elements
- Debounce rapid events (like auto-save)

### PHP
- Use `include_once` or `require_once` to avoid duplicate includes
- Close database connections when done (handled by mysqli)
- Minimize file I/O operations

---

## Testing Conventions

### Manual Testing Checklist
1. Test in both light and dark themes
2. Test all user permission levels
3. Verify AJAX responses in Network tab
4. Check console for JavaScript errors
5. Validate database changes in phpMyAdmin

### Debug Mode
```php
// Enable in app.php
$debug = 1;

// Or via URL
http://localhost/app.barril.cl/?debug=1
```

---

## Common Patterns

### Form Data Collection
```javascript
function getDataForm(entity) {
  var data = {};
  $("form#" + entity + "-form :input").each(function(){
    var input = $(this);
    if(input.attr("name") != undefined) {
      data[input.attr("name")] = input.val();
      if($(this).attr('type') == 'checkbox') {
        data[input.attr("name")] = input.is(":checked");
      }
    }
  });
  return data;
}
```

### Modal Pattern
```javascript
// Show modal
$('#myModal').modal('show');

// Hide modal
$('#myModal').modal('hide');

// Handle modal events
$('#myModal').on('hidden.bs.modal', function() {
  // Cleanup
});
```

### DataTable Initialization
```javascript
$('#myTable').DataTable({
  language: {
    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
  },
  order: [[0, 'desc']],
  pageLength: 25
});
```

---

## Don'ts (Anti-Patterns to Avoid)

❌ Don't use short PHP tags: `<? ?>`
❌ Don't use global variables excessively
❌ Don't hard-delete records (use soft delete with estado='eliminado')
❌ Don't commit sensitive data (passwords, API keys)
❌ Don't use inline styles (use CSS classes)
❌ Don't skip authentication checks in AJAX endpoints
❌ Don't use tabs for indentation (use 2 spaces)
❌ Don't concatenate user input directly in SQL queries

---

**Last Updated**: 2025-01-15
**Maintained by**: Barril.cl Development Team
