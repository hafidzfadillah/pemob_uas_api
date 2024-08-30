<?php
// services/NotificationService.php

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;

require_once 'models/Order.php';
require_once 'models/User.php';

require 'vendor/autoload.php';

class NotificationService {
    private $fcmUrl = 'https://fcm.googleapis.com/v1/projects/flutter-commerce-1c739/messages:send';
    private $orderModel;
    private $userModel;

    public function __construct() {
        $this->orderModel = new Order();
        $this->userModel = new User();
    }

    public function sendOrderStatusUpdate($orderId, $status) {
        $order = $this->orderModel->getOrderById($orderId);
        $user = $this->userModel->getUser($order['user_id']);
        $admin = $this->userModel->getUserAdmin($order['user_id']);

        // Notify customer
        $customerFields = [
            'message'=>[
                'token' => $user['fcm_token'],
                'notification' => [
                    'title' => 'Order Status Update',
                    'body' => "Your order #$orderId has been updated to $status"
                ],
                'data' => [
                    'order_id' => $orderId,
                    'status' => $status
                ]
            ]
        ];
        $this->sendPushNotification($customerFields);

        // Notify admin
        $adminFields = [
            'message'=>[
                'token' => $admin['fcm_token'],
                'notification' => [
                    'title' => 'Order Status Updated',
                    'body' => "Order #$orderId has been updated to $status"
                ],
                'data' => [
                    'order_id' => $orderId,
                    'status' => $status,
                    'customer_id' => $order['user_id']
                ]
            ]
        ];
        $this->sendPushNotification($adminFields);
    }

    public function sendOrderCreatedNotification($orderId) {
        $order = $this->orderModel->getOrderById($orderId);
        $user = $this->userModel->getUser($order['user_id']);
        $admin = $this->userModel->getUserAdmin($order['user_id']);

        $fields = [
            'message'=>[
                'token' => $user['fcm_token'],
                'notification' => [
                    'title' => 'New Order Created',
                    'body' => "Your order #$orderId has been successfully placed. Total amount: Rp" . number_format($order['total_amount'], 2)
                ],
                'data' => [
                    'order_id' => $orderId,
                    'total_amount' => $order['total_amount']
                ]
            ]
        ];

        $this->sendPushNotification($fields);

        // Notify admin
        $adminFields = [
            'message'=> [
                'token' => $admin['fcm_token'],
                'notification' => [
                    'title' => 'New Order Received',
                    'body' => "Order #$orderId has been placed. Total amount: Rp" . number_format($order['total_amount'], 2)
                ],
                // 'data' => [
                //     'order_id' => $orderId,
                //     'total_amount' => $order['total_amount'],
                //     'customer_id' => $order['user_id']
                // ]
            ]
        ];
        $this->sendPushNotification($adminFields);
    }

    private function sendPushNotification($fields) {
        $credentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            json_decode(file_get_contents("pvKey.json"), true)
        );
        
        $token = $credentials->fetchAuthToken(HttpHandlerFactory::build());

        $headers = [
            'Authorization: Bearer ' . $token['access_token'],
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);

        return $result;
    }
}