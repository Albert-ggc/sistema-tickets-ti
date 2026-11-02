<?php
$host = 'localhost';
$dbname = 'sistema_tickets';
$username = 'root'; // Usuario por defecto de XAMPP
$password = '';     // XAMPP no tiene contraseña por defecto

try {
    // Se crea la conexión
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Se configura para que lance excepciones si hay errores SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Se configura para que devuelva arreglos asociativos por defecto
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Si falla, detenemos la ejecución y mostramos el error
    die("Error de conexión: " . $e->getMessage());
}
?>