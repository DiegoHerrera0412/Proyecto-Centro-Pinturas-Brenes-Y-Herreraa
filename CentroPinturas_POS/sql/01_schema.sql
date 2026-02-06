CREATE DATABASE CentroPinturas;
GO
USE CentroPinturas;
GO

IF OBJECT_ID('dbo.inventario_mov', 'U') IS NOT NULL DROP TABLE dbo.inventario_mov;
IF OBJECT_ID('dbo.detalle_venta', 'U') IS NOT NULL DROP TABLE dbo.detalle_venta;
IF OBJECT_ID('dbo.factura', 'U') IS NOT NULL DROP TABLE dbo.factura;
IF OBJECT_ID('dbo.venta', 'U') IS NOT NULL DROP TABLE dbo.venta;
IF OBJECT_ID('dbo.producto', 'U') IS NOT NULL DROP TABLE dbo.producto;
IF OBJECT_ID('dbo.cliente', 'U') IS NOT NULL DROP TABLE dbo.cliente;
IF OBJECT_ID('dbo.usuario', 'U') IS NOT NULL DROP TABLE dbo.usuario;
IF OBJECT_ID('dbo.rol', 'U') IS NOT NULL DROP TABLE dbo.rol;
GO

CREATE TABLE dbo.rol (
  id_rol INT IDENTITY(1,1) PRIMARY KEY,
  nombre NVARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE dbo.usuario (
  id_usuario INT IDENTITY(1,1) PRIMARY KEY,
  nombre NVARCHAR(120) NOT NULL,
  correo NVARCHAR(160) NOT NULL UNIQUE,
  pass_hash NVARCHAR(255) NOT NULL,
  id_rol INT NOT NULL,
  activo BIT NOT NULL DEFAULT 1,
  creado_en DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  FOREIGN KEY (id_rol) REFERENCES dbo.rol(id_rol)
);

CREATE TABLE dbo.cliente (
  id_cliente INT IDENTITY(1,1) PRIMARY KEY,
  nombre NVARCHAR(120) NOT NULL,
  apellido NVARCHAR(120) NULL,
  telefono NVARCHAR(40) NULL,
  correo NVARCHAR(160) NULL,
  direccion NVARCHAR(240) NULL,
  creado_en DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME()
);

CREATE TABLE dbo.producto (
  id_producto INT IDENTITY(1,1) PRIMARY KEY,
  nombre NVARCHAR(180) NOT NULL,
  sku NVARCHAR(60) NULL UNIQUE,
  precio DECIMAL(18,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  activo BIT NOT NULL DEFAULT 1,
  creado_en DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME()
);

CREATE TABLE dbo.venta (
  id_venta INT IDENTITY(1,1) PRIMARY KEY,
  fecha DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  id_cliente INT NULL,
  id_usuario INT NOT NULL,
  subtotal DECIMAL(18,2) NOT NULL,
  descuento DECIMAL(18,2) NOT NULL DEFAULT 0,
  impuesto DECIMAL(18,2) NOT NULL DEFAULT 0,
  total DECIMAL(18,2) NOT NULL,
  metodo_pago NVARCHAR(40) NOT NULL DEFAULT 'Efectivo',
  observacion NVARCHAR(240) NULL,
  FOREIGN KEY (id_cliente) REFERENCES dbo.cliente(id_cliente),
  FOREIGN KEY (id_usuario) REFERENCES dbo.usuario(id_usuario)
);

CREATE TABLE dbo.factura (
  id_factura INT IDENTITY(1,1) PRIMARY KEY,
  id_venta INT NOT NULL UNIQUE,
  fecha_emision DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  total DECIMAL(18,2) NOT NULL,
  consecutivo NVARCHAR(30) NOT NULL UNIQUE,
  FOREIGN KEY (id_venta) REFERENCES dbo.venta(id_venta)
);

CREATE TABLE dbo.detalle_venta (
  id_detalle INT IDENTITY(1,1) PRIMARY KEY,
  id_venta INT NOT NULL,
  id_producto INT NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(18,2) NOT NULL,
  total_linea AS (CONVERT(DECIMAL(18,2), cantidad) * precio_unitario) PERSISTED,
  FOREIGN KEY (id_venta) REFERENCES dbo.venta(id_venta),
  FOREIGN KEY (id_producto) REFERENCES dbo.producto(id_producto)
);

CREATE TABLE dbo.inventario_mov (
  id_mov INT IDENTITY(1,1) PRIMARY KEY,
  id_producto INT NOT NULL,
  tipo NVARCHAR(20) NOT NULL,
  cantidad INT NOT NULL,
  fecha DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
  referencia NVARCHAR(60) NULL,
  FOREIGN KEY (id_producto) REFERENCES dbo.producto(id_producto)
);

CREATE INDEX IX_producto_nombre ON dbo.producto(nombre);
CREATE INDEX IX_cliente_nombre ON dbo.cliente(nombre, apellido);
CREATE INDEX IX_venta_fecha ON dbo.venta(fecha);
GO
