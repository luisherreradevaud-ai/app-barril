# Plan de Implementación de Seguridad - app.barril.cl
## Presupuesto y Cronograma Detallado 2025

---

## RESUMEN EJECUTIVO

**Inversión Total Estimada**: $2.850.000 - $4.200.000 CLP
**Tiempo de Implementación**: 90 días
**ROI Esperado**: Evitar multas (hasta $3.000.000 CLP) + protección reputacional
**Modalidad**: 70% trabajo interno + 30% servicios externos

---

## FASE 1: CRÍTICA - SEGURIDAD INMEDIATA (Días 0-30)
### **Inversión Fase 1**: $800.000 - $1.200.000 CLP

### 1.1 Certificado SSL/HTTPS (OBLIGATORIO)
**Problema detectado**: Login sin HTTPS, datos en texto plano

| Ítem | Proveedor | Costo Anual | Implementación |
|------|-----------|-------------|----------------|
| Certificado SSL Estándar | Let's Encrypt | **GRATIS** | 4 horas |
| Certificado SSL Wildcard | Sectigo/GoDaddy Chile | $45.000 CLP | 4 horas |
| Certificado SSL EV (recomendado comercio) | DigiCert | $180.000 CLP | 6 horas |

**Opción recomendada**: Let's Encrypt (gratis) + renovación automática
**Costo implementación**: $0 (autoinstalable) o $80.000 CLP (técnico externo)

**Tareas**:
- [ ] Obtener certificado SSL
- [ ] Configurar Apache para HTTPS
- [ ] Redirección HTTP→HTTPS en .htaccess
- [ ] Actualizar URLs internas a HTTPS
- [ ] Verificar mixed content

**Responsable**: Desarrollador interno
**Tiempo estimado**: 1 día
**Costo**: $0 - $80.000 CLP

---

### 1.2 Auditoría y Refactorización de Contraseñas
**Problema detectado**: Necesita verificación de hashing

**Tareas**:
- [ ] Auditar tabla `usuarios` (método de hash actual)
- [ ] Migrar a `password_hash()` con BCRYPT
- [ ] Implementar validación de complejidad
- [ ] Sistema de expiración de contraseñas (90 días)
- [ ] Rate limiting login (máx 5 intentos/15 min)

**Código ejemplo a implementar**:
```php
// /php/classes/Usuario.php - Método de login seguro
public function login($email, $password) {
    // Rate limiting
    if($this->checkLoginAttempts($email) > 5) {
        return ['status' => 'ERROR', 'message' => 'Demasiados intentos'];
    }

    $user = $this->getUserByEmail($email);
    if(password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        return ['status' => 'OK'];
    }

    $this->logFailedAttempt($email);
    return ['status' => 'ERROR', 'message' => 'Credenciales inválidas'];
}
```

**Archivos a modificar**:
- `/php/classes/Usuario.php`
- `/php/login.php`
- `/php/registro.php`

**Responsable**: Desarrollador senior interno
**Tiempo estimado**: 3-4 días
**Costo**: $120.000 - $200.000 CLP (si externo)

---

### 1.3 Protección SQL Injection - PDO Migration
**Problema detectado**: Uso de clases Base sin validación de prepared statements

**Tareas**:
- [ ] Auditar clase `/php/classes/Base.php`
- [ ] Migrar a PDO con prepared statements
- [ ] Revisar todos los archivos `/ajax/*.php`
- [ ] Implementar validación de inputs

**Archivos críticos a revisar** (74 archivos en /ajax):
```
/ajax/ajax_getProductos.php
/ajax/ajax_setRecuperacion.php (usado en login)
... (todos los ajax)
```

**Ejemplo código seguro**:
```php
// Método seguro en Base.php
protected function query($sql, $params = []) {
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Uso seguro
$productos = $this->query(
    "SELECT * FROM productos WHERE id_clientes = ?",
    [$clienteId]
);
```

**Responsable**: Desarrollador senior + auditoría externa
**Tiempo estimado**: 8-10 días
**Costo auditoría externa**: $300.000 - $500.000 CLP
**Costo implementación interna**: 80 horas desarrollo

---

### 1.4 Sesiones Seguras
**Problema detectado**: No hay configuración de sesiones seguras visible

