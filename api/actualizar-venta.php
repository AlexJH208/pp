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
if (!isset($data['id_registro']) || !isset($data['id_venta']) || !isset($data['fecha']) || !isset($data['id_producto']) || !isset($data['cantidad'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

// Incluir archivo de conexión
require_once 'conexion.php';

// Iniciar transacción
$conn->begin_transaction();

try {
    // Obtener datos de la venta actual
    $sql = "SELECT id_producto, cantidad FROM registro_ventas WHERE id_registro = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $data['id_registro']);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        throw new Exception("Venta no encontrada");
    }
    
    $ventaActual = $resultado->fetch_assoc();
    
    // Restaurar stock anterior
    $sql = "UPDATE productos SET stock = stock + ? WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $ventaActual['cantidad'], $ventaActual['id_producto']);
    $stmt->execute();
    
    // Obtener precio del nuevo producto
    $sql = "SELECT precio, stock FROM productos WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $data['id_producto']);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        throw new Exception("Producto no encontrado");
    }
    
    $producto = $resultado->fetch_assoc();
    
    // Verificar stock disponible
    if ($producto['stock'] < $data['cantidad']) {
        throw new Exception("Stock insuficiente. Disponible: " . $producto['stock']);
    }
    
    $precio_unitario = $producto['precio'];
    $subtotal = $precio_unitario * $data['cantidad'];
    $total = $subtotal; // Puedes modificar esto si hay descuentos o impuestos
    
    // Actualizar registro de venta
    $sql = "UPDATE registro_ventas SET id_venta = ?, fecha = ?, id_producto = ?, cantidad = ?, precio_unitario = ?, subtotal = ?, total = ? WHERE id_registro = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssiddddi",
        $data['id_venta'],
        $data['fecha'],
        $data['id_producto'],
        $data['cantidad'],
        $precio_unitario,
        $subtotal,
        $total,
        $data['id_registro']
    );
    $stmt->execute();
    
    // Actualizar stock del nuevo producto
    $sql = "UPDATE productos SET stock = stock - ? WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $data['cantidad'], $data['id_producto']);
    $stmt->execute();
    
    // Confirmar transacción
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Venta actualizada correctamente']);
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la venta: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>

