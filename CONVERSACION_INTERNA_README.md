# Sistema de Conversaciones Internas - Barril.cl

Sistema completo de conversaciones con comentarios, archivos adjuntos, menciones de usuarios y likes, traducido desde TypeScript/React a PHP/jQuery para el sistema Barril.cl.

## 📋 Características

- ✅ Conversaciones por entidad (batches, pedidos, clientes, etc.)
- ✅ Comentarios con texto enriquecido
- ✅ Archivos adjuntos (imágenes, PDF, documentos)
- ✅ Menciones de usuarios (@usuario)
- ✅ Sistema de likes
- ✅ Actualización automática
- ✅ Permisos de eliminación
- ✅ Diseño responsivo
- ✅ Compatible con tema oscuro

## 📁 Estructura de Archivos Creados

```
app.barril.cl/
├── sql/
│   └── conversaciones_internas.sql         # Script de creación de tablas
├── php/classes/
│   ├── ConversacionInterna.php             # Modelo principal
│   ├── ConversacionInternaComentario.php   # Modelo de comentarios
│   ├── ConversacionInternaArchivo.php      # Modelo de archivos
│   └── ConversacionInternaTag.php          # Modelo de menciones
├── ajax/
│   ├── ajax_getConversacion.php            # Obtener conversación
│   ├── ajax_guardarComentarioConArchivos.php # Guardar comentario
│   ├── ajax_eliminarComentario.php         # Eliminar comentario
│   └── ajax_actualizarLikesComentario.php  # Actualizar likes
├── templates/components/
│   └── conversacion-interna.php            # Componente reutilizable
├── js/
│   └── conversacion-interna.js             # Lógica JavaScript
└── css/
    └── conversacion-interna.css            # Estilos del componente
```

## 🚀 Instalación

### Paso 1: Crear las Tablas en la Base de Datos

Ejecuta el script SQL en tu base de datos MySQL:

```bash
mysql -u barrcl_cocholg -p barrcl_cocholg < sql/conversaciones_internas.sql
```

O desde phpMyAdmin, importa el archivo `sql/conversaciones_internas.sql`.

Esto creará 4 tablas:
- `conversaciones_internas`
- `conversaciones_internas_comentarios`
- `conversaciones_internas_archivos`
- `conversaciones_internas_tags`

### Paso 2: Crear Directorio de Archivos

Crea el directorio donde se guardarán los archivos adjuntos:

```bash
mkdir -p media/conversaciones
chmod 755 media/conversaciones
```

### Paso 3: Incluir CSS y JavaScript en tu Layout Principal

Edita tu archivo `/index.php` o el layout principal y agrega:

**En el `<head>`:**
```html
<link rel="stylesheet" href="/css/conversacion-interna.css">
```

**Antes del cierre de `</body>`:**
```html
<script src="/js/conversacion-interna.js"></script>
```

### Paso 4: Configurar Variable Global de Usuario

En tu JavaScript global (probablemente `/js/app.js`), asegúrate de tener:

```javascript
// ID del usuario actual (necesario para likes y permisos)
window.currentUserId = '<?php echo $GLOBALS['usuario']->id; ?>';
```

## 💻 Uso del Componente

### Uso Básico

En cualquier template donde quieras mostrar una conversación, simplemente incluye:

```php
<?php
  $conversation_view_name = "batch"; // Nombre de la vista/entidad
  $conversation_entity_id = $batch->id; // ID de la entidad
  include($GLOBALS['base_dir']."/templates/components/conversacion-interna.php");
?>
```

**Nota:** Se usa `$GLOBALS['base_dir']` para referencia absoluta, definida en `/php/app.php` líneas 128-132.

### Ejemplos de Uso

#### 1. En una página de Batch

```php
<!-- /templates/batch-detalle.php -->

<div class="container">
  <h2>Batch #<?php echo $batch->numero; ?></h2>

  <!-- Información del batch -->
  <div class="batch-info">
    <!-- ... datos del batch ... -->
  </div>

  <!-- Conversación del batch -->
  <div class="mt-4">
    <?php
      $conversation_view_name = "batch";
      $conversation_entity_id = $batch->id;
      include($GLOBALS['base_dir']."/templates/components/conversacion-interna.php");
    ?>
  </div>
</div>
```

#### 2. En una página de Pedido

```php
<!-- /templates/pedido-detalle.php -->

<?php
  $conversation_view_name = "pedido";
  $conversation_entity_id = $pedido->id;
  include($GLOBALS['base_dir']."/templates/components/conversacion-interna.php");
?>
```

