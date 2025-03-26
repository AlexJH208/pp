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

if (!$data || !isset($data['id_proveedor'])) {
    echo json_encode(['success' => false, 'message' => 'ID de proveedor no proporcionado']);
    exit;
}

// Incluir archivo de conexión
require_once 'conexion.php';

// Verificar si el proveedor está siendo utilizado en productos o compras
$sql = "SELECT COUNT(*) as total FROM productos WHERE id_proveedor = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $data['id_proveedor']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    echo json_encode(['success' => false, 'message' => 'No se puede eliminar el proveedor porque está siendo utilizado en productos']);
    $stmt->close();
    $conn->close();
    exit;
}

$sql = "SELECT COUNT(*) as total FROM compras_proveedores WHERE id_proveedor = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $data['id_proveedor']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['total'] > 0) {
    echo json_encode(['success' => false, 'message' => 'No se puede eliminar el proveedor porque está siendo utilizado en compras']);
    $stmt->close();
    $conn->close();
    exit;
}

// Eliminar proveedor
$sql = "DELETE FROM proveedores WHERE id_proveedor = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $data['id_proveedor']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Proveedor eliminado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el proveedor: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

