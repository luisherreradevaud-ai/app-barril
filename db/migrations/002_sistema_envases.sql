-- =============================================
-- Migración: Sistema de Envases (Latas + Botellas)
-- Fecha: 2025-11-29
-- Descripción: Generaliza el sistema de latas para soportar múltiples tipos de envases
-- =============================================

-- =============================================
-- 1. RENOMBRAR TABLAS
-- =============================================

RENAME TABLE formatos_de_latas TO formatos_de_envases;
RENAME TABLE batches_de_latas TO batches_de_envases;
RENAME TABLE latas TO envases;
RENAME TABLE cajas_de_latas TO cajas_de_envases;

-- =============================================
-- 2. AGREGAR CAMPO TIPO A formatos_de_envases
-- =============================================

ALTER TABLE formatos_de_envases
ADD COLUMN tipo VARCHAR(20) NOT NULL DEFAULT 'Lata' COMMENT 'Tipo de envase: Lata, Botella' AFTER nombre;

-- Agregar índice para búsquedas por tipo
ALTER TABLE formatos_de_envases
ADD INDEX idx_tipo (tipo);

-- =============================================
-- 3. AGREGAR CAMPO TIPO A batches_de_envases
-- =============================================

ALTER TABLE batches_de_envases
ADD COLUMN tipo VARCHAR(20) NOT NULL DEFAULT 'Lata' COMMENT 'Tipo de envase: Lata, Botella' AFTER id;

-- Renombrar columna FK
ALTER TABLE batches_de_envases
CHANGE id_formatos_de_latas id_formatos_de_envases INT(11) NOT NULL COMMENT 'Formato de envase utilizado';

-- Renombrar columna cantidad
ALTER TABLE batches_de_envases
CHANGE cantidad_de_latas cantidad_de_envases INT(11) NOT NULL COMMENT 'Cantidad total de envases creados';

-- Agregar índice para búsquedas por tipo
ALTER TABLE batches_de_envases
ADD INDEX idx_tipo (tipo);

-- =============================================
-- 4. MODIFICAR TABLA envases
-- =============================================

ALTER TABLE envases
CHANGE id_formatos_de_latas id_formatos_de_envases INT(11) NOT NULL COMMENT 'Formato del envase',
CHANGE id_batches_de_latas id_batches_de_envases INT(11) NOT NULL COMMENT 'Batch de envases al que pertenece',
CHANGE id_cajas_de_latas id_cajas_de_envases INT(11) NOT NULL DEFAULT 0 COMMENT 'Caja a la que pertenece (0 si no está en caja)';

-- =============================================
-- 5. MODIFICAR TABLA productos
-- =============================================

ALTER TABLE productos
CHANGE id_formatos_de_latas id_formatos_de_envases INT(11) NOT NULL DEFAULT 0 COMMENT 'Formato de envase para este producto',
CHANGE cantidad_de_latas cantidad_de_envases INT(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad de envases por caja/pack';

-- Agregar campo tipo de envase
ALTER TABLE productos
ADD COLUMN tipo_envase VARCHAR(20) NOT NULL DEFAULT 'Lata' COMMENT 'Tipo de envase: Lata, Botella' AFTER cantidad_de_envases;

-- Agregar índice
ALTER TABLE productos
ADD INDEX idx_tipo_envase (tipo_envase);

-- =============================================
-- 6. DATOS INICIALES DE FORMATOS DE BOTELLAS
-- =============================================

INSERT INTO formatos_de_envases (nombre, tipo, volumen_ml, estado) VALUES
('Botella 330ml', 'Botella', 330, 'activo'),
('Botella 500ml', 'Botella', 500, 'activo'),
('Botella 750ml', 'Botella', 750, 'activo');

-- =============================================
-- FIN DE MIGRACIÓN
-- =============================================
