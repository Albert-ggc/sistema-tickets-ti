\# 🛠️ IT Helpdesk \& Ticket Management System



Un sistema web centralizado para la gestión de incidentes de Soporte de TI y mesas de ayuda corporativas, construido con \*\*PHP (PDO)\*\* y \*\*MySQL\*\*. Diseñado bajo principios de seguridad, arquitectura relacional robusta y el flujo de trabajo estándar de soporte Nivel 1 y Nivel 2 (Tier 1 / Tier 2).



\---



\## 🚀 Características Principales



\* \*\*Control de Acceso Basado en Roles (RBAC):\*\*

&#x20; \* \*\*Empleado:\*\* Creación de solicitudes con título, descripción y archivos adjuntos (evidencias). Seguimiento en tiempo real del estado de sus incidentes.

&#x20; \* \*\*Técnico (Soporte TI):\*\* Asignación de tickets de la Cola General (\*Cherry-picking\*), gestión de incidentes asignados, revisión de adjuntos, resolución y \*\*escalado a Soporte Tier 2\*\*.

&#x20; \* \*\*Administrador:\*\* Dashboard analítico en tiempo real, auditoría global de incidentes (trazabilidad completa de usuario/técnico/tiempos) y gestión integral de usuarios (creación, asignación de roles y borrado seguro).



\* \*\*Gestión del Ciclo de Vida del Ticket:\*\*

&#x20; \* Estados: `Abierto` ➔ `En Progreso` ➔ `Escalado (Tier 2)` ➔ `Resuelto`.

&#x20; \* Preservación de integridad referencial: Manejo automático de registros huérfanos en la base de datos mediante reglas `ON DELETE CASCADE` y `SET NULL`.



\* \*\*Gestión de Evidencias:\*\*

&#x20; \* Subida segura de capturas de pantalla y documentos adjuntos (`.jpg`, `.png`, `.pdf`) implementando transacciones SQL atómicas (`beginTransaction` / `commit`) para asegurar la consistencia del almacenamiento.



\* \*\*Dashboard Analítico (Admin):\*\*

&#x20; \* Conteo dinámico de métricas clave (Tickets Totales, Abiertos, En Progreso y Resueltos).



\---



\## 🔑 Credenciales de Prueba (Demo)



El script `database.sql` incluye tres cuentas preconfiguradas para explorar todas las vistas e interfaces del sistema:



\*\*Contraseña general para todos los usuarios:\*\* `admin123`



| Rol | Correo Electrónico | Contraseña | Permisos y Alcance |

| :--- | :--- | :--- | :--- |

| \*\*Administrador\*\* | `admin@tickets.local` | `admin123` | Dashboard de métricas, auditoría global y alta/baja de usuarios |

| \*\*Técnico (TI)\*\* | `tecnico@tickets.local` | `admin123` | Tomar tickets, ver evidencias, resolver y escalar |

| \*\*Empleado\*\* | `empleado@tickets.local` | `admin123` | Generar tickets, adjuntar archivos y ver su historial personal |



\---



\## 🛠️ Arquitectura y Tecnologías



\* \*\*Backend:\*\* PHP 8.x (Consultas preparadas vía PDO para prevención de inyecciones SQL).

\* \*\*Base de Datos:\*\* MySQL / MariaDB (Modelo relacional normalizado).

\* \*\*Frontend:\*\* HTML5, CSS3.

\* \*\*Servidor Local:\*\* Apache (XAMPP).



\---



\## ⚙️ Instalación y Configuración Local



1\. \*\*Clonar el repositorio:\*\*

&#x20;  ```bash

&#x20;  git clone \[https://github.com/TU-USUARIO/sistema-tickets-ti.git](https://github.com/TU-USUARIO/sistema-tickets-ti.git)

