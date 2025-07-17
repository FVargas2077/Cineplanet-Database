USE CineDB;

-- 1. Insertar Sedes (5 registros)
-- Se asegura que las sedes principales existan.
INSERT INTO Sede (ID_sede, nombre, ciudad) VALUES
(1, 'Cineplanet Tacna Centro', 'Tacna'),
(2, 'Cineplanet Arequipa Real Plaza', 'Arequipa'),
(3, 'Cineplanet Lima San Miguel', 'Lima'),
(4, 'Cineplanet Trujillo Real Plaza', 'Trujillo'),
(5, 'Cineplanet Cusco', 'Cusco');

-- 2. Insertar Clientes (1 admin, 5 clientes normales + 1 cliente del caso de uso)
INSERT INTO Cliente (DNI, nombre, apellidos, email, password, es_admin, es_socio) VALUES
('12345678', 'Admin', 'Maestro', 'admin@cineplanet.com', '1234', TRUE, TRUE),
('87654321', 'Juan', 'Pérez García', 'juan.perez@email.com', '1234', FALSE, TRUE),
('11223344', 'María', 'López Torres', 'maria.lopez@email.com', '1234', FALSE, FALSE),
('55667788', 'Carlos', 'Ruiz Mendoza', 'carlos.ruiz@email.com', '1234', FALSE, TRUE),
('99887766', 'Ana', 'Chávez Soto', 'ana.chavez@email.com', '1234', FALSE, FALSE),
('77777777', 'Lucia', 'Vargas Solis', 'lucia.vargas@email.com', '1234', FALSE, FALSE);

-- 3. Insertar Socios (3 clientes base + 1 socio del caso de uso)
INSERT INTO Socio (DNI, numero_socio, puntos_acumulados) VALUES
('87654321', 'SOCIO-001', 150),
('11223344', 'SOCIO-002', 40),
('99887766', 'SOCIO-003', 500),
('77777777', 'SOCIO-777', 210);

-- 4. Insertar Películas (5 registros base + 1 película del caso de uso)
INSERT INTO Pelicula (ID_pelicula, titulo, genero, duracion_minutos, clasificacion, sinopsis) VALUES
(1, 'Intensamente 2', 'Animación', 96, 'ATP', 'Riley entra en la adolescencia y nuevas emociones llegan al cuartel general.'),
(2, 'Bad Boys: Hasta la Muerte', 'Acción/Comedia', 115, '+13', 'Los policías más famosos del mundo regresan con su icónica mezcla de acción al límite y comedia escandalosa.'),
(3, 'El Planeta de los Simios: Nuevo Reino', 'Ciencia Ficción', 145, '+13', 'Muchos años después del reinado de César, un joven simio emprende un viaje que le llevará a cuestionar todo lo que le han enseñado.'),
(4, 'Garfield: Fuera de Casa', 'Animación/Comedia', 101, 'ATP', 'Garfield está a punto de tener una salvaje aventura al aire libre.'),
(5, 'Hachiko 2: Siempre a tu lado', 'Drama', 125, 'ATP', 'Una nueva versión de la conmovedora historia del perro leal.'),
-- Película para el caso de uso:
(10, 'Cómo entrenar tu dragón', 'Animación/Aventura', 98, 'ATP', 'Un joven vikingo llamado Hipo debe matar a un dragón para marcar su paso a la edad adulta y convertirse en el líder de su tribu. Sin embargo, él termina haciéndose amigo de un dragón.');

-- 5. Insertar Salas (Salas base + 1 sala para el caso de uso)
INSERT INTO Sala (ID_sala, ID_sede, numero_sala, tipo_sala, capacidad) VALUES
(1, 1, 1, '2D', 150), -- Sede Tacna
(2, 1, 2, '3D', 150), -- Sede Tacna
(3, 2, 1, '2D', 150), -- Sede Arequipa
(4, 2, 2, 'VIP', 150),  -- Sede Arequipa
(5, 3, 5, '3D', 150),  -- Sede Lima
-- Sala para el caso de uso: Sala 3D específica en Tacna
(6, 1, 3, '3D', 150);


INSERT INTO Funcion (ID_funcion, ID_pelicula, ID_sala, fecha_hora, precio_base) VALUES
(1, 1, 1, '2025-08-14 18:00:00', 15.00), -- Intensamente 2 en Tacna Sala 1
(2, 1, 2, '2025-08-15 20:30:00', 20.00), -- Intensamente 2 en Tacna Sala 2 (3D)
(3, 2, 3, '2025-08-12 19:15:00', 18.00), -- Bad Boys en Arequipa Sala 1
(4, 3, 4, '2025-08-14 21:00:00', 35.00), -- Planeta Simios en Arequipa Sala 2 (VIP)
(5, 4, 5, '2025-08-17 16:00:00', 22.00), -- Garfield en Lima Sala 5 (3D)
-- Función para el caso de uso:
(101, 10, 6, '2025-09-15 20:40:00', 25.00); -- Cómo entrenar tu dragón en Tacna (Sala 3D) a las 8:40 PM

-- 7. Insertar Productos de Dulcería (5 registros)
INSERT INTO Dulceria (nombre, categoria, precio_unitario, stock) VALUES
('Cancha Gigante Salada', 'Salado', 25.50, 100),
('Gaseosa Mediana', 'Bebidas', 10.00, 200),
('Hot-Dog Clásico', 'Salado', 12.00, 80),
('Combo Amigos (1 Cancha Gigante + 2 Gaseosas Medianas)', 'Combos', 42.00, 50),
('M&Ms de Chocolate', 'Dulces', 8.50, 150);
