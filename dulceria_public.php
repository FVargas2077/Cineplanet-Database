<?php
require_once 'includes/public_header.php';
?>
<div class="container">
    <div class="cartelera-header">
        <h2>Nuestra Dulcería</h2>
        <a href="dulceria_compra.php" class="btn">Comprar Productos</a>
    </div>
    <p>Conoce los productos que tenemos para ti.</p>
    
    <div class="info-grid">
        <?php
        $sql = "SELECT nombre, categoria, precio_unitario FROM Dulceria ORDER BY categoria, nombre";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($producto = $result->fetch_assoc()) {
        ?>
            <div class="info-card">
                <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                <p><strong>Categoría:</strong> <?php echo htmlspecialchars($producto['categoria']); ?></p>
                <p class="price">S/ <?php echo number_format($producto['precio_unitario'], 2); ?></p>
            </div>
        <?php
            }
        } else {
            echo "<p>No hay productos de dulcería registrados en este momento.</p>";
        }
        ?>
    </div>
</div>
<?php
require_once 'includes/footer.php';
?>
