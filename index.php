<?php
session_start(); // Inicia el manejo de sesiones de PHP
require_once 'config/db.php';

// 1. Si el usuario ya tiene sesión activa, lo enviamos directamente a su panel
if (isset($_SESSION['usuario_id'])) {
    $rol = $_SESSION['usuario_rol'];
    if ($rol == 'admin') { header("Location: vistas/admin.php"); exit; }
    if ($rol == 'tecnico') { header("Location: vistas/tecnico.php"); exit; }
    header("Location: vistas/empleado.php"); exit;
}

$error = '';

// 2. Procesar el formulario cuando el usuario hace clic en "Ingresar"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Buscamos al usuario por correo
    $stmt = $pdo->prepare("SELECT id, nombre_completo, password_hash, rol FROM usuarios WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch();

    // password_verify compara la contraseña plana con la encriptada en la BD
    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        // Credenciales correctas: Creamos las variables de sesión
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre_completo'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        
        // Recargamos la página para que se active la redirección del paso 1
        header("Location: index.php");
        exit;
    } else {
        $error = 'Correo o contraseña incorrectos.';
    }
}

// 3. Si no hay sesión ni datos enviados, mostramos la pantalla de login
require 'vistas/login.php';
?>