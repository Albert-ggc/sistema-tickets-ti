<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'empleado') {
    header("Location: ../index.php");
    exit;
}
require_once '../config/db.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['titulo'])) {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $creador_id = $_SESSION['usuario_id'];

    if (!empty($titulo) && !empty($descripcion)) {
        try {
            // Iniciamos una transacción: o se guarda todo (ticket + imagen), o no se guarda nada
            $pdo->beginTransaction();

            // 1. Guardar el ticket base
            $stmt = $pdo->prepare("INSERT INTO tickets (titulo, descripcion, creador_id) VALUES (:titulo, :descripcion, :creador_id)");
            $stmt->execute(['titulo' => $titulo, 'descripcion' => $descripcion, 'creador_id' => $creador_id]);
            
            $ticket_id = $pdo->lastInsertId(); // Obtenemos el ID del ticket recién creado

            // 2. Procesar la subida de la imagen/evidencia (si el usuario adjuntó una)
            if (isset($_FILES['evidencia']) && $_FILES['evidencia']['error'] == UPLOAD_ERR_OK) {
                $nombre_temporal = $_FILES['evidencia']['tmp_name'];
                $nombre_original = basename($_FILES['evidencia']['name']);
                $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
                
                // Validamos que solo suban imágenes o PDFs por seguridad
                $formatos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
                
                if (in_array($extension, $formatos_permitidos)) {
                    // Renombramos el archivo para evitar duplicados o espacios en blanco
                    $nuevo_nombre = "ticket_" . $ticket_id . "_" . time() . "." . $extension;
                    $ruta_destino = "../assets/uploads/" . $nuevo_nombre;

                    // Movemos el archivo a nuestra carpeta uploads
                    if (move_uploaded_file($nombre_temporal, $ruta_destino)) {
                        // Guardamos la ruta en la base de datos
                        $ruta_bd = "assets/uploads/" . $nuevo_nombre;
                        $stmt_evidencia = $pdo->prepare("INSERT INTO evidencias_ticket (ticket_id, ruta_archivo) VALUES (:ticket_id, :ruta)");
                        $stmt_evidencia->execute(['ticket_id' => $ticket_id, 'ruta' => $ruta_bd]);
                    }
                }
            }

            $pdo->commit(); // Confirmamos los cambios
            $mensaje = "<div class='alerta exito'>¡Ticket #$ticket_id creado correctamente! Soporte lo revisará pronto.</div>";
            
        } catch (Exception $e) {
            $pdo->rollBack(); // Si algo falla, deshacemos el registro del ticket
            $mensaje = "<div class='alerta error'>Hubo un error al crear el ticket: " . $e->getMessage() . "</div>";
        }
    } else {
        $mensaje = "<div class='alerta error'>Por favor, completa el título y la descripción.</div>";
    }
}

// Obtener el historial de tickets del empleado
$stmt_tickets = $pdo->prepare("SELECT id, titulo, estado, fecha_creacion FROM tickets WHERE creador_id = :creador_id ORDER BY fecha_creacion DESC");
$stmt_tickets->execute(['creador_id' => $_SESSION['usuario_id']]);
$mis_tickets = $stmt_tickets->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Portal de Empleado</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; margin: 0; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); max-width: 1000px; margin: auto; }
        nav { background: #059669; padding: 15px; border-radius: 6px; margin-bottom: 20px; overflow: hidden; }
        nav a { color: white; text-decoration: none; margin-right: 15px; font-weight: bold; float: left; }
        .btn-logout { float: right; color: #a7f3d0; }
        
        .grid { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }
        .card { border: 1px solid #e5e7eb; padding: 20px; border-radius: 6px; background: #fff; }
        
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; color: #374151;}
        input[type="text"], input[type="file"], textarea { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; }
        button { background: #059669; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%;}
        button:hover { background: #047857; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background-color: #f9fafb; color: #4b5563; }
        .estado-Abierto { color: #d97706; font-weight: bold; }
        .estado-Resuelto { color: #059669; font-weight: bold; }
        .estado-EnProgreso { color: #2563eb; font-weight: bold; }
        
        .alerta { padding: 10px; margin-bottom: 20px; border-radius: 4px; text-align: center; font-weight: bold; }
        .exito { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
        .nota { font-size: 11px; color: #6b7280; margin-top: -10px; margin-bottom: 15px; display: block; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Portal del Empleado - <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></h2>
        <nav>
            <a href="empleado.php">Inicio</a>
            <a href="../logout.php" class="btn-logout">Cerrar Sesión</a>
        </nav>
        
        <?php if(!empty($mensaje)) echo $mensaje; ?>

        <div class="grid">
            <div class="card">
                <h3>Reportar Problema</h3>
                <!-- Agregamos enctype para poder subir archivos -->
                <form action="empleado.php" method="POST" enctype="multipart/form-data">
                    <label>Título del incidente:</label>
                    <input type="text" name="titulo" required placeholder="Ej. Pantalla azul">
                    
                    <label>Descripción detallada:</label>
                    <textarea name="descripcion" rows="5" required placeholder="Describe el error..."></textarea>
                    
                    <label>Captura de pantalla (Opcional):</label>
                    <!-- Input de tipo file -->
                    <input type="file" name="evidencia" accept=".jpg, .jpeg, .png, .gif, .pdf">
                    <span class="nota">Formatos: JPG, PNG, GIF o PDF.</span>
                    
                    <button type="submit">Generar Ticket</button>
                </form>
            </div>

            <div class="card">
                <h3>Mis Tickets Generados</h3>
                <?php if (count($mis_tickets) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Asunto</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mis_tickets as $ticket): ?>
                                <tr>
                                    <td>#<?php echo $ticket['id']; ?></td>
                                    <td><?php echo htmlspecialchars($ticket['titulo']); ?></td>
                                    <td><?php echo date("d/m H:i", strtotime($ticket['fecha_creacion'])); ?></td>
                                    <td class="estado-<?php echo str_replace(' ', '', $ticket['estado']); ?>">
                                        <?php echo $ticket['estado']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #6b7280; text-align: center;">No has reportado ningún problema todavía.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>