# 🛠️ IT Helpdesk & Ticket Management System

Un sistema web centralizado para la gestión de incidentes de Soporte de TI y mesas de ayuda corporativas, construido con **PHP (PDO)** y **MySQL**. Diseñado bajo principios de seguridad, arquitectura relacional robusta y el flujo de trabajo estándar de soporte Nivel 1 y Nivel 2 (Tier 1 / Tier 2).

---

## 🚀 Características Principales

* **Control de Acceso Basado en Roles (RBAC):**
  * **Empleado:** Creación de solicitudes con título, descripción y archivos adjuntos (evidencias). Seguimiento en tiempo real del estado de sus incidentes.
  * **Técnico (Soporte TI):** Asignación de tickets de la Cola General (*Cherry-picking*), gestión de incidentes asignados, revisión de adjuntos, resolución y **escalado a Soporte Tier 2**.
  * **Administrador:** Dashboard analítico en tiempo real, auditoría global de incidentes (trazabilidad completa de usuario/técnico/tiempos) y gestión integral de usuarios (creación, asignación de roles y borrado seguro).

* **Gestión del Ciclo de Vida del Ticket:**
  * Estados: `Abierto` ➔ `En Progreso` ➔ `Escalado (Tier 2)` ➔ `Resuelto`.
  * Preservación de integridad referencial: Manejo automático de registros huérfanos en la base de datos mediante reglas `ON DELETE CASCADE` y `SET NULL`.

* **Gestión de Evidencias:**
  * Subida segura de capturas de pantalla y documentos adjuntos (`.jpg`, `.png`, `.pdf`) implementando transacciones SQL atómicas (`beginTransaction` / `commit`) para asegurar la consistencia del almacenamiento.

* **Dashboard Analítico (Admin):**
  * Conteo dinámico de métricas clave (Tickets Totales, Abiertos, En Progreso y Resueltos).

---

## 🔑 Credenciales de Prueba (Demo)

El script `database.sql` incluye tres cuentas preconfiguradas para explorar todas las vistas e interfaces del sistema.

**Contraseña general para todos los usuarios:** `admin123`

| Rol | Correo Electrónico | Contraseña | Permisos y Alcance |
| :--- | :--- | :--- | :--- |
| **Administrador** | `admin@tickets.local` | `admin123` | Dashboard de métricas, auditoría global y alta/baja de usuarios |
| **Técnico (TI)** | `tecnico@tickets.local` | `admin123` | Tomar tickets, ver evidencias, resolver y escalar |
| **Empleado** | `empleado@tickets.local` | `admin123` | Generar tickets, adjuntar archivos y ver su historial personal |

---

## 🛠️ Arquitectura y Tecnologías

* **Backend:** PHP 8.x (Consultas preparadas vía PDO para prevención de inyecciones SQL).
* **Base de Datos:** MySQL / MariaDB (Modelo relacional normalizado con claves foráneas).
* **Frontend:** HTML5, CSS3.
* **Servidor Local:** Apache (XAMPP).

---

## ⚙️ Instalación y Configuración Local

1. **Clonar el repositorio:**
   ```bash
   git clone [https://github.com/Albert-ggc/sistema-tickets-ti.git](https://github.com/Albert-ggc/sistema-tickets-ti.git)

Desplegar en el servidor web:
Coloca la carpeta del proyecto dentro del directorio htdocs de XAMPP:C:\xampp\htdocs\sistema_tickets\

Configurar la Base de Datos:

Abre phpMyAdmin (http://localhost/phpmyadmin).

Crea una base de datos llamada sistema_tickets con cotejamiento utf8mb4_general_ci.

Importa o ejecuta el archivo database.sql suministrado en la raíz del proyecto.

Iniciar la aplicación:
Accede desde tu navegador web a http://localhost/sistema_tickets/ e ingresa con cualquiera de las credenciales de prueba.


👨‍💻 Autor
Albert Gabriel García Carrasco
Estudiante de Seguridad Informática
GitHub: github.com/Albert-ggc
2/11/2026