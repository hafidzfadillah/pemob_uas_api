<?php
require_once 'config.php';

class User
{
    private $conn;

    public function __construct()
    {
        $this->conn = getDBConnection();
    }

    public function getUser($id)
    {
        $sql = "SELECT user_name, user_email, fcm_token, is_admin FROM tb_user WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getUserAdmin($id)
    {
        $sql = "SELECT * FROM tb_user WHERE is_admin = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function createUser($username, $email, $password, $fcmToken, $isAdmin)
    {
        $sql = "INSERT INTO tb_user (user_name, user_email, password, fcm_token, is_admin) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("sssss", $username, $email, $hashedPassword, $fcmToken, $isAdmin);
        return $stmt->execute();
    }

    public function updateFcmToken($id, $fcmToken)
    {
        $sql = "UPDATE tb_user SET fcm_token = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $fcmToken, $id);
        return $stmt->execute();
    }

    public function authenticateUser($username, $password, $fcm)
    {
        $sql = "SELECT * FROM tb_user WHERE user_email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $this->updateFcmToken($user['user_id'], $fcm);
            return $user;
        }
        return false;
    }

    public function emailExists($email) {
        $sql = "SELECT * FROM tb_user WHERE user_email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
    
        return $result->num_rows > 0; // Returns true if email exists
    }
}
