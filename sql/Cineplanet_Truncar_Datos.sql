/*
================================================================================
SCRIPT DE GESTIÓN DE DATOS - CINEPLANET
================================================================================
- Contiene comandos para limpiar la base de datos.
- ¡CUIDADO! Estos comandos eliminan datos de forma permanente.
*/

USE CineDB;

-- ==============================================================================
-- OPCIÓN 1: VACIAR TODAS LAS TABLAS (TRUNCATE)
-- ==============================================================================
-- TRUNCATE es más rápido que DELETE y resetea los contadores AUTO_INCREMENT.
-- Es necesario desactivar temporalmente la revisión de llaves foráneas para
-- poder truncar las tablas en cualquier orden.

SET FOREIGN_KEY_CHECKS = 0; -- Desactivar la revisión de llaves foráneas

TRUNCATE TABLE Boleto;
TRUNCATE TABLE Compra;
TRUNCATE TABLE Dulceria;
TRUNCATE TABLE Funcion;
TRUNCATE TABLE Pelicula;
TRUNCATE TABLE Sala;
TRUNCATE TABLE Socio;
TRUNCATE TABLE Cliente;
TRUNCATE TABLE Sede;

SET FOREIGN_KEY_CHECKS = 1; -- Reactivar la revisión de llaves foráneas

-- ==============================================================================
-- OPCIÓN 2: ELIMINAR REGISTROS ESPECÍFICOS (DELETE)
-- ==============================================================================
-- A diferencia de TRUNCATE, DELETE permite usar la cláusula WHERE para ser selectivo.
-- No resetea el AUTO_INCREMENT.

-- Ejemplo 1: Eliminar una película específica por su título.
-- DELETE FROM Pelicula WHERE titulo = 'Garfield: Fuera de Casa';
-- NOTA: Esto fallará si la película tiene funciones asociadas, debido a la restricción ON DELETE RESTRICT.
-- Primero deberías eliminar las funciones de esa película.

-- Ejemplo 2: Eliminar todos los productos de la categoría 'Combos' en la dulcería.
-- DELETE FROM Dulceria WHERE categoria = 'Combos';

-- Ejemplo 3: Eliminar un cliente por su DNI.
-- DELETE FROM Cliente WHERE DNI = '99887766';
-- NOTA: Gracias a ON DELETE CASCADE, al borrar este cliente también se borrará su registro de la tabla Socio.
-- Sin embargo, fallará si el cliente tiene compras registradas.
