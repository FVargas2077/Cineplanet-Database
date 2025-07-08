/*
================================================================================
SCRIPT DE CREACIÓN DE BASE DE DATOS OPTIMIZADA - CINEPLANET
================================================================================
- Versión optimizada para el proyecto universitario.
- Se usan llaves foráneas con ON DELETE RESTRICT para proteger la integridad de los datos.
- Se usan AUTO_INCREMENT para IDs para facilitar la inserción desde PHP.
*/

-- Se recomienda ejecutar este script en una base de datos limpia.
DROP DATABASE IF EXISTS CineDB;
CREATE DATABASE CineDB;
USE CineDB;

-- TABLA: Sede
-- Almacena las diferentes ubicaciones del cine.
CREATE TABLE Sede (
    ID_sede INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ciudad VARCHAR(50) NOT NULL
);

-- TABLA: Cliente
-- Almacena los usuarios. La contraseña debería ser hasheada en un proyecto real.
CREATE TABLE Cliente (
    DNI CHAR(8) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- Campo para la contraseña
    es_admin BOOLEAN DEFAULT FALSE, -- Flag para identificar administradores
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABLA: Socio
-- Relacionada 1 a 1 con Cliente. Contiene datos específicos del programa de lealtad.
CREATE TABLE Socio (
    DNI CHAR(8) PRIMARY KEY,
    numero_socio VARCHAR(20) UNIQUE NOT NULL,
    puntos_acumulados INT DEFAULT 0,
    FOREIGN KEY (DNI) REFERENCES Cliente(DNI) ON DELETE CASCADE -- Si se borra el cliente, se borra el socio.
);

-- TABLA: Pelicula
-- Contiene la información de las películas disponibles.
CREATE TABLE Pelicula (
    ID_pelicula INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    genero VARCHAR(50) NOT NULL,
    duracion_minutos INT NOT NULL,
    clasificacion ENUM('ATP', '+13', '+16', '+18') DEFAULT 'ATP',
    sinopsis TEXT
);

-- TABLA: Sala
-- Define las salas de cine, asociadas a una Sede.
CREATE TABLE Sala (
    ID_sala INT AUTO_INCREMENT PRIMARY KEY,
    ID_sede INT NOT NULL,
    numero_sala INT NOT NULL,
    tipo_sala ENUM('2D', '3D', 'VIP') DEFAULT '2D',
    capacidad INT NOT NULL,
    FOREIGN KEY (ID_sede) REFERENCES Sede(ID_sede) ON DELETE RESTRICT -- No se puede borrar una sede si tiene salas.
);

-- TABLA: Funcion
-- El corazón del sistema. Define qué película se proyecta, en qué sala, fecha y hora.
CREATE TABLE Funcion (
    ID_funcion INT AUTO_INCREMENT PRIMARY KEY,
    ID_pelicula INT NOT NULL,
    ID_sala INT NOT NULL,
    fecha_hora DATETIME NOT NULL, -- Un solo campo para fecha y hora.
    precio_base DECIMAL(6,2) NOT NULL,
    FOREIGN KEY (ID_pelicula) REFERENCES Pelicula(ID_pelicula) ON DELETE RESTRICT,
    FOREIGN KEY (ID_sala) REFERENCES Sala(ID_sala) ON DELETE RESTRICT
);

-- TABLA: Compra
-- Registra una transacción hecha por un cliente.
CREATE TABLE Compra (
    ID_compra INT AUTO_INCREMENT PRIMARY KEY,
    DNI_cliente CHAR(8) NOT NULL,
    fecha_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(8,2) NOT NULL,
    metodo_pago ENUM('Tarjeta', 'Efectivo', 'Yape') DEFAULT 'Tarjeta',
    FOREIGN KEY (DNI_cliente) REFERENCES Cliente(DNI) ON DELETE RESTRICT
);

-- TABLA: Boleto
-- Tabla de detalle de una Compra. Cada fila es un boleto para una función y asiento específico.
CREATE TABLE Boleto (
    ID_boleto INT AUTO_INCREMENT PRIMARY KEY,
    ID_compra INT NOT NULL,
    ID_funcion INT NOT NULL,
    fila CHAR(1) NOT NULL,
    numero_asiento INT NOT NULL,
    precio_pagado DECIMAL(6,2) NOT NULL,
    FOREIGN KEY (ID_compra) REFERENCES Compra(ID_compra) ON DELETE CASCADE, -- Si se anula la compra, se anulan los boletos.
    FOREIGN KEY (ID_funcion) REFERENCES Funcion(ID_funcion) ON DELETE RESTRICT,
    UNIQUE KEY asiento_unico_por_funcion (ID_funcion, fila, numero_asiento) -- RESTRICCIÓN CLAVE: Evita vender el mismo asiento dos veces para la misma función.
);

-- TABLA: Dulceria
-- Almacena los productos de la dulcería.
CREATE TABLE Dulceria (
    ID_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    categoria ENUM('Dulces', 'Bebidas', 'Salado', 'Combos') DEFAULT 'Dulces',
    precio_unitario DECIMAL(6,2) NOT NULL,
    stock INT DEFAULT 0
);
