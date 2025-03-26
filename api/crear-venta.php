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
if (!isset($data['id_venta']) || !isset($data['fecha']) || !isset($data['id_producto']) || !isset($data['cantidad'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

// Incluir archivo de conexión
require_once 'conexion.php';

// Obtener el precio del producto
$sqlPrecio = "SELECT precio, stock FROM productos WHERE id_producto = ?";
$stmt = $conn->prepare($sqlPrecio);
$stmt->bind_param("i", $data['id_producto']);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    $stmt->close();
    $conn->close();
    exit;
}

$producto = $resultado->fetch_assoc();

// Verificar stock disponible
if ($producto['stock'] < $data['cantidad']) {
    echo json_encode(['success' => false, 'message' => 'Stock insuficiente. Disponible: ' . $producto['stock']]);
    $stmt->close();
    $conn->close();
    exit;
}

$precio_unitario = $producto['precio'];
$subtotal = $precio_unitario * $data['cantidad'];
$total = $subtotal; // Puedes modificar esto si hay descuentos o impuestos

// Iniciar transacción
$conn->begin_transaction();

try {
    // Insertar registro de venta
    $sql = "INSERT INTO registro_ventas (id_venta, fecha, id_producto, cantidad, precio_unitario, subtotal, total) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssidddd",
        $data['id_venta'],
        $data['fecha'],
        $data['id_producto'],
        $data['cantidad'],
        $precio_unitario,
        $subtotal,
        $total
    );
    $stmt->execute();
    
    // Actualizar stock del producto
    $nuevoStock = $producto['stock'] - $data['cantidad'];
    $sql = "UPDATE productos SET stock = ? WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $nuevoStock, $data['id_producto']);
    $stmt->execute();
    
    // Confirmar transacción
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Venta registrada correctamente',
        'id' => $conn->insert_id
    ]);
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error al registrar la venta: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>