**Tareas**:
- [ ] Configurar `session_set_cookie_params()` en `/php/app.php`
- [ ] Implementar timeout de sesión (30 min)
- [ ] Regenerar session_id en login
- [ ] Destruir sesión en logout

**Código a agregar**:
```php
// /php/app.php - Inicio de archivo
session_set_cookie_params([
    'lifetime' => 1800, // 30 minutos
    'path' => '/',
    'domain' => 'app.barril.cl',
    'secure' => true,      // Solo HTTPS
    'httponly' => true,    // No accesible por JS
    'samesite' => 'Strict' // Protección CSRF
]);
session_start();

// Timeout de inactividad
if (isset($_SESSION['LAST_ACTIVITY']) &&
    (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: /login.php?msg=timeout');
}
$_SESSION['LAST_ACTIVITY'] = time();
```

**Responsable**: Desarrollador interno
**Tiempo estimado**: 1 día
**Costo**: $0 (interno)

---

### 1.5 Headers de Seguridad HTTP
**Problema detectado**: `.htaccess` sin headers de seguridad

**Tareas**:
- [ ] Actualizar `.htaccess` con headers de seguridad
- [ ] Configurar Content Security Policy (CSP)
- [ ] Implementar HSTS

**Código a agregar en `.htaccess`**:
```apache
# Seguridad HTTP Headers
<IfModule mod_headers.c>
    # Prevención de clickjacking
    Header always set X-Frame-Options "SAMEORIGIN"

    # Prevención XSS
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"

    # HSTS - Force HTTPS (1 año)
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

    # Referrer Policy
    Header always set Referrer-Policy "strict-origin-when-cross-origin"

    # Permissions Policy
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"

    # Content Security Policy
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://code.jquery.com https://cdnjs.cloudflare.com https://stackpath.bootstrapcdn.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:;"
</IfModule>

# Redirección HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Proteger archivos sensibles
<FilesMatch "^\.">
    Require all denied
</FilesMatch>

<FilesMatch "\.(sql|md|log|ini|conf|bak)$">
    Require all denied
</FilesMatch>

# Proteger directorio context
<Directory "/Applications/XAMPP/xamppfiles/htdocs/app.barril.cl/context">
    Require all denied
</Directory>
```

**Responsable**: Desarrollador interno
**Tiempo estimado**: 4 horas
**Costo**: $0 (interno)

---

### 1.6 Protección de Credenciales de Base de Datos
**Problema actual**: Necesita verificación

**Tareas**:
- [ ] Localizar archivo de configuración DB
- [ ] Mover fuera de document root o proteger con .htaccess
- [ ] Usar variables de entorno (.env)
- [ ] Cambiar credenciales de BD

**Estructura recomendada**:
```
/Applications/XAMPP/xamppfiles/htdocs/
    ├── config/
    │   └── database.php  (fuera de app.barril.cl)
    └── app.barril.cl/
        └── index.php
```

**Archivo de configuración seguro**:
```php
// /config/database.php
<?php
return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'name' => getenv('DB_NAME') ?: 'barrcl_cocholg',
    'user' => getenv('DB_USER') ?: 'barril_user',
    'pass' => getenv('DB_PASS') ?: 'CAMBIAR_PASSWORD_FUERTE',
];
```

**Responsable**: Desarrollador interno
**Tiempo estimado**: 2 horas
**Costo**: $0 (interno)

---

### RESUMEN FASE 1

| Tarea | Días | Costo |
|-------|------|-------|
| SSL/HTTPS | 1 | $0 - $80.000 |
| Contraseñas | 4 | $120.000 - $200.000 |
| SQL Injection Audit | 10 | $300.000 - $500.000 |
| Sesiones seguras | 1 | $0 |
| Headers HTTP | 0.5 | $0 |
| Protección DB | 0.5 | $0 |
| **TOTAL FASE 1** | **17 días** | **$420.000 - $780.000 CLP** |

---

## FASE 2: ALTA PRIORIDAD - CUMPLIMIENTO LEGAL (Días 30-60)
### **Inversión Fase 2**: $600.000 - $900.000 CLP

### 2.1 Política de Privacidad y Términos de Uso
**Obligatorio según Ley 19.628**

