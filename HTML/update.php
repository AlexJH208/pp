<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conexion = new mysqli("localhost", "root", "", "puntodeventa");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$id_venta = $_POST['id_venta'];
$fecha = $_POST['fecha'];
$id_producto = $_POST['id_producto'];
$cantidad = $_POST['cantidad'];

$sqlPrecio = "SELECT precio FROM productos WHERE id_producto = $id_producto";
$resultado = $conexion->query($sqlPrecio);
$producto = $resultado->fetch_assoc();

if ($producto) {
    $precio_unitario = $producto['precio'];
    $subtotal = $precio_unitario * $cantidad;
    $total = $subtotal; // Puedes modificar esto si hay descuentos o impuestos.

    $sql = "INSERT INTO registro_ventas (id_venta, fecha, id_producto, cantidad, precio_unitario, subtotal, total) 
            VALUES ('$id_venta', '$fecha', '$id_producto', '$cantidad', '$precio_unitario', '$subtotal', '$total') 
            ON DUPLICATE KEY UPDATE cantidad = '$cantidad', subtotal = '$subtotal', total = '$total'";

    if ($conexion->query($sql) === TRUE) {
        echo "Registro actualizado correctamente";
    } else {
        echo "Error al actualizar: " . $conexion->error;
    }
} else {
    echo "Error: Producto no encontrado.";
}

$conexion->close();
?>