#### 3. En una página de Cliente

```php
<!-- /templates/cliente-detalle.php -->

<?php
  $conversation_view_name = "cliente";
  $conversation_entity_id = $cliente->id;
  include($GLOBALS['base_dir']."/templates/components/conversacion-interna.php");
?>
```

### Variables Requeridas

| Variable | Tipo | Descripción | Ejemplo |
|----------|------|-------------|---------|
| `$conversation_view_name` | string | Nombre de la vista/entidad | `"batch"`, `"pedido"`, `"cliente"` |
| `$conversation_entity_id` | string | ID de la entidad | `$batch->id`, `$pedido->id` |

## 🎨 Personalización de Estilos

El componente incluye estilos predeterminados en `/css/conversacion-interna.css`, pero puedes sobrescribirlos:

```css
/* En tu app.css */

/* Cambiar color primario */
.conversacion-comentario:hover {
  border-left-color: #your-color !important;
}

/* Cambiar estilo de menciones */
.conversacion-mention {
  background: #your-background;
  color: #your-color;
}

/* Personalizar botón de enviar */
.conversacion-btn-enviar {
  background: #your-color;
}
```

## 🔧 Configuración Avanzada

### Configurar Límites de Archivos

En `/js/conversacion-interna.js`, busca y modifica:

```javascript
var config = {
  maxFiles: 5,                    // Máximo de archivos por comentario
  maxFileSize: 20 * 1024 * 1024, // Tamaño máximo por archivo (20MB)
  refreshInterval: 60000,         // Intervalo de actualización (1 minuto)
  baseUrl: '/ajax'                // URL base para AJAX
};
```

### Deshabilitar Auto-refresh

Si no quieres que la conversación se actualice automáticamente:

```javascript
// Comentar esta línea en conversacion-interna.js
// this.setupAutoRefresh();
```

## 🔐 Permisos y Seguridad

### Eliminación de Comentarios

Solo pueden eliminar comentarios:
- El autor del comentario
- Usuarios con nivel "Administrador"

Esto se valida en `/ajax/ajax_eliminarComentario.php`.

### Validación de Archivos

El sistema valida:
- Tamaño máximo por archivo (20MB por defecto)
- Número máximo de archivos (5 por defecto)
- Tipos de archivos permitidos (configurables en el input file)

### Cross-Entity Protection

Cada conversación está ligada a:
- `nombre_vista`: Tipo de entidad
- `id_entidad`: ID específico de la entidad

Esto previene acceso cruzado entre entidades.

## 📊 Base de Datos

### Esquema de Tablas

#### conversaciones_internas
```sql
id (varchar 36) - PK
nombre_vista (varchar 100) - Tipo de entidad
id_entidad (varchar 36) - ID de la entidad
estado (varchar 50) - Estado de la conversación
id_propietario (varchar 36)
id_creado_por (varchar 36)
id_actualizado_por (varchar 36)
fecha_creacion (datetime)
fecha_actualizacion (datetime)
```

#### conversaciones_internas_comentarios
```sql
id (varchar 36) - PK
id_conversacion_interna (varchar 36) - FK
contenido (text) - Texto del comentario
id_autor (varchar 36) - Usuario autor
estado (varchar 50)
likes (text) - JSON array de user IDs
fecha_creacion (datetime)
fecha_actualizacion (datetime)
```

#### conversaciones_internas_archivos
```sql
id (varchar 36) - PK
id_comentario (varchar 36) - FK
ruta_archivo (varchar 500) - Path relativo
nombre (varchar 255) - Nombre del archivo
descripcion (text)
estado (varchar 50)
metadata (text) - JSON con info adicional
id_subido_por (varchar 36)
fecha_creacion (datetime)
fecha_actualizacion (datetime)
```

#### conversaciones_internas_tags
```sql
id (varchar 36) - PK
id_comentario (varchar 36) - FK
id_usuario (varchar 36) - Usuario mencionado
id_creado_por (varchar 36) - Quien mencionó
fecha_creacion (datetime)
```

## 🔍 API Endpoints

### GET /ajax/ajax_getConversacion.php

Obtiene o crea una conversación.

**Parámetros:**
- `nombre_vista` (string): Tipo de entidad
- `id_entidad` (string): ID de la entidad

**Respuesta:**
```json
{
  "status": "OK",
  "mensaje": "OK",
  "data": {
    "conversacion": { ... },
    "comentarios": [ ... ]
  }
}
```

