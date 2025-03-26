<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener datos del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    // Si no hay datos JSON, intentar obtener de POST
    $data = $_POST;
}

// Validar datos requeridos
if (!isset($data['id_venta']) || !isset($data['fecha']) || !isset($data['id_producto']) || !isset($data['cantidad'])) {
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

// Incluir archivo de conexión
require_once 'conexion.php';

// Obtener el precio del producto
$sqlPrecio = "SELECT precio FROM productos WHERE id_producto = ?";
$stmt = $conn->prepare($sqlPrecio);
$stmt->bind_param("i", $data['id_producto']);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode(['error' => 'Producto no encontrado']);
    exit;
}

$producto = $resultado->fetch_assoc();
$precio_unitario = $producto['precio'];
$subtotal = $precio_unitario * $data['cantidad'];
$total = $subtotal; // Puedes modificar esto si hay descuentos o impuestos

// Insertar o actualizar el registro de venta
$sql = "INSERT INTO registro_ventas (id_venta, fecha, id_producto, cantidad, precio_unitario, subtotal, total) 
        VALUES (?, ?, ?, ?, ?, ?, ?) 
        ON DUPLICATE KEY UPDATE cantidad = ?, subtotal = ?, total = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssidddiddd",
    $data['id_venta'],
    $data['fecha'],
    $data['id_producto'],
    $data['cantidad'],
    $precio_unitario,
    $subtotal,
    $total,
    $data['cantidad'],
    $subtotal,
    $total
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Registro actualizado correctamente']);
} else {
    echo json_encode(['error' => 'Error al actualizar: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

