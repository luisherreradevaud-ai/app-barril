# AJAX Endpoint Migration Helper

Quick reference for migrating old AJAX endpoints to the new secure pattern.

---

## Quick Migration Pattern

### Step 1: Replace Top Section

**OLD:**
```php
<?php
  if($_POST == array()) {
    die();
  }

  require_once("./../php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  if(!isset($_POST['param'])) {
    print "error";
    die();
  }
```

**NEW:**
```php
<?php

require_once("./../php/app.php");

$ajax = AjaxSecurity::init([
  'methods' => ['POST'],
  'csrf' => true,
  'auth' => true,
  'required_params' => ['param']
]);

try {
```

### Step 2: Replace Input Access

**OLD:**
```php
$id = $_POST['id'];
$nombre = $_POST['nombre'];
```

**NEW:**
```php
$id = $ajax->input('id');
$nombre = $ajax->input('nombre');
```

### Step 3: Replace Response

**OLD:**
```php
  $response["status"] = "OK";
  $response["mensaje"] = "Success";
  $response["obj"] = $obj;

  print json_encode($response, JSON_PRETTY_PRINT);
?>
```

**NEW:**
```php
  $ajax->success(['obj' => $obj], 'Success');
} catch (Exception $e) {
  error_log("Error in ajax_filename.php: " . $e->getMessage());
  $ajax->error($e->getMessage(), 'error_code', 500);
}

?>
```

---

## Common Patterns

### Pattern 1: Simple GET endpoint

```php
<?php

require_once("./../php/app.php");

$ajax = AjaxSecurity::init([
  'methods' => ['GET'],
  'csrf' => false,
  'auth' => true
]);

try {
  $id = $ajax->input('id');
  $data = MyClass::getData($id);
  $ajax->success(['data' => $data]);
} catch (Exception $e) {
  $ajax->error($e->getMessage());
}

?>
```

### Pattern 2: Save/Create endpoint

```php
<?php

require_once("./../php/app.php");

$ajax = AjaxSecurity::init([
  'methods' => ['POST'],
  'csrf' => true,
  'auth' => true,
  'input_rules' => [
    'id' => 'string',
    'nombre' => 'string',
    'email' => 'email'
  ]
]);

try {
  $obj = new MyClass($ajax->input('id'));
  $obj->setProperties($ajax->input());
  $obj->save();

  $ajax->success(['obj' => $obj], 'Guardado exitosamente');
} catch (Exception $e) {
  error_log("Error: " . $e->getMessage());
  $ajax->error($e->getMessage(), 'save_error', 500);
}

?>
```

### Pattern 3: Delete endpoint

```php
<?php

require_once("./../php/app.php");

$ajax = AjaxSecurity::init([
  'methods' => ['POST'],
  'csrf' => true,
  'auth' => true,
  'min_level' => 'Operario',
  'required_params' => ['id']
]);

try {
  $obj = new MyClass($ajax->input('id'));
  $obj->delete();

  $ajax->success(['id' => $obj->id], 'Eliminado exitosamente');
} catch (Exception $e) {
  $ajax->error($e->getMessage(), 'delete_error', 500);
}

?>
```

### Pattern 4: List/GetAll endpoint

```php
<?php

require_once("./../php/app.php");

$ajax = AjaxSecurity::init([
  'methods' => ['GET'],
  'csrf' => false,
  'auth' => true
]);

try {
  $where = $ajax->input('where', '');
  $items = MyClass::getAll($where);

  $ajax->success(['items' => $items]);
} catch (Exception $e) {
  $ajax->error($e->getMessage());
}

?>
```

### Pattern 5: Admin-only endpoint

```php
<?php

require_once("./../php/app.php");

$ajax = AjaxSecurity::init([
  'methods' => ['POST'],
  'csrf' => true,
  'auth' => true,
  'min_level' => 'Administrador'
]);

try {
  // Admin logic here

  $ajax->success(['result' => 'success']);
} catch (Exception $e) {
  $ajax->error($e->getMessage());
}

?>
```

---

## Configuration Quick Reference

```php
$ajax = AjaxSecurity::init([

  // HTTP Methods (choose one or more)
  'methods' => ['GET'],              // Read-only
  'methods' => ['POST'],             // Create/Update (default)
  'methods' => ['POST', 'PUT'],      // Create/Update both
  'methods' => ['DELETE'],           // Delete

  // CSRF Token
  'csrf' => false,                   // GET endpoints
  'csrf' => true,                    // POST/PUT/DELETE (default)

  // Authentication
  'auth' => false,                   // Public endpoint
  'auth' => true,                    // Require login (default)

  // Permission Level
  'min_level' => null,               // Any authenticated user (default)
  'min_level' => 'Operario',         // Operario and above
  'min_level' => 'Jefe de Planta',   // Managers only
  'min_level' => 'Administrador',    // Admins only

  // Rate Limiting
  'rate_limit' => true,              // Enabled (default)
  'rate_limit' => false,             // Disabled (development only)

  // Required Parameters
  'required_params' => [],           // None required (default)
  'required_params' => ['id'],       // id required
  'required_params' => ['id', 'nombre'],  // Multiple required

  // Input Sanitization
  'input_rules' => [],               // Default string sanitization
  'input_rules' => [
    'nombre' => 'string',
    'email' => 'email',
    'cantidad' => 'int',
    'precio' => 'float',
    'url' => 'url',
    'descripcion' => 'html'
  ]
]);
```

