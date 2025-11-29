-- =============================================
-- MIGRACION 006: Agregar cliente destino a despachos
-- Fecha: 2025-11-29
-- Descripcion: Agrega campo id_clientes a la tabla despachos
--              para saber el destino del despacho
-- =============================================

-- Agregar columna id_clientes
ALTER TABLE despachos
ADD COLUMN id_clientes INT(11) NOT NULL DEFAULT 0
COMMENT 'Cliente destino del despacho'
AFTER id_usuarios_repartidor;

-- Agregar indice para busquedas por cliente
ALTER TABLE despachos
ADD INDEX idx_id_clientes (id_clientes);

-- =============================================
-- FIN DE MIGRACION
-- =============================================
