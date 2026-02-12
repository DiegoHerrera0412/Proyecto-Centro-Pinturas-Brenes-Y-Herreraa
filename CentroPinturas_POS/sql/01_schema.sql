DROP DATABASE IF EXISTS CentroPinturas;
CREATE DATABASE CentroPinturas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE CentroPinturas;

-- ======================
-- TABLA ROL
-- ======================
CREATE TABLE rol (
  id_rol INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL UNIQUE
);

-- ======================
-- TABLA USUARIO
-- ======================
CREATE TABLE usuario (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  correo VARCHAR(160) NOT NULL UNIQUE,
  pass_hash VARCHAR(255) NOT NULL,
  id_rol INT NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
);

-- ======================
-- TABLA CLIENTE
-- ======================
CREATE TABLE cliente (
  id_cliente INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  apellido VARCHAR(120),
  telefono VARCHAR(40),
  correo VARCHAR(160),
  direccion VARCHAR(240),
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ======================
-- TABLA PRODUCTO
-- ======================
CREATE TABLE producto (
  id_producto INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(180) NOT NULL,
  sku VARCHAR(60) UNIQUE,
  precio DECIMAL(18,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ======================
-- TABLA VENTA
-- ======================
CREATE TABLE venta (
  id_venta INT AUTO_INCREMENT PRIMARY KEY,
  fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_cliente INT,
  id_usuario INT NOT NULL,
  subtotal DECIMAL(18,2) NOT NULL,
  descuento DECIMAL(18,2) NOT NULL DEFAULT 0,
  impuesto DECIMAL(18,2) NOT NULL DEFAULT 0,
  total DECIMAL(18,2) NOT NULL,
  metodo_pago VARCHAR(40) NOT NULL DEFAULT 'Efectivo',
  observacion VARCHAR(240),
  FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente),
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

-- ======================
-- TABLA FACTURA
-- ======================
CREATE TABLE factura (
  id_factura INT AUTO_INCREMENT PRIMARY KEY,
  id_venta INT NOT NULL UNIQUE,
  fecha_emision DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total DECIMAL(18,2) NOT NULL,
  consecutivo VARCHAR(30) NOT NULL UNIQUE,
  FOREIGN KEY (id_venta) REFERENCES venta(id_venta)
);

-- ======================
-- TABLA DETALLE VENTA
-- ======================
CREATE TABLE detalle_venta (
  id_detalle INT AUTO_INCREMENT PRIMARY KEY,
  id_venta INT NOT NULL,
  id_producto INT NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(18,2) NOT NULL,
  total_linea DECIMAL(18,2) GENERATED ALWAYS AS (cantidad * precio_unitario) STORED,
  FOREIGN KEY (id_venta) REFERENCES venta(id_venta),
  FOREIGN KEY (id_producto) REFERENCES producto(id_producto)
);

-- ======================
-- TABLA INVENTARIO MOV
-- ======================
CREATE TABLE inventario_mov (
  id_mov INT AUTO_INCREMENT PRIMARY KEY,
  id_producto INT NOT NULL,
  tipo VARCHAR(20) NOT NULL,
  cantidad INT NOT NULL,
  fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  referencia VARCHAR(60),
  FOREIGN KEY (id_producto) REFERENCES producto(id_producto)
);

-- ======================
-- ÍNDICES
-- ======================
CREATE INDEX IX_producto_nombre ON producto(nombre);
CREATE INDEX IX_cliente_nombre ON cliente(nombre, apellido);
CREATE INDEX IX_venta_fecha ON venta(fecha);