---

## Search & Replace Guide

Use your editor's find & replace:

### Replace 1: Remove old validation

**Find:**
```
if\(\$_POST == array\(\)\) \{[\s\S]*?die\(\);[\s\S]*?\}
```

**Replace:** (leave empty)

### Replace 2: Remove session check

**Find:**
```
\$usuario = new Usuario;[\s\S]*?session_start\(\);[\s\S]*?\$usuario->checkSession\([^)]*\);
```

**Replace:** (leave empty)

### Replace 3: Replace $_POST access

**Find:** `\$_POST\['([^']+)'\]`

**Replace:** `$ajax->input('$1')`

### Replace 4: Replace $_GET access

**Find:** `\$_GET\['([^']+)'\]`

**Replace:** `$ajax->input('$1')`

---

## Checklist per File

When migrating each endpoint:

- [ ] Remove `if($_POST == array())` check
- [ ] Remove manual session start/check
- [ ] Add `AjaxSecurity::init()` with config
- [ ] Add `try {` block
- [ ] Replace `$_POST['x']` with `$ajax->input('x')`
- [ ] Replace `$_GET['x']` with `$ajax->input('x')`
- [ ] Remove `$response = array()` creation
- [ ] Replace `print json_encode()` with `$ajax->success()`
- [ ] Add `} catch` block
- [ ] Add `error_log()` in catch
- [ ] Add `$ajax->error()` in catch
- [ ] Test endpoint with CSRF token
- [ ] Check security log for issues

---

## Testing Each Migrated Endpoint

### 1. Test with valid request

```javascript
$.ajax({
  url: '/ajax/ajax_yourEndpoint.php',
  method: 'POST',
  data: {
    csrf_token: csrfToken,
    // your params
  },
  success: function(r) {
    console.log('✅ Success:', r);
  },
  error: function(e) {
    console.error('❌ Error:', e);
  }
});
```

### 2. Test without CSRF (should fail)

```javascript
$.ajax({
  url: '/ajax/ajax_yourEndpoint.php',
  method: 'POST',
  data: { /* no csrf_token */ },
  error: function(e) {
    console.log('✅ Correctly blocked:', e.responseJSON);
  }
});
```

### 3. Test with invalid user level (if applicable)

Login as lower-level user and test - should get 403.

### 4. Check security log

```bash
tail -f /logs/security.log
```

---

## Common Issues & Fixes

### Issue: "CSRF token missing"

**Cause:** Frontend not sending token

**Fix:** Add to AJAX call:
```javascript
data: { csrf_token: csrfToken, ...otherData }
```

### Issue: "Required parameter missing"

**Cause:** Parameter not in request or empty

**Fix:** Check parameter name matches exactly
```php
'required_params' => ['id']  // Must match $ajax->input('id')
```

### Issue: "Insufficient permissions"

**Cause:** User level too low

**Fix:** Adjust `min_level` or check user's nivel:
```php
'min_level' => 'Operario'  // Lower requirement
```

### Issue: Input is NULL

**Cause:** Input sanitized away (failed validation)

**Fix:** Check input_rules type:
```php
'input_rules' => ['cantidad' => 'int']  // Use correct type
```

### Issue: Array input not working

**Cause:** Arrays need special handling

**Fix:** Don't specify rule for arrays, access directly:
```php
$tags = $ajax->raw('tags');  // For arrays
```

---

## Batch Migration Script (Optional)

If you want to migrate many files at once:

```bash
#!/bin/bash

# List all AJAX files not yet migrated
find ajax/ -name "ajax_*.php" -type f | while read file; do
  # Check if file uses old pattern
  if grep -q "if(\$_POST == array())" "$file"; then
    echo "TODO: Migrate $file"
  fi
done
```

---

## Priority Order

Migrate in this order:

**Priority 1 - Critical (User-facing):**
- ajax_guardarEntidad.php ✅
- ajax_eliminarEntidad.php ✅
- ajax_getConversacion.php ✅
- ajax_guardarComentarioConArchivos.php
- ajax_eliminarComentario.php

**Priority 2 - Important (Common operations):**
- ajax_guardarTarea.php
- ajax_eliminarTarea.php
- ajax_guardarTablero.php
- ajax_moverTarea.php

**Priority 3 - Standard (Regular features):**
- All other ajax_guardar*.php files
- All other ajax_eliminar*.php files
- All other ajax_get*.php files

**Priority 4 - Low (Rarely used):**
- Specialized/admin endpoints
- Reporting endpoints
- Export/import endpoints

---

## Need Help?

1. Check `AJAX_SECURITY_EXAMPLES.php` for 10 detailed examples
2. Review `SECURITY_IMPLEMENTATION_GUIDE.md` for full documentation
3. Look at already-migrated files for reference:
   - ajax_guardarEntidad.php
   - ajax_eliminarEntidad.php
   - ajax_getConversacion.php

---

**Happy migrating! 🚀**