**Tareas**:
- [ ] Redactar política de privacidad en español
- [ ] Crear términos y condiciones
- [ ] Integrar en sitio web (/politica-privacidad.php)
- [ ] Implementar checkbox de aceptación en registro

**Opciones**:

| Opción | Proveedor | Costo | Tiempo |
|--------|-----------|-------|--------|
| Template genérico adaptado | Interno | $0 | 2 días |
| Abogado especialista datos | Externo | $250.000 - $400.000 | 5 días |
| Servicio online (Iubenda) | SaaS | $90.000/año | 1 día |

**Recomendación**: Abogado especialista (única vez)

**Contenido mínimo requerido**:
1. Identificación del responsable del tratamiento
2. Datos personales recopilados
3. Finalidad del tratamiento
4. Plazo de conservación
5. Derechos ARCO del titular
6. Medidas de seguridad
7. Transferencias internacionales (si aplica)
8. Contacto para ejercer derechos

**Responsable**: Abogado especialista + desarrollador para integración
**Tiempo estimado**: 5-7 días
**Costo**: $250.000 - $400.000 CLP

---

### 2.2 Sistema de Consentimiento Explícito
**Tareas**:
- [ ] Agregar checkbox en `/php/registro.php`
- [ ] Crear tabla `usuarios_consentimientos`
- [ ] Registrar fecha/hora de aceptación
- [ ] Implementar doble opt-in por email

