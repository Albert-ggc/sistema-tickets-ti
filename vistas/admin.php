<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
require_once '../config/db.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['crear_usuario'])) {
        $nombre = trim($_POST['nombre_completo']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $rol = $_POST['rol'];
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_completo, email, password_hash, rol) VALUES (:nombre, :email, :hash, :rol)");
            if ($stmt->execute(['nombre' => $nombre, 'email' => $email, 'hash' => $hash, 'rol' => $rol])) {
                $mensaje = "<div class='alerta exito'>Usuario $nombre creado exitosamente.</div>";
            }
        } catch (PDOException $e) {
            $mensaje = "<div class='alerta error'>Error: El correo ya existe.</div>";
        }
    } elseif (isset($_POST['eliminar_usuario'])) {
        $id_eliminar = $_POST['usuario_id'];
        if ($id_eliminar == $_SESSION['usuario_id']) {
            $mensaje = "<div class='alerta error'>Acción denegada: No puedes eliminar tu propia cuenta.</div>";
        } else {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
            if ($stmt->execute(['id' => $id_eliminar])) {
                $mensaje = "<div class='alerta exito'>Usuario eliminado correctamente.</div>";
            }
        }
    }
}

// --- CONSULTAS PARA ESTADÍSTICAS ---
$stats = [];
$stats['total_tickets'] = $pdo->query("SELECT COUNT(*) FROM tickets")->fetchColumn();
$stats['tickets_abiertos'] = $pdo->query("SELECT COUNT(*) FROM tickets WHERE estado = 'Abierto'")->fetchColumn();
$stats['tickets_progreso'] = $pdo->query("SELECT COUNT(*) FROM tickets WHERE estado = 'En Progreso'")->fetchColumn();
$stats['tickets_resueltos'] = $pdo->query("SELECT COUNT(*) FROM tickets WHERE estado = 'Resuelto'")->fetchColumn();

$auditoria_tickets = $pdo->query("
    SELECT t.id, t.titulo, t.estado, t.fecha_creacion, t.fecha_cierre,
           c.nombre_completo AS creador_nombre, te.nombre_completo AS tecnico_nombre
    FROM tickets t
    LEFT JOIN usuarios c ON t.creador_id = c.id
    LEFT JOIN usuarios te ON t.tecnico_id = te.id
    ORDER BY t.fecha_creacion DESC
")->fetchAll();

$lista_usuarios = $pdo->query("SELECT id, nombre_completo, email, rol, fecha_creacion FROM usuarios ORDER BY rol, nombre_completo")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración TI</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; margin: 0; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 1200px; margin: auto; }
        nav { background: #1f2937; padding: 15px; border-radius: 6px; margin-bottom: 20px; overflow: hidden; }
        nav a { color: white; text-decoration: none; margin-right: 15px; font-weight: bold; float: left; }
        .btn-logout { float: right; color: #fca5a5; }
        
        /* Dashboard de estadísticas */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
        .stat-box { padding: 20px; border-radius: 8px; color: white; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-box h3 { margin: 0; font-size: 36px; }
        .stat-box p { margin: 5px 0 0 0; font-weight: bold; text-transform: uppercase; font-size: 12px; opacity: 0.9; }
        .bg-total { background: #4b5563; }
        .bg-abiertos { background: #d97706; }
        .bg-progreso { background: #2563eb; }
        .bg-resueltos { background: #059669; }

        .grid { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; margin-bottom: 30px; }
        .card { border: 1px solid #e5e7eb; padding: 20px; border-radius: 6px; background: #fff; overflow-x: auto; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background-color: #f9fafb; color: #4b5563; }
        
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 13px; color: #374151;}
        input, select { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; }
        .btn-crear { background: #1f2937; color: white; border: none; padding: 10px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%;}
        .btn-accion { padding: 6px 10px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; color: white; font-size: 12px; }
        .btn-eliminar { background-color: #ef4444; }
        .badge-rol { padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; color: white; }
        .rol-admin { background: #1f2937; } .rol-tecnico { background: #2563eb; } .rol-empleado { background: #059669; }
        
        .alerta { padding: 10px; margin-bottom: 20px; border-radius: 4px; text-align: center; font-weight: bold; }
        .exito { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Panel de Control (Admin) - <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></h2>
        <nav>
            <a href="admin.php">Dashboard</a>
            <a href="../logout.php" class="btn-logout">Cerrar Sesión</a>
        </nav>

        <?php if(!empty($mensaje)) echo $mensaje; ?>

        <!-- NUEVO: Dashboard de Estadísticas -->
        <div class="stats-grid">
            <div class="stat-box bg-total">
                <h3><?php echo $stats['total_tickets']; ?></h3>
                <p>Tickets Totales</p>
            </div>
            <div class="stat-box bg-abiertos">
                <h3><?php echo $stats['tickets_abiertos']; ?></h3>
                <p>Abiertos (En Cola)</p>
            </div>
            <div class="stat-box bg-progreso">
                <h3><?php echo $stats['tickets_progreso']; ?></h3>
                <p>En Progreso</p>
            </div>
            <div class="stat-box bg-resueltos">
                <h3><?php echo $stats['tickets_resueltos']; ?></h3>
                <p>Resueltos</p>
            </div>
        </div>

        <div class="grid">
            <!-- Formulario de creación de usuarios -->
            <div class="card">
                <h3>Crear Nuevo Usuario</h3>
                <form action="admin.php" method="POST">
                    <label>Nombre Completo:</label>
                    <input type="text" name="nombre_completo" required>
                    <label>Correo Electrónico:</label>
                    <input type="email" name="email" required>
                    <label>Contraseña Temporal:</label>
                    <input type="password" name="password" required>
                    <label>Rol en el Sistema:</label>
                    <select name="rol" required>
                        <option value="empleado">Empleado (Solo reporta)</option>
                        <option value="tecnico">Técnico (Atiende tickets)</option>
                        <option value="admin">Administrador (Acceso total)</option>
                    </select>
                    <button type="submit" name="crear_usuario" class="btn-crear">Registrar Usuario</button>
                </form>
            </div>

            <!-- Directorio de Usuarios -->
            <div class="card">
                <h3>👥 Directorio de Credenciales</h3>
                <table>
                    <thead>
                        <tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Acción</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lista_usuarios as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['nombre_completo']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><span class="badge-rol rol-<?php echo $user['rol']; ?>"><?php echo strtoupper($user['rol']); ?></span></td>
                                <td>
                                    <form method="POST" style="margin:0;" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
                                        <input type="hidden" name="usuario_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="eliminar_usuario" class="btn-accion btn-eliminar">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Módulo de Auditoría de Tickets -->
        <div class="card">
            <h3>📊 Auditoría Global de Tickets</h3>
            <table>
                <thead>
                    <tr><th>ID</th><th>Incidente</th><th>Origen</th><th>Responsable</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($auditoria_tickets as $ticket): ?>
                        <tr>
                            <td>#<?php echo $ticket['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($ticket['titulo']); ?></strong></td>
                            <td><?php echo htmlspecialchars($ticket['creador_nombre'] ?? 'Usuario Eliminado'); ?></td>
                            <td><?php echo $ticket['tecnico_nombre'] ? htmlspecialchars($ticket['tecnico_nombre']) : '<span style="color: #9ca3af; font-style: italic;">Sin asignar</span>'; ?></td>
                            <td><?php echo $ticket['estado']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>