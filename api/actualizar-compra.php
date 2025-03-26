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
if (!isset($data['id_compra']) || !isset($data['id_proveedor']) || !isset($data['fecha']) || !isset($data['id_producto']) || !isset($data['cantidad']) || !isset($data['costo_unitario'])) {
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
    // Obtener datos de la compra actual
    $sql = "SELECT id_producto, cantidad FROM compras_proveedores WHERE id_compra = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $data['id_compra']);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        throw new Exception("Compra no encontrada");
    }
    
    $compraActual = $resultado->fetch_assoc();
    
    // Restaurar stock anterior
    $sql = "UPDATE productos SET stock = stock - ? WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $compraActual['cantidad'], $compraActual['id_producto']);
    $stmt->execute();
    
    // Actualizar registro de compra
    $sql = "UPDATE compras_proveedores SET id_proveedor = ?, fecha = ?, id_producto = ?, cantidad = ?, costo_unitario = ?, total = ? WHERE id_compra = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "isiiddi",
        $data['id_proveedor'],
        $data['fecha'],
        $data['id_producto'],
        $data['cantidad'],
        $data['costo_unitario'],
        $total,
        $data['id_compra']
    );
    $stmt->execute();
    
    // Actualizar stock del nuevo producto
    $sql = "UPDATE productos SET stock = stock + ? WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $data['cantidad'], $data['id_producto']);
    $stmt->execute();
    
    // Confirmar transacción
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Compra actualizada correctamente']);
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la compra: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>

