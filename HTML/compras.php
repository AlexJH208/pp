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
                                    <!-- Los datos se cargarán dinámicamente -->
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
        
        // Cargar datos
        fetch('datos.php')
            .then(response => response.json())
            .then(data => {
                // Llenar tabla de compras
                const comprasTable = document.getElementById('compras-table');
                comprasTable.innerHTML = data.compras.map(compra => `
                    <tr>
                        <td>${compra.id_compra}</td>
                        <td>${compra.id_proveedor}</td>
                        <td>${compra.fecha}</td>
                        <td>${compra.id_producto}</td>
                        <td>${compra.cantidad}</td>
                        <td>$${parseFloat(compra.costo_unitario).toFixed(2)}</td>
                        <td>$${parseFloat(compra.total).toFixed(2)}</td>
                    </tr>
                `).join('');
                
                // Crear gráfico de compras
                const comprasChart = document.getElementById('compras-chart').getContext('2d');
                
                // Agrupar compras por proveedor
                const comprasPorProveedor = {};
                data.compras.forEach(compra => {
                    if (comprasPorProveedor[compra.id_proveedor]) {
                        comprasPorProveedor[compra.id_proveedor] += parseFloat(compra.total);
                    } else {
                        comprasPorProveedor[compra.id_proveedor] = parseFloat(compra.total);
                    }
                });
                
                new Chart(comprasChart, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(comprasPorProveedor).map(id => `Proveedor ${id}`),
                        datasets: [{
                            label: 'Total de Compras',
                            data: Object.values(comprasPorProveedor),
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
            })
            .catch(error => console.error('Error al cargar los datos:', error));
    </script>
</body>
</html>

