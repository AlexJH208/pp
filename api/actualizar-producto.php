<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
    exit;
}

// Validar datos requeridos
if (!isset($data['id_producto']) || !isset($data['nombre']) || !isset($data['precio']) || !isset($data['stock']) || !isset($data['id_proveedor'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

// Incluir archivo de conexión
require_once 'conexion.php';

// Actualizar producto
$sql = "UPDATE productos SET nombre = ?, precio = ?, stock = ?, id_proveedor = ? WHERE id_producto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sdiii", $data['nombre'], $data['precio'], $data['stock'], $data['id_proveedor'], $data['id_producto']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Producto actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el producto: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

