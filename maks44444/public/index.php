<?php
declare(strict_types=1);

session_start();

spl_autoload_register(function (string $class): void {
    $baseDir = dirname(__DIR__) . '/src/';
    $paths = [
        $baseDir . 'Controllers/' . $class . '.php',
        $baseDir . 'Models/' . $class . '.php',
        $baseDir . 'Repositories/' . $class . '.php',
        $baseDir . 'Exceptions/' . $class . '.php',
        $baseDir . $class . '.php',
    ];

    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

$router = new Router();

$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/', [TaskController::class, 'index']);
$router->get('/tasks/create', [TaskController::class, 'createForm']);
$router->post('/tasks/create', [TaskController::class, 'store']);
$router->post('/tasks/complete', [TaskController::class, 'complete']);
$router->post('/tasks/delete', [TaskController::class, 'delete']);

$router->dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');