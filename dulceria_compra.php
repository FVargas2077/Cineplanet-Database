<?php
// Página para seleccionar productos de dulcería y procesar la compra.

require_once 'includes/public_header.php';
if (!isset($_SESSION['user_dni'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

$dni_cliente = $_SESSION['user_dni'];
$error_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cart_data'])) {
    $cart_data = json_decode($_POST['cart_data'], true);
    $metodo_pago = $_POST['metodo_pago'];

    if (empty($cart_data) || !is_array($cart_data)) {
        $error_message = "Error: El carrito está vacío.";
    } else {
        $conn->begin_transaction();
        try {
            // Calcular monto original (sin descuento)
            $monto_original = 0;
            foreach ($cart_data as $item) {
                $monto_original += $item['price'] * $item['quantity'];
            }

            // Verificar si es socio
            $sql_socio = "SELECT es_socio FROM Cliente WHERE DNI = ?";
            $stmt_socio = $conn->prepare($sql_socio);
            $stmt_socio->bind_param("s", $dni_cliente);
            $stmt_socio->execute();
            $es_socio = $stmt_socio->get_result()->fetch_assoc()['es_socio'];

            // Aplicar descuento si es socio
            $total_compra = $monto_original;
            if ($es_socio) {
                $total_compra *= 0.85;
            }

            // Redondeo
            $total_compra = round($total_compra * 10) / 10;

            // Guardar monto original para mostrar luego
            if ($es_socio) {
                $_SESSION['monto_original'] = $monto_original;
           }

            // 1. Insertar en la tabla Compra
            $sql_compra = "INSERT INTO Compra (DNI_cliente, total, metodo_pago) VALUES (?, ?, ?)";
            $stmt_compra = $conn->prepare($sql_compra);
            $stmt_compra->bind_param("sds", $dni_cliente, $total_compra, $metodo_pago);
            $stmt_compra->execute();
            $id_compra_nueva = $conn->insert_id;

            // 2. Insertar cada producto en la tabla de detalle
            $sql_detalle = "INSERT INTO Detalle_Compra_Dulceria (ID_compra, ID_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
            $stmt_detalle = $conn->prepare($sql_detalle);

            foreach ($cart_data as $item) {
                $precio_unitario = $item['price'];
                if ($es_socio) {
                    $precio_unitario *= 0.85;
                }
                $precio_unitario = round($precio_unitario * 10) / 10;

                $stmt_detalle->bind_param("iiid", $id_compra_nueva, $item['id'], $item['quantity'], $precio_unitario);
                $stmt_detalle->execute();
            }


            $conn->commit();
            header("Location: compra_exitosa.php?id_compra=" . $id_compra_nueva);
            exit();

        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            $error_message = "Error al procesar la compra: " . $exception->getMessage();
        }
    }
}

// Consultar todos los productos de la dulcería
$productos_result = $conn->query("SELECT ID_producto, nombre, categoria, precio_unitario FROM Dulceria WHERE stock > 0 ORDER BY categoria, nombre");
?>

<div class="container">
    <h2>Compra en nuestra Dulcería</h2>
    <p>Añade productos a tu carrito y completa tu pedido.</p>

    <?php if (!empty($error_message)): ?>
        <div class="message error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="dulceria-compra-container">
        <!-- Tabla de Productos -->
        <div class="product-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($producto = $productos_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td>S/ <?php echo number_format($producto['precio_unitario'], 2); ?></td>
                        <td>
                            <input type="number" class="quantity-input" value="1" min="1" max="10" id="qty-<?php echo $producto['ID_producto']; ?>">
                        </td>
                        <td>
                            <button class="btn-add-cart" 
                                    data-id="<?php echo $producto['ID_producto']; ?>"
                                    data-name="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                    data-price="<?php echo $producto['precio_unitario']; ?>">
                                Añadir
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Carrito de Compras -->
        <div class="cart-container">
            <h3>Mi Carrito</h3>
            <div id="cart-items">
                <p>El carrito está vacío.</p>
            </div>
            <div class="cart-summary">
                <p><strong>Total:</strong> S/ <span id="cart-total">0.00</span></p>
                <form action="dulceria_compra.php" method="POST" id="cart-form">
                    <div class="form-group">
                        <label for="metodo_pago">Método de Pago:</label>
                        <select name="metodo_pago" id="metodo_pago">
                            <option value="Tarjeta">Tarjeta de Crédito/Débito</option>
                            <option value="Yape">Yape</option>
                        </select>
                    </div>
                    <input type="hidden" name="cart_data" id="cart-data-input">
                    <button type="submit" class="btn" id="btn-checkout" disabled>Completar Compra</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cart = {}; // { productId: { name, price, quantity } }
    const btnAddCart = document.querySelectorAll('.btn-add-cart');
    const cartItemsDiv = document.getElementById('cart-items');
    const cartTotalSpan = document.getElementById('cart-total');
    const cartDataInput = document.getElementById('cart-data-input');
    const btnCheckout = document.getElementById('btn-checkout');

    btnAddCart.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price);
            const quantityInput = document.getElementById('qty-' + id);
            const quantity = parseInt(quantityInput.value);

            if (cart[id]) {
                cart[id].quantity += quantity;
            } else {
                cart[id] = { id, name, price, quantity };
            }
            updateCart();
        });
    });

    function updateCart() {
        cartItemsDiv.innerHTML = '';
        let total = 0;
        const productIds = Object.keys(cart);

        if (productIds.length === 0) {
            cartItemsDiv.innerHTML = '<p>El carrito está vacío.</p>';
            btnCheckout.disabled = true;
        } else {
            const ul = document.createElement('ul');
            productIds.forEach(id => {
                const item = cart[id];
                total += item.price * item.quantity;
                const li = document.createElement('li');
                li.innerHTML = `<span>${item.quantity} x ${item.name}</span> <span>S/ ${(item.price * item.quantity).toFixed(2)}</span>`;
                ul.appendChild(li);
            });
            cartItemsDiv.appendChild(ul);
            btnCheckout.disabled = false;
        }

        cartTotalSpan.textContent = total.toFixed(2);
        cartDataInput.value = JSON.stringify(Object.values(cart));
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
