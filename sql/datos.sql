-- ============================================
-- ZD.TechLab — Datos de prueba
-- Día 9: mínimo veinte productos, diez clientes y quince pedidos
-- ============================================

USE zdtechlab;

-- ---------- Usuarios (contraseñas reales generadas con password_hash de PHP) ----------
-- admin@zdtechlab.co       / Admin2026*
-- vendedor@zdtechlab.co    / Vendedor2026*
-- consultor@zdtechlab.co   / Consultor2026*
INSERT INTO usuarios (nombre, correo, clave_hash, rol) VALUES
('Juan Zambrano',  'admin@zdtechlab.co',     '$2y$12$tM6St2Mi6rk5gy0DcvfF/ehdkLybd/foOsFbRBAeVsbzHPXPwlr9q', 'administrador'),
('Laura Gómez',    'vendedor@zdtechlab.co',  '$2y$12$UR5V0SZKpxAe5JVzQCcXg.1CQzJGK0PvepYf3qZfw3WYKH2egauqi', 'vendedor'),
('Carlos Pérez',   'consultor@zdtechlab.co', '$2y$12$2TXxY5OOmROF7i8TslTpSupOftjRqIdShsp3cu6ApH5nct3sFUtwu', 'consultor');

-- ---------- Categorías ----------
INSERT INTO categorias (nombre) VALUES
('Periféricos'), ('Pantallas'), ('Almacenamiento'), ('Redes');

-- ---------- Productos (20) ----------
INSERT INTO productos (categoria_id, nombre, precio, stock, stock_minimo) VALUES
(1, 'Teclado mecánico RGB',        120000, 14, 5),
(1, 'Mouse inalámbrico',            65000, 32, 5),
(1, 'Mousepad XL',                  35000, 20, 5),
(1, 'Audífonos con micrófono',     150000,  8, 5),
(1, 'Webcam Full HD',              180000,  3, 5),
(2, 'Monitor 24 pulgadas',         890000,  0, 5),
(2, 'Monitor curvo 27 pulgadas',  1250000,  6, 3),
(2, 'Monitor portátil 15 pulgadas', 620000,  4, 3),
(3, 'SSD 1TB',                     320000,  7, 5),
(3, 'SSD 500GB',                   210000, 18, 5),
(3, 'Disco duro externo 2TB',      280000, 10, 5),
(3, 'Memoria USB 64GB',             45000, 40, 10),
(3, 'Tarjeta microSD 128GB',        55000, 25, 10),
(4, 'Router WiFi 6',               310000,  9, 5),
(4, 'Switch de 8 puertos',         150000, 12, 5),
(4, 'Cable de red Cat6 (5m)',       25000, 50, 10),
(4, 'Repetidor WiFi',               90000,  2, 5),
(1, 'Teclado numérico USB',         38000, 15, 5),
(2, 'Soporte para monitor',         75000, 11, 5),
(1, 'Hub USB-C 6 en 1',            110000,  6, 5);

-- ---------- Clientes (10) ----------
INSERT INTO clientes (nombre, documento, correo) VALUES
('Ana María Rodríguez',  '1010123456', 'ana.rodriguez@example.com'),
('Pedro Gutiérrez',      '1020234567', 'pedro.gutierrez@example.com'),
('Sofía Martínez',       '1030345678', 'sofia.martinez@example.com'),
('Luis Fernando Ríos',   '1040456789', 'luis.rios@example.com'),
('Camila Torres',        '1050567890', 'camila.torres@example.com'),
('Andrés Felipe Vargas', '1060678901', 'andres.vargas@example.com'),
('Valentina Salas',      '1070789012', 'valentina.salas@example.com'),
('Jorge Ramírez',        '1080890123', 'jorge.ramirez@example.com'),
('Daniela Castro',       '1090901234', 'daniela.castro@example.com'),
('Mateo Gómez',          '1100012345', 'mateo.gomez@example.com');

-- ---------- Pedidos (15) — el total se calcula a partir del detalle ----------
INSERT INTO pedidos (cliente_id, fecha, total, estado) VALUES
(1,  '2026-07-03 10:15:00', 0, 'confirmado'),
(2,  '2026-07-08 14:40:00', 0, 'confirmado'),
(3,  '2026-07-15 09:05:00', 0, 'confirmado'),
(4,  '2026-07-22 16:30:00', 0, 'confirmado'),
(5,  '2026-08-01 11:00:00', 0, 'confirmado'),
(1,  '2026-08-05 13:20:00', 0, 'confirmado'),
(6,  '2026-08-10 15:45:00', 0, 'confirmado'),
(7,  '2026-08-14 10:10:00', 0, 'confirmado'),
(8,  '2026-08-20 17:00:00', 0, 'confirmado'),
(9,  '2026-08-27 12:25:00', 0, 'confirmado'),
(2,  '2026-09-02 09:50:00', 0, 'confirmado'),
(10, '2026-09-08 14:15:00', 0, 'confirmado'),
(3,  '2026-09-14 16:00:00', 0, 'confirmado'),
(5,  '2026-09-20 10:40:00', 0, 'confirmado'),
(6,  '2026-09-25 11:30:00', 0, 'anulado');

-- ---------- Detalle de pedidos ----------
INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES
(1, 1, 1, 120000), (1, 2, 2, 65000),
(2, 6, 1, 890000),
(3, 9, 1, 320000), (3, 12, 3, 45000),
(4, 14, 1, 310000), (4, 16, 4, 25000),
(5, 4, 1, 150000), (5, 5, 1, 180000),
(6, 7, 1, 1250000),
(7, 10, 2, 210000),
(8, 1, 2, 120000), (8, 3, 1, 35000),
(9, 11, 1, 280000),
(10, 15, 1, 150000), (10, 16, 2, 25000),
(11, 2, 1, 65000), (11, 4, 1, 150000),
(12, 8, 1, 620000),
(13, 13, 2, 55000), (13, 12, 1, 45000),
(14, 17, 1, 90000),
(15, 19, 1, 75000);

-- ---------- Recalcular el total de cada pedido a partir de su detalle ----------
UPDATE pedidos p
SET total = (
  SELECT COALESCE(SUM(d.cantidad * d.precio_unitario), 0)
  FROM
