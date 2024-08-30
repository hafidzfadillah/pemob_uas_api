<?php
    require_once 'config.php';

    class Order{
        private $conn;

        public function __construct()
        {
            $this->conn = getDBConnection();
        }

        public function getAllOrders() {
            $sql = "SELECT 
                tb_order.order_id, 
                tb_order.items, 
                tb_order.total_amount,
                tb_order.order_date,
                tb_order.order_lat,
                tb_order.order_lon,
                tb_order.order_status,
                tb_user.user_name
                FROM tb_order JOIN tb_user 
                ON tb_order.user_id = tb_user.user_id
                ORDER BY order_id DESC";
            $result = $this->conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function getOrderById($id) {
            $sql = "SELECT * FROM tb_order WHERE order_id = ? ORDER BY order_id DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }

        public function getOrdersByUser($userId) {
            $sql = "SELECT * FROM tb_order WHERE user_id = ? ORDER BY order_id DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $orders = [];
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
            return $orders;
        }

        public function createOrder($customerId, $items, $totalAmount, $lat, $lng) {
            $sql = "INSERT INTO tb_order (user_id, items, total_amount, order_date, order_status, order_lat, order_lon) VALUES (?, ?, ?, NOW(), 'Pending', ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $itemsJson = json_encode($items);
            $stmt->bind_param("isddd", $customerId, $itemsJson, $totalAmount, $lat, $lng);
            
            if ($stmt->execute()) {
                return $this->conn->insert_id;  // Return the ID of the newly created order
            } else {
                return false;
            }
        }

        public function updateStatus($id, $status) {
            $sql = "UPDATE tb_order SET order_status = ? WHERE order_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("si", $status, $id);
            return $stmt->execute();
        }
    }
?>