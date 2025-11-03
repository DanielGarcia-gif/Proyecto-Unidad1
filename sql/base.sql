DROP DATABASE IF EXISTS fadaSportsBD;
CREATE DATABASE fadaSportsBD;
USE fadaSportsBD;

-- 1. ROLES
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(20) NOT NULL UNIQUE
);

-- 2. COLORES Y TALLAS
CREATE TABLE colores (
    id_color INT AUTO_INCREMENT PRIMARY KEY,
    nombre_color VARCHAR(50) NOT NULL
);

CREATE TABLE tallas (
    id_talla INT AUTO_INCREMENT PRIMARY KEY,
    nombre_talla VARCHAR(20) NOT NULL UNIQUE
);

-- 3. USUARIOS REGISTRADOS
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    telefono VARCHAR(15),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
);

CREATE TABLE direccionesUsuario (
    id_direccion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    codigo_postal VARCHAR(10) NOT NULL,
    es_predeterminada BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- 4. COMPRADORES TEMPORALES (sin cuenta)
CREATE TABLE compradores_temporales (
    id_temporal INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(15),
    direccion VARCHAR(255) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    codigo_postal VARCHAR(10) NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 5. PRODUCTOS Y VARIANTES
CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255),
    material VARCHAR(50),
    imagen VARCHAR(255),
    categoria VARCHAR(50)
);

CREATE TABLE variantesProducto (
    id_variante INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    id_color INT NOT NULL,
    id_talla INT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
    FOREIGN KEY (id_color) REFERENCES colores(id_color),
    FOREIGN KEY (id_talla) REFERENCES tallas(id_talla),
    UNIQUE KEY uk_producto_variante (id_producto, id_color, id_talla)
);

-- 6. ENVÍOS
CREATE TABLE envios (
    id_envio INT AUTO_INCREMENT PRIMARY KEY,
    direccion_envio VARCHAR(255) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    codigo_postal VARCHAR(10) NOT NULL,
    numero_rastreo VARCHAR(50),
    costo_envio DECIMAL(10, 2) NOT NULL DEFAULT 0
);

-- 7. COMPRAS (Pedidos)
CREATE TABLE compras (
    id_compra INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    id_temporal INT NULL,
    id_envio INT NOT NULL,
    fecha_compra DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_compra DECIMAL(10, 2) NOT NULL,
    estado VARCHAR(50) NOT NULL DEFAULT 'Pendiente',
    metodo_pago ENUM('Tarjeta', 'PayPal') DEFAULT 'Tarjeta',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_temporal) REFERENCES compradores_temporales(id_temporal),
    FOREIGN KEY (id_envio) REFERENCES envios(id_envio)
);

-- 8. DETALLE DE COMPRA
CREATE TABLE detalleCompra (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_compra INT NOT NULL,
    id_variante INT NOT NULL,
    cantidad INT UNSIGNED NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_compra) REFERENCES compras(id_compra),
    FOREIGN KEY (id_variante) REFERENCES variantesProducto(id_variante)
);