### POST /ajax/ajax_guardarComentarioConArchivos.php

Guarda un nuevo comentario con archivos.

**Parámetros:**
- `id_conversacion` (string): ID de la conversación
- `contenido` (string): Texto del comentario
- `tags` (JSON array): IDs de usuarios mencionados
- `estado` (string): Estado del comentario
- `archivo_*` (files): Archivos adjuntos

**Respuesta:**
```json
{
  "status": "OK",
  "mensaje": "Comentario guardado exitosamente.",
  "comentario_id": "...",
  "archivos_count": 2
}
```

### POST /ajax/ajax_eliminarComentario.php

Elimina un comentario y sus archivos.

**Parámetros:**
- `id_comentario` (string): ID del comentario

### POST /ajax/ajax_actualizarLikesComentario.php

Actualiza los likes de un comentario.

**Parámetros:**
- `id_comentario` (string): ID del comentario
- `accion` (string): "toggle", "add", o "remove"

## 🧪 Testing

### Verificar Instalación

1. **Verificar tablas creadas:**
```sql
SHOW TABLES LIKE 'conversaciones_internas%';
```

2. **Verificar archivos PHP:**
```bash
ls -la php/classes/ConversacionInterna*.php
```

3. **Verificar endpoints AJAX:**
```bash
curl -X GET "http://localhost/ajax/ajax_getConversacion.php?nombre_vista=test&id_entidad=1"
```

### Probar Componente

Crea un archivo de prueba `/templates/test-conversacion.php`:

```php
<?php
  require_once("./php/app.php");

  $usuario = new Usuario;
  session_start();
  $usuario->checkSession($_SESSION);

  $conversation_view_name = "test";
  $conversation_entity_id = "123";
?>

<!DOCTYPE html>
<html>
<head>
  <title>Test Conversación</title>
  <link rel="stylesheet" href="/css/app.css">
  <link rel="stylesheet" href="/css/conversacion-interna.css">
</head>
<body>
  <div class="container mt-5">
    <h1>Test de Conversación</h1>
    <?php include("./templates/components/conversacion-interna.php"); ?>
  </div>

  <script src="/js/jquery.min.js"></script>
  <script src="/js/bootstrap.bundle.min.js"></script>
  <script>window.currentUserId = '<?php echo $GLOBALS['usuario']->id; ?>';</script>
  <script src="/js/conversacion-interna.js"></script>
</body>
</html>
```

## 🐛 Troubleshooting

### Los comentarios no se cargan

1. Verifica que las tablas existan en la base de datos
2. Revisa la consola del navegador (F12) para errores JavaScript
3. Verifica que los archivos AJAX estén accesibles
4. Revisa los logs de PHP para errores

### Los archivos no se suben

1. Verifica que el directorio `/media/conversaciones/` exista y tenga permisos de escritura
2. Revisa el límite de `upload_max_filesize` en `php.ini`
3. Revisa el límite de `post_max_size` en `php.ini`

### Errores de permisos

1. Verifica que el usuario esté autenticado
2. Verifica que `$GLOBALS['usuario']->id` esté disponible
3. Revisa que `window.currentUserId` esté definido en JavaScript

### Los likes no funcionan

1. Verifica que `window.currentUserId` esté definido
2. Revisa la consola del navegador para errores
3. Verifica que el endpoint `/ajax/ajax_actualizarLikesComentario.php` sea accesible

## 📝 Changelog

### Versión 1.0.0 (2024-11-14)
- ✅ Implementación inicial
- ✅ Sistema de comentarios
- ✅ Archivos adjuntos
- ✅ Menciones de usuarios
- ✅ Sistema de likes
- ✅ Auto-refresh
- ✅ Responsive design

## 🤝 Contribuir

Para agregar nuevas funcionalidades:

1. Modifica las clases PHP en `/php/classes/`
2. Actualiza los endpoints AJAX en `/ajax/`
3. Actualiza el componente en `/templates/components/conversacion-interna.php`
4. Actualiza el JavaScript en `/js/conversacion-interna.js`
5. Actualiza los estilos en `/css/conversacion-interna.css`

## 📄 Licencia

Este componente es parte del sistema Barril.cl para Cerveza Cocholgue.

## 🆘 Soporte

Para soporte, contacta al equipo de desarrollo o revisa la documentación en `/Conversation/backend/service.md` y `/Conversation/backend/route.md`.

---

**Desarrollado para Barril.cl - Sistema de Gestión de Cervecería**
