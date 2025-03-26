<?php
$servername = "localhost";
$database = "puntodeventa";
$username = "root";
$password = "";

// Crear conexión
$conn = mysqli_connect($servername, $username, $password, $database);

// Verificar conexión
if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}

// Configurar codificación UTF-8
mysqli_set_charset($conn, "utf8");
?>

