# Barril.cl - Brewery Management System Documentation

## Application Overview

**Barril.cl** is a comprehensive PHP-based brewery management system designed specifically for craft beer production and distribution. Built for Cerveza Cocholgue, a Chilean craft brewery, it serves as an end-to-end ERP solution.

### Primary Purpose
- Complete brewery production management (batch brewing, fermentation tracking)
- Inventory control (ingredients, products, barrels, cases)
- Sales and distribution (orders, deliveries, dispatches)
- Customer relationship management
- Financial tracking (expenses, payments, Chilean electronic invoicing)
- Project and task management
- Staff attendance and user management

### Industry
Craft Beer/Brewery Manufacturing and Distribution (Chilean market)

---

## Technical Stack

### Backend
- **PHP** - Object-Oriented Architecture
- **MySQL/MySQLi** - Database (charset: utf8mb4)
- **Custom MVC-like architecture** with Active Record pattern
- **Session-based authentication**
- **Timezone:** America/Santiago

### Database
```
Host: localhost
Database: barrcl_cocholg
Username: barrcl_cocholg
Credentials: Stored in /php/app.php
```

### Frontend
- **Bootstrap 5** - UI framework
- **jQuery 1.12.4** + jQuery UI 1.12.1
- **DataTables 2.1.4** - Table management
- **CKEditor** - Rich text editing
- **Lucide Icons** - Icon system
- **Dark/Light theme** with localStorage persistence

### Third-Party Integrations

#### Payment Processing
- **Transbank SDK** - Chilean payment gateway (WebPay Plus)
- Location: `/vendor_php/transbank/`
- Merchant Code: 597047933778
- Supports credit/debit card payments

#### Electronic Invoicing
- **LibreDTE** - Chilean Electronic Tax Document system (DTE)
- Location: `/vendor_php/libredte-lib-master/`
- Required for Chilean tax compliance (SII)
- Generates legally compliant invoices

#### Other
- **Guzzle 6.5.5** - HTTP client
- **Botpress** - AI chatbot integration
- **Native PHP mail()** - Email system (contacto@cervezacocholgue.cl)

---

## Architecture

### Directory Structure

```
app.barril.cl/
├── php/                          # Core application logic
│   ├── app.php                   # Main configuration & bootstrap
│   ├── login.php                 # Authentication handler
│   ├── logout.php                # Session termination
│   ├── Pago.php                  # Payment processing
│   └── classes/                  # 70+ OOP class definitions
│       ├── Base.php              # Abstract base for all models
│       ├── Usuario.php           # User management
│       ├── Cliente.php           # Customer management
│       ├── Batch.php             # Brewing batch tracking
│       ├── Barril.php            # Barrel/keg management
│       ├── Producto.php          # Product catalog
│       ├── Insumo.php            # Ingredients/supplies
│       ├── Receta.php            # Beer recipes
│       ├── Pedido.php            # Orders
│       ├── Entrega.php           # Deliveries
│       ├── Despacho.php          # Dispatches
│       ├── Gasto.php             # Expenses
│       └── [65+ other classes]
├── templates/                    # 160+ view templates
│   ├── inicio.php                # Dashboard
│   ├── batches.php               # Batch management
│   ├── barriles.php              # Barrel tracking
│   ├── inventario-de-productos.php
│   ├── pedidos.php               # Order management
│   └── [155+ other templates]
├── ajax/                         # 60+ AJAX endpoints
│   ├── ajax_guardarEntidad.php   # Generic entity save
│   ├── ajax_eliminarEntidad.php  # Generic entity delete
│   ├── ajax_getNotificaciones.php
│   └── [55+ other endpoints]
├── js/                           # JavaScript
│   ├── app.js                    # Main application JS
│   └── ckeditor/                 # Rich text editor
├── css/
│   └── app.css                   # Main stylesheet
├── img/                          # Images
├── media/                        # Uploaded media files
├── vendor_php/                   # Dependencies
│   ├── transbank/
│   ├── libredte-lib-master/
│   └── guzzlehttp/
├── cron4582h/                    # Scheduled tasks
├── clientes/                     # Customer files
├── index.php                     # Main entry point
└── login.php                     # Login page
```

