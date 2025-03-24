<?php
header("Content-Type: application/json");
$conexion = new mysqli("localhost", "root", "", "puntodeventa");

if ($conexion->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conexion->connect_error]));
}

$result = $conexion->query("
    SELECT rv.id_registro, rv.id_venta, rv.fecha, p.nombre AS producto, rv.cantidad, rv.subtotal, rv.total
    FROM registro_ventas rv
    JOIN productos p ON rv.id_producto = p.id_producto
");

$ventas = [];
while ($row = $result->fetch_assoc()) {
    $ventas[] = $row;
}

$result = $conexion->query("SELECT * FROM productos");
$productos = [];
while ($row = $result->fetch_assoc()) {
    $productos[] = $row;
}

$result = $conexion->query("SELECT * FROM proveedores");
$proveedores = [];
while ($row = $result->fetch_assoc()) {
    $proveedores[] = $row;
}

$result = $conexion->query("SELECT * FROM compras_proveedores");
$compras = [];
while ($row = $result->fetch_assoc()) {
    $compras[] = $row;
}

echo json_encode([
    "ventas" => $ventas,
    "productos" => $productos,
    "proveedores" => $proveedores,
    "compras" => $compras
]);

$conexion->close();
?>
