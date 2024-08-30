<?php
    require_once 'config.php';

class Cart {
    private $conn;

    public function __construct() {
        $this->conn = getDBConnection();
    }

    public function getCart($customerId) {
        $sql = "SELECT * FROM tb_cart WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function addToCart($customerId, $productId, $productName, $quantity, $price) {
        $cart = $this->getCart($customerId);
        if ($cart) {
            $items = json_decode($cart['items'], true);
            $items = array_filter($items, function($item) use ($productId) {
                return $item['product_id'] != $productId;
            });
            $items[] = ['product_id' => $productId, 'product_name'=>$productName, 'quantity' => $quantity, 'price'=>$price];
            $sql = "UPDATE tb_cart SET items = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $itemsJson = json_encode(array_values($items));
            $stmt->bind_param("si", $itemsJson, $customerId);
        } else {
            $sql = "INSERT INTO tb_cart (user_id, items) VALUES (?, ?)";
            $stmt = $this->conn->prepare($sql);
            $items = [['product_id' => $productId, 'product_name'=>$productName, 'quantity' => $quantity, 'price'=>$price]];
            $itemsJson = json_encode($items);
            $stmt->bind_param("is", $customerId, $itemsJson);
        }
        return $stmt->execute();
    }

    public function removeFromCart($customerId, $productId) {
        $cart = $this->getCart($customerId);
        if ($cart) {
            $items = json_decode($cart['items'], true);
            $items = array_filter($items, function($item) use ($productId) {
                return $item['product_id'] != $productId;
            });
            $sql = "UPDATE tb_cart SET items = ? WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $itemsJson = json_encode(array_values($items));
            $stmt->bind_param("si", $itemsJson, $customerId);
            return $stmt->execute();
        }
        return false;
    }

    public function clearCart($customerId) {
        $sql = "UPDATE tb_cart SET items = '[]' WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customerId);
        return $stmt->execute();
    }
}