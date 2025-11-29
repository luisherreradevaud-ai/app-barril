-- =============================================
-- MIGRACION 005: Corregir campo cantidad_latas en cajas_de_envases
-- Fecha: 2025-11-29
-- Descripcion: Renombrar cantidad_latas a cantidad_envases
-- =============================================

-- Renombrar columna cantidad_latas a cantidad_envases
ALTER TABLE cajas_de_envases
CHANGE COLUMN cantidad_latas cantidad_envases INT(11) NOT NULL DEFAULT 0
COMMENT 'Cantidad de envases en la caja';

-- =============================================
-- FIN DE MIGRACION
-- =============================================
