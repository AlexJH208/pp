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

if (!$data || !isset($data['id_registro'])) {
    echo json_encode(['success' => false, 'message' => 'ID de registro no proporcionado']);
    exit;
}


// Incluir archivo de conexión
require_once 'conexion.php';

// Iniciar transacción
$conn->begin_transaction();

try {
    // Obtener datos de la venta
    $sql = "SELECT id_producto, cantidad FROM registro_ventas WHERE id_registro = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $data['id_registro']);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        throw new Exception("Venta no encontrada");
    }
    
    $venta = $resultado->fetch_assoc();
    
    // Restaurar stock
    $sql = "UPDATE productos SET stock = stock + ? WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $venta['cantidad'], $venta['id_producto']);
    $stmt->execute();
    
    // Eliminar venta
    $sql = "DELETE FROM registro_ventas WHERE id_registro = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $data['id_registro']);
    $stmt->execute();
    
    // Confirmar transacción
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Venta eliminada correctamente']);
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error al eliminar la venta: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>

