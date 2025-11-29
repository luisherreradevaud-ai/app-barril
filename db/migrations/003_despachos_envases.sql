-- =============================================
-- Migracion: Integracion Sistema de Envases con Despachos
-- Fecha: 2025-11-29
-- Descripcion: Agrega campos para vincular CajaDeEnvases con Despachos y Entregas
-- =============================================

-- =============================================
-- 1. AGREGAR CAMPO A despachos_productos
-- =============================================

ALTER TABLE despachos_productos
ADD COLUMN id_cajas_de_envases INT(11) NOT NULL DEFAULT 0
COMMENT 'Caja de envases asociada (si tipo=CajaEnvases)'
AFTER id_productos;

-- Agregar indice para busquedas
ALTER TABLE despachos_productos
ADD INDEX idx_id_cajas_de_envases (id_cajas_de_envases);

-- =============================================
-- 2. AGREGAR CAMPO A entregas_productos
-- =============================================

ALTER TABLE entregas_productos
ADD COLUMN id_cajas_de_envases INT(11) NOT NULL DEFAULT 0
COMMENT 'Caja de envases entregada'
AFTER id_barriles;

-- Agregar indice para busquedas
ALTER TABLE entregas_productos
ADD INDEX idx_id_cajas_de_envases (id_cajas_de_envases);

-- =============================================
-- FIN DE MIGRACION
-- =============================================
