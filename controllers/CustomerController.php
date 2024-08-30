<?php
// controllers/CustomerController.php

require_once 'models/Product.php';
require_once 'models/Order.php';
require_once 'models/Cart.php';
require_once 'services/NotificationService.php';

class CustomerController {
    private $productModel;
    private $orderModel;
    private $cartModel;
    private $notificationService;

    public function __construct() {
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->cartModel = new Cart();
        $this->notificationService = new NotificationService();
    }

    public function getProducts() {
        $products = $this->productModel->getAllProducts(true);
        echo json_encode($products);
    }

    public function getProduct($id) {
        $product = $this->productModel->getProduct($id);
        if ($product) {
            echo json_encode($product);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
        }
    }

    public function getCart($userId) {
        $cart = $this->cartModel->getCart($userId);

        if($cart == null) {
            http_response_code(404);
            echo json_encode(['error'=>'Cart data not found',]);
            return;
        }

        http_response_code(200);
        echo json_encode($cart);
    }

    public function addToCart() {
        $data = json_decode(file_get_contents('php://input'), true);
        $productId = $data['product_id'] ?? 0;
        $customerId = $data['user_id'] ?? 0;
        $productName = $data['product_name'] ?? '';
        $quantity = $data['quantity'] ?? 1;
        $price = $data['price'] ?? 0;

        if ($productId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Product ID is required']);
            return;
        }

        if ($this->cartModel->addToCart($customerId, $productId, $productName, $quantity, $price)) {
            http_response_code(200);

            echo json_encode(['message' => 'Product added to cart successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add product to cart']);
        }
    }

    public function removeFromCart() {
        $data = json_decode(file_get_contents('php://input'), true);
        $productId = $data['product_id'] ?? 0;
        $customerId = $data['user_id'] ?? 0;

        if ($this->cartModel->removeFromCart($customerId, $productId)) {
            http_response_code(200);
            echo json_encode(['message' => 'Product removed from cart successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to remove product from cart']);
        }
    }

    public function createOrder() {
        $data = json_decode(file_get_contents('php://input'), true);
        $customerId = $data['user_id'] ?? 0;
        $items = $data['items'] ?? '[]';
        $items = json_decode($items, true);
        $totalAmount = $data['total'] ?? 0;
        $lat = $data['lat'] ?? 0;
        $lng = $data['lng'] ?? 0;

        $orderId = $this->orderModel->createOrder($customerId, $items, $totalAmount, $lat, $lng);

        if ($orderId) {
            // Clear the cart after creating the order
            $this->cartModel->clearCart($customerId);
            
            $this->notificationService->sendOrderCreatedNotification($orderId);
            http_response_code(201);
            echo json_encode(['message' => 'Order created successfully', 'order_id' => $orderId]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create order']);
        }
    }

    public function getOrders($userId) {
        $orders = $this->orderModel->getOrdersByUser($userId);

        if($orders == null) {
            http_response_code(404);
            echo json_encode(['error'=>'Order history not found']);
            return;
        }

        http_response_code(200);
        echo json_encode($orders);
    }

    public function getOrder($customerId, $orderId) {
        $order = $this->orderModel->getOrderById($orderId);
        if ($order && $order['customer_id'] == $customerId) {
            echo json_encode($order);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Order not found']);
        }
    }
}