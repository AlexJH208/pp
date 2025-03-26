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

if (!$data || !isset($data['id_compra'])) {
    echo json_encode(['success' => false, 'message' => 'ID de compra no proporcionado']);
    exit;
}

// Incluir archivo de conexión
require_once 'conexion.php';

// Iniciar transacción
$conn->begin_transaction();

try {
    // Obtener datos de la compra
    $sql = "SELECT id_producto, cantidad FROM compras_proveedores WHERE id_compra = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $data['id_compra']);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        throw new Exception("Compra no encontrada");
    }
    
    $compra = $resultado->fetch_assoc();
    
    // Actualizar stock
    $sql = "UPDATE productos SET stock = stock - ? WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $compra['cantidad'], $compra['id_producto']);
    $stmt->execute();
    
    // Eliminar compra
    $sql = "DELETE FROM compras_proveedores WHERE id_compra = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $data['id_compra']);
    $stmt->execute();
    
    // Confirmar transacción
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Compra eliminada correctamente']);
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la compra: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>

