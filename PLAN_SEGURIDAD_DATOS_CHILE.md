# Plan de Acción: Seguridad y Cumplimiento de Normativas de Datos en Chile

## 1. MARCO LEGAL Y NORMATIVO

### 1.1 Ley 19.628 de Protección de la Vida Privada (LPVP)
- **Ámbito**: Regula el tratamiento de datos personales en Chile
- **Alcance**: Toda persona natural o jurídica que trate datos personales
- **Principios clave**:
  - Consentimiento del titular
  - Finalidad del tratamiento
  - Proporcionalidad
  - Calidad de los datos
  - Seguridad

### 1.2 Ley 21.459 de Delitos Informáticos
- Penaliza el acceso ilícito a sistemas informáticos
- Establece sanciones por vulneraciones de datos

### 1.3 Normativa SII (Servicio de Impuestos Internos)
- Protección de datos tributarios y comerciales
- Facturación electrónica (DTE)

---

## 2. ANÁLISIS DE DATOS SENSIBLES EN LA APLICACIÓN

### 2.1 Datos Personales Identificados
Según el esquema de base de datos analizado:

#### **Tabla `usuarios`**
- RUT/DNI (datos sensibles en Chile)
- Nombre y apellido
- Email
- Teléfono
- Contraseñas (hash requerido)
- Datos de autenticación

#### **Tabla `clientes`**
- RUT empresa
- Razón Social (RznSoc)
- Email y teléfono
- Dirección (Dir)
- Comuna (Cmna)
- Giro comercial

#### **Tabla `transacciones` y `pagos`**
- Datos financieros
- Tokens de pago
- Montos de transacciones
- Información de Transbank

#### **Tabla `entregas`**
- Datos de ubicación
- Información comercial

### 2.2 Nivel de Riesgo
- **ALTO**: Datos financieros, contraseñas, RUT
- **MEDIO**: Información comercial, emails, teléfonos
- **BAJO**: Datos de productos, inventario

---

## 3. MEDIDAS DE SEGURIDAD TÉCNICAS OBLIGATORIAS

### 3.1 Seguridad de Contraseñas
**Estado Actual**: Necesita verificación
**Acciones Requeridas**:
- [ ] Implementar hashing con algoritmo seguro (bcrypt, Argon2, o password_hash de PHP)
- [ ] Verificar que NO se almacenen contraseñas en texto plano
- [ ] Implementar política de contraseñas robustas:
  - Mínimo 8 caracteres
  - Combinación de mayúsculas, minúsculas, números y símbolos
  - Expiración periódica (recomendado: 90 días)
- [ ] Implementar protección contra fuerza bruta (límite de intentos)
- [ ] Sistema de recuperación segura de contraseñas

**Archivo a revisar**: `/php/registro.php`, `/login.php`

### 3.2 Encriptación de Datos Sensibles
**Acciones Requeridas**:
- [ ] Implementar HTTPS/TLS en todo el sitio
  - Certificado SSL válido
  - Redirección automática HTTP → HTTPS
  - Verificar archivo `.htaccess`
- [ ] Encriptar datos sensibles en base de datos:
  - Tokens de pago
  - Información financiera
  - RUT (considerar)
- [ ] Usar prepared statements en todas las consultas SQL (prevención SQL injection)

**Archivos críticos**:
- `/php/classes/Transaccion.php`
- Todos los archivos en `/ajax/`

### 3.3 Protección de Base de Datos
**Acciones Requeridas**:
- [ ] Verificar credenciales de base de datos NO estén en archivos públicos
- [ ] Usar archivos de configuración fuera del document root
- [ ] Implementar privilegios mínimos para usuarios de BD
- [ ] Backups automáticos encriptados
- [ ] Rotación de credenciales periódica

### 3.4 Control de Acceso y Autenticación
**Existente**: Sistema de permisos (`permisos`, `usuarios_niveles`)
**Acciones Requeridas**:
- [ ] Implementar sesiones seguras:
  ```php
  session_set_cookie_params([
      'lifetime' => 0,
      'path' => '/',
      'domain' => 'app.barril.cl',
      'secure' => true,
      'httponly' => true,
      'samesite' => 'Strict'
  ]);
  ```
- [ ] Timeout de sesión automático (15-30 minutos inactividad)
- [ ] Validación de roles en cada solicitud
- [ ] Regenerar session_id después del login
- [ ] Implementar autenticación de dos factores (2FA) para usuarios administrativos

### 3.5 Protección contra Vulnerabilidades Web
**Acciones Requeridas**:
- [ ] **XSS (Cross-Site Scripting)**:
  - Sanitizar todas las salidas con `htmlspecialchars()`
  - Validar y escapar datos de usuario

- [ ] **CSRF (Cross-Site Request Forgery)**:
  - Implementar tokens CSRF en formularios
  - Validar origen de peticiones

- [ ] **SQL Injection**:
  - Usar PDO con prepared statements (OBLIGATORIO)
  - Validar tipos de datos

- [ ] **File Upload Security**:
  - Validar tipos MIME
  - Restringir extensiones permitidas
  - Almacenar archivos fuera del document root
  - Escaneo antivirus de archivos subidos

**Archivos a auditar**:
- Todos los archivos PHP en `/ajax/`
- `/templates/` (archivos de formularios)
- `/php/registro.php`

### 3.6 Logging y Auditoría
**Existente**: Tablas `historial`, `errores`
**Acciones Requeridas**:
- [ ] Registrar todos los accesos a datos sensibles
- [ ] Log de modificaciones de datos personales
- [ ] Registro de intentos de acceso fallidos
- [ ] Conservar logs por 1 año mínimo
- [ ] Implementar monitoreo de anomalías
- [ ] NO registrar datos sensibles en logs (contraseñas, tokens completos)

**Estructura recomendada**:
```sql
ALTER TABLE historial ADD COLUMN ip_address VARCHAR(45);
ALTER TABLE historial ADD COLUMN user_agent VARCHAR(255);
ALTER TABLE historial ADD COLUMN tipo_accion VARCHAR(50);
```

---

## 4. MEDIDAS DE SEGURIDAD ORGANIZACIONALES

### 4.1 Política de Privacidad y Términos de Uso
**Acciones Requeridas**:
- [ ] Crear documento de Política de Privacidad conforme a Ley 19.628
- [ ] Incluir:
  - Qué datos se recopilan
  - Finalidad del tratamiento
  - Plazo de conservación
  - Derechos de los titulares (ARCO)
  - Medidas de seguridad implementadas
  - Datos de contacto del responsable
- [ ] Obtener consentimiento explícito de usuarios
- [ ] Implementar checkbox de aceptación en registro

### 4.2 Derechos ARCO (Acceso, Rectificación, Cancelación, Oposición)
**Acciones Requeridas**:
- [ ] Implementar mecanismo para que usuarios puedan:
  - **Acceder** a sus datos personales
  - **Rectificar** datos incorrectos
  - **Cancelar** su cuenta
  - **Oponerse** al tratamiento
- [ ] Plazo máximo de respuesta: 2 días hábiles (Ley 19.628)
- [ ] Crear formulario web o email dedicado

### 4.3 Retención y Eliminación de Datos
**Acciones Requeridas**:
- [ ] Definir política de retención por tipo de dato:
  - **Datos tributarios**: 6 años (según SII)
  - **Datos transaccionales**: 5 años (recomendado)
  - **Datos de usuarios inactivos**: 2 años
- [ ] Implementar eliminación automática o manual programada
- [ ] Anonimización de datos para análisis estadístico

### 4.4 Transferencia de Datos
**Situación**: Integración con Transbank
**Acciones Requeridas**:
- [ ] Verificar que Transbank cumpla con estándares PCI-DSS
- [ ] No almacenar datos completos de tarjetas de crédito
- [ ] Usar solo tokens proporcionados por Transbank
- [ ] Revisar contrato de procesamiento de datos con Transbank

### 4.5 Capacitación de Personal
**Acciones Requeridas**:
- [ ] Capacitar a desarrolladores en:
  - Desarrollo seguro (OWASP Top 10)
  - Normativa chilena de protección de datos
  - Manejo de incidentes de seguridad
- [ ] Capacitar a personal administrativo en:
  - Manejo confidencial de datos
  - Detección de phishing
  - Procedimientos de seguridad

---

## 5. PLAN DE RESPUESTA A INCIDENTES

### 5.1 Protocolo de Notificación de Brechas
**Acciones Requeridas**:
- [ ] Crear procedimiento documentado:
  1. Detección de brecha
  2. Contención inmediata
  3. Evaluación de impacto
  4. Notificación a afectados (en 48-72 horas)
  5. Reporte a autoridades (si aplica)
  6. Implementación de medidas correctivas
- [ ] Designar responsable de seguridad de datos
- [ ] Mantener registro de incidentes

### 5.2 Backup y Recuperación
**Acciones Requeridas**:
- [ ] Implementar backup automático diario
- [ ] Almacenar backups en ubicación separada (offsite)
- [ ] Encriptar backups
- [ ] Probar restauración mensualmente
- [ ] Conservar backups por 90 días mínimo

---

## 6. CONFIGURACIÓN SEGURA DE SERVIDOR

### 6.1 Servidor Web (Apache/XAMPP)
**Acciones Requeridas**:
- [ ] Actualizar a última versión estable de PHP (mínimo 7.4, recomendado 8.x)
- [ ] Deshabilitar funciones peligrosas en `php.ini`:
  ```ini
  disable_functions = exec,passthru,shell_exec,system,proc_open,popen
  expose_php = Off
  display_errors = Off
  log_errors = On
  ```
- [ ] Configurar límites:
  ```ini
  upload_max_filesize = 2M
  post_max_size = 8M
  max_execution_time = 30
  memory_limit = 128M
  ```
- [ ] Remover archivos de ejemplo y directorios innecesarios
- [ ] Configurar permisos de archivos correctamente (644 archivos, 755 directorios)

### 6.2 Configuración .htaccess
**Archivo actual**: `/.htaccess`
**Acciones Requeridas**:
- [ ] Agregar headers de seguridad:
  ```apache
  # Seguridad HTTP Headers
  Header always set X-Frame-Options "SAMEORIGIN"
  Header always set X-Content-Type-Options "nosniff"
  Header always set X-XSS-Protection "1; mode=block"
  Header always set Referrer-Policy "strict-origin-when-cross-origin"
  Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"

  # Content Security Policy
  Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';"

  # HTTPS Redirect
  RewriteEngine On
  RewriteCond %{HTTPS} off
  RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

  # Proteger archivos sensibles
  <FilesMatch "^\.">
    Order allow,deny
    Deny from all
  </FilesMatch>

  <FilesMatch "\.(sql|md|log|ini|conf)$">
    Order allow,deny
    Deny from all
  </FilesMatch>
  ```

### 6.3 Protección de Directorios Sensibles
**Acciones Requeridas**:
- [ ] Mover `/context/` fuera del document root
- [ ] Proteger `/php/classes/` con .htaccess
- [ ] Restringir acceso a `/vendor_php/`
- [ ] Eliminar archivos innecesarios de CKEditor samples

---

## 7. CUMPLIMIENTO ESPECÍFICO PARA CHILE

### 7.1 Facturación Electrónica (DTE)
**Tabla**: `dte`
**Acciones Requeridas**:
- [ ] Verificar conexión segura con SII
- [ ] Almacenar XMLs de DTE encriptados
- [ ] Conservar documentos por 6 años
- [ ] Implementar firma electrónica válida
- [ ] Backup redundante de documentos tributarios

### 7.2 Protección de RUT
**Acciones Requeridas**:
- [ ] Validar formato de RUT en frontend y backend
- [ ] Considerar encriptación de RUT en BD
- [ ] Restringir acceso a consultas masivas de RUT
- [ ] Implementar rate limiting en búsquedas

### 7.3 Registro ante el Servicio de Registro Civil (SRCI)
**Nota**: Actualmente NO es obligatorio en Chile, pero se recomienda:
- [ ] Evaluar inscripción voluntaria de base de datos
- [ ] Mantener inventario actualizado de bases de datos

---

## 8. CRONOGRAMA DE IMPLEMENTACIÓN

### Fase 1: CRÍTICO (0-30 días)
**Prioridad MÁXIMA**:
1. [ ] Auditoría de almacenamiento de contraseñas
2. [ ] Implementar HTTPS/SSL
3. [ ] Revisar y corregir vulnerabilidades SQL Injection
4. [ ] Proteger credenciales de base de datos
5. [ ] Implementar sesiones seguras
6. [ ] Configurar headers de seguridad en .htaccess

### Fase 2: ALTA PRIORIDAD (30-60 días)
7. [ ] Implementar política de privacidad
8. [ ] Sistema de consentimiento
9. [ ] Implementar tokens CSRF
10. [ ] Sanitización XSS global
11. [ ] Sistema de logging completo
12. [ ] Backups automáticos encriptados
13. [ ] Actualizar versión de PHP

### Fase 3: MEDIA PRIORIDAD (60-90 días)
14. [ ] Implementar derechos ARCO
15. [ ] Sistema de 2FA para administradores
16. [ ] Encriptación de datos sensibles en BD
17. [ ] Plan de respuesta a incidentes documentado
18. [ ] Capacitación de equipo
19. [ ] Política de retención y eliminación

### Fase 4: MEJORA CONTINUA (90+ días)
20. [ ] Auditoría de seguridad externa
21. [ ] Penetration testing
22. [ ] Certificación ISO 27001 (opcional)
23. [ ] Monitoreo continuo de seguridad
24. [ ] Revisión trimestral de políticas

---

## 9. CHECKLIST DE VERIFICACIÓN

### Seguridad Técnica
- [ ] HTTPS implementado y forzado
- [ ] Contraseñas con hash seguro (bcrypt/Argon2)
- [ ] Prepared statements en todas las consultas
- [ ] Validación de entrada en todos los formularios
- [ ] Escape de salida para prevenir XSS
- [ ] Tokens CSRF en formularios
- [ ] Sesiones seguras configuradas
- [ ] Headers de seguridad HTTP configurados
- [ ] Archivos sensibles protegidos
- [ ] Logs de auditoría funcionando
- [ ] Backups automáticos configurados
- [ ] Plan de recuperación probado

### Cumplimiento Legal
- [ ] Política de privacidad publicada
- [ ] Términos de uso actualizados
- [ ] Consentimiento explícito implementado
- [ ] Derechos ARCO habilitados
- [ ] Política de retención definida
- [ ] Responsable de datos designado
- [ ] Procedimiento de notificación de brechas
- [ ] Contratos con procesadores de datos (Transbank)

### Organizacional
- [ ] Equipo capacitado
- [ ] Documentación actualizada
- [ ] Responsabilidades asignadas
- [ ] Presupuesto de seguridad definido
- [ ] Revisiones periódicas programadas

---

## 10. RECURSOS Y CONTACTOS

### Autoridades Relevantes
- **Consejo para la Transparencia**: https://www.consejotransparencia.cl/
- **Servicio de Impuestos Internos (SII)**: https://www.sii.cl/
- **Sernac** (protección consumidor): https://www.sernac.cl/

### Normativa Legal
- Ley 19.628: https://www.bcn.cl/leychile/navegar?idNorma=141599
- Ley 21.459 (Delitos Informáticos): https://www.bcn.cl/leychile/navegar?idNorma=1179135

### Estándares Técnicos Recomendados
- OWASP Top 10: https://owasp.org/www-project-top-ten/
- PCI-DSS (pagos): https://www.pcisecuritystandards.org/
- ISO 27001 (opcional): Gestión de seguridad de la información

---

## NOTAS FINALES

Este plan de acción está diseñado específicamente para cumplir con la normativa chilena de protección de datos. La implementación debe ser supervisada por personal técnico calificado y revisada por asesoría legal especializada en protección de datos.

**Responsabilidad**: El incumplimiento de la Ley 19.628 puede resultar en:
- Multas de hasta 50 UTM (aprox. $3.000.000 CLP)
- Indemnizaciones por daños y perjuicios
- Responsabilidad penal según Ley 21.459

**Documento creado**: 2025-11-02
**Próxima revisión**: Trimestral o ante cambios legislativos
