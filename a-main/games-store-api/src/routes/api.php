<?php
/**
 * Definição das rotas da API
 */

use GamesStore\Controllers\UserController;
use GamesStore\Controllers\ProductController;
use GamesStore\Controllers\OrderController;

// Rota de informações da API
$router->get('/', function() {
    \GamesStore\Core\Response::success([
        'name' => 'Games Store API',
        'version' => '1.0.0',
        'description' => 'API RESTful para sistema de loja de jogos',
        'endpoints' => [
            'users' => '/api/users',
            'products' => '/api/products',
            'orders' => '/api/orders'
        ]
    ], 'API Games Store funcionando corretamente');
});

// Rota de status da API
$router->get('/api/status', function() {
    \GamesStore\Core\Response::success([
        'status' => 'online',
        'timestamp' => date('Y-m-d H:i:s'),
        'database' => 'connected'
    ], 'API está funcionando');
});

// ===== ROTAS DE USUÁRIOS =====
$userController = new UserController();

// CRUD básico de usuários
$router->get('/api/users', [$userController, 'index']);
$router->get('/api/users/{id}', [$userController, 'show']);
$router->post('/api/users', [$userController, 'create']);
$router->put('/api/users/{id}', [$userController, 'update']);
$router->delete('/api/users/{id}', [$userController, 'delete']);

// Rotas especiais de usuários
$router->post('/api/users/login', [$userController, 'login']);
$router->get('/api/users/type/{type}', [$userController, 'byType']);

// ===== ROTAS DE PRODUTOS =====
$productController = new ProductController();

// Rotas específicas PRIMEIRO (antes das genéricas)
$router->get('/api/products/search', [$productController, 'search']);
$router->get('/api/products/categories', [$productController, 'categories']);
$router->get('/api/products/platforms', [$productController, 'platforms']);
$router->get('/api/products/bestsellers', [$productController, 'bestSellers']);
$router->get('/api/products/onsale', [$productController, 'onSale']);
$router->get('/api/products/category/{category}', [$productController, 'byCategory']);
$router->get('/api/products/platform/{platform}', [$productController, 'byPlatform']);

// Rotas genéricas DEPOIS
$router->get('/api/products', [$productController, 'index']);
// $router->get('/api/products/{id}', [$productController, 'show']);
$router->get('/api/products/{id}', function($id) {
    \GamesStore\Core\Response::success(['id' => $id, 'teste' => 'funcionou'], 'Teste de parâmetro');
});
$router->post('/api/products', [$productController, 'create']);
$router->put('/api/products/{id}', [$productController, 'update']);
$router->delete('/api/products/{id}', [$productController, 'delete']);

// ===== ROTAS DE PEDIDOS =====
$orderController = new OrderController();

// CRUD básico de pedidos
$router->get('/api/orders', [$orderController, 'index']);
$router->get('/api/orders/{id}', [$orderController, 'show']);
$router->post('/api/orders', [$orderController, 'create']);

// Rotas especiais de pedidos
$router->put('/api/orders/{id}/status', [$orderController, 'updateStatus']);
$router->put('/api/orders/{id}/cancel', [$orderController, 'cancel']);
$router->get('/api/orders/user/{userId}', [$orderController, 'byUser']);
$router->get('/api/orders/status/{status}', [$orderController, 'byStatus']);
$router->get('/api/orders/stats', [$orderController, 'stats']);
