# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Barril.cl** is a comprehensive PHP-based brewery management system (ERP) for craft beer production and distribution, built for Cerveza Cocholgue (Chilean brewery). XAMPP-based development environment.

## Architecture

### Core Pattern: Active Record ORM
- All entity classes extend `Base.php` (abstract class with CRUD operations)
- Auto-loading: Classes in `/php/classes/` are auto-loaded from `/php/app.php`
- Database: MySQL (barrcl_cocholg), utf8mb4 charset
- Timezone: America/Santiago

### Class Convention
- **Whenever you see an object referenced, the corresponding class will be in `/php/classes/`**
- Example: `$batch = new Batch($id)` → class in `/php/classes/Batch.php`
- All classes inherit from `Base.php` which provides:
  - `save()` - Insert/update entity
  - `delete()` - Soft delete (sets estado='eliminado')
  - `getAll()` - Fetch all records
  - `tableFields()` - Auto-inspect table schema
  - `setProperties($array)` - Bulk property setter
  - Many-to-many relationship management
  - Media attachment support

### Directory Structure
```
/php/
  app.php              # Bootstrap, config, globals, utility functions
  classes/             # 97 entity classes (Base.php + 96 models)
/templates/            # 174 view templates (PHP/HTML)
/ajax/                 # 88 AJAX endpoints
/js/                   # JavaScript (jQuery, jQuery UI, DataTables, CKEditor)
/css/                  # Stylesheets (Bootstrap 5)
/media/                # Uploaded files
/vendor_php/           # Dependencies (Transbank, LibreDTE, Guzzle)
index.php              # Main entry point (layout + routing)
login.php              # Authentication
```

### Global Variables (defined in `/php/app.php`)
```php
$GLOBALS['base_dir']      # Absolute path to app root
$GLOBALS['usuario']       # Current logged-in user object
$GLOBALS['mysqli']        # Database connection
$passwordHash             # "mister420" (crypt salt)
$estados                  # Order states array
$tipos_barril             # Barrel sizes: 20L, 30L, 50L
$tipos_barril_cerveza     # Beer types: Ambar, IPA, PaleAle, Calafate, BIPA
$niveles_usuario          # User roles (8 levels)
$tipos_de_gasto           # Expense categories
$insumos_unidades_de_medida  # Ingredient units
$secciones_clasificacion  # Section categories
```

## Development Commands

### Local Development
```bash
# XAMPP is installed, so PHP server runs via Apache
# Access at: http://localhost/app.barril.cl/

# Database access
mysql -u barrcl_cocholg -p barrcl_cocholg
# Password in /php/app.php

# View PHP errors (debug mode enabled in app.php)
tail -f /Applications/XAMPP/xamppfiles/logs/error_log
```

### Running Database Migrations
```bash
# Import schema changes
mysql -u barrcl_cocholg -p barrcl_cocholg < sql/your_migration.sql

# Database backups exist in /context/ and /sql/
```

### Testing AJAX Endpoints
```bash
# Test locally (pre-approved in CLAUDE.md user tools)
curl -s "http://localhost/ajax/ajax_getConversacion.php?nombre_vista=test&id_entidad=test-123"
```

## Common Tasks

### Creating a New Entity Class
1. Create `/php/classes/YourEntity.php`:
```php
<?php
class YourEntity extends Base {
  public $id = "";
  public $nombre = "";
  public $table_name = "your_table";
  public $table_fields = array();

  function __construct($id = null) {
    $this->tableFields();
    if($id) {
      $this->id = $id;
      $this->getFromDB();
    }
  }
}
```

2. Update `/php/app.php` `createObjFromTableName()` function if needed for generic operations

### Creating a New Template
1. Create `/templates/your-template.php`
2. Use `incluir_template("your-template")` or `switch_templates("your-template")`
3. Template has access to `$GLOBALS['usuario']` and all globals

### Creating an AJAX Endpoint
1. Create `/ajax/ajax_yourAction.php`:
```php
<?php
require_once("../php/app.php");
// Your logic here
// Return JSON:
header('Content-Type: application/json');
echo json_encode(['status' => 'OK', 'data' => $result]);
```

2. Call from JavaScript:
```javascript
$.ajax({
  url: '/ajax/ajax_yourAction.php',
  method: 'POST',
  data: { param: value },
  success: function(response) { }
});
```

### Including Templates/Components
```php
<?php
  // Relative include (from index.php context)
  incluir_template("template-name");

  // Absolute include (from anywhere)
  include($GLOBALS['base_dir']."/templates/components/component-name.php");
?>
```

## Key Systems

### Authentication Flow
- Session validation: `Usuario::checkSession($_SESSION)` on every page
- Authorization: `Usuario::checkAutorizacion($section)` checks permissions
- Roles: Administrador, Jefe de Planta, Cliente, Jefe de Cocina, Operario, Repartidor, Vendedor, Visita

### Template Routing
- Main router: `switch_templates($section)` in `index.php`
- URL pattern: `?s=template-name` (e.g., `?s=batches`)
- Permission-checked before rendering

### Kanban Task System
- Location: `/tareas-php/` (see `/tareas-php/README.md`)
- Classes: `KanbanTablero`, `KanbanColumna`, `KanbanTarea`, etc.
- Drag & drop with jQuery UI Sortable
- Integration: Set `entityId = 'entity_type_id'`, include template
- Already integrated in main app at `/templates/tablero-kanban.php`

### Internal Conversations System
- Location: `/php/classes/ConversacionInterna*.php` (see `/CONVERSACION_INTERNA_README.md`)
- Features: Comments, file attachments, @mentions, likes
- Integration: Include `/templates/components/conversacion-interna.php`
- Variables: `$conversation_view_name`, `$conversation_entity_id`
- Auto-refresh every 60 seconds

### Media/File Uploads
- Storage: `/media/` directory with subdirectories per entity type
- Base class methods: `attachMedia()`, `getMedia()`
- Common pattern:
  - Upload file to `/media/entity_type/`
  - Create `Media` object with file path
  - Link via many-to-many table (e.g., `media_batches`)

## Chilean Market Integrations

### Transbank (Payment Gateway)
- SDK: `/vendor_php/transbank/`
- Configuration in `/php/app.php`
- Handler: `/php/Pago.php`
- WebPay Plus integration for credit/debit cards

### LibreDTE (Electronic Invoicing)
- SDK: `/vendor_php/libredte-lib-master/`
- Chilean SII (tax authority) compliance
- Document types: Factura, Boleta, Nota de Crédito

## Important Conventions

### Database
- IDs are VARCHAR(36) (UUID-like strings generated with `uniqid()`)
- Date format: YYYY-MM-DD (MySQL DATE)
- DateTime format: YYYY-MM-DD HH:MM:SS
- Soft deletes: `estado = 'eliminado'` (not hard DELETE)
- Boolean as strings: "1" or "0" in VARCHAR fields

### Naming
- Tables: lowercase, plural (e.g., `batches`, `productos`)
- Classes: PascalCase, singular (e.g., `Batch`, `Producto`)
- Files: kebab-case (e.g., `detalle-batch.php`)
- AJAX files: `ajax_actionName.php`

### Code Style
- PHP tags: `<?php` (full, not short tags)
- String interpolation: Use concatenation or double quotes
- SQL: Direct mysqli queries (no PDO)
- Use `addslashes()` for string escaping (already in `Base::update()`)
- HTML: Mixed PHP/HTML in templates (not pure PHP templating)

## Utility Functions (in `/php/app.php`)

```php
incluir_template($name)           # Include template by name
switch_templates($name)           # Include with auth check
validaIdExists($array, $key)      # Check if key exists and not empty
convertToChileanDate($date)       # YYYY-MM-DD → DD-MM-YYYY
convertToMySQLDate($date)         # DD-MM-YYYY → YYYY-MM-DD
convertRutToInt($rut)             # Remove dots/dashes from RUT
calcularDV($rut)                  # Calculate Chilean RUT verification digit
```

## User Roles & Permissions

**8-tier hierarchy:**
1. **Administrador** - Full system access
2. **Jefe de Planta** - Production management
3. **Cliente** - Customer portal (orders, invoices, tracking)
4. **Jefe de Cocina** - Recipe/batch execution
5. **Operario** - Data entry, inventory movements
6. **Repartidor** - Delivery management
7. **Vendedor** - Sales, customer management
8. **Visita** - Limited read-only access

## Data Model Key Entities

**Production:**
- `Batch` - Beer production batch
- `Receta` - Recipe
- `Insumo` - Ingredient/supply
- `Barril` - Keg/barrel tracking
- `Producto` - Finished product

**Sales/Distribution:**
- `Cliente` - Customer
- `Pedido` - Order
- `Entrega` - Delivery
- `Despacho` - Dispatch
- `CentralDeDespacho` - Dispatch center

**Inventory:**
- `InventarioProducto` - Product inventory
- `InventarioBarril` - Barrel inventory
- `CompraDeInsumo` - Ingredient purchase

**Financial:**
- `Gasto` - Expense
- `DocumentoPago` - Payment document
- `Factura` - Invoice

**System:**
- `Usuario` - User
- `Configuracion` - System configuration
- `Notificacion` - Notification
- `RegistroAsistencia` - Attendance log

## Frontend Stack

- **Bootstrap 5** - Main UI framework
- **jQuery 1.12.4** - DOM manipulation
- **jQuery UI 1.12.1** - Drag & drop (Sortable)
- **DataTables 2.1.4** - Table management
- **CKEditor** - Rich text editing
- **Bootstrap Icons** - Icon system
- **Dark/Light theme** - Toggled via `#darkModeSwitch`, stored in localStorage

## Security Notes

- Password hashing: `crypt($password, $passwordHash)` with salt "mister420"
- Session-based authentication (no JWT)
- CSRF protection: Not implemented (consider adding for production)
- SQL injection: Mitigated via `addslashes()` in Base class
- XSS: Some use of `htmlspecialchars()`, but not comprehensive
- File uploads: Basic validation in place, verify before production

## Testing

No automated test suite. Manual testing via:
1. Browser access to `http://localhost/app.barril.cl/`
2. AJAX testing with browser DevTools Network tab
3. Direct database queries for data validation

## Chilean Context

- Currency: Chilean Peso (CLP)
- RUT: Chilean tax ID (format: 12.345.678-9)
- SII: Chilean IRS (Servicio de Impuestos Internos)
- DTE: Electronic tax documents
- Regions: Chilean regions for shipping addresses

## Additional Documentation

- Kanban system: `/tareas-php/README.md`
- Internal conversations: `/CONVERSACION_INTERNA_README.md`
- Agent/AI context: `/agent.md` (comprehensive technical documentation)
- Database schema: `/context/barrcl_cocholg.sql`

---

**Important Instructions:**
- Whenever you see an object, add the class from `/php/classes/` to your context
- Do what has been asked; nothing more, nothing less
- NEVER create files unless absolutely necessary
- ALWAYS prefer editing existing files to creating new ones
- NEVER proactively create documentation files unless explicitly requested
