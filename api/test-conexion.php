<?php
// Incluir archivo de conexión
require_once 'conexion.php';

// Verificar conexión
if ($conn) {
    echo "Conexión exitosa a la base de datos.";
    
    // Intentar hacer una consulta simple
    $result = $conn->query("SELECT 1");
    if ($result) {
        echo "<br>Consulta de prueba exitosa.";
    } else {
        echo "<br>Error en consulta de prueba: " . $conn->error;
    }
} else {
    echo "Error de conexión: " . mysqli_connect_error();
}

// Cerrar conexión
$conn->close();
?>

