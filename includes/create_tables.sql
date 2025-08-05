-- Script para crear tablas faltantes en Puntos Estilo
-- Ejecutar este script para completar la estructura de la base de datos

-- Tabla de reset de contraseñas
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    INDEX idx_email (email),
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
);

-- Tabla de canjes
CREATE TABLE IF NOT EXISTS canjes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    producto_id INT NOT NULL,
    puntos_canjeados INT NOT NULL,
    fecha_canje TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'aprobado', 'rechazado', 'entregado') DEFAULT 'pendiente',
    notas TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (fecha_canje),
    INDEX idx_estado (estado)
);

-- Tabla de mensajes
CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    remitente_id INT,
    destinatario_id INT NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leido BOOLEAN DEFAULT FALSE,
    fecha_lectura TIMESTAMP NULL,
    tipo ENUM('sistema', 'usuario', 'admin') DEFAULT 'usuario',
    FOREIGN KEY (remitente_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (destinatario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_destinatario (destinatario_id),
    INDEX idx_fecha_envio (fecha_envio),
    INDEX idx_leido (leido)
);

-- Tabla de bonos
CREATE TABLE IF NOT EXISTS bonos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    puntos_bono INT NOT NULL,
    codigo VARCHAR(50) UNIQUE,
    fecha_inicio DATE,
    fecha_fin DATE,
    activo BOOLEAN DEFAULT TRUE,
    max_usos INT DEFAULT NULL,
    usos_actuales INT DEFAULT 0,
    tipo ENUM('registro', 'referido', 'compra', 'promocional') DEFAULT 'promocional',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo),
    INDEX idx_activo (activo),
    INDEX idx_fechas (fecha_inicio, fecha_fin)
);

-- Tabla de bonos de usuarios
CREATE TABLE IF NOT EXISTS bonos_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    bono_id INT NOT NULL,
    puntos_otorgados INT NOT NULL,
    fecha_otorgado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento DATE,
    usado BOOLEAN DEFAULT FALSE,
    fecha_uso TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (bono_id) REFERENCES bonos(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_bono (bono_id),
    INDEX idx_fecha_vencimiento (fecha_vencimiento),
    INDEX idx_usado (usado)
);

-- Insertar usuario administrador si no existe
INSERT IGNORE INTO usuarios (nombre, correo, contraseña, rol, puntos, fecha_registro) 
VALUES (
    'Administrador',
    'admin@puntosestilo.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'admin',
    0,
    NOW()
);

-- Crear índices adicionales para mejorar rendimiento
CREATE INDEX IF NOT EXISTS idx_usuarios_correo ON usuarios(correo);
CREATE INDEX IF NOT EXISTS idx_usuarios_rol ON usuarios(rol);
CREATE INDEX IF NOT EXISTS idx_transacciones_usuario ON transacciones(usuario_id);
CREATE INDEX IF NOT EXISTS idx_transacciones_fecha ON transacciones(fecha);
CREATE INDEX IF NOT EXISTS idx_referidos_referidor ON referidos(usuario_referidor_id);
CREATE INDEX IF NOT EXISTS idx_referidos_referido ON referidos(usuario_referido_id);

-- Comentarios sobre las tablas creadas
-- password_resets: Para recuperación de contraseñas
-- canjes: Para canjes de productos con puntos
-- mensajes: Sistema de mensajería interna
-- bonos: Catálogo de bonos disponibles
-- bonos_usuarios: Bonos asignados a usuarios específicos 