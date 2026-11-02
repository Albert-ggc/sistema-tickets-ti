<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema de Tickets</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 350px; }
        .login-box h2 { margin-top: 0; color: #374151; text-align: center; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; color: #4b5563; font-size: 14px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background-color: #2563eb; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        button:hover { background-color: #1d4ed8; }
        .error { background-color: #fee2e2; color: #991b1b; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; text-align: center; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Sistema de Tickets TI</h2>
    
    <!-- Mostrar mensaje de error si existe desde index.php -->
    <?php if(!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="index.php" method="POST">
        <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input type="email" id="email" name="email" required autocomplete="email">
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Ingresar</button>
    </form>
</div>

</body>
</html>