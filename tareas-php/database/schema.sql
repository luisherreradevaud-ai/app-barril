-- =====================================================
-- ESQUEMA DE BASE DE DATOS - SISTEMA KANBAN
-- =====================================================

-- Tabla de Tableros (Boards)
CREATE TABLE IF NOT EXISTS `kanban_tableros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `id_entidad` varchar(100) NOT NULL,
  `orden` int(11) DEFAULT 0,
  `creada` datetime NOT NULL,
  `actualizada` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_entidad` (`id_entidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Columnas (Columns/Lists)
CREATE TABLE IF NOT EXISTS `kanban_columnas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `id_kanban_tableros` int(11) NOT NULL,
  `orden` int(11) DEFAULT 0,
  `color` varchar(7) DEFAULT '#6A1693',
  `creada` datetime NOT NULL,
  `actualizada` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_kanban_tableros` (`id_kanban_tableros`),
  KEY `orden` (`orden`),
  CONSTRAINT `fk_kanban_columnas_tableros` FOREIGN KEY (`id_kanban_tableros`) REFERENCES `kanban_tableros` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Tareas Kanban (Cards)
CREATE TABLE IF NOT EXISTS `kanban_tareas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `id_kanban_columnas` int(11) NOT NULL,
  `orden` int(11) DEFAULT 0,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `recordatorio_vencimiento` varchar(50) DEFAULT NULL,
  `checklist` text,
  `links` text,
  `estado` varchar(50) DEFAULT 'Pendiente',
  `creada` datetime NOT NULL,
  `actualizada` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_kanban_columnas` (`id_kanban_columnas`),
  KEY `orden` (`orden`),
  KEY `estado` (`estado`),
  KEY `fecha_vencimiento` (`fecha_vencimiento`),
  CONSTRAINT `fk_kanban_tareas_columnas` FOREIGN KEY (`id_kanban_columnas`) REFERENCES `kanban_columnas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de relación Tareas-Usuarios (many-to-many)
CREATE TABLE IF NOT EXISTS `kanban_tareas_usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_kanban_tareas` int(11) NOT NULL,
  `id_usuarios` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_kanban_tareas` (`id_kanban_tareas`),
  KEY `id_usuarios` (`id_usuarios`),
  CONSTRAINT `fk_kanban_tareas_usuarios_tareas` FOREIGN KEY (`id_kanban_tareas`) REFERENCES `kanban_tareas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kanban_tareas_usuarios_usuarios` FOREIGN KEY (`id_usuarios`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Etiquetas Kanban
CREATE TABLE IF NOT EXISTS `kanban_etiquetas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `codigo_hex` varchar(7) DEFAULT '#6A1693',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de relación Tareas-Etiquetas (many-to-many)
CREATE TABLE IF NOT EXISTS `kanban_tareas_etiquetas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_kanban_tareas` int(11) NOT NULL,
  `id_kanban_etiquetas` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_kanban_tareas` (`id_kanban_tareas`),
  KEY `id_kanban_etiquetas` (`id_kanban_etiquetas`),
  CONSTRAINT `fk_kanban_tareas_etiquetas_tareas` FOREIGN KEY (`id_kanban_tareas`) REFERENCES `kanban_tareas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kanban_tareas_etiquetas_etiquetas` FOREIGN KEY (`id_kanban_etiquetas`) REFERENCES `kanban_etiquetas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de relación Media-Tareas (para archivos adjuntos)
CREATE TABLE IF NOT EXISTS `media_kanban_tareas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_media` int(11) NOT NULL,
  `id_kanban_tareas` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_media` (`id_media`),
  KEY `id_kanban_tareas` (`id_kanban_tareas`),
  CONSTRAINT `fk_media_kanban_tareas_media` FOREIGN KEY (`id_media`) REFERENCES `media` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_media_kanban_tareas_tareas` FOREIGN KEY (`id_kanban_tareas`) REFERENCES `kanban_tareas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar etiquetas de ejemplo
INSERT INTO `kanban_etiquetas` (`nombre`, `codigo_hex`) VALUES
('Urgente', '#DC3545'),
('En Progreso', '#FFC107'),
('Completado', '#28A745'),
('Bloqueado', '#6C757D'),
('Revisión', '#17A2B8');

-- Insertar datos de ejemplo (opcional)
-- Tablero de ejemplo
INSERT INTO `kanban_tableros` (`nombre`, `descripcion`, `id_entidad`, `orden`, `creada`, `actualizada`) VALUES
('Mi Primer Tablero', 'Tablero de ejemplo', 'ejemplo', 1, NOW(), NOW());

-- Columnas de ejemplo (para el tablero con id=1)
INSERT INTO `kanban_columnas` (`nombre`, `id_kanban_tableros`, `orden`, `color`, `creada`, `actualizada`) VALUES
('Por Hacer', 1, 1, '#6C757D', NOW(), NOW()),
('En Progreso', 1, 2, '#FFC107', NOW(), NOW()),
('Completado', 1, 3, '#28A745', NOW(), NOW());
