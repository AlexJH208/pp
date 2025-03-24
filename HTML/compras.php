<?php
// Incluir archivo de conexión
require_once 'conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compras - Sistema de Punto de Venta</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo">Sistema de Punto de Venta</a>
            <nav>
                <a href="ventas.php">Ventas</a>
                <a href="productos.php">Productos</a>
                <a href="proveedores.php">Proveedores</a>
                <a href="compras.php">Compras</a>
            </nav>
        </div>
    </header>
    
    <main>
        <div class="container">
            <h1>Compras a Proveedores</h1>
            
            <div class="tabs">
                <div class="tab active" onclick="showTab('tabla-compras')">Tabla de Compras</div>
                <div class="tab" onclick="showTab('grafico-compras')">Gráfico de Compras</div>
            </div>
            
            <div id="tabla-compras" class="tab-content active">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Registro de Compras</h2>
                    </div>
                    <div class="card-content">
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID Compra</th>
                                        <th>ID Proveedor</th>
                                        <th>Fecha</th>
                                        <th>ID Producto</th>
                                        <th>Cantidad</th>
                                        <th>Costo Unitario</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody id="compras-table">
                                    <?php
                                    // Consulta de compras
                                    $result = $conn->query("SELECT * FROM compras_proveedores");
                                    
                                    if ($result) {
                                        while ($compra = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . $compra['id_compra'] . "</td>";
                                            echo "<td>" . $compra['id_proveedor'] . "</td>";
                                            echo "<td>" . $compra['fecha'] . "</td>";
                                            echo "<td>" . $compra['id_producto'] . "</td>";
                                            echo "<td>" . $compra['cantidad'] . "</td>";
                                            echo "<td>$" . number_format($compra['costo_unitario'], 2) . "</td>";
                                            echo "<td>$" . number_format($compra['total'], 2) . "</td>";
                                            echo "</tr>";
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="grafico-compras" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Gráfico de Compras</h2>
                    </div>
                    <div class="card-content">
                        <div class="chart-container">
                            <canvas id="compras-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="container footer-content">
            <p class="footer-text">© <?php echo date('Y'); ?> Sistema de Punto de Venta. Todos los derechos reservados.</p>
        </div>
    </footer>
    
    <script>
        // Función para mostrar/ocultar tabs
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.getElementById(tabId).classList.add('active');
            document.querySelector(`.tab[onclick="showTab('${tabId}')"]`).classList.add('active');
        }
        
        // Crear gráfico de compras
        <?php
        // Consulta de compras para el gráfico
        $result = $conn->query("
            SELECT id_proveedor, SUM(total) AS total
            FROM compras_proveedores
            GROUP BY id_proveedor
        ");
        
        $comprasPorProveedor = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $comprasPorProveedor["Proveedor " . $row['id_proveedor']] = floatval($row['total']);
            }
        }
        
        // Cerrar conexión
        $conn->close();
        ?>
        
        const comprasChart = document.getElementById('compras-chart').getContext('2d');
        
        new Chart(comprasChart, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($comprasPorProveedor)); ?>,
                datasets: [{
                    label: 'Total de Compras',
                    data: <?php echo json_encode(array_values($comprasPorProveedor)); ?>,
                    backgroundColor: '#82ca9d',
                    borderColor: '#3cba9f',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

