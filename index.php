<?php
// index.php

require_once 'routes.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/AdminController.php';
require_once 'controllers/CustomerController.php';
require_once 'controllers/GlobalController.php';

$router = new Router();

$router->addRoute('GET','/','GlobalController','index');

// Auth routes
$router->addRoute('POST', '/auth/login', 'AuthController', 'login');
$router->addRoute('POST', '/auth/register', 'AuthController', 'register');
$router->addRoute('GET', '/auth/getUser/:userId', 'AuthController', 'getInfo');

// Admin routes
$router->addRoute('GET', '/admin/products', 'AdminController', 'getProducts');
$router->addRoute('POST', '/admin/products', 'AdminController', 'createProduct');
$router->addRoute('PUT', '/admin/products/:id', 'AdminController', 'updateProduct');
$router->addRoute('DELETE', '/admin/products/:id', 'AdminController', 'deleteProduct');
$router->addRoute('GET', '/admin/orders', 'AdminController', 'getOrders');
$router->addRoute('PUT', '/admin/orders/:id/status', 'AdminController', 'updateOrderStatus');

// Customer routes
$router->addRoute('GET', '/products', 'CustomerController', 'getProducts');
$router->addRoute('GET', '/products/:id', 'CustomerController', 'getProduct');
$router->addRoute('GET', '/cart/:userId', 'CustomerController', 'getCart');
$router->addRoute('POST', '/cart/items', 'CustomerController', 'addToCart');
$router->addRoute('DELETE', '/cart/items', 'CustomerController', 'removeFromCart');
$router->addRoute('POST', '/orders', 'CustomerController', 'createOrder');
$router->addRoute('GET', '/orders/:userId', 'CustomerController', 'getOrders');
// $router->addRoute('GET', '/orders/:id', 'CustomerController', 'getOrder');

// Handle the request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->handleRequest($method, $path);