-- Tabla de logros
CREATE TABLE IF NOT EXISTS logros (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    puntos INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de puntos de usuarios
CREATE TABLE IF NOT EXISTS puntos_usuario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    logro_id INT,
    puntos INT NOT NULL,
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP,
    descripcion TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (logro_id) REFERENCES logros(id)
);

-- Tabla de beneficios
CREATE TABLE IF NOT EXISTS beneficios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    puntos_requeridos INT NOT NULL,
    stock INT DEFAULT -1, -- -1 significa stock ilimitado
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de canjes
CREATE TABLE IF NOT EXISTS canjes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    beneficio_id INT NOT NULL,
    puntos_canjeados INT NOT NULL,
    fecha_canje TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'aprobado', 'rechazado', 'completado') DEFAULT 'pendiente',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (beneficio_id) REFERENCES beneficios(id)
);

-- Tabla de categorías de logros
CREATE TABLE IF NOT EXISTS categorias_logros (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT
);

-- Relación entre logros y categorías
ALTER TABLE logros ADD COLUMN categoria_id INT;
ALTER TABLE logros ADD FOREIGN KEY (categoria_id) REFERENCES categorias_logros(id);

-- Tabla de historial de importaciones
CREATE TABLE IF NOT EXISTS historial_importaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    archivo VARCHAR(255) NOT NULL,
    registros_procesados INT NOT NULL,
    total_puntos INT NOT NULL,
    fecha_importacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES usuarios(id)
); 