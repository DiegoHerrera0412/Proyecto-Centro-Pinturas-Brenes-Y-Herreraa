USE CentroPinturas;
GO

INSERT INTO dbo.rol(nombre) VALUES (N'admin'), (N'cajero'), (N'vendedor');

INSERT INTO dbo.usuario(nombre, correo, pass_hash, id_rol)
VALUES
 (N'Administrador', N'admin@demo.com', N'719f5e3e74eee4f53ff1eea39db5046742667602adc8a95c890d9a594c32efdf', (SELECT id_rol FROM dbo.rol WHERE nombre=N'admin')),
 (N'Cajero', N'cajero@demo.com', N'62de9cdf16b021e39458f6a04299e96f374b565ae7a81793c183cc74aae5429c', (SELECT id_rol FROM dbo.rol WHERE nombre=N'cajero'));

INSERT INTO dbo.cliente(nombre, apellido, telefono, correo, direccion)
VALUES
 (N'Cliente', N'Contado', N'', N'', N''),
 (N'María', N'Herrera', N'8888-8888', N'maria@email.com', N'San Rafael'),
 (N'Luis', N'Brenes', N'8777-7777', N'luis@email.com', N'Heredia Centro');

INSERT INTO dbo.producto(nombre, sku, precio, stock)
VALUES
 (N'Pintura Látex Blanca 1 Galón', N'LAT-BCO-1G', 13500, 25),
 (N'Pintura Látex Blanca 1/4 Galón', N'LAT-BCO-1Q', 4500, 40),
 (N'Esmalte Sintético Negro 1/4', N'ESM-NGR-1Q', 5200, 18),
 (N'Thinner 1 Litro', N'THN-1L', 3200, 30),
 (N'Rodillo Profesional 9"', N'RDL-9', 3900, 15),
 (N'Brocha 2"', N'BRC-2', 1500, 50);
GO
