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
if (!isset($data['id_proveedor']) || !isset($data['nombre']) || !isset($data['telefono']) || !isset($data['direccion'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

// Incluir archivo de conexión
require_once 'conexion.php';

// Actualizar proveedor
$sql = "UPDATE proveedores SET nombre = ?, telefono = ?, direccion = ? WHERE id_proveedor = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $data['nombre'], $data['telefono'], $data['direccion'], $data['id_proveedor']);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Proveedor actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el proveedor: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

