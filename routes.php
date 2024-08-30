<?php
// Router.php

class Router {
    private $routes = [];

    public function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => 'pemob-uas/api'.$path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    public function handleRequest($method, $path) {
        // echo $method;
        // echo '<br>';
        // echo $path;
        // echo '<br><br>';
        foreach ($this->routes as $route) {
            // echo $route['method'];
            // echo '<br>';
            // echo $route['path'];
            // echo '<br>';
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                $controller = new $route['controller']();
                $action = $route['action'];
                $params = $this->extractParams($route['path'], $path);
                call_user_func_array([$controller, $action], $params);
                return;
            }
        }
        
        // If no route matches, return a 404 error
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }

    private function matchPath($routePath, $requestPath) {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        if (count($routeParts) !== count($requestParts)) {
            return false;
        }

        foreach ($routeParts as $index => $routePart) {
            if ($routePart[0] === ':') {
                continue; // This is a parameter, skip matching
            }
            if ($routePart !== $requestParts[$index]) {
                return false;
            }
        }

        return true;
    }

    private function extractParams($routePath, $requestPath) {
        $params = [];
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        foreach ($routeParts as $index => $routePart) {
            if ($routePart[0] === ':') {
                $params[] = $requestParts[$index];
            }
        }

        return $params;
    }
}