### Core Architecture Files

#### `/php/app.php` - Application Bootstrap
- Configuration constants (database, Transbank, password hash)
- PSR-0 autoloader for class files
- Global arrays: user roles, product states, barrel types, beer types, expense categories, measurement units
- Utility functions: date conversion, validation, sanitization
- Template routing via `switch_templates()` function

#### `/php/classes/Base.php` - ORM Foundation
Abstract base class providing Active Record pattern:
- CRUD operations (insert, update, delete, getAll)
- Dynamic table field inspection
- Property setters/getters with `__get()` and `__set()`
- Many-to-many relationship management
- Media attachment support
- Automatic data sanitization

#### `/index.php` - Main Entry Point
- Session validation
- User authentication check
- Main application layout (sidebar, navbar, content area, footer)
- Dark/light theme toggle
- Real-time notification polling (10-second intervals)
- Global search autocomplete
- Attendance registration modal

---

## Authentication & Authorization

### Login Flow
1. User submits email/password via `/login.php`
2. Password hashed with `crypt()` using salt "mister420"
3. Validated against `usuarios` table
4. Session created with user data in `$_SESSION`
5. Redirect to dashboard or return URL

### Session Management
- Validated on every page load via `Usuario::checkSession()`
- Checks: email, password hash, not blocked, invitation completed
- Falls back to "Invitado" (guest) if invalid
- Stores: user ID, name, email, level, active customer ID

### Authorization System
**Three-tier hierarchy:**
```
Menus (grupos) → Secciones (pages/templates) → Permisos (permissions)
```

**Permission check on every page:**
```php
Usuario::checkAutorizacion($section)
```
- Looks up section by template file
- Gets user level permissions
- Returns true/false for access

### Breadcrumb Navigation
- `PrevLink` class tracks navigation history
- "Volver" (Back) button functionality
- URL and section name stored

---

## User Roles & Capabilities

### 1. Administrador (Administrator)
- Full system access
- User and permission management
- Financial management and reports
- System configuration
- All CRUD operations

### 2. Jefe de Planta (Plant Manager)
- Production management
- Recipe and batch oversight
- Inventory management
- Asset/equipment management
- Maintenance scheduling
- Production reports

### 3. Jefe de Cocina (Kitchen/Brew Manager)
- Recipe creation/editing
- Batch execution
- Ingredient usage tracking
- Fermentation monitoring
- Quality measurements (pH, gravity, temperature)

### 4. Operario (Operator)
- Batch data entry (temperatures, times, measurements)
- Inventory movements
- Equipment operation logs
- Basic reporting

### 5. Vendedor (Sales Representative)
- Customer management
- Order creation
- Delivery coordination
- Sales reports
- Sales targets tracking
- Customer-specific pricing

### 6. Repartidor (Delivery Driver)
- Dispatch management
- Delivery confirmation and status updates
- Route viewing
- Barrel pickup/delivery tracking

### 7. Cliente (Customer)
- Customer portal access
- View/create orders
- Track deliveries
- View barrel locations (at their site)
- Invoice viewing
- Payment processing

### 8. Visita (Visitor/Guest)
- Limited read-only access
- Dashboard viewing
- Basic reports

---

## Core Features & Modules

### A. Production Management (Brewing)

#### Complete Batch Lifecycle (`Batch` class)

**10-Stage Brewing Process:**

1. **Batch Creation**
   - Batch number/name
   - Recipe selection
   - Assigned brewer
   - Target volume (liters)
   - Automatic ingredient deduction from inventory

2. **Maceración (Mashing)**
   - Start/end time
   - Temperature monitoring
   - pH levels
   - Volume tracking

3. **Lavado de Granos (Sparging)**
   - Must volume
   - Density (OG - Original Gravity)
   - Time tracking

4. **Cocción (Boiling)**
   - Initial/final pH
   - Recirculation data
   - Gas consumption

5. **Lupulización (Hop Additions)**
   - Multiple hop schedules (`BatchLupulizacion`)
   - Type, quantity, and timing

6. **Enfriado (Cooling)**
   - Temperature stages (`BatchEnfriado`)
   - pH and density measurements

7. **Inoculación (Yeast Pitching)**
   - Pitching temperature
   - Yeast strain

8. **Fermentación (Fermentation)**
   - Vessel assignment (`BatchActivo`)
   - Temperature control
   - pH and density tracking (FG - Final Gravity)
   - Duration monitoring

9. **Traspasos (Transfers)**
   - Tank-to-tank transfers (`BatchTraspaso`)
   - Volume tracking
   - Multiple transfers supported

10. **Maduración & Finalización**
    - Conditioning parameters
    - Final product allocation (barrels/cases)
    - Batch closure

#### Recipes (`Receta` class)
- Recipe name and code
- Beer style classification
- Target volume
- Ingredient list with quantities (`RecetaInsumo`)
- Multi-stage ingredient additions

### B. Inventory Management

#### Insumos (Ingredients) (`Insumo` class)
- Ingredient catalog by type (`TipoDeInsumo`)
- **Measurement units:** kg, gr, L, ml, unidades
- **Stock locations:**
  - Bodega (warehouse)
  - Despacho (staging area)
- Purchase tracking (`CompraDeInsumo`)
- Automatic deduction on batch creation
- Low stock notifications

#### Productos (Products) (`Producto` class)
- Product catalog with barcodes
- Product types: Barril, Caja
- Classification system
- Recipe linkage
- Composite products (`ProductoItem`)
- Customer-specific pricing (`ClienteProductoPrecio`)

#### Barriles (Barrels/Kegs) (`Barril` class)

**Comprehensive keg tracking:**

**Types:**
- 20L, 30L, 50L kegs
- CO2 tanks (separate inventory)

**Beer Types:**
- Ambar
- IPA
- Pale Ale
- Calafate
- BIPA (Black IPA)

**States:**
- En planta (at brewery)
- En sala de frio (cold storage)
- En terreno (at customer location)
- Perdido (lost)

**Features:**
- Unique barrel codes
- Batch assignment tracking
- Customer assignment
- Location tracking
- State history (`BarrilEstado` - timestamps for all changes)
- Replacement tracking (`BarrilReemplazo`)
- Volume tracking

#### Cajas (Cases) (`Caja` class)
**Types:** Unidad, 6-pack, 12-pack, 24-pack, Tripack
- Batch association (`BatchCaja`)
- Mixed case support
- Beer type classification

### C. Sales & Distribution

#### Pedidos (Orders) (`Pedido` class)
- Customer assignment
- Product line items (`PedidoProducto`)
- Order status tracking
- Creation timestamps

#### Entregas (Deliveries) (`Entrega` class)
**Delivery management:**
- Assigned driver
- Customer information
- **States:**
  - Venta y entrega en tienda
  - Pendiente retiro en tienda
  - Pendiente despacho
  - Retirado en tienda
  - Despachado
- Invoice number (factura)
- Payment tracking (paid/pending)
- Due date and payment amount
- Product details (`EntregaProducto`)
- Linked to dispatch

#### Despachos (Dispatches) (`Despacho` class)
- Route planning
- Driver assignment
- Multiple deliveries per dispatch
- Dispatch products (`DespachoProducto`)
- Barrel state updates (En planta → En terreno)

#### Customer Portal
- Customer-specific dashboard
- Order history and creation
- Barrel tracking (at customer location)
- Delivery scheduling
- Invoice viewing
- Payment processing

### D. Financial Management

#### Gastos (Expenses) (`Gasto` class)

**Categories:**
- Agua, Arriendo, Caja Chica, Combustible
- Envios, Gas, Inversion, Luz
- RRHH Marketing, RRHH Tecnicos
- Sueldos, Insumos (linked to purchases)

**Features:**
- Approval workflow
- Due date tracking
- Receipt attachment (`id_media_header`)
- Status tracking (pending, approved)
- Comments/notes

#### Gastos Fijos (Fixed Expenses)
- Monthly recurring expenses (`GastoFijoMes`)
- Annual view and planning
- Business line allocation (`LineaDeNegocio`)

#### Pagos (Payments) (`Pago` class)
**Payment processing:**
- Transbank integration
- Methods: card, cash, transfer
- Transaction tracking (buy_order, session_id, token)
- Multi-delivery payment support
- Invoice association

#### Documentos (Invoices) (`Documento` class)
- Electronic tax documents (DTE)
- Approval workflow
- LibreDTE integration
- Folio (invoice number) tracking
- Chilean tax compliance

### E. Asset & Maintenance

#### Activos (Assets) (`Activo` class)
- Equipment registry
- **Fermentation vessels:**
  - Capacity (liters)
  - Current batch assignment
  - Temperature control
- Maintenance tracking (`Mantencion`)
- Asset detail pages

#### Mantenciones (Maintenance)
- Scheduled maintenance
- Maintenance history
- Pending alerts

### F. Project & Task Management

#### Proyectos (Projects) (`Proyecto` class)
- Project tracking by classification
- Start/end dates
- Status tracking
- Revenue tracking (`ProyectoIngreso`)
- Product allocation

#### Tareas (Tasks) (`Tarea` class)
**Task system:**
- Assignment (sender/receiver)
- Email integration
- Importance levels
- **States:** Pendiente, En progreso, Completada, Archivada
- Deadline tracking
- Comment threads (`TareaComentario`)
- Email notifications
- Resend functionality

### G. Notification System

#### Real-time notifications (`Notificacion` class)
- **Polling frequency:** Every 10 seconds
- **Triggers:**
  - New batches
  - Low stock alerts
  - New tasks
  - New expenses
  - Delivery updates
- User-specific notifications
- Read/unread tracking
- Browser title updates with unread count
- Configurable notification types per user level

### H. Reporting & Analytics

#### Reportes Diarios (Daily Reports) (`ReporteDiario` class)
- Daily production reports
- JSON-based data storage
- Discrepancy tracking
- Approval workflow

#### Available Reports:
- **Production:** batches completed, volumes, ingredient consumption
- **Sales:** by customer, product, salesperson, vs. targets
- **Inventory:** stock levels, barrel locations, low stock alerts
- **Financial:** expenses by category/date, fixed vs. variable costs, accounts receivable

#### Export Capabilities:
- CSV/Excel export (DataTables)
- Print-friendly views
- PDF invoices (LibreDTE)

### I. Additional Features

#### Brewing Calculators
- **ABV Calculator** - Alcohol by Volume
- **Alcohol Dilution** - Water needed to reduce ABV
- **Mash Calculator** - Water volumes for mashing
- **Water Profile** - Mineral additions

#### Attendance Tracking (`RegistroAsistencia`)
- Employee entry/exit times
- Modal popup on login (if enabled)
- Daily reports

#### Suggestion System
- User feedback collection
- Admin review panel
- Status tracking

#### Multi-Customer Support
- Customers can belong to multiple users
- Active customer switching in navbar
- Data filtered by active customer

---

## Key Business Workflows

### Batch Production Workflow

```
CREATE BATCH (recipe, volume)
   → Ingredients automatically deducted
   ↓
MASHING DATA (time, temp, pH, volume)
   ↓
SPARGING (must volume, gravity)
   ↓
BOILING (pH, recirculation, gas)
   ↓
HOP SCHEDULE (multiple additions)
   ↓
COOLING (temp stages, pH, gravity)
   ↓
YEAST PITCHING (temp)
   ↓
FERMENTATION (vessel assignment, monitoring)
   ↓
TRANSFERS (optional vessel-to-vessel)
   ↓
MATURATION (conditioning)
   ↓
PACKAGING (assign to barrels/cases)
   ↓
FINALIZE BATCH
```

### Order-to-Delivery Workflow

```
CUSTOMER CREATES PEDIDO (Order)
   → Select products, quantities
   ↓
ORDER CONFIRMATION
   → Admin/Vendedor reviews, stock check
   ↓
PREPARE ENTREGA (Delivery)
   → Select barrels, generate invoice, set type
   ↓
CREATE DESPACHO (Dispatch)
   → Group deliveries, assign driver, plan route
   ↓
DISPATCH IN PROGRESS
   → Update barrel states (En planta → En terreno)
   → Driver marks complete
   ↓
PAYMENT PROCESSING
   → Customer pays (Transbank/cash/transfer)
   → Mark entrega as paid
   ↓
BARREL TRACKING
   → Barrels now at customer
   → Track returns/replacements
```

