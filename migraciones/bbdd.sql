-- UQ Lead Dev - bbdd.sql
-- Base de datos para el proyecto UniQuiz
-- Estructura optimizada para cumplir con los requisitos de la ETSII-UPM 2025-26

-- 1. Crear la Base de Datos (si no existe)
CREATE DATABASE IF NOT EXISTS uniquiz_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uniquiz_db;

-- --------------------------------------------------------

-- 2. Tabla: Usuarios
-- Cumple con el requisito de Login/Registro y gestión de imagen de perfil
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Se guardará el hash, no texto plano
    foto_perfil VARCHAR(255) DEFAULT 'default_user.png', -- Apunta a /almacen/
    rol ENUM('estudiante', 'profesor', 'admin') DEFAULT 'estudiante',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- --------------------------------------------------------

-- 3. Tabla: Cuestionarios
-- Relación 1:N con Usuarios (Un usuario crea N cuestionarios)
CREATE TABLE IF NOT EXISTS cuestionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    es_publico TINYINT(1) DEFAULT 1, -- 1: Público, 0: Privado
    es_aleatorio TINYINT(1) DEFAULT 0, -- Si las preguntas salen en orden random
    num_preguntas_aleatorias INT DEFAULT 10,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------

-- 4. Tabla: Preguntas
-- Relación 1:N con Cuestionarios. Soporta imagen adjunta.
CREATE TABLE IF NOT EXISTS preguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cuestionario_id INT NOT NULL,
    enunciado TEXT NOT NULL,
    tipo ENUM('opcion_multiple', 'verdadero_falso', 'texto') DEFAULT 'opcion_multiple',
    imagen VARCHAR(255) NULL, -- Apunta a /almacen/, opcional
    orden INT DEFAULT 0,
    FOREIGN KEY (cuestionario_id) REFERENCES cuestionarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------

-- 5. Tabla: Opciones
-- Relación 1:N con Preguntas.
CREATE TABLE IF NOT EXISTS opciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta_id INT NOT NULL,
    texto_opcion TEXT NOT NULL,
    es_correcta TINYINT(1) DEFAULT 0,
    FOREIGN KEY (pregunta_id) REFERENCES preguntas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------

-- 6. Tabla: Resultados (Entidad Extra)
-- Relación N:M implícita (Usuario realiza Cuestionario). Guarda la nota.
CREATE TABLE IF NOT EXISTS resultados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    cuestionario_id INT NOT NULL,
    puntuacion DECIMAL(5,2) NOT NULL, -- Ej: 8.50
    fecha_realizacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (cuestionario_id) REFERENCES cuestionarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- --------------------------------------------------------
-- DATOS DE PRUEBA (SEEDERS)
-- Para que no empieces con la BBDD vacía al probar el código PHP
-- --------------------------------------------------------

-- Usuario: pass = '12345678' (hash de ejemplo, en producción usar password_hash)
INSERT INTO usuarios (nombre, email, password, foto_perfil) VALUES 
('Estudiante Demo', 'demo@upm.es', '$2y$10$e.g./w.e.g.HASH_GENERICO_PARA_12345678', 'default_user.png');

-- Cuestionario de prueba
INSERT INTO cuestionarios (usuario_id, titulo, descripcion, es_publico) VALUES 
(1, 'Bases de Datos Avanzadas', 'Preguntas sobre SQL y diseño relacional.', 1),
(1, 'Desarrollo Web PHP', 'Conceptos básicos de la arquitectura LAMP.', 0);

-- Preguntas para el Cuestionario 1
INSERT INTO preguntas (cuestionario_id, enunciado, tipo) VALUES 
(1, '¿Qué comando SQL se usa para eliminar una tabla?', 'opcion_multiple'),
(1, 'La clave primaria debe ser única.', 'verdadero_falso');

-- Opciones para la Pregunta 1
INSERT INTO opciones (pregunta_id, texto_opcion, es_correcta) VALUES 
(1, 'DELETE TABLE', 0),
(1, 'DROP TABLE', 1),
(1, 'REMOVE TABLE', 0);

-- Opciones para la Pregunta 2
INSERT INTO opciones (pregunta_id, texto_opcion, es_correcta) VALUES 
(2, 'Verdadero', 1),
(2, 'Falso', 0);