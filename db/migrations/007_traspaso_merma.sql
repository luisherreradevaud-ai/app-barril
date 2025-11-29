-- =============================================
-- MIGRACION 007: Agregar campo merma a traspasos
-- Fecha: 2025-11-29
-- Descripcion: Agrega campo merma_litros a la tabla batches_traspasos
--              para registrar pérdidas durante el traspaso
-- =============================================

-- Agregar columna merma_litros
ALTER TABLE batches_traspasos
ADD COLUMN merma_litros DECIMAL(10,2) NOT NULL DEFAULT 0
COMMENT 'Litros perdidos durante el traspaso'
AFTER cantidad;

-- =============================================
-- FIN DE MIGRACION
-- =============================================
