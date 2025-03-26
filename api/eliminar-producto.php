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

if (!$data || !isset($data['id_producto'])) {
    echo json_encode(['success' => false, 'message' => 'ID de producto no proporcionado']);
    exit;
}

// Incluir archivo de conexión
require_once 'conexion.php';

// Verificar si el producto está siendo utilizado en ventas o compras
$sql = "SELECT COUNT(*) as total FROM registro_ventas WHERE id_producto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $data['id_producto']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    echo json_encode(['success' => false, 'message' => 'No se puede eliminar el producto porque está siendo utilizado en ventas']);
    $stmt->close();
    $conn->close();
    exit;
}

$sql = "SELECT COUNT(*) as total FROM compras_proveedores WHERE id_producto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $data['id_producto']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    echo json_encode(['success' => false, 'message' => 'No se puede eliminar el producto porque está siendo utilizado en compras']);
    $stmt->close();
    $conn->close();
    exit;
}

// Eliminar producto
$sql = "DELETE FROM productos WHERE id_producto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $data['id_producto']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

