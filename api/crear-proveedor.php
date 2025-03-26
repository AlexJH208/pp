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
if (!isset($data['nombre']) || !isset($data['telefono']) || !isset($data['direccion'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

// Incluir archivo de conexión
require_once 'conexion.php';

// Insertar nuevo proveedor
$sql = "INSERT INTO proveedores (nombre, telefono, direccion) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $data['nombre'], $data['telefono'], $data['direccion']);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'message' => 'Proveedor creado correctamente',
        'id' => $conn->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al crear el proveedor: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

