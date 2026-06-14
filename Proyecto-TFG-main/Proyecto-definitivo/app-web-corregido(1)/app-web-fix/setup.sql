-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS MiTienda
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE MiTienda;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS Usuarios (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(100)  NOT NULL,
    apellidos     VARCHAR(100),
    email         VARCHAR(100)  UNIQUE NOT NULL,
    password_hash VARCHAR(255)  NOT NULL,
    creado_en     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Usuario administrador por defecto
-- Contraseña: aromaris2024
INSERT IGNORE INTO Usuarios (nombre, email, password_hash)
VALUES (
    'Administrador',
    'admin@aromaris.es',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
);
-- IMPORTANTE: Cambia esta contraseña desde el panel admin tras el primer acceso.