### Expense Approval Workflow

```
USER CREATES GASTO
   → Category, amount, receipt, due date
   ↓
SUBMIT FOR APPROVAL
   → Notification to approver
   ↓
ADMIN/MANAGER REVIEWS
   → View details, check receipt
   ↓
APPROVAL
   → Status updated, included in reports
```

---

## Database Structure

### Core Tables (70+ tables inferred from classes)

**Users & Auth:**
- usuarios, usuarios_niveles, usuarios_clientes
- permisos, secciones, menus
- return_urls, prev_links
- registro_asistencia

**Production:**
- batches, batches_insumos, batches_lupulizaciones
- batches_enfriados, batches_traspasos, batches_activos, batches_cajas
- recetas, recetas_insumos

**Inventory:**
- insumos, tipos_de_insumos
- compras_de_insumos, compras_de_insumos_insumos
- productos, productos_items
- barriles, barriles_estados, barriles_reemplazos
- cajas, activos

**Sales & Distribution:**
- clientes, clientes_productos_precios
- pedidos, pedidos_productos
- entregas, entregas_productos
- despachos, despachos_productos

**Financial:**
- gastos, tipos_de_gastos, gastos_fijos, gastos_fijos_mes
- lineas_de_negocio, gastos_lineas_de_negocio
- pagos, dte, documentos

**System:**
- notificaciones, tipos_de_notificaciones
- tareas, tareas_comentarios
- proyectos, proyectos_ingresos, proyectos_productos
- reportes_diarios, sugerencias
- configuraciones, media, historial

---

## API Endpoints (AJAX)

### Generic Operations
- **POST** `/ajax/ajax_guardarEntidad.php` - Save any entity
- **POST** `/ajax/ajax_eliminarEntidad.php` - Delete any entity

### Specific Operations
- **POST** `/ajax/ajax_guardarPedido.php` - Save order
- **POST** `/ajax/ajax_guardarDespacho.php` - Save dispatch
- **POST** `/ajax/ajax_guardarEntrega.php` - Save delivery
- **GET** `/ajax/ajax_getNotificaciones.php` - Fetch notifications
- **POST** `/ajax/ajax_setNotificado.php` - Mark notifications read
- **GET** `/ajax/ajax_getSearch.php` - Global search
- **POST** `/ajax/ajax_agregarLupulizacion.php` - Add hop schedule
- **POST** `/ajax/ajax_agregarTraspasos.php` - Add transfer
- **POST** `/ajax/ajax_llenarBarriles.php` - Fill barrels
- **POST** `/ajax/ajax_marcarDespachado.php` - Mark dispatched
- **POST** `/ajax/ajax_marcarEntregaPagada.php` - Mark paid
- **POST** `/ajax/ajax_generarPago.php` - Process payment
- **POST** `/ajax/ajax_crearFactura.php` - Generate invoice
- **POST** `/ajax/ajax_cambiarEstadoTareas.php` - Update task status
- **POST** `/ajax/ajax_cambiarEstadoGastos.php` - Update expense status
- **POST** `/ajax/ajax_nuevoUsuario.php` - Create user with invitation

### Response Format
```json
{
  "status": "OK" | "ERROR",
  "mensaje": "Success/error message",
  "obj": { ... entity data ... }
}
```

---

## Configuration

### Database (`/php/app.php`)
```php
$mysqli_user = "barrcl_cocholg";
$mysqli_pass = "rglgd8ZdWWiP";
$mysqli_db = "barrcl_cocholg";
```

### Transbank (Payment Gateway)
```php
$transbank['codigo_comercio'] = "597047933778";
$transbank['api_secret_key'] = "b520a2a3-59e9-476e-8c01-9df2a231658b";
```

### Email
- Sender: contacto@cervezacocholgue.cl
- Method: PHP `mail()`
- HTML templates for invitations, password recovery, tasks

### System Settings (`Configuracion` class)
Stored in database, customizable:
- Company branding
- Email templates
- Contact information
- Logo/header image
- Notification settings
- History retention
- Login page styling

### Other
- **Timezone:** America/Santiago
- **Debug mode:** Enabled by default (`$debug = 1`)
- **Password salt:** "mister420"
- **Session:** PHP native sessions

### File Uploads
- Images: `/media/images/`
- Thumbnails: `/media/thumbnails/`
- Customer files: `/clientes/`

### Cron Jobs (`/cron4582h/`)
- `cron_enviarCorreos.php` - Email queue processing

---

## Security Notes

### Current Implementation
- Password hashing: `crypt()` with static salt
- Session-based authentication
- Role-based access control (RBAC)
- Input sanitization: `sanitize_input()` (htmlspecialchars)
- SQL queries: mysqli (string concatenation, not prepared statements)

### Potential Security Concerns
1. **SQL Injection Risk:** Base class uses string concatenation
2. **Static Password Salt:** All passwords use same salt
3. **Credentials in Code:** Hardcoded in app.php
4. **Debug Mode:** Enabled by default
5. **No CSRF Protection:** No visible CSRF tokens
6. **Limited XSS Protection:** Inconsistent use of htmlspecialchars

### Recommendations for Production
- Implement prepared statements
- Use environment variables for credentials
- Implement bcrypt/Argon2 for passwords
- Add CSRF tokens to forms
- Enable HTTPS enforcement
- Implement rate limiting
- Regular security audits

---

## Key Business Logic Rules

### Inventory Management
- Batch creation → deduct ingredients from bodega
- Batch deletion → restore ingredients
- Delivery dispatch → barrels marked "En terreno"
- Barrel return → marked "En planta"

### Barrel Lifecycle
```
En planta → En sala de frio → En terreno → En planta
                                ↓
                            Perdido (manual)
```
- Every state change logged in `barriles_estados`

### Pricing
- Check `clientes_productos_precios` for custom pricing
- Fall back to default product price

### Notifications
- Low stock triggered when ingredients insufficient for recipe
- Configurable per user level
- Automatic cleanup after configured duration

---

## Frontend Features

### Dark/Light Theme
- Toggle in navbar
- Stored in localStorage
- Persists across sessions

### Global Search
- jQuery UI Autocomplete
- Searches products, customers, batches, etc.
- Navigates to entity detail page

### DataTables
- Sortable columns
- Pagination
- Search/filter
- CSV/Excel export

### Real-time Updates
- Notification polling: 10 seconds
- Browser title shows unread count
- Visual indicators (bell icon badge)

### Custom JavaScript
- `getDataForm(entity)` - Extract all form data
- Dark mode toggle handler
- Notification management
- Client switching

---

## Development Notes

### Custom Architecture
- No external framework
- Custom ORM (Active Record pattern)
- Template-based MVC
- AJAX-heavy frontend
- Mix of Spanish/English in code

### Autoloading
```php
spl_autoload_register(function($clase){
  $ruta = $GLOBALS['base_dir']."/php/classes/".str_replace("\\","/",$clase).".php";
  require_once($ruta);
});
```

### Template Routing
```php
function switch_templates($section) {
  $template_file = "./templates/{$section}.php";
  if(file_exists($template_file)) {
    include($template_file);
  } else {
    include("./templates/404.php");
  }
}
```

### Base Class Pattern
All models extend `Base.php`:
- Automatic table/field mapping
- CRUD operations
- Relationship handling
- Media attachments

---

## Summary

**Barril.cl** is a sophisticated, domain-specific ERP for craft breweries managing the complete lifecycle from ingredients to delivered product. Built with PHP/MySQL and a custom ORM, it features comprehensive brewing process tracking (10+ stages), dual inventory systems, multi-role access control (8 levels), integrated financial management, Chilean tax compliance (DTE), and Chilean payment processing (Transbank).

**Primary users:** Brewery administrators, production managers, brew masters, sales representatives, delivery drivers, and B2B customers.

**Key differentiators:** Complete barrel tracking throughout lifecycle, batch-to-barrel traceability, Chilean regulatory compliance, customer-specific pricing, fermentation vessel management, and specialized brewing calculators.

This system consolidates inventory software, production tracking, delivery management, accounting, and CRM into a single integrated platform tailored for craft brewery operations.