**Estructura de tabla nueva**:
```sql
CREATE TABLE `usuarios_consentimientos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuarios` int(11) NOT NULL,
  `tipo_consentimiento` varchar(50) NOT NULL,
  `aceptado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_aceptacion` datetime NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuarios` (`id_usuarios`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Responsable**: Desarrollador interno
**Tiempo estimado**: 2 días
**Costo**: $0 (interno)

---

### 2.3 Implementación Derechos ARCO
**Acceso, Rectificación, Cancelación, Oposición**

**Tareas**:
- [ ] Crear módulo "Mis Datos" en panel de usuario
- [ ] Exportación de datos personales (JSON/PDF)
- [ ] Formulario de rectificación de datos
- [ ] Sistema de eliminación de cuenta
- [ ] Email de confirmación para cambios sensibles

**Archivos a crear**:
- `/templates/mis-datos.php`
- `/ajax/ajax_exportarDatos.php`
- `/ajax/ajax_eliminarCuenta.php`

**Funcionalidades**:
```php
// Exportar datos del usuario
public function exportarDatosPersonales($idUsuario) {
    $datos = [
        'usuario' => $this->getUsuario($idUsuario),
        'clientes' => $this->getClientesRelacionados($idUsuario),
        'historial' => $this->getHistorial($idUsuario),
        'fecha_exportacion' => date('Y-m-d H:i:s')
    ];

    // Generar PDF o JSON
    return json_encode($datos, JSON_PRETTY_PRINT);
}

// Eliminar cuenta (anonimización)
public function eliminarCuenta($idUsuario) {
    // NO eliminar, anonimizar para conservar integridad referencial
    $this->query(
        "UPDATE usuarios SET
            nombre = 'Usuario eliminado',
            apellido = '',
            email = CONCAT('deleted_', id, '@barril.cl'),
            telefono = '',
            password = '',
            estado = 'eliminado',
            fecha_eliminacion = NOW()
        WHERE id = ?",
        [$idUsuario]
    );
}
```

**Responsable**: Desarrollador interno
**Tiempo estimado**: 4-5 días
**Costo**: $0 (interno)

---

### 2.4 Protección XSS y CSRF
**Tareas XSS**:
- [ ] Crear función global de sanitización
- [ ] Aplicar `htmlspecialchars()` en todas las salidas
- [ ] Validar inputs en frontend y backend

**Tareas CSRF**:
- [ ] Implementar tokens CSRF en formularios
- [ ] Validar token en cada POST request

**Código a implementar**:
```php
// /php/classes/Security.php (NUEVO)
class Security {
    public static function generateCSRFToken() {
        if(!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) &&
               hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function sanitizeOutput($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeInput($data) {
        return trim(strip_tags($data));
    }
}

// En formularios
<input type="hidden" name="csrf_token"
       value="<?= Security::generateCSRFToken() ?>">

// En procesamiento
if(!Security::validateCSRFToken($_POST['csrf_token'])) {
    die('Invalid CSRF token');
}
```

**Archivos a modificar**: Todos los formularios (aprox. 50 archivos en /templates)

**Responsable**: Desarrollador interno
**Tiempo estimado**: 6-8 días
**Costo**: $0 (interno)

---

### 2.5 Sistema de Logging Completo
**Tabla existente**: `historial`, `errores`

**Tareas**:
- [ ] Ampliar tabla `historial` con campos adicionales
- [ ] Implementar logging automático en acciones sensibles
- [ ] Sistema de alertas para actividades sospechosas
- [ ] Retención de logs por 12 meses

**Modificación de tabla**:
```sql
ALTER TABLE historial
ADD COLUMN ip_address VARCHAR(45) AFTER id_usuarios,
ADD COLUMN user_agent VARCHAR(255) AFTER ip_address,
ADD COLUMN tipo_accion ENUM('login','logout','create','update','delete','view','export','error') AFTER user_agent,
ADD COLUMN datos_adicionales JSON AFTER tipo_accion,
ADD INDEX idx_tipo_accion (tipo_accion),
ADD INDEX idx_creada (creada);
```

**Implementación**:
```php
// /php/classes/Logger.php (NUEVO)
class Logger {
    public static function log($accion, $tipo, $datos = []) {
        global $conn;

        $idUsuario = $_SESSION['id_usuarios'] ?? 0;
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        $stmt = $conn->prepare(
            "INSERT INTO historial
            (accion, id_usuarios, ip_address, user_agent, tipo_accion, datos_adicionales, creada)
            VALUES (?, ?, ?, ?, ?, ?, NOW())"
        );

        $stmt->execute([
            $accion,
            $idUsuario,
            $ip,
            $userAgent,
            $tipo,
            json_encode($datos)
        ]);
    }
}

// Uso
Logger::log('Usuario modificó datos de cliente', 'update', [
    'id_cliente' => $clienteId,
    'campos_modificados' => ['email', 'telefono']
]);
```

**Responsable**: Desarrollador interno
**Tiempo estimado**: 3 días
**Costo**: $0 (interno)

---

### 2.6 Backups Automáticos Encriptados
**Tareas**:
- [ ] Configurar backup diario automático
- [ ] Encriptación de backups (GPG o AES-256)
- [ ] Almacenamiento offsite (Backblaze B2, AWS S3, Google Drive)
- [ ] Script de restauración

**Opciones de almacenamiento**:

| Servicio | Almacenamiento | Costo Mensual | Costo Anual |
|----------|----------------|---------------|-------------|
| Backblaze B2 | 100 GB | $600 CLP | $7.200 CLP |
| AWS S3 (Chile) | 100 GB | $2.400 CLP | $28.800 CLP |
| Google Drive Business | 2 TB | $10.000 CLP | $120.000 CLP |

**Recomendación**: Backblaze B2 (más económico)

**Script de backup** (cron diario):
```bash
#!/bin/bash
# /home/scripts/backup_barril.sh

DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="barrcl_cocholg"
BACKUP_DIR="/backups"
BACKUP_FILE="$BACKUP_DIR/barril_$DATE.sql"
ENCRYPTED_FILE="$BACKUP_FILE.gpg"

# Dump database
mysqldump -u root -p$DB_PASS $DB_NAME > $BACKUP_FILE

# Encriptar
gpg --symmetric --cipher-algo AES256 --passphrase "$ENCRYPT_PASS" $BACKUP_FILE

# Subir a Backblaze B2
b2 upload-file $BUCKET_NAME $ENCRYPTED_FILE backups/$(basename $ENCRYPTED_FILE)

# Eliminar backups locales > 7 días
find $BACKUP_DIR -name "*.gpg" -mtime +7 -delete

# Log
echo "Backup completado: $DATE" >> /var/log/backup_barril.log
```

**Configuración cron**:
```bash
# Ejecutar a las 2 AM diariamente
0 2 * * * /home/scripts/backup_barril.sh
```

**Responsable**: Administrador de sistemas / DevOps
**Tiempo estimado**: 2 días
**Costo inicial**: $50.000 CLP (configuración)
**Costo recurrente**: $7.200 CLP/año (Backblaze)

---

### RESUMEN FASE 2

| Tarea | Días | Costo |
|-------|------|-------|
| Política de Privacidad | 7 | $250.000 - $400.000 |
| Sistema de Consentimiento | 2 | $0 |
| Derechos ARCO | 5 | $0 |
| XSS/CSRF Protection | 8 | $0 |
| Sistema de Logging | 3 | $0 |
| Backups Automáticos | 2 | $57.200 (año 1) |
| **TOTAL FASE 2** | **27 días** | **$307.200 - $457.200 CLP** |

---

## FASE 3: MEDIA PRIORIDAD - MEJORAS (Días 60-90)
### **Inversión Fase 3**: $400.000 - $700.000 CLP

### 3.1 Autenticación de Dos Factores (2FA)
**Solo para administradores inicialmente**

**Opciones**:

| Método | Proveedor | Costo | Complejidad |
|--------|-----------|-------|-------------|
| Google Authenticator (TOTP) | Gratis | $0 | Media |
| SMS (Twilio Chile) | Twilio | ~$50/mes | Baja |
| Email OTP | Propio | $0 | Baja |

**Recomendación**: Google Authenticator (TOTP)

**Librería PHP**: `pragmarx/google2fa-laravel` o `robthree/twofactorauth`

**Implementación**:
```bash
composer require robthree/twofactorauth
```

```php
// /php/classes/TwoFactorAuth.php
use RobThree\Auth\TwoFactorAuth;

class TwoFactorAuthManager {
    private $tfa;

    public function __construct() {
        $this->tfa = new TwoFactorAuth('Barril.cl');
    }

    public function generateSecret() {
        return $this->tfa->createSecret();
    }

    public function getQRCode($secret, $email) {
        return $this->tfa->getQRCodeImageAsDataUri($email, $secret);
    }

    public function verifyCode($secret, $code) {
        return $this->tfa->verifyCode($secret, $code);
    }
}
```

**Tabla adicional**:
```sql
ALTER TABLE usuarios
ADD COLUMN tfa_secret VARCHAR(32) DEFAULT NULL,
ADD COLUMN tfa_enabled TINYINT(1) DEFAULT 0;
```

**Responsable**: Desarrollador interno
**Tiempo estimado**: 4 días
**Costo**: $0 (gratis con TOTP)

---

### 3.2 Encriptación de Datos Sensibles en BD
**Datos a encriptar**:
- Tokens de Transbank
- RUT de clientes (opcional)
- Números de cuenta bancaria

**Método**: AES-256-GCM con claves maestras en .env

**Implementación**:
```php
// /php/classes/Encryption.php
class Encryption {
    private static $method = 'AES-256-GCM';

    public static function encrypt($data) {
        $key = getenv('ENCRYPTION_KEY');
        $iv = random_bytes(12);
        $tag = '';

        $encrypted = openssl_encrypt(
            $data,
            self::$method,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return base64_encode($iv . $tag . $encrypted);
    }

    public static function decrypt($encrypted) {
        $key = getenv('ENCRYPTION_KEY');
        $decoded = base64_decode($encrypted);

        $iv = substr($decoded, 0, 12);
        $tag = substr($decoded, 12, 16);
        $ciphertext = substr($decoded, 28);

        return openssl_decrypt(
            $ciphertext,
            self::$method,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );
    }
}

// Generar clave de encriptación (ejecutar una vez)
$key = base64_encode(random_bytes(32));
// Guardar en .env: ENCRYPTION_KEY=base64:xxxxxx
```

**Modificación de tablas**:
```sql
ALTER TABLE pagos
MODIFY token TEXT,  -- Almacenar encriptado
MODIFY codigo_transaccion TEXT;

ALTER TABLE clientes
ADD COLUMN rut_encrypted TEXT;  -- Nueva columna encriptada
```

**Responsable**: Desarrollador senior
**Tiempo estimado**: 5 días
**Costo**: $0 (interno)

---

### 3.3 Plan de Respuesta a Incidentes
**Tareas**:
- [ ] Documento de procedimiento (PDF)
- [ ] Designar responsable de seguridad
- [ ] Template de comunicación a usuarios
- [ ] Contactos de emergencia (abogado, proveedor hosting)

**Documento a crear**: `/docs/Plan_Respuesta_Incidentes.pdf`

**Contenido**:
1. Clasificación de incidentes (bajo, medio, alto, crítico)
2. Equipo de respuesta (roles y contactos)
3. Procedimiento paso a paso
4. Plantillas de comunicación
5. Checklist de contención
6. Reporte post-incidente

**Responsable**: Gerencia + Abogado
**Tiempo estimado**: 3 días
**Costo**: $150.000 - $250.000 CLP (asesoría legal)

---

### 3.4 Capacitación de Equipo
**Tareas**:
- [ ] Curso online OWASP Top 10 (desarrolladores)
- [ ] Taller Ley 19.628 (todo el equipo)
- [ ] Workshop manejo de incidentes

**Opciones**:

| Curso | Proveedor | Costo | Participantes |
|-------|-----------|-------|---------------|
| OWASP Top 10 | Udemy/Coursera | $30.000 CLP | 2-3 devs |
| Taller Ley 19.628 | Abogado externo | $200.000 CLP | Todo equipo |
| Ciberseguridad básica | LinkedIn Learning | $20.000/mes | Ilimitado |

**Responsable**: Recursos Humanos + CTO
**Tiempo estimado**: 5 días (distribuidos)
**Costo**: $250.000 - $400.000 CLP

---

### 3.5 Actualización de PHP y Servidor
**Versión actual detectada**: PHP 7.2.30 (EOL desde 2020)

**Tareas**:
- [ ] Backup completo de aplicación
- [ ] Actualizar a PHP 8.1 o 8.2
- [ ] Probar compatibilidad en entorno staging
- [ ] Migrar cambios deprecados
- [ ] Actualizar Apache/MySQL

**Problemas potenciales PHP 8**:
- Funciones deprecadas
- Type hints estrictos
- Cambios en error handling

**Responsable**: DevOps + Desarrollador senior
**Tiempo estimado**: 5-7 días
**Costo**: $0 (interno) o $150.000 - $300.000 (externo)

---

### RESUMEN FASE 3

| Tarea | Días | Costo |
|-------|------|-------|
| 2FA (TOTP) | 4 | $0 |
| Encriptación BD | 5 | $0 |
| Plan Respuesta Incidentes | 3 | $150.000 - $250.000 |
| Capacitación | 5 | $250.000 - $400.000 |
| Actualizar PHP | 7 | $0 - $300.000 |
| **TOTAL FASE 3** | **24 días** | **$400.000 - $950.000 CLP** |

---

## FASE 4: MEJORA CONTINUA (Post-90 días)
### **Inversión anual**: $500.000 - $1.000.000 CLP

### 4.1 Auditoría de Seguridad Externa
**Frecuencia**: Anual

**Opciones**:

| Servicio | Proveedor Chile | Alcance | Costo |
|----------|-----------------|---------|-------|
| Pentesting básico | Netready, DragonJAR | Web app | $800.000 - $1.500.000 |
| Auditoría completa | EY, Deloitte Cybersecurity | App + infraestructura | $3.000.000 - $6.000.000 |
| Scan automatizado | Detectify, Intruder | Web app | $500.000/año |

**Recomendación año 1**: Pentesting básico

**Responsable**: Proveedor externo
**Costo**: $800.000 - $1.500.000 CLP/año

---

### 4.2 WAF (Web Application Firewall)
**Opciones**:

| Servicio | Proveedor | Costo Mensual | Costo Anual |
|----------|-----------|---------------|-------------|
| Cloudflare Pro | Cloudflare | $20 USD | $240 USD (~$220.000 CLP) |
| Sucuri Firewall | Sucuri | $200 USD | $2.400 USD (~$2.200.000 CLP) |
| AWS WAF | Amazon | Variable | ~$500.000 CLP |

**Recomendación**: Cloudflare Pro (mejor relación costo/beneficio)

**Beneficios**:
- Protección DDoS
- Bloqueo de bots maliciosos
- SSL/TLS gratis
- CDN incluido
- Rate limiting

**Responsable**: DevOps
**Costo**: $220.000 CLP/año

---

### 4.3 Monitoreo de Seguridad
**Herramientas**:

| Servicio | Función | Costo |
|----------|---------|-------|
| UptimeRobot | Monitoreo uptime | Gratis (50 monitores) |
| Sentry | Error tracking | Gratis (5K eventos/mes) |
| Fail2Ban | Bloqueo automático IPs | Gratis (open source) |

**Implementación Fail2Ban**:
```bash
# Instalar en servidor
apt-get install fail2ban

# Configurar /etc/fail2ban/jail.local
[apache-auth]
enabled = true
port = http,https
logpath = /var/log/apache2/error.log
maxretry = 3
bantime = 3600
```

**Responsable**: DevOps
**Costo**: $0 (open source)

---

### 4.4 Certificación ISO 27001 (Opcional)
**Costo**: $8.000.000 - $15.000.000 CLP (primera certificación)
**Mantenimiento**: $2.000.000 - $4.000.000 CLP/año

**Recomendación**: NO prioritario para año 1
**Considerar**: Años 2-3 si crecimiento lo justifica

---

### RESUMEN FASE 4 (Anual)

| Tarea | Frecuencia | Costo Anual |
|-------|------------|-------------|
| Auditoría externa | Anual | $800.000 - $1.500.000 |
| WAF (Cloudflare) | Mensual | $220.000 |
| Backups (Backblaze) | Mensual | $7.200 |
| Monitoreo | Continuo | $0 |
| Capacitación continua | Trimestral | $100.000 |
| **TOTAL FASE 4** | - | **$1.127.200 - $1.827.200 CLP/año** |

---

## PRESUPUESTO TOTAL CONSOLIDADO

### Inversión Inicial (90 días)

| Fase | Tiempo | Costo Mínimo | Costo Máximo |
|------|--------|--------------|--------------|
| Fase 1 - Crítica | 17 días | $420.000 | $780.000 |
| Fase 2 - Alta prioridad | 27 días | $307.200 | $457.200 |
| Fase 3 - Media prioridad | 24 días | $400.000 | $950.000 |
| **TOTAL 90 DÍAS** | **68 días** | **$1.127.200** | **$2.187.200** |

### Costos Recurrentes (Anual)

| Concepto | Costo Anual |
|----------|-------------|
| SSL (Let's Encrypt) | $0 |
| Backups (Backblaze) | $7.200 |
| WAF (Cloudflare) | $220.000 |
| Auditoría externa | $800.000 - $1.500.000 |
| Capacitación | $100.000 |
| **TOTAL ANUAL** | **$1.127.200 - $1.827.200** |

---

## CRONOGRAMA GANTT

```
SEMANA │ 1  2  3  4  5  6  7  8  9 10 11 12
───────┼───────────────────────────────────
FASE 1 │ ████████████████
SSL    │ ██
Passw  │   ████
SQL Inj│     ██████████
Sesion │              ██
Headers│              ██
───────┼───────────────────────────────────
FASE 2 │                 ████████████████████
Privac │                 ██████
Consent│                       ██
ARCO   │                         ████
XSS/CSR│                             ████████
Log    │                                 ████
Backup │                                   ██
───────┼───────────────────────────────────
FASE 3 │                                     ████████████
2FA    │                                     ████
Encript│                                         ████
Plan   │                                           ███
Capac  │                                              █████
PHP    │                                                  ███
```

---

## ANÁLISIS COSTO-BENEFICIO

### Costos de NO Implementar

| Riesgo | Probabilidad | Impacto Económico |
|--------|--------------|-------------------|
| Multa Ley 19.628 | Media | $500.000 - $3.000.000 |
| Brecha de datos | Media | $2.000.000 - $10.000.000 |
| Pérdida de clientes | Alta | $5.000.000+ |
| Daño reputacional | Alta | Incalculable |
| **TOTAL RIESGO** | - | **$7.500.000 - $18.000.000+** |

### ROI Proyectado

**Inversión Total Año 1**: $2.300.000 CLP
**Riesgo Evitado**: $7.500.000+ CLP
**ROI**: 226% (retorno positivo)

---

## RECURSOS NECESARIOS

### Equipo Interno

| Rol | Dedicación | Costo |
|-----|------------|-------|
| Desarrollador Senior | 40% (3 meses) | Interno |
| Desarrollador Junior | 20% (3 meses) | Interno |
| DevOps/SysAdmin | 10% (3 meses) | Interno |
| Project Manager | 10% (3 meses) | Interno |

### Proveedores Externos

| Servicio | Proveedor Sugerido | Contacto |
|----------|-------------------|----------|
| Asesoría legal datos | Estudio jurídico especializado | TBD |
| Auditoría seguridad | Netready, DragonJAR Chile | contacto@netready.cl |
| Certificado SSL | Let's Encrypt (gratis) | - |
| Backups | Backblaze B2 | backblaze.com |
| WAF | Cloudflare | cloudflare.com |

---

## HITOS DE VERIFICACIÓN

### Día 30 - Fin Fase 1
- [ ] HTTPS funcionando en producción
- [ ] Contraseñas con hash seguro verificado
- [ ] 0 vulnerabilidades SQL Injection críticas
- [ ] Headers de seguridad activos
- [ ] Sesiones seguras implementadas

### Día 60 - Fin Fase 2
- [ ] Política de privacidad publicada
- [ ] Sistema ARCO funcional
- [ ] Formularios con CSRF tokens
- [ ] XSS sanitization global
- [ ] Backups automáticos diarios funcionando
- [ ] Logs de auditoría completos

### Día 90 - Fin Fase 3
- [ ] 2FA para administradores
- [ ] Datos sensibles encriptados
- [ ] Plan de incidentes documentado
- [ ] Equipo capacitado
- [ ] PHP actualizado a 8.x

---

## PLAN DE CONTINGENCIA

### Si el presupuesto es limitado

**Prioridad MÁXIMA (obligatorio legal)**:
1. HTTPS ($0 - $80.000)
2. Contraseñas seguras ($0 interno)
3. SQL Injection fix ($300.000 mínimo)
4. Política de Privacidad ($250.000)
5. **TOTAL MÍNIMO**: $550.000 - $630.000 CLP

**Posponer para Fase 2**:
- 2FA (implementar en 6 meses)
- Encriptación BD (implementar en 6 meses)
- Auditoría externa (implementar en 12 meses)

---

## MÉTRICAS DE ÉXITO

### KPIs a monitorear

| Métrica | Línea Base | Objetivo 90 días |
|---------|-----------|------------------|
| Vulnerabilidades críticas | TBD | 0 |
| Intentos de login fallidos | TBD | < 100/día |
| Tiempo de sesión promedio | TBD | 30 min |
| Incidentes de seguridad | TBD | 0 |
| Uptime | 95% | 99.5% |
| Cumplimiento legal | 30% | 95% |

---

## APROBACIÓN Y FIRMA

**Documento preparado por**: [Nombre]
**Fecha**: 2025-11-02
**Versión**: 1.0

**Aprobaciones requeridas**:
- [ ] Gerencia General
- [ ] CTO/Responsable Técnico
- [ ] CFO/Finanzas
- [ ] Legal/Compliance

**Presupuesto aprobado**: $ ________________ CLP

**Fecha inicio**: ________________

**Responsable ejecución**: ________________

---

## ANEXOS

### Anexo A: Checklist de Implementación Detallado
Ver archivo: `CHECKLIST_IMPLEMENTACION.xlsx`

### Anexo B: Scripts de Automatización
Ver carpeta: `/scripts/seguridad/`

### Anexo C: Políticas y Documentos Legales
Ver carpeta: `/docs/legal/`

### Anexo D: Contactos de Emergencia
```
Responsable Seguridad: [Nombre] - [Teléfono]
Abogado Datos: [Nombre] - [Teléfono]
Proveedor Hosting: [Nombre] - [Teléfono]
Soporte Técnico: [Nombre] - [Teléfono]
```

---

## NOTAS FINALES

Este plan de implementación está diseñado para ser **ejecutable, realista y adaptado al contexto chileno**.

**Puntos clave**:
- 70% del trabajo puede hacerse internamente
- Inversión inicial justificable vs. multas potenciales
- Cumplimiento Ley 19.628 en 60 días
- Mejora continua post-implementación

**Próximos pasos inmediatos**:
1. Aprobar presupuesto
2. Designar responsable del proyecto
3. Contratar abogado especialista en datos
4. Iniciar Fase 1 (HTTPS + passwords)

**Soporte**: Para consultas sobre este plan, contactar a [email protección de datos]

---

**Última actualización**: 2025-11-02
**Próxima revisión**: 2025-12-02
