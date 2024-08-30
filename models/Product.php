<?php
    require_once 'config.php';

    class Product{
        private $conn;

        public function __construct()
        {
            $this->conn = getDBConnection();
        }

        public function getAllProducts($isCustomer) {
            $sql = "SELECT * FROM tb_product";
            if($isCustomer) {
                $sql .= " WHERE is_publish = true";
            }
            $result = $this->conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        }

        public function getProduct($id) {
            $sql = "SELECT * FROM tb_product WHERE product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }

        public function createProduct($name, $desc, $price, $stock) {
            $sql = "INSERT INTO tb_product (product_name, product_desc, product_price, product_stock, is_publish) VALUES (?, ?, ?, ?, 0)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssdi", $name, $desc, $price, $stock);
            return $stmt->execute();
        }

        public function updateProduct($id, $name, $description, $price, $stock, $isPublish) {
            $sql = "UPDATE tb_product SET product_name = ?, product_desc = ?, product_price = ?, product_stock = ?, is_publish = ? WHERE product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssdiii", $name, $description, $price, $stock, $isPublish, $id);
            return $stmt->execute();
        }

        public function deleteProduct($id) {
            $sql = "DELETE FROM tb_product WHERE product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        }
    }
?>