
INSERT INTO `colores` (nombre_color)
VALUES 
('Azul'),
('Negro'),
('Blanco'),
('Rojo'),
('Verde'),
('Gris');


INSERT INTO `tallas` (nombre_talla)
VALUES 
('S'),
('M'),
('L'),
('XL'),
('24'),
('25'),
('26'),
('27'),
('28');

INSERT INTO `productos`(nombre, descripcion, material)
VALUES
-- Playeras
('Playera Brutal', 'Playera ligera con tecnología de secado rápido.', 'Poliéster'),
('Playera Xplit', 'Playera cómoda ideal para uso casual o deportivo.', 'Algodón'),
('Playera Borregos', 'Playera fresca para entrenamientos intensos.', 'Poliéster'),
('Playera Sprint', 'Playera de algodón con corte moderno.', 'Algodón'),

-- Pantalones / Shorts
('Pantalón Hunder', 'Pantalón ajustado ideal para entrenar o uso diario.', 'Poliéster'),
('Pantalón Spoilt', 'Pantalón resistente y suave para clima frío.', 'Algodón'),
('Short Udak', 'Short liviano con cintura elástica.', 'Poliéster'),
('Short Nike', 'Short cómodo de algodón.', 'Algodón'),

-- Tenis
('Tenis Adidas', 'Tenis ligeros y transpirables.', 'Sintético'),
('Tenis Charly', 'Tenis con amortiguación avanzada.', 'Sintético'),
('Tenis Sneakers', 'Tenis cómodos con diseño ergonómico.', 'Sintético'),
('Tenis Puma', 'Tenis premium de alto rendimiento.', 'Sintético'),

-- Sudaderas
('Sudadera Drap', 'Sudadera clásica con ajuste cómodo.', 'Algodón'),
('Sudadera Puma', 'Sudadera cálida ideal para días fríos.', 'Algodón'),
('Sudadera XLSX', 'Sudadera ligera de algodón.', 'Algodón'),
('Sudadera Coding', 'Sudadera gruesa y resistente.', 'Algodón');


-- 1. Playera Poliéster ($299) | Tallas: S,M,L,XL | Colores: Azul, Negro, Blanco
INSERT INTO `variantesproducto`  (id_producto, id_color, id_talla, precio, stock)
VALUES
(1, 1, 1, 299.00, 10), -- Azul S
(1, 2, 1, 299.00, 10), -- Negro S
(1, 3, 1, 299.00, 10),
(1, 1, 3, 299.00, 8),
(1, 2, 3, 299.00, 8),
(1, 3, 3, 299.00,8);

-- 2. Playera Algodón ($349) | Tallas: M,L,XL | Colores: Rojo, Negro, Blanco
INSERT INTO `variantesproducto` (id_producto, id_color, id_talla, precio, stock)
VALUES
(2, 4, 2, 349.00, 10),
(2, 2, 2, 349.00, 10),
(2, 3, 2, 349.00, 10),
(2, 4, 3, 349.00, 8),
(2, 2, 3, 349.00, 8),
(2, 3, 3, 349.00, 8);

-- 3. Playera Poliéster ($329) | Tallas: S,M,L | Colores: Verde, Negro
INSERT INTO `variantesproducto` (id_producto, id_color, id_talla, precio, stock)
VALUES
(3, 5, 1, 329.00, 8),
(3, 2, 1, 329.00, 8),
(3, 5, 2, 329.00, 8),
(3, 2, 2, 329.00, 8),
(3, 5, 3, 329.00, 8),
(3, 2, 3, 329.00, 8);

-- 4. Playera Algodón ($359) | Tallas: L,XL | Colores: Azul, Gris
INSERT INTO `variantesproducto` (id_producto, id_color, id_talla, precio, stock)
VALUES
(4, 1, 3, 359.00, 6),
(4, 6, 3, 359.00, 6),
(4, 1, 4, 359.00, 6),
(4, 6, 4, 359.00, 6);

-- 5. Pantalón Poliéster ($499) | Tallas: S,M,L,XL | Colores: Negro, Gris
INSERT INTO `variantesproducto` (id_producto, id_color, id_talla, precio, stock)
VALUES
(5, 2, 1, 499.00, 10),
(5, 6, 1, 499.00, 10),
(5, 2, 2, 499.00, 8),
(5, 6, 2, 499.00, 8),
(5, 2, 3, 499.00, 8),
(5, 6, 3, 499.00, 8);

-- 6. Pantalón Algodón ($549) | Tallas: M,L,XL | Colores: Azul, Negro
INSERT INTO `variantesproducto` (id_producto, id_color, id_talla, precio, stock)
VALUES
(6, 1, 2, 549.00, 8),
(6, 2, 2, 549.00, 8),
(6, 1, 3, 549.00, 8),
(6, 2, 3, 549.00, 8),
(6, 1, 4, 549.00, 8),
(6, 2, 4, 549.00, 8);

-- 7. Short Poliéster ($519) | Tallas: S,M,L | Colores: Gris, Negro
INSERT INTO `variantesproducto` (id_producto, id_color, id_talla, precio, stock)
VALUES
(7, 6, 1, 519.00, 10),
(7, 2, 1, 519.00, 10),
(7, 6, 2, 519.00, 8),
(7, 2, 2, 519.00, 8),
(7, 6, 3, 519.00, 8),
(7, 2, 3, 519.00, 8);

-- 8. Short Algodón ($559) | Tallas: L,XL | Colores: Azul, Negro
INSERT INTO `variantesproducto` (id_producto, id_color, id_talla, precio, stock)
VALUES
(8, 1, 3, 559.00, 6),
(8, 2, 3, 559.00, 6),
(8, 1, 4, 559.00, 6),
(8, 2, 4, 559.00, 6);
-- Tenis
INSERT INTO `variantesproducto` (id_producto, id_color, id_talla, precio, stock) VALUES
(9, 3, 5, 1200, 10), (9, 2, 5, 1200, 10), (9, 3, 6, 1200, 10), (9, 2, 6, 1200, 10),
(9, 3, 7, 1200, 10), (9, 2, 7, 1200, 10), (9, 3, 8, 1200, 10), (9, 2, 8, 1200, 10),

(10, 1, 6, 1299, 8), (10, 3, 6, 1299, 8), (10, 1, 7, 1299, 8), (10, 3, 7, 1299, 8),
(10, 1, 8, 1299, 8), (10, 3, 8, 1299, 8), (10, 1, 9, 1299, 8), (10, 3, 9, 1299, 8),

(11, 2, 5, 1250, 6), (11, 6, 5, 1250, 6), (11, 2, 6, 1250, 6), (11, 6, 6, 1250, 6),
(11, 2, 7, 1250, 6), (11, 6, 7, 1250, 6),

(12, 3, 7, 1350, 6), (12, 2, 7, 1350, 6), (12, 3, 8, 1350, 6), (12, 2, 8, 1350, 6), (12, 3, 9, 1350, 6), (12, 2, 9, 1350, 6);

-- 13. Sudadera ($799) | Tallas: S,M,L,XL | Colores: Negro, Gris
INSERT INTO `variantesproducto` (id_producto, id_color, id_talla, precio, stock)
VALUES
(13, 2, 1, 799.00, 8),
(13, 6, 1, 799.00, 8),
(13, 2, 2, 799.00, 8),
(13, 6, 2, 799.00, 8);

-- 14. Sudadera ($849) | Tallas: M,L,XL | Colores: Azul, Negro
INSERT INTO `variantesproducto` (id_producto, id_color, id_talla, precio, stock)
VALUES
(14, 1, 2, 849.00, 8),
(14, 2, 2, 849.00, 8),
(14, 1, 3, 849.00, 8),
(14, 2, 3, 849.00, 8);

-- 15. Sudadera ($799) | Tallas: S,M,L | Colores: Gris, Negro
INSERT INTO `variantesproducto` (id_producto, id_color, id_talla, precio, stock)
VALUES
(15, 6, 1, 799.00, 8),
(15, 2, 1, 799.00, 8),
(15, 6, 2, 799.00, 8),
(15, 2, 2, 799.00, 8);

-- 16. Sudadera ($859) | Tallas: L,XL | Colores: Azul, Gris
INSERT INTO `variantesproducto` (id_producto, id_color, id_talla, precio, stock)
VALUES
(16, 1, 3, 859.00, 6),
(16, 6, 3, 859.00, 6),
(16, 1, 4, 859.00, 6),
(16, 6, 4, 859.00, 6);

-- Script para actualizar las columnas 'imagen' y 'categoria' de la tabla 'productos'

-- Categoría: Playeras
UPDATE `productos` SET imagen = 'img/Catalogo/Playeras/playeraDep.jpeg', categoria = 'Playeras' WHERE id_producto = 1;
UPDATE `productos` SET imagen = 'img/Catalogo/Playeras/playeraDep2.jpeg', categoria = 'Playeras' WHERE id_producto = 2;
UPDATE `productos` SET imagen = 'img/Catalogo/Playeras/playeraDep3.jpeg', categoria = 'Playeras' WHERE id_producto = 3;
UPDATE `productos` SET imagen = 'img/Catalogo/Playeras/playeraDep4.jpeg', categoria = 'Playeras' WHERE id_producto = 4;

-- Categoría: Pantalones
UPDATE `productos` SET imagen = 'img/Catalogo/Pantalones/pantalonDep.jpeg', categoria = 'Pantalones' WHERE id_producto = 5;
UPDATE `productos` SET imagen = 'img/Catalogo/Pantalones/pantalonDep1.jpeg', categoria = 'Pantalones' WHERE id_producto = 6;
UPDATE `productos` SET imagen = 'img/Catalogo/Pantalones/pantalonDep2.jpeg', categoria = 'Pantalones' WHERE id_producto = 7;
UPDATE `productos` SET imagen = 'img/Catalogo/Pantalones/pantalonDep4.jpeg', categoria = 'Pantalones' WHERE id_producto = 8;

-- Categoría: Tenis
UPDATE `productos` SET imagen = 'img/Catalogo/Tenis/tenisDep.jpeg', categoria = 'Tenis' WHERE id_producto = 9;
UPDATE `productos` SET imagen = 'img/Catalogo/Tenis/tenisDep2.jpeg', categoria = 'Tenis' WHERE id_producto = 10;
UPDATE `productos` SET imagen = 'img/Catalogo/Tenis/tenisDep3.jpeg', categoria = 'Tenis' WHERE id_producto = 11;
UPDATE `productos` SET imagen = 'img/Catalogo/Tenis/tenisDep4.jpeg', categoria = 'Tenis' WHERE id_producto = 12;

-- Categoría: Sudaderas
UPDATE `productos` SET imagen = 'img/Catalogo/Sudaderas/sudaderaDep.jpeg', categoria = 'Sudaderas' WHERE id_producto = 13;
UPDATE `productos` SET imagen = 'img/Catalogo/Sudaderas/sudaderaDep2.jpeg', categoria = 'Sudaderas' WHERE id_producto = 14;
UPDATE `productos` SET imagen = 'img/Catalogo/Sudaderas/sudaderaDep3.jpeg', categoria = 'Sudaderas' WHERE id_producto = 15;
UPDATE `productos` SET imagen = 'img/Catalogo/Sudaderas/sudaderaDep4.jpeg', categoria = 'Sudaderas' WHERE id_producto = 16;