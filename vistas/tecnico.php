<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'tecnico') {
    header("Location: ../index.php");
    exit;
}
require_once '../config/db.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tomar_ticket'])) {
        $ticket_id = $_POST['ticket_id'];
        $tecnico_id = $_SESSION['usuario_id'];
        
        $stmt = $pdo->prepare("UPDATE tickets SET tecnico_id = :tecnico_id, estado = 'En Progreso' WHERE id = :id");
        if ($stmt->execute(['tecnico_id' => $tecnico_id, 'id' => $ticket_id])) {
            $mensaje = "<div class='alerta exito'>Te has asignado el ticket #$ticket_id correctamente.</div>";
        }
    } elseif (isset($_POST['resolver_ticket'])) {
        $ticket_id = $_POST['ticket_id'];
        
        $stmt = $pdo->prepare("UPDATE tickets SET estado = 'Resuelto', fecha_cierre = CURRENT_TIMESTAMP WHERE id = :id");
        if ($stmt->execute(['id' => $ticket_id])) {
            $mensaje = "<div class='alerta exito'>Ticket #$ticket_id marcado como Resuelto.</div>";
        }
    } elseif (isset($_POST['escalar_ticket'])) {
        // LÓGICA DE ESCALADO: Liberamos el técnico y cambiamos el estado
        $ticket_id = $_POST['ticket_id'];
        $stmt = $pdo->prepare("UPDATE tickets SET tecnico_id = NULL, estado = 'Escalado' WHERE id = :id");
        if ($stmt->execute(['id' => $ticket_id])) {
            $mensaje = "<div class='alerta exito'>El ticket #$ticket_id ha sido escalado a la cola general para soporte de Nivel 2.</div>";
        }
    }
}

