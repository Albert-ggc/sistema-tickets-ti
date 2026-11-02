-- --------------------------------------------------------
-- ESTRUCTURA DE LA BASE DE DATOS: sistema_tickets
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS departamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_departamento VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO departamentos (id, nombre_departamento) VALUES 
(1, 'Soporte Técnico'),
(2, 'Recursos Humanos'),
(3, 'Contabilidad');

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('empleado', 'tecnico', 'admin') DEFAULT 'empleado',
    departamento_id INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- USUARIOS DE DEMOSTRACIÓN (Contraseña para todos: admin123)
INSERT INTO usuarios (nombre_completo, email, password_hash, rol, departamento_id) VALUES
('Administrador Demo', 'admin@tickets.local', '$2y$10$8vS2.b.3tqXW3q9jV2E3g.P457/a1g6aO6Q6tZz4B3G0N4R7s7g8.', 'admin', 1),
('Técnico TI (Soporte)', 'tecnico@tickets.local', '$2y$10$8vS2.b.3tqXW3q9jV2E3g.P457/a1g6aO6Q6tZz4B3G0N4R7s7g8.', 'tecnico', 1),
('Empleado Demo', 'empleado@tickets.local', '$2y$10$8vS2.b.3tqXW3q9jV2E3g.P457/a1g6aO6Q6tZz4B3G0N4R7s7g8.', 'empleado', 2);

CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT NOT NULL,
    estado ENUM('Abierto', 'En Progreso', 'Escalado', 'Resuelto', 'Duplicado') DEFAULT 'Abierto',
    creador_id INT NOT NULL,
    tecnico_id INT DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (creador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (tecnico_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS evidencias_ticket (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;