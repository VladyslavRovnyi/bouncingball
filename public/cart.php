<?php
session_start();
include '../src/db.php';

// Ensure secure session ID generation
if (!isset($_SESSION['session_id'])) {
    session_regenerate_id(true);
    $_SESSION['session_id'] = uniqid();
}
$session_id = $_SESSION['session_id'];

// Add to cart logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);

    if (!$product_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid product ID.']);
        exit;
    }

    try {
        // Check if the product is already in the cart
        $stmt = $conn->prepare("SELECT * FROM cart WHERE session_id = :session_id AND product_id = :product_id");
        $stmt->execute(['session_id' => $session_id, 'product_id' => $product_id]);
        $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cart_item) {
            // If product exists, increase quantity
            $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = :id");
            $stmt->execute(['id' => $cart_item['id']]);
        } else {
            // Otherwise, add new product to cart
            $stmt = $conn->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (:session_id, :product_id, 1)");
            $stmt->execute(['session_id' => $session_id, 'product_id' => $product_id]);
        }

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'An error occurred. Please try again later.']);
        exit;
    }
}

// Fetch cart items
try {
    $stmt = $conn->prepare("
        SELECT c.quantity, p.name, p.price 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.session_id = :session_id
    ");
    $stmt->execute(['session_id' => $session_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $cart_items = [];
    $total = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <h1>Your Cart</h1>
</header>
<nav>
    <a href="index.php">Home</a>
    <a href="products.php">Products</a>
    <a href="checkout.php">Checkout</a>
</nav>
<div class="container">
    <?php if (!empty($cart_items)): ?>
        <table>
            <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>$<?= number_format($item['price'], 2) ?></td>
                    <td><?= htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p><strong>Grand Total:</strong> $<?= number_format($total, 2) ?></p>
        <a href="checkout.php"><button>Proceed to Checkout</button></a>
    <?php else: ?>
        <p>Your cart is empty.</p>
        <a href="products.php"><button>Browse Products</button></a>
    <?php endif; ?>
</div>
<footer>
    Rolex Shop Honeypot | All Rights Reserved
</footer>
</body>
</html>