// 1. Obtener la Cola General 
// (Actualizado: Ahora busca tickets 'Abiertos' O 'Escalados' y ordena los escalados primero)
$stmt_cola = $pdo->query("SELECT t.id, t.titulo, t.descripcion, t.estado, t.fecha_creacion, 
                                 u.nombre_completo AS creador, e.ruta_archivo 
                          FROM tickets t 
                          JOIN usuarios u ON t.creador_id = u.id 
                          LEFT JOIN evidencias_ticket e ON t.id = e.ticket_id
                          WHERE t.tecnico_id IS NULL AND t.estado IN ('Abierto', 'Escalado') 
                          ORDER BY t.estado DESC, t.fecha_creacion ASC");
$tickets_cola = $stmt_cola->fetchAll();

// 2. Obtener los tickets asignados a este técnico
$stmt_mis_tickets = $pdo->prepare("SELECT t.id, t.titulo, t.descripcion, t.estado, t.fecha_creacion, 
                                          u.nombre_completo AS creador, e.ruta_archivo 
                                   FROM tickets t 
                                   JOIN usuarios u ON t.creador_id = u.id 
                                   LEFT JOIN evidencias_ticket e ON t.id = e.ticket_id
                                   WHERE t.tecnico_id = :tecnico_id 
                                   ORDER BY t.estado ASC, t.fecha_creacion DESC");
$stmt_mis_tickets->execute(['tecnico_id' => $_SESSION['usuario_id']]);
$mis_tickets = $stmt_mis_tickets->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Técnico TI</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; margin: 0; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 1100px; margin: auto; }
        nav { background: #2563eb; padding: 15px; border-radius: 6px; margin-bottom: 20px; overflow: hidden; }
        nav a { color: white; text-decoration: none; margin-right: 15px; font-weight: bold; float: left; }
        .btn-logout { float: right; color: #bfdbfe; }
        
        .card { border: 1px solid #e5e7eb; padding: 20px; border-radius: 6px; background: #fff; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        th { background-color: #f9fafb; color: #4b5563; }
        
        .btn-accion { padding: 8px 12px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; color: white; width: 100%; margin-bottom: 5px; font-size: 12px; }
        .btn-tomar { background-color: #2563eb; }
        .btn-tomar:hover { background-color: #1d4ed8; }
        .btn-resolver { background-color: #059669; }
        .btn-resolver:hover { background-color: #047857; }
        .btn-escalar { background-color: #ea580c; } /* Naranja para advertencia */
        .btn-escalar:hover { background-color: #c2410c; }
        
        .btn-adjunto { display: inline-block; padding: 4px 8px; background-color: #f3f4f6; color: #374151; text-decoration: none; border: 1px solid #d1d5db; border-radius: 4px; font-size: 12px; font-weight: bold; margin-top: 8px; }
        
        .detalles-incidente { max-width: 400px; }
        .descripcion { font-size: 13px; color: #6b7280; margin-top: 5px; line-height: 1.4; }
        
        .estado-EnProgreso { color: #2563eb; font-weight: bold; }
        .estado-Resuelto { color: #059669; font-weight: bold; }
        .estado-Escalado { color: #ea580c; font-weight: bold; }
        
        .badge-escalado { background-color: #fee2e2; color: #991b1b; padding: 3px 6px; border-radius: 4px; font-size: 11px; margin-left: 5px; border: 1px solid #f87171; }
        
        .alerta { padding: 10px; margin-bottom: 20px; border-radius: 4px; text-align: center; font-weight: bold; }
        .exito { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Área de Soporte - <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></h2>
        <nav>
            <a href="tecnico.php">Gestor de Incidentes</a>
            <a href="../logout.php" class="btn-logout">Cerrar Sesión</a>
        </nav>
        
        <?php if(!empty($mensaje)) echo $mensaje; ?>

        <div class="card">
            <h3>📥 Cola General (Sin Asignar)</h3>
            <?php if (count($tickets_cola) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Reporta</th>
                            <th>Detalles del Incidente</th>
                            <th>Fecha</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets_cola as $ticket): ?>
                            <tr>
                                <td>
                                    #<?php echo $ticket['id']; ?>
                                    <?php if($ticket['estado'] === 'Escalado'): ?>
                                        <br><span class="badge-escalado">⚠️ TIER 2</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($ticket['creador']); ?></strong></td>
                                <td class="detalles-incidente">
                                    <strong><?php echo htmlspecialchars($ticket['titulo']); ?></strong>
                                    <div class="descripcion"><?php echo nl2br(htmlspecialchars($ticket['descripcion'])); ?></div>
                                    <?php if ($ticket['ruta_archivo']): ?>
                                        <a href="../<?php echo htmlspecialchars($ticket['ruta_archivo']); ?>" target="_blank" class="btn-adjunto">📎 Ver Adjunto</a>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date("d/m H:i", strtotime($ticket['fecha_creacion'])); ?></td>
                                <td>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                        <button type="submit" name="tomar_ticket" class="btn-accion btn-tomar">
                                            <?php echo ($ticket['estado'] === 'Escalado') ? 'Asumir Escalado' : 'Asignarme Ticket'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #6b7280;">No hay tickets nuevos en la cola general.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>🛠️ Mi Área de Trabajo</h3>
            <?php if (count($mis_tickets) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Reporta</th>
                            <th>Detalles del Incidente</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mis_tickets as $ticket): ?>
                            <tr>
                                <td>#<?php echo $ticket['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($ticket['creador']); ?></strong></td>
                                <td class="detalles-incidente">
                                    <strong><?php echo htmlspecialchars($ticket['titulo']); ?></strong>
                                    <div class="descripcion"><?php echo nl2br(htmlspecialchars($ticket['descripcion'])); ?></div>
                                    <?php if ($ticket['ruta_archivo']): ?>
                                        <a href="../<?php echo htmlspecialchars($ticket['ruta_archivo']); ?>" target="_blank" class="btn-adjunto">📎 Ver Adjunto</a>
                                    <?php endif; ?>
                                </td>
                                <td class="estado-<?php echo str_replace(' ', '', $ticket['estado']); ?>">
                                    <?php echo $ticket['estado']; ?>
                                </td>
                                <td>
                                    <?php if ($ticket['estado'] !== 'Resuelto'): ?>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                        <button type="submit" name="resolver_ticket" class="btn-accion btn-resolver">✅ Marcar Resuelto</button>
                                        <button type="submit" name="escalar_ticket" class="btn-accion btn-escalar" onclick="return confirm('¿Seguro que deseas escalar este ticket a otro técnico?');">⚠️ Escalar Problema</button>
                                    </form>
                                    <?php else: ?>
                                        <span style="color: #6b7280; font-size: 13px; font-weight: bold;">Finalizado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #6b7280;">Aún no te has asignado ningún ticket.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>