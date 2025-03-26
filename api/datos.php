<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Incluir archivo de conexión
require_once 'conexion.php';

// Consulta de ventas
$result = $conn->query("
    SELECT rv.id_registro, rv.id_venta, rv.fecha, p.nombre AS producto, rv.cantidad, rv.subtotal, rv.total
    FROM registro_ventas rv
    JOIN productos p ON rv.id_producto = p.id_producto
");

$ventas = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $ventas[] = $row;
    }
}

// Consulta de productos
$result = $conn->query("SELECT * FROM productos");
$productos = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
}

// Consulta de proveedores
$result = $conn->query("SELECT * FROM proveedores");
$proveedores = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $proveedores[] = $row;
    }
}

// Consulta de compras
$result = $conn->query("SELECT * FROM compras_proveedores");
$compras = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $compras[] = $row;
    }
}

// Devolver datos en formato JSON
echo json_encode([
    "ventas" => $ventas,
    "productos" => $productos,
    "proveedores" => $proveedores,
    "compras" => $compras
]);

// Cerrar conexión
$conn->close();
?>


