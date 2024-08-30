<?php
// controllers/AdminController.php

require_once 'models/Product.php';
require_once 'models/Order.php';
require_once 'services/NotificationService.php';

class AdminController {
    private $productModel;
    private $orderModel;
    private $notificationService;

    public function __construct() {
        $this->productModel = new Product();
        $this->orderModel = new Order();
        $this->notificationService = new NotificationService();
    }

    public function getProducts() {
        $products = $this->productModel->getAllProducts(false);
        echo json_encode($products);
    }

    public function createProduct() {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $price = $data['price'] ?? 0;
        $stock = $data['stock'] ?? 0;

        if (empty($name) || $price <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Name and price are required']);
            return;
        }

        if ($this->productModel->createProduct($name, $description, $price, $stock)) {
            http_response_code(201);
            echo json_encode(['message' => 'Product created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create product']);
        }
    }

    public function updateProduct($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $price = $data['price'] ?? 0;
        $stock = $data['stock'] ?? 0;
        $isPublish = $data['is_publish'] ?? false;

        if (empty($name) || $price <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Name and price are required']);
            return;
        }

        if ($this->productModel->updateProduct($id, $name, $description, $price, $stock, $isPublish)) {
            http_response_code(200);
            echo json_encode(['message' => 'Product updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update product']);
        }
    }

    public function deleteProduct($id) {
        if ($this->productModel->deleteProduct($id)) {
            http_response_code(200);
            echo json_encode(['message' => 'Product deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete product']);
        }
    }

    public function getOrders() {
        $orders = $this->orderModel->getAllOrders();
        echo json_encode($orders);
    }

    public function updateOrderStatus($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $status = $data['status'] ?? '';

        if (empty($status)) {
            http_response_code(400);
            echo json_encode(['error' => 'Status is required']);
            return;
        }

        if ($this->orderModel->updateStatus($id, $status)) {
            $this->notificationService->sendOrderStatusUpdate($id, $status);
            http_response_code(200);
            echo json_encode(['message' => 'Order status updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update order status']);
        }
    }
}