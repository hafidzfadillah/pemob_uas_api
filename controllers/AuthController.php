<?php
// controllers/AuthController.php

require_once 'models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $fcm = $data['fcm_token'] ?? '';

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }

        $user = $this->userModel->authenticateUser($email, $password, $fcm);

        if ($user) {
            // Generate a simple token (in a real-world scenario, use a proper JWT library)
            $token = bin2hex(random_bytes(16));
            
            http_response_code(200);
            echo json_encode([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['user_id'],
                    'username' => $user['user_name'],
                    'email' => $user['user_email'],
                    'fcm_token' => $user['fcm_token'],
                    'is_admin' => $user['is_admin'],
                    'token' => $token
                ],
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    }

    public function register() {
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $fcmToken = $data['fcm_token'] ?? '';
        $isAdmin = $data['is_admin'] ?? 'false'; // Default role is customer

        if (empty($username) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Username, email, and password are required']);
            return;
        }

        if ($this->userModel->emailExists($email)) {
            http_response_code(409); // Conflict
            echo json_encode(['error' => 'Email already registered']);
            return;
        }

        if ($this->userModel->createUser($username, $email, $password, $fcmToken, $isAdmin)) {
            http_response_code(201);
            echo json_encode(['message' => 'User registered successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to register user']);
        }
    }

    public function getInfo($userId) {
        $user = $this->userModel->getUser($userId);

        if($user != null) {
            http_response_code(200);
            echo json_encode([
                'message'=>"User info found!",
                'data'=>$user
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'message'=>"User info not found",
                'data'=>null
            ]);
        }
    }
}