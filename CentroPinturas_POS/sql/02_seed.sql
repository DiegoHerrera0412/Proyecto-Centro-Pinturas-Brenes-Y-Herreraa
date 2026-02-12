USE CentroPinturas;

INSERT INTO rol(nombre) VALUES 
('admin'), 
('cajero'), 
('vendedor');

INSERT INTO usuario(nombre, correo, pass_hash, id_rol)
VALUES
('Administrador', 'admin@demo.com',
'719f5e3e74eee4f53ff1eea39db5046742667602adc8a95c890d9a594c32efdf',
(SELECT id_rol FROM rol WHERE nombre='admin')),

('Cajero', 'cajero@demo.com',
'62de9cdf16b021e39458f6a04299e96f374b565ae7a81793c183cc74aae5429c',
(SELECT id_rol FROM rol WHERE nombre='cajero'));

INSERT INTO cliente(nombre, apellido, telefono, correo, direccion)
VALUES
('Cliente', 'Contado', '', '', ''),
('María', 'Herrera', '8888-8888', 'maria@email.com', 'San Rafael'),
('Luis', 'Brenes', '8777-7777', 'luis@email.com', 'Heredia Centro');

INSERT INTO producto(nombre, sku, precio, stock)
VALUES
('Pintura Látex Blanca 1 Galón', 'LAT-BCO-1G', 13500, 25),
('Pintura Látex Blanca 1/4 Galón', 'LAT-BCO-1Q', 4500, 40),
('Esmalte Sintético Negro 1/4', 'ESM-NGR-1Q', 5200, 18),
('Thinner 1 Litro', 'THN-1L', 3200, 30),
('Rodillo Profesional 9"', 'RDL-9', 3900, 15),
('Brocha 2"', 'BRC-2', 1500, 50);
