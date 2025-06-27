-- Crear base de datos
CREATE DATABASE turismo_db WITH ENCODING 'UTF8';

-- Conectarse a la base de datos (esto se hace desde la línea de comandos o herramienta de gestión)
-- \c turismo_db

-- Tabla de usuarios
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    tipo_usuario VARCHAR(20) CHECK (tipo_usuario IN ('cliente', 'vendedor', 'admin')) DEFAULT 'cliente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de productos turísticos
CREATE TABLE productos (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    categoria VARCHAR(20) CHECK (categoria IN ('hotel', 'vuelo', 'paquete', 'auto', 'excursion')) NOT NULL,
    destino VARCHAR(100),
    duracion_dias INTEGER,
    disponible BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de carrito
CREATE TABLE carrito (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER NOT NULL,
    producto_id INTEGER NOT NULL,
    cantidad INTEGER DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
);

-- Tabla de pedidos
CREATE TABLE pedidos (
    id SERIAL PRIMARY KEY,
    numero_pedido VARCHAR(20) UNIQUE NOT NULL,
    usuario_id INTEGER NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    estado VARCHAR(20) CHECK (estado IN ('pendiente', 'procesando', 'entregado', 'cancelado')) DEFAULT 'pendiente',
    fecha_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_entrega TIMESTAMP NULL,
    notas TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabla de detalle de pedidos
CREATE TABLE pedidos_detalle (
    id SERIAL PRIMARY KEY,
    pedido_id INTEGER NOT NULL,
    producto_id INTEGER NOT NULL,
    cantidad INTEGER NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- Agregar tabla de consultas
CREATE TABLE consultas (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    asunto VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_consulta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(20) CHECK (estado IN ('pendiente', 'respondida')) DEFAULT 'pendiente'
);

-- Tabla de configuración para emails
CREATE TABLE configuracion (
    id SERIAL PRIMARY KEY,
    clave VARCHAR(50) UNIQUE NOT NULL,
    valor TEXT NOT NULL,
    descripcion VARCHAR(200)
);

-- Insertar datos de prueba
INSERT INTO usuarios (nombre, email, password, tipo_usuario) VALUES
('Admin Sistema', 'admin@turismo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Juan Vendedor', 'vendedor@turismo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vendedor'),
('María Cliente', 'cliente@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente');

INSERT INTO productos (codigo, nombre, descripcion, precio, categoria, destino, duracion_dias) VALUES
('PKG001', 'Paquete Bariloche Completo', 'Hotel 4 estrellas + vuelos + excursiones', 85000.00, 'paquete', 'Bariloche', 7),
('PKG002', 'Mendoza Wine Tour', 'Tour por bodegas con degustación incluida', 45000.00, 'paquete', 'Mendoza', 3),
('HTL001', 'Hotel Llao Llao', 'Estadía en hotel 5 estrellas', 25000.00, 'hotel', 'Bariloche', 1),
('VUE001', 'Vuelo Buenos Aires - Bariloche', 'Vuelo ida y vuelta', 35000.00, 'vuelo', 'Bariloche', 0),
('AUTO001', 'Alquiler Auto Compacto', 'Auto por día con seguro incluido', 8000.00, 'auto', 'Nacional', 1);

INSERT INTO configuracion (clave, valor, descripcion) VALUES
('email_ventas',
'ventas@turismo.com', 'Email del departamento de ventas'),
('email_admin', 'admin@turismo.com', 'Email del administrador'),
('empresa_nombre', 'Turismo Aventura', 'Nombre de la empresa');
