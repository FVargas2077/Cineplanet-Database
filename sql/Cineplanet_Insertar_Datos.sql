/*
================================================================================
SCRIPT DE INSERCIÓN DE DATOS DE PRUEBA - CINEPLANET
================================================================================
- Ejecutar DESPUÉS del script de estructura.
- Contraseña para todos los usuarios: '1234' (en un entorno real, usar hash).
*/

USE CineDB;

-- 1. Insertar Sedes (5 registros)
INSERT INTO Sede (nombre, ciudad) VALUES
('Cineplanet Tacna Centro', 'Tacna'),
('Cineplanet Arequipa Real Plaza', 'Arequipa'),
('Cineplanet Lima San Miguel', 'Lima'),
('Cineplanet Trujillo Real Plaza', 'Trujillo'),
('Cineplanet Cusco', 'Cusco');

-- 2. Insertar Clientes (1 admin, 4 clientes normales)
INSERT INTO Cliente (DNI, nombre, apellidos, email, password, es_admin) VALUES
('12345678', 'Admin', 'Maestro', 'admin@cineplanet.com', '1234', TRUE),
('87654321', 'Juan', 'Pérez García', 'juan.perez@email.com', '1234', FALSE),
('11223344', 'María', 'López Torres', 'maria.lopez@email.com', '1234', FALSE),
('55667788', 'Carlos', 'Ruiz Mendoza', 'carlos.ruiz@email.com', '1234', FALSE),
('99887766', 'Ana', 'Chávez Soto', 'ana.chavez@email.com', '1234', FALSE);

-- 3. Insertar Socios (3 de los clientes son socios)
INSERT INTO Socio (DNI, numero_socio, puntos_acumulados) VALUES
('87654321', 'SOCIO-001', 150),
('11223344', 'SOCIO-002', 40),
('99887766', 'SOCIO-003', 500);

-- 4. Insertar Películas (5 registros)
INSERT INTO Pelicula (titulo, genero, duracion_minutos, clasificacion, sinopsis) VALUES
('Intensamente 2', 'Animación', 96, 'ATP', 'Riley entra en la adolescencia y nuevas emociones llegan al cuartel general.'),
('Bad Boys: Hasta la Muerte', 'Acción/Comedia', 115, '+13', 'Los policías más famosos del mundo regresan con su icónica mezcla de acción al límite y comedia escandalosa.'),
('El Planeta de los Simios: Nuevo Reino', 'Ciencia Ficción', 145, '+13', 'Muchos años después del reinado de César, un joven simio emprende un viaje que le llevará a cuestionar todo lo que le han enseñado.'),
('Garfield: Fuera de Casa', 'Animación/Comedia', 101, 'ATP', 'Garfield está a punto de tener una salvaje aventura al aire libre.'),
('Hachiko 2: Siempre a tu lado', 'Drama', 125, 'ATP', 'Una nueva versión de la conmovedora historia del perro leal.');

-- 5. Insertar Salas (Asociadas a las sedes)
-- 2 salas en Tacna, 2 en Arequipa, 1 en Lima
INSERT INTO Sala (ID_sede, numero_sala, tipo_sala, capacidad) VALUES
(1, 1, '2D', 100), -- Sede Tacna
(1, 2, '3D', 120), -- Sede Tacna
(2, 1, '2D', 150), -- Sede Arequipa
(2, 2, 'VIP', 80),  -- Sede Arequipa
(3, 5, '3D', 200);  -- Sede Lima

-- 6. Insertar Funciones (Asociando películas y salas)
INSERT INTO Funcion (ID_pelicula, ID_sala, fecha_hora, precio_base) VALUES
(1, 1, '2025-07-15 18:00:00', 15.00), -- Intensamente 2 en Tacna Sala 1
(1, 2, '2025-07-15 20:30:00', 20.00), -- Intensamente 2 en Tacna Sala 2 (3D)
(2, 3, '2025-07-16 19:15:00', 18.00), -- Bad Boys en Arequipa Sala 1
(3, 4, '2025-07-16 21:00:00', 35.00), -- Planeta Simios en Arequipa Sala 2 (VIP)
(4, 5, '2025-07-17 16:00:00', 22.00); -- Garfield en Lima Sala 5 (3D)

-- 7. Insertar Productos de Dulcería (5 registros)
INSERT INTO Dulceria (nombre, categoria, precio_unitario, stock) VALUES
('Cancha Gigante Salada', 'Salado', 25.50, 100),
('Gaseosa Mediana', 'Bebidas', 10.00, 200),
('Hot-Dog Clásico', 'Salado', 12.00, 80),
('Combo Amigos (1 Cancha Gigante + 2 Gaseosas Medianas)', 'Combos', 42.00, 50),
('M&Ms de Chocolate', 'Dulces', 8.50, 150);
