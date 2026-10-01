<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: my-orders.php");
    exit();
}

$user_id = (int)$_SESSION["user_id"];
$order_id = (int)($_POST["order_id"] ?? 0);

if ($order_id <= 0) {
    header("Location: my-orders.php?cancel=error");
    exit();
}

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare(
        "SELECT id, order_status FROM orders WHERE id = ? AND user_id = ? FOR UPDATE"
    );
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        throw new Exception("Order not found.");
    }

    if (!in_array($order["order_status"], ["Pending", "Processing"], true)) {
        throw new Exception("This order can no longer be cancelled.");
    }

    $items = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $items->bind_param("i", $order_id);
    $items->execute();
    $result = $items->get_result();

    $stock = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
    while ($item = $result->fetch_assoc()) {
        $quantity = (int)$item["quantity"];
        $product_id = (int)$item["product_id"];
        $stock->bind_param("ii", $quantity, $product_id);
        if (!$stock->execute()) {
            throw new Exception("Failed to restore product stock.");
        }
    }
    $stock->close();
    $items->close();

    $update = $conn->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE id = ?");
    $update->bind_param("i", $order_id);
    if (!$update->execute()) {
        throw new Exception("Failed to cancel order.");
    }
    $update->close();

    $conn->commit();
    header("Location: my-orders.php?cancel=success");
    exit();
} catch (Throwable $e) {
    $conn->rollback();
    header("Location: my-orders.php?cancel=error");
    exit();
}
?>
