-- =============================================
-- Migración: Sistema de Latas
-- Fecha: 2025-11-29
-- Descripción: Crea las tablas necesarias para el sistema de enlatado
-- =============================================

-- =============================================
-- 1. TABLA: formatos_de_latas
-- Almacena los diferentes formatos de latas disponibles
-- =============================================
CREATE TABLE IF NOT EXISTS `formatos_de_latas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(100) NOT NULL COMMENT 'Nombre descriptivo (ej: Lata 350ml)',
  `volumen_ml` INT(11) NOT NULL COMMENT 'Volumen en mililitros',
  `estado` VARCHAR(50) NOT NULL DEFAULT 'activo',
  `creada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_estado` (`estado`),
  INDEX `idx_volumen_ml` (`volumen_ml`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos iniciales de formatos
INSERT INTO `formatos_de_latas` (`nombre`, `volumen_ml`) VALUES
('Lata 350ml', 350),
('Lata 473ml', 473),
('Lata 500ml', 500);

-- =============================================
-- 2. TABLA: batches_de_latas
-- Registra cada proceso de enlatado desde fermentador o barril
-- =============================================
CREATE TABLE IF NOT EXISTS `batches_de_latas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_batches` INT(11) NOT NULL DEFAULT 0 COMMENT 'Batch de cerveza origen',
  `id_activos` INT(11) NOT NULL DEFAULT 0 COMMENT 'Fermentador origen (si aplica)',
  `id_barriles` INT(11) NOT NULL DEFAULT 0 COMMENT 'Barril origen (si aplica)',
  `id_batches_activos` INT(11) NOT NULL DEFAULT 0 COMMENT 'BatchActivo origen (si aplica)',
  `id_formatos_de_latas` INT(11) NOT NULL COMMENT 'Formato de lata utilizado',
  `id_recetas` INT(11) NOT NULL DEFAULT 0 COMMENT 'Receta del batch',
  `cantidad_de_latas` INT(11) NOT NULL COMMENT 'Cantidad total de latas creadas',
  `volumen_origen_ml` INT(11) NOT NULL DEFAULT 0 COMMENT 'Volumen disponible antes de enlatar (ml)',
  `rendimiento_ml` INT(11) NOT NULL DEFAULT 0 COMMENT 'Volumen efectivamente enlatado (ml)',
  `merma_ml` INT(11) NOT NULL DEFAULT 0 COMMENT 'Volumen perdido (origen - rendimiento)',
  `id_usuarios` INT(11) NOT NULL COMMENT 'Usuario que realizó el enlatado',
  `estado` VARCHAR(50) NOT NULL DEFAULT 'Cargado en planta' COMMENT 'Cargado en planta, Sin latas',
  `creada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_batches` (`id_batches`),
  INDEX `idx_id_activos` (`id_activos`),
  INDEX `idx_id_barriles` (`id_barriles`),
  INDEX `idx_id_formatos_de_latas` (`id_formatos_de_latas`),
  INDEX `idx_estado` (`estado`),
  INDEX `idx_id_recetas` (`id_recetas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 3. TABLA: latas
-- Registro individual de cada lata con trazabilidad completa
-- =============================================
CREATE TABLE IF NOT EXISTS `latas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `id_formatos_de_latas` INT(11) NOT NULL COMMENT 'Formato de la lata',
  `volumen_ml` INT(11) NOT NULL COMMENT 'Volumen de la lata en ml',
  `id_batches_de_latas` INT(11) NOT NULL COMMENT 'Batch de latas al que pertenece',
  `id_batches` INT(11) NOT NULL DEFAULT 0 COMMENT 'Batch de cerveza origen',
  `id_barriles` INT(11) NOT NULL DEFAULT 0 COMMENT 'Barril origen (si aplica)',
  `id_activos` INT(11) NOT NULL DEFAULT 0 COMMENT 'Fermentador origen (si aplica)',
  `id_cajas_de_latas` INT(11) NOT NULL DEFAULT 0 COMMENT 'Caja a la que pertenece (0 si no está en caja)',
  `estado` VARCHAR(50) NOT NULL DEFAULT 'Enlatada' COMMENT 'Enlatada, En caja en planta, En despacho, Entregada',
  `creada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_id_batches_de_latas` (`id_batches_de_latas`),
  INDEX `idx_id_cajas_de_latas` (`id_cajas_de_latas`),
  INDEX `idx_estado` (`estado`),
  INDEX `idx_id_batches` (`id_batches`),
  INDEX `idx_id_formatos_de_latas` (`id_formatos_de_latas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 4. TABLA: cajas_de_latas
-- Cajas que contienen latas (relación one-to-many con latas)
-- =============================================
CREATE TABLE IF NOT EXISTS `cajas_de_latas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `codigo` VARCHAR(50) NOT NULL COMMENT 'Código único de la caja (ej: CL-2025-0001)',
  `id_productos` INT(11) NOT NULL COMMENT 'Producto asociado',
  `cantidad_latas` INT(11) NOT NULL COMMENT 'Cantidad de latas en la caja',
  `id_usuarios` INT(11) NOT NULL DEFAULT 0 COMMENT 'Usuario que creó la caja',
  `estado` VARCHAR(50) NOT NULL DEFAULT 'En planta' COMMENT 'En planta, En despacho, Entregada',
  `creada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizada` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_codigo` (`codigo`),
  INDEX `idx_id_productos` (`id_productos`),
  INDEX `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- 5. MODIFICAR TABLA: productos
-- Agregar campos para configuración de latas
-- =============================================
ALTER TABLE `productos`
ADD COLUMN `id_formatos_de_latas` INT(11) NOT NULL DEFAULT 0 COMMENT 'Formato de lata para este producto',
ADD COLUMN `cantidad_de_latas` INT(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad de latas por caja/pack';

-- Agregar índice para búsquedas por formato
ALTER TABLE `productos`
ADD INDEX `idx_id_formatos_de_latas` (`id_formatos_de_latas`);

-- =============================================
-- FIN DE MIGRACIÓN
-- =============================================
