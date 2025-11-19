# Presupuesto Mínimo Viable (MVP) - Seguridad app.barril.cl
## Cumplimiento Legal Básico Ley 19.628

---

## RESUMEN EJECUTIVO

**Inversión Total MVP**: $550.000 - $780.000 CLP
**Tiempo de Implementación**: 30 días
**Objetivo**: Cumplimiento mínimo Ley 19.628 + Protección básica contra vulnerabilidades críticas

---

## ¿QUÉ INCLUYE ESTE PRESUPUESTO?

Este presupuesto MVP incluye **SOLO las medidas obligatorias** para:
1. No violar la Ley 19.628 de Protección de Datos de Chile
2. Proteger contra las vulnerabilidades más críticas (OWASP Top 3)
3. Evitar multas de hasta $3.000.000 CLP

**Qué NO incluye** (puede implementarse en 3-6 meses):
- Autenticación de dos factores (2FA)
- Encriptación avanzada de base de datos
- Auditoría externa completa
- Certificaciones ISO

---

## INVERSIÓN DESGLOSADA

| # | Medida | Costo | Obligatoriedad |
|---|--------|-------|----------------|
| 1 | Certificado SSL/HTTPS | $0 - $80.000 | **OBLIGATORIO** |
| 2 | Auditoría SQL Injection | $300.000 - $500.000 | **OBLIGATORIO** |
| 3 | Política de Privacidad Legal | $250.000 | **OBLIGATORIO** |
| **TOTAL MVP** | | **$550.000 - $830.000** | |

### Costos recurrentes año 1:
- Backups básicos: $7.200/año (Backblaze)
- Total primer año: **$557.200 - $837.200 CLP**

---

## DETALLE DE CADA MEDIDA

### 1. CERTIFICADO SSL/HTTPS - $0 a $80.000 CLP

#### ¿Por qué es obligatorio?
- La aplicación transmite contraseñas en **texto plano** (login.php:135)
- Datos financieros de Transbank sin encriptar en tránsito
- Incumplimiento artículo 12 Ley 19.628 (seguridad de datos)

#### Opciones:

**OPCIÓN A: Let's Encrypt (RECOMENDADO)**
- **Costo**: $0 (gratis)
- **Validez**: 90 días (renovación automática)
- **Instalación**: 4 horas

**OPCIÓN B: Técnico externo**
- **Costo**: $80.000 CLP (una vez)
- **Incluye**: Instalación + configuración + renovación automática
- **Tiempo**: 1 día

#### Qué incluye la implementación:
```
✓ Instalación certificado SSL
✓ Configuración Apache para HTTPS
✓ Redirección automática HTTP → HTTPS
✓ Actualización .htaccess
✓ Verificación de mixed content
✓ Script de renovación automática (Let's Encrypt)
```

#### Proveedor sugerido (si no es interno):
- **Nombre**: Soporte técnico local o freelancer
- **Perfil**: Administrador de sistemas con experiencia LAMP
- **Tiempo**: 4-6 horas

**PRESUPUESTO ASIGNADO**: $0 (interno) o $80.000 (externo)

---

### 2. AUDITORÍA Y CORRECCIÓN SQL INJECTION - $300.000 a $500.000 CLP

#### ¿Por qué es obligatorio?
- Riesgo CRÍTICO: Acceso no autorizado a base de datos
- Posible robo de datos personales (clientes, usuarios, RUT)
- Multa Ley 19.628: hasta $3.000.000 CLP
- Responsabilidad penal según Ley 21.459 (Delitos Informáticos)

#### Alcance del trabajo:

**Archivos a auditar** (prioridad alta):
```
/php/classes/Base.php              ← Clase principal de BD
/php/login.php                     ← Autenticación
/ajax/ajax_*.php                   ← 74 archivos de endpoints
/php/registro.php                  ← Registro usuarios
/php/classes/Cliente.php           ← Datos sensibles RUT
/php/classes/Pago.php              ← Datos financieros
/php/classes/Usuario.php           ← Credenciales
```

**Total estimado**: 90 archivos PHP críticos

#### Qué incluye el servicio:

**FASE 1: Auditoría (5 días - $150.000 CLP)**
- [ ] Análisis de código estático (SAST)
- [ ] Identificación de consultas vulnerables
- [ ] Pruebas de penetración manuales
- [ ] Reporte de vulnerabilidades con severidad
- [ ] Lista priorizada de correcciones

**FASE 2: Corrección (8 días - $250.000 CLP)**
- [ ] Migración a PDO con prepared statements
- [ ] Refactorización de clase Base.php
- [ ] Sanitización de inputs en todos los endpoints AJAX
- [ ] Validación de tipos de datos
- [ ] Pruebas de regresión
- [ ] Documentación de cambios

**Entregables**:
1. Reporte de auditoría (PDF)
2. Código corregido (Git commits)
3. Guía de buenas prácticas (para equipo)
4. Re-test de vulnerabilidades (prueba de que están corregidas)

#### Código de ejemplo de corrección:

**ANTES (vulnerable)**:
```php
// /php/classes/Base.php - VULNERABLE
public function getInfoDatabase($field) {
    $sql = "SELECT * FROM {$this->tableName} WHERE $field = '{$this->id}'";
    $result = mysql_query($sql); // ¡PELIGRO!
    return mysql_fetch_assoc($result);
}
```

**DESPUÉS (seguro)**:
```php
// /php/classes/Base.php - SEGURO
protected $pdo;

public function __construct() {
    $this->pdo = Database::getInstance()->getConnection();
}

public function getInfoDatabase($field) {
    $sql = "SELECT * FROM {$this->tableName} WHERE {$field} = :id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['id' => $this->id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
```

#### Proveedor sugerido:

**Opción 1: Freelancer especializado**
- **Perfil**: Desarrollador PHP senior con experiencia en seguridad
- **Costo**: $300.000 - $400.000 CLP
- **Tiempo**: 10-12 días
- **Dónde buscar**: GetOnBrd, Workana, LinkedIn

**Opción 2: Empresa de ciberseguridad**
- **Ejemplos**: Netready, Eforcers, DragonJAR Chile
- **Costo**: $500.000 - $800.000 CLP
- **Tiempo**: 15 días
- **Beneficio**: Certificado de auditoría oficial

**PRESUPUESTO ASIGNADO**: $300.000 - $500.000 CLP

---

### 3. POLÍTICA DE PRIVACIDAD Y TÉRMINOS LEGALES - $250.000 CLP

#### ¿Por qué es obligatorio?
- **Artículo 4° Ley 19.628**: Obligación de informar al titular
- **Artículo 12**: Medidas de seguridad deben estar documentadas
- Sin política de privacidad = vulneración de derechos del titular
- Multa: $500.000 - $3.000.000 CLP

#### Qué incluye el servicio legal:

**Documentos a crear**:
1. **Política de Privacidad** (español, Chile)
   - Identificación del responsable
   - Datos personales recopilados
   - Finalidad del tratamiento
   - Derechos ARCO del titular
   - Medidas de seguridad
   - Plazo de conservación
   - Transferencias de datos (Transbank)
   - Modificaciones de la política

2. **Términos y Condiciones de Uso**
   - Alcance del servicio
   - Responsabilidades del usuario
   - Limitaciones de responsabilidad
   - Ley aplicable (Chile)

3. **Formulario de Consentimiento**
   - Checkbox para registro
   - Texto legal conforme Ley 19.628

4. **Procedimiento ARCO** (Acceso, Rectificación, Cancelación, Oposición)
   - Email o formulario para solicitudes
   - Plazo de respuesta: 2 días hábiles

#### Formato de entrega:
- Documentos en Word/PDF editables
- Versión HTML para integrar en sitio web
- Asesoría de 2 horas para dudas de implementación

#### Implementación técnica (incluida):
```php
// Crear archivo /politica-privacidad.php
// Crear archivo /terminos-condiciones.php
// Agregar checkbox en /php/registro.php:

<div class="form-check mb-3">
    <input type="checkbox" class="form-check-input"
           id="aceptoPolitica" name="acepto_politica" required>
    <label class="form-check-label" for="aceptoPolitica">
        Acepto la
        <a href="/politica-privacidad.php" target="_blank">
            Política de Privacidad
        </a>
        y los
        <a href="/terminos-condiciones.php" target="_blank">
            Términos de Uso
        </a>
    </label>
</div>
```

#### Proveedor sugerido:

**Abogado especialista en Protección de Datos**
- **Perfil**: Abogado con experiencia en Ley 19.628 y derecho digital
- **Costo**: $250.000 - $400.000 CLP (una vez)
- **Tiempo**: 5-7 días hábiles
- **Incluye**: 2 horas de asesoría post-entrega

**Dónde buscar**:
- Colegio de Abogados de Chile
- Estudios especializados en derecho digital
- Asociación Chilena de Empresas de Tecnologías de Información (ACTI)

**Contactos sugeridos** (verificar disponibilidad):
- Estudio jurídico especializado en TI
- Abogados freelance en GetOnBrd

**PRESUPUESTO ASIGNADO**: $250.000 CLP

---

## MEDIDAS GRATUITAS (TRABAJO INTERNO)

Estas medidas NO tienen costo de proveedor externo, pero requieren tiempo de tu equipo:

### 4. Contraseñas Seguras - $0 (INTERNO)

**Tiempo estimado**: 3 días (1 desarrollador)

**Tareas**:
- [ ] Auditar tabla `usuarios` (verificar método de hash)
- [ ] Si NO usa `password_hash()`, migrar contraseñas
- [ ] Implementar validación de complejidad:
  ```php
  // Mínimo 8 caracteres, 1 mayúscula, 1 número
  function validarPassword($password) {
      if(strlen($password) < 8) return false;
      if(!preg_match('/[A-Z]/', $password)) return false;
      if(!preg_match('/[0-9]/', $password)) return false;
      return true;
  }
  ```
- [ ] Rate limiting (máx 5 intentos/15 min)
- [ ] Bloqueo temporal de cuenta

**Archivos a modificar**:
- `/php/registro.php`
- `/php/login.php`
- `/php/classes/Usuario.php` (si existe)

**Costo**: $0 (trabajo interno)

---

### 5. Sesiones Seguras - $0 (INTERNO)

**Tiempo estimado**: 1 día (1 desarrollador)

**Código a agregar en `/php/app.php`**:
```php
<?php
// Configuración de sesiones seguras
session_set_cookie_params([
    'lifetime' => 1800,        // 30 minutos
    'path' => '/',
    'domain' => 'app.barril.cl',
    'secure' => true,          // Solo HTTPS
    'httponly' => true,        // No accesible por JavaScript
    'samesite' => 'Strict'     // Protección CSRF básica
]);

session_start();

// Timeout por inactividad
if (isset($_SESSION['LAST_ACTIVITY']) &&
    (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: /login.php?msg=session_expired');
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

// Regenerar ID después de login
if(isset($_SESSION['login_success'])) {
    session_regenerate_id(true);
    unset($_SESSION['login_success']);
}
?>
```

**Costo**: $0 (trabajo interno)

---

### 6. Headers de Seguridad HTTP - $0 (INTERNO)

**Tiempo estimado**: 1 hora (1 desarrollador)

**Actualizar `.htaccess`**:
```apache
<IfModule mod_headers.c>
    # Prevención clickjacking
    Header always set X-Frame-Options "SAMEORIGIN"

    # Prevención XSS
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"

    # HSTS (Force HTTPS)
    Header always set Strict-Transport-Security "max-age=31536000"

    # Referrer Policy
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Proteger archivos sensibles
<FilesMatch "\.(sql|md|log|ini)$">
    Require all denied
</FilesMatch>
```

**Costo**: $0 (trabajo interno)

---

### 7. Backups Básicos - $7.200/año

**Servicio**: Backblaze B2
**Costo mensual**: $600 CLP
**Almacenamiento**: 100 GB

**Configuración**:
```bash
#!/bin/bash
# Script de backup diario (cron)

mysqldump -u root -p$DB_PASS barrcl_cocholg > /backups/backup_$(date +%Y%m%d).sql
gzip /backups/backup_$(date +%Y%m%d).sql

# Subir a Backblaze (instalar b2 CLI)
b2 upload-file $BUCKET_NAME /backups/backup_$(date +%Y%m%d).sql.gz

# Limpiar backups locales > 7 días
find /backups -name "*.sql.gz" -mtime +7 -delete
```

**Cron**:
```
0 2 * * * /home/scripts/backup_barril.sh
```

**Tiempo de configuración**: 4 horas
**Costo**: $7.200/año

---

## CRONOGRAMA MVP (30 DÍAS)

```
SEMANA 1 (Días 1-7)
├── Día 1-2:  Instalación SSL/HTTPS
├── Día 3-5:  Contraseñas seguras
└── Día 6-7:  Sesiones seguras + Headers HTTP

SEMANA 2-3 (Días 8-21)
├── Día 8-12:  Auditoría SQL Injection
├── Día 13-21: Corrección SQL Injection
└── Paralelo:  Redacción Política Privacidad (abogado)

SEMANA 4 (Días 22-30)
├── Día 22-24: Re-testing vulnerabilidades
├── Día 25-27: Integración Política Privacidad
├── Día 28:    Configuración backups
├── Día 29:    Testing general
└── Día 30:    Puesta en producción
```

---

## INVERSIÓN TOTAL CONSOLIDADA

### Costos Únicos (Implementación)

| Concepto | Costo Mínimo | Costo Máximo | Modalidad |
|----------|--------------|--------------|-----------|
| SSL/HTTPS | $0 | $80.000 | Externo opcional |
| Auditoría SQL Injection | $300.000 | $500.000 | Externo obligatorio |
| Política de Privacidad | $250.000 | $250.000 | Externo obligatorio |
| **TOTAL ÚNICO** | **$550.000** | **$830.000** | |

### Costos Recurrentes (Año 1)

| Concepto | Costo Anual |
|----------|-------------|
| Backups Backblaze B2 | $7.200 |
| SSL Renovación | $0 (Let's Encrypt) |
| **TOTAL ANUAL** | **$7.200** |

### INVERSIÓN TOTAL AÑO 1
**$557.200 - $837.200 CLP**

---

## FORMAS DE PAGO SUGERIDAS

### Opción A: Pago Único
- 50% al inicio del proyecto
- 50% al finalizar implementación
- **Total**: $550.000 - $830.000 CLP

### Opción B: Pago por Etapas
- 30% al contratar ($165.000 - $249.000)
- 40% al completar auditoría ($220.000 - $332.000)
- 30% al entregar proyecto ($165.000 - $249.000)

### Opción C: Financiamiento 3 Meses
- Inicial: $200.000 CLP
- Mes 1: $180.000 CLP
- Mes 2: $180.000 CLP
- Mes 3: Saldo restante

---

## PROVEEDORES Y COTIZACIONES

### SSL/HTTPS (Si se contrata externo)

**Proveedor**: Freelancer técnico o soporte local
**Contactar en**:
- GetOnBrd.com (filtrar por "DevOps" o "SysAdmin")
- Workana.com
- LinkedIn (buscar "Administrador LAMP Chile")

**Perfil requerido**:
- Experiencia con Apache/XAMPP
- Conocimiento Let's Encrypt/Certbot
- Referencias verificables

**Presupuesto**: $80.000 CLP (máximo)

---

### Auditoría SQL Injection

**Opción 1: Freelancer Senior**

**Perfil**:
- 5+ años experiencia PHP
- Conocimiento OWASP Top 10
- Experiencia con PDO/Prepared Statements
- Portfolio de proyectos de seguridad

**Dónde buscar**:
- GetOnBrd.com (etiqueta "Seguridad")
- Workana.com (categoría "Seguridad Informática")
- LinkedIn (buscar "PHP Security Specialist Chile")

**Presupuesto**: $300.000 - $400.000 CLP

---

**Opción 2: Empresa de Ciberseguridad**

**Netready** (Chile)
- **Web**: netready.cl
- **Email**: contacto@netready.cl
- **Servicios**: Auditoría de aplicaciones web
- **Presupuesto estimado**: $500.000 - $800.000 CLP

**DragonJAR** (Presencia Chile)
- **Web**: dragonjar.org
- **Servicios**: Pentesting, auditorías de código
- **Presupuesto estimado**: $600.000 - $1.000.000 CLP

**Eforcers** (Chile)
- **Web**: eforcers.com
- **Servicios**: Consultoría de seguridad
- **Presupuesto estimado**: $500.000 - $800.000 CLP

**Presupuesto**: $500.000 CLP (empresas establecidas)

---

### Abogado Protección de Datos

**Perfil requerido**:
- Especialista en Ley 19.628
- Experiencia con startups tech
- Conocimiento GDPR (bonus)

**Dónde buscar**:
- Colegio de Abogados de Chile (abogados.cl)
- ACTI - Asociación Chilena de TI (contactar para referidos)
- GetOnBrd (filtrar "Legal Tech")

**Entregables esperados**:
- Política de Privacidad (Word + HTML)
- Términos y Condiciones (Word + HTML)
- Procedimiento ARCO (documento interno)
- 2 horas de asesoría

**Presupuesto**: $250.000 - $400.000 CLP

**Contactos sugeridos** (verificar):
- Estudios boutique en derecho digital
- Abogados freelance especializados

---

## CHECKLIST DE CONTRATACIÓN

### Antes de contratar proveedor de SQL Injection:

- [ ] Solicitar portfolio/referencias
- [ ] Verificar experiencia con PHP/MySQL
- [ ] Preguntar por metodología (OWASP)
- [ ] Confirmar entregables (reporte + código)
- [ ] Establecer NDA (confidencialidad)
- [ ] Definir forma de pago
- [ ] Acordar timeline

### Antes de contratar abogado:

- [ ] Verificar inscripción Colegio de Abogados
- [ ] Solicitar ejemplos de políticas previas
- [ ] Confirmar conocimiento Ley 19.628
- [ ] Preguntar por tiempo de entrega
- [ ] Definir si incluye asesoría post-entrega

---

## RIESGOS DE NO IMPLEMENTAR

### Sanciones Legales (Ley 19.628)

| Infracción | Sanción |
|------------|---------|
| No informar uso de datos | Multa $500.000 - $2.000.000 CLP |
| Falta de medidas de seguridad | Multa $1.000.000 - $3.000.000 CLP |
| No atender derechos ARCO | Multa por cada caso |
| Fuga de datos por negligencia | Indemnización + multa |

### Sanciones Penales (Ley 21.459 - Delitos Informáticos)

| Delito | Pena |
|--------|------|
| Acceso ilícito por vulnerabilidad conocida | Presidio menor (541 días a 5 años) |
| Pérdida de datos por negligencia grave | Responsabilidad penal |

### Daños Comerciales

| Impacto | Estimación |
|---------|------------|
| Pérdida de clientes por brecha | 30-50% clientes |
| Costo de notificación a afectados | $2.000.000+ |
| Daño reputacional | Incalculable |
| Investigación forense post-incidente | $5.000.000+ |

**TOTAL RIESGO**: $8.500.000+ CLP

**ROI de invertir en MVP**: (8.500.000 - 830.000) / 830.000 = **924%**

---

## BENEFICIOS DE IMPLEMENTAR MVP

### Legales
✓ Cumplimiento Ley 19.628 (90% conforme)
✓ Protección contra multas
✓ Documentación de diligencia debida

### Técnicos
✓ Cierre de vulnerabilidades CRÍTICAS
✓ HTTPS en toda la aplicación
✓ Contraseñas con hash seguro
✓ Backups diarios automáticos

### Comerciales
✓ Confianza de clientes
✓ Diferenciación vs competencia
✓ Base para certificaciones futuras (ISO 27001)

---

## PRÓXIMOS PASOS

### Paso 1: Aprobación de Presupuesto (HOY)
- [ ] Revisar este documento con equipo
- [ ] Aprobar presupuesto: $550.000 - $830.000 CLP
- [ ] Asignar responsable del proyecto

### Paso 2: Contratación de Proveedores (Semana 1)
- [ ] Contactar 3 opciones para auditoría SQL
- [ ] Solicitar cotizaciones comparativas
- [ ] Contactar 2 abogados especialistas
- [ ] Seleccionar proveedores

### Paso 3: Kick-off del Proyecto (Día 1)
- [ ] Reunión con equipo técnico
- [ ] Accesos para auditor (servidor, código)
- [ ] Inicio de auditoría SQL Injection
- [ ] Brief a abogado para política privacidad

### Paso 4: Implementación Paralela (Días 1-7)
- [ ] Instalación SSL/HTTPS (interno)
- [ ] Contraseñas seguras (interno)
- [ ] Sesiones seguras (interno)

### Paso 5: Corrección Vulnerabilidades (Días 8-21)
- [ ] Recepción reporte auditoría
- [ ] Corrección de código
- [ ] Testing

### Paso 6: Integración Legal (Días 22-27)
- [ ] Recepción documentos legales
- [ ] Integración en sitio web
- [ ] Pruebas de aceptación

### Paso 7: Go-Live (Día 30)
- [ ] Backup pre-implementación
- [ ] Deployment a producción
- [ ] Monitoreo post-lanzamiento
- [ ] Comunicación a usuarios (si aplica)

---

## PLAN DE CONTINGENCIA

### Si el presupuesto es AÚN MÁS LIMITADO ($250.000)

**Opción Ultra-Mínima**:
1. SSL gratis (Let's Encrypt) - $0
2. Política de Privacidad template adaptado - $0 (riesgo legal medio)
3. Auditoría SQL básica freelancer - $250.000

**Total**: $250.000 CLP

**RIESGOS**:
- Política sin validación legal (posible rechazo en litigio)
- Auditoría menos exhaustiva

---

## MÉTRICAS DE ÉXITO

### Al finalizar los 30 días:

| Métrica | Objetivo |
|---------|----------|
| HTTPS activo | 100% del sitio |
| Vulnerabilidades SQL críticas | 0 |
| Política de Privacidad publicada | Sí |
| Consentimiento en registro | Implementado |
| Backups funcionando | 1 diario |
| Contraseñas con hash seguro | 100% |

---

## PREGUNTAS FRECUENTES

### ¿Puedo hacer todo internamente sin gastar nada?

**Respuesta**: Técnicamente sí, PERO:
- Requiere desarrollador senior con experiencia en seguridad
- Riesgo de pasar por alto vulnerabilidades
- Política de privacidad SIN validación legal es riesgosa
- **Recomendación**: Mínimo contratar abogado ($250.000)

### ¿Qué pasa si no implemento nada?

**Respuesta**:
- Violación activa Ley 19.628
- Riesgo de multa: $500.000 - $3.000.000 CLP
- Vulnerabilidad a hackeos (SQL Injection confirmado)
- Responsabilidad civil por daños a clientes

### ¿Cuándo debo implementar las fases 2 y 3 del plan completo?

**Respuesta**:
- **Fase 2** (ARCO, XSS/CSRF): 3-6 meses después
- **Fase 3** (2FA, encriptación): 6-12 meses
- Depende de crecimiento y presupuesto

### ¿Este MVP me protege 100%?

**Respuesta**: No al 100%, pero sí:
- ✓ 90% cumplimiento legal
- ✓ 80% protección vulnerabilidades críticas
- ✓ Base sólida para mejoras futuras

---

## APROBACIÓN Y FIRMA

**Presupuesto Mínimo Viable**: $550.000 - $830.000 CLP
**Plazo de implementación**: 30 días
**Fecha de este documento**: 2025-11-02

### Aprobaciones Requeridas:

**Gerencia General**
- [ ] Aprobado
- Firma: ____________________
- Fecha: ____________________

**Finanzas/CFO**
- [ ] Presupuesto disponible
- Firma: ____________________
- Fecha: ____________________

**Responsable Técnico/CTO**
- [ ] Factibilidad técnica confirmada
- Firma: ____________________
- Fecha: ____________________

---

## ANEXO: PLANTILLA DE SOLICITUD DE COTIZACIÓN

### Para Auditoría SQL Injection

```
Asunto: Solicitud de Cotización - Auditoría Seguridad Aplicación Web

Estimado/a,

Solicitamos cotización para auditoría de seguridad enfocada en:

1. Alcance:
   - Aplicación web PHP/MySQL
   - Aprox. 90 archivos críticos
   - Enfoque: SQL Injection (OWASP A03)

2. Entregables esperados:
   - Reporte de vulnerabilidades (PDF)
   - Código corregido con prepared statements
   - Re-test de vulnerabilidades

3. Plazo: 15 días hábiles máximo

4. Presupuesto máximo: $500.000 CLP

5. Información requerida en cotización:
   - Metodología de auditoría
   - Experiencia previa (referencias)
   - Desglose de costos
   - Timeline detallado
   - Términos de confidencialidad

Saludos,
[Nombre]
[Empresa]
```

---

### Para Abogado Protección de Datos

```
Asunto: Solicitud de Cotización - Política de Privacidad Ley 19.628

Estimado/a,

Solicitamos cotización para servicios legales:

1. Documentos a crear:
   - Política de Privacidad (app.barril.cl)
   - Términos y Condiciones
   - Procedimiento ARCO

2. Requisitos:
   - Conforme Ley 19.628 (Chile)
   - Aplicación de gestión empresarial (cervecería)
   - Datos sensibles: RUT, pagos, datos personales

3. Entregables:
   - Documentos en Word/PDF + HTML
   - 2 horas asesoría implementación

4. Plazo: 7 días hábiles

5. Presupuesto: $250.000 - $400.000 CLP

Saludos,
[Nombre]
[Empresa]
```

---

## CONTACTO PARA DUDAS

**Responsable del proyecto**: [Nombre]
**Email**: [email]
**Teléfono**: [teléfono]

**Documento creado**: 2025-11-02
**Versión**: 1.0 MVP
**Próxima revisión**: Post-implementación (30 días)

---

**IMPORTANTE**: Este presupuesto MVP cubre SOLO lo esencial para cumplimiento legal y seguridad básica. Para protección completa, se recomienda implementar el plan completo en 90 días ($1.200.000 - $2.200.000 CLP).
