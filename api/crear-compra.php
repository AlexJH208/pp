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
if (!isset($data['id_proveedor']) || !isset($data['fecha']) || !isset($data['id_producto']) || !isset($data['cantidad']) || !isset($data['costo_unitario'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

// Incluir archivo de conexión
require_once 'conexion.php';

// Calcular total
$total = $data['cantidad'] * $data['costo_unitario'];

// Iniciar transacción
$conn->begin_transaction();

try {
    // Insertar registro de compra
    $sql = "INSERT INTO compras_proveedores (id_proveedor, fecha, id_producto, cantidad, costo_unitario, total) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "isiidd",
        $data['id_proveedor'],
        $data['fecha'],
        $data['id_producto'],
        $data['cantidad'],
        $data['costo_unitario'],
        $total
    );
    $stmt->execute();
    
    // Actualizar stock del producto
    $sql = "UPDATE productos SET stock = stock + ? WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $data['cantidad'], $data['id_producto']);
    $stmt->execute();
    
    // Confirmar transacción
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Compra registrada correctamente',
        'id' => $conn->insert_id
    ]);
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error al registrar la compra: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>

