<?php
/**
 * AJAX SECURITY EXAMPLES
 * This file contains examples of how to secure different types of AJAX endpoints
 * DO NOT EXECUTE THIS FILE - IT'S FOR REFERENCE ONLY
 */

// =============================================================================
// EXAMPLE 1: Basic POST endpoint with full security
// =============================================================================
function example_basic_post() {
  require_once("./../php/app.php");

  $ajax = AjaxSecurity::init([
    'methods' => ['POST'],
    'csrf' => true,
    'auth' => true,
    'rate_limit' => true
  ]);

  try {
    $data = $ajax->input('data');
    // Process data...
    $ajax->success(['result' => 'success']);
  } catch (Exception $e) {
    $ajax->error($e->getMessage());
  }
}

// =============================================================================
// EXAMPLE 2: GET endpoint (no CSRF needed)
// =============================================================================
function example_get_endpoint() {
  require_once("./../php/app.php");

  $ajax = AjaxSecurity::init([
    'methods' => ['GET'],
    'csrf' => false, // GET doesn't need CSRF
    'auth' => true,
    'rate_limit' => true,
    'required_params' => ['id']
  ]);

  try {
    $id = $ajax->input('id');
    // Fetch data...
    $ajax->success(['data' => $result]);
  } catch (Exception $e) {
    $ajax->error($e->getMessage());
  }
}

// =============================================================================
// EXAMPLE 3: Endpoint with required parameters and validation rules
// =============================================================================
function example_with_validation() {
  require_once("./../php/app.php");

  $ajax = AjaxSecurity::init([
    'methods' => ['POST'],
    'csrf' => true,
    'auth' => true,
    'required_params' => ['email', 'nombre', 'cantidad'],
    'input_rules' => [
      'email' => 'email',
      'nombre' => 'string',
      'cantidad' => 'int',
      'precio' => 'float',
      'descripcion' => 'html'
    ]
  ]);

  try {
    $email = $ajax->input('email');        // Sanitized as email
    $nombre = $ajax->input('nombre');      // Sanitized as string
    $cantidad = $ajax->input('cantidad');  // Sanitized as int
    $precio = $ajax->input('precio', 0);   // Optional with default

    // Process validated data...
    $ajax->success(['saved' => true]);
  } catch (Exception $e) {
    $ajax->error($e->getMessage());
  }
}

// =============================================================================
// EXAMPLE 4: Admin-only endpoint with permission level
// =============================================================================
function example_admin_only() {
  require_once("./../php/app.php");

  $ajax = AjaxSecurity::init([
    'methods' => ['POST'],
    'csrf' => true,
    'auth' => true,
    'min_level' => 'Administrador', // Only administrators can access
    'rate_limit' => true
  ]);

  try {
    // Only administrators reach here
    $ajax->success(['admin_action' => 'completed']);
  } catch (Exception $e) {
    $ajax->error($e->getMessage());
  }
}

// =============================================================================
// EXAMPLE 5: Different permission levels
// =============================================================================
function example_permission_levels() {
  require_once("./../php/app.php");

  // Options for min_level:
  // - 'Visita'
  // - 'Cliente'
  // - 'Repartidor'
  // - 'Vendedor'
  // - 'Operario'
  // - 'Jefe de Cocina'
  // - 'Jefe de Planta'
  // - 'Administrador'

  $ajax = AjaxSecurity::init([
    'methods' => ['POST'],
    'min_level' => 'Operario' // Operario and above can access
  ]);

  try {
    $ajax->success(['access_granted' => true]);
  } catch (Exception $e) {
    $ajax->error($e->getMessage());
  }
}

// =============================================================================
// EXAMPLE 6: Public endpoint (no auth required)
// =============================================================================
function example_public_endpoint() {
  require_once("./../php/app.php");

  $ajax = AjaxSecurity::init([
    'methods' => ['POST'],
    'csrf' => true,
    'auth' => false, // No authentication required
    'rate_limit' => true // Still rate limit for DDoS protection
  ]);

  try {
    // Public endpoint logic...
    $ajax->success(['public' => 'data']);
  } catch (Exception $e) {
    $ajax->error($e->getMessage());
  }
}

// =============================================================================
// EXAMPLE 7: Using the handle() wrapper for cleaner code
// =============================================================================
function example_with_handle() {
  require_once("./../php/app.php");

  AjaxSecurity::handle([
    'methods' => ['POST'],
    'csrf' => true,
    'auth' => true
  ], function($ajax) {
    // Your logic here
    $data = $ajax->input('data');

    // Process...

    $ajax->success(['result' => $data]);
  });
}

// =============================================================================
// EXAMPLE 8: Accessing user information
// =============================================================================
function example_user_info() {
  require_once("./../php/app.php");

  $ajax = AjaxSecurity::init(['auth' => true]);

  try {
    // Get current user
    $user = $ajax->user();

    $response = [
      'user_id' => $user->id,
      'user_name' => $user->nombre,
      'user_level' => $user->nivel
    ];

    $ajax->success($response);
  } catch (Exception $e) {
    $ajax->error($e->getMessage());
  }
}

// =============================================================================
// EXAMPLE 9: Multiple input types
// =============================================================================
function example_multiple_types() {
  require_once("./../php/app.php");

  $ajax = AjaxSecurity::init([
    'methods' => ['POST'],
    'input_rules' => [
      'nombre' => 'string',      // XSS-safe string
      'email' => 'email',        // Valid email
      'url' => 'url',           // Valid URL
      'cantidad' => 'int',       // Integer
      'precio' => 'float',       // Float
      'descripcion' => 'html',   // HTML (escaped)
      'query' => 'sql'          // SQL-safe (addslashes)
    ]
  ]);

  try {
    $all_inputs = $ajax->input(); // Get all validated inputs as array

    $ajax->success($all_inputs);
  } catch (Exception $e) {
    $ajax->error($e->getMessage());
  }
}

// =============================================================================
// EXAMPLE 10: Custom error handling
// =============================================================================
function example_custom_errors() {
  require_once("./../php/app.php");

  $ajax = AjaxSecurity::init();

  try {
    $id = $ajax->input('id');

    $obj = MyClass::find($id);

    if (!$obj) {
      // Send custom error with HTTP 404
      $ajax->error('Objeto no encontrado', 'not_found', 404);
    }

    $ajax->success($obj);
  } catch (Exception $e) {
    // Send custom error with HTTP 500
    $ajax->error('Error interno: ' . $e->getMessage(), 'internal_error', 500);
  }
}

// =============================================================================
// HOW TO UPDATE EXISTING ENDPOINTS
// =============================================================================

/*

BEFORE (Old insecure code):
------------------------
<?php
  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if(!isset($_POST['id'])) {
    print "error";
    die();
  }

  $id = $_POST['id'];
  $obj = new MyClass($id);
  $obj->setProperties($_POST);
  $obj->save();

  $response["status"] = "OK";
  print json_encode($response);
?>


AFTER (New secure code):
------------------------
<?php

require_once("./../php/app.php");

$ajax = AjaxSecurity::init([
  'methods' => ['POST'],
  'csrf' => true,
  'auth' => true,
  'required_params' => ['id'],
  'input_rules' => [
    'id' => 'string'
  ]
]);

try {
  $id = $ajax->input('id');
  $obj = new MyClass($id);
  $obj->setProperties($ajax->input());
  $obj->save();

  $ajax->success(['obj' => $obj], 'Guardado exitosamente');
} catch (Exception $e) {
  $ajax->error($e->getMessage());
}

?>

*/

// =============================================================================
// FRONTEND CHANGES REQUIRED
// =============================================================================

/*

You need to include CSRF token in AJAX requests:

JAVASCRIPT EXAMPLE:
------------------

// 1. Add CSRF token to form data
$.ajax({
  url: '/ajax/ajax_myEndpoint.php',
  method: 'POST',
  data: {
    csrf_token: '<?php echo Security::getCSRFToken(); ?>',
    id: myId,
    nombre: myName
  },
  success: function(response) {
    if(response.status === 'OK') {
      console.log(response.data);
    }
  }
});

// 2. Or set it as a header
$.ajax({
  url: '/ajax/ajax_myEndpoint.php',
  method: 'POST',
  headers: {
    'X-CSRF-TOKEN': '<?php echo Security::getCSRFToken(); ?>'
  },
  data: {
    id: myId,
    nombre: myName
  }
});

// 3. Set globally for all AJAX requests
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': '<?php echo Security::getCSRFToken(); ?>'
  }
});

// 4. Add to form
<form id="myForm">
  <input type="hidden" name="csrf_token" value="<?php echo Security::getCSRFToken(); ?>">
  <!-- other fields -->
</form>

*/

?>
