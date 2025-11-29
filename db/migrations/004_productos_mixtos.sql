-- =============================================
-- Migracion: Soporte para Productos Mixtos
-- Fecha: 2025-11-29
-- Descripcion: Agrega campo es_mixto para productos que aceptan multiples recetas
--              (manteniendo mismo formato de envase)
-- =============================================

-- =============================================
-- 1. AGREGAR CAMPO es_mixto A productos
-- =============================================

ALTER TABLE productos
ADD COLUMN es_mixto TINYINT(1) NOT NULL DEFAULT 0
COMMENT '1 = producto mixto que acepta multiples recetas (mismo formato)'
AFTER tipo_envase;

-- Agregar indice para consultas
ALTER TABLE productos
ADD INDEX idx_es_mixto (es_mixto);

-- =============================================
-- FIN DE MIGRACION
-- =============================================
