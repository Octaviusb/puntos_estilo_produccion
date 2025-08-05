SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Tabla usuarios
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `puntos` int(11) DEFAULT 0,
  `rol` enum('usuario','admin') DEFAULT 'usuario',
  `estado` tinyint(1) DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla productos
CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `puntos` int(11) NOT NULL,
  `destacado` tinyint(1) DEFAULT 0,
  `stock` int(11) DEFAULT 0,
  `categoria` varchar(50) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla aliados
CREATE TABLE `aliados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `descuento` varchar(50) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla canjes
CREATE TABLE `canjes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `puntos_canjeados` int(11) NOT NULL,
  `fecha_canje` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','aprobado','rechazado','entregado') DEFAULT 'pendiente',
  `notas` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla transacciones_puntos
CREATE TABLE `transacciones_puntos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('carga','descuento','canje','bono') NOT NULL,
  `puntos` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla codigos_otp
CREATE TABLE `codigos_otp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `codigo` varchar(6) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_expiracion` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla bonos
CREATE TABLE `bonos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `puntos_bono` int(11) NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `max_usos` int(11) DEFAULT NULL,
  `usos_actuales` int(11) DEFAULT 0,
  `tipo` enum('registro','referido','compra','promocional') DEFAULT 'promocional',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla bonos_usuarios
CREATE TABLE `bonos_usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `bono_id` int(11) NOT NULL,
  `puntos_otorgados` int(11) NOT NULL,
  `fecha_otorgado` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_vencimiento` date DEFAULT NULL,
  `usado` tinyint(1) DEFAULT 0,
  `fecha_uso` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla notificaciones
CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('vencimiento','carga','descuento','canje','general') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla mensajes
CREATE TABLE `mensajes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remitente_id` int(11) DEFAULT NULL,
  `destinatario_id` int(11) NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp(),
  `leido` tinyint(1) DEFAULT 0,
  `fecha_lectura` timestamp NULL DEFAULT NULL,
  `tipo` enum('sistema','usuario','admin') DEFAULT 'usuario',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla mensajes_contacto
CREATE TABLE `mensajes_contacto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `celular` varchar(20) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('nuevo','leido','respondido') DEFAULT 'nuevo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla configuracion_sistema
CREATE TABLE `configuracion_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla password_resets
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `used` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla logs_sistema
CREATE TABLE `logs_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nivel` enum('INFO','WARNING','ERROR','DEBUG') NOT NULL,
  `mensaje` text NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar datos de ejemplo
INSERT INTO `aliados` (`nombre`, `descripcion`, `imagen`, `categoria`, `descuento`, `direccion`, `telefono`, `email`, `estado`, `fecha_creacion`) VALUES
('Cine Colombia', 'Disfruta de las mejores películas con descuentos especiales', 'img/boletas.jpg', 'Entretenimiento', '20% descuento', 'Centro Comercial Plaza Central', '555-0101', 'info@cinecolombia.com', 1, NOW()),
('Restaurante El Buen Sabor', 'La mejor comida tradicional con beneficios exclusivos', 'img/restaurante.jpg', 'Gastronomía', '15% descuento', 'Calle 15 #23-45', '555-0202', 'contacto@buensabor.com', 1, NOW()),
('Gimnasio Fitness Pro', 'Mantén tu salud con descuentos en membresías', 'img/gimnasio.jpg', 'Salud', '25% descuento', 'Avenida Principal #67-89', '555-0303', 'info@fitnesspro.com', 1, NOW()),
('Tienda de Ropa Moda Express', 'Las últimas tendencias con precios especiales', 'img/ropa.jpg', 'Moda', '30% descuento', 'Centro Comercial Galerías', '555-0404', 'ventas@modaexpress.com', 1, NOW());

INSERT INTO `productos` (`nombre`, `descripcion`, `imagen`, `puntos`, `destacado`, `stock`, `categoria`, `estado`, `fecha_creacion`) VALUES
('Camiseta Puntos Estilo', 'Camiseta oficial con logo de Puntos Estilo', 'img/camiseta.jpg', 500, 1, 50, 'Ropa', 1, NOW()),
('Taza Personalizada', 'Taza con diseño exclusivo de Puntos Estilo', 'img/taza.jpg', 300, 0, 100, 'Accesorios', 1, NOW()),
('Descuento 20%', 'Descuento del 20% en tu próxima compra', 'img/descuento.jpg', 200, 1, 999, 'Descuentos', 1, NOW()),
('Auriculares Bluetooth', 'Auriculares inalámbricos de alta calidad', 'img/auriculares.jpg', 800, 1, 25, 'Tecnología', 1, NOW()),
('Gorra Deportiva', 'Gorra con bordado del logo', 'img/gorra.jpg', 350, 0, 75, 'Accesorios', 1, NOW());

INSERT INTO `configuracion_sistema` (`clave`, `valor`, `descripcion`, `fecha_actualizacion`) VALUES
('email_notificaciones', 'true', 'Habilitar notificaciones por email', NOW()),
('dias_vencimiento_puntos', '365', 'Días antes del vencimiento para notificar', NOW()),
('stock_minimo_alerta', '5', 'Stock mínimo para generar alertas', NOW()),
('puntos_por_registro', '100', 'Puntos otorgados al registrarse', NOW()),
('max_puntos_usuario', '100000', 'Máximo de puntos por usuario', NOW());

COMMIT;