<?php
/**
 * Games Store API - Ponto de entrada principal
 * 
 * Este arquivo serve como roteador principal da API RESTful
 * para o sistema de loja de jogos.
 */

require_once __DIR__ . '/../src/autoload.php';

use GamesStore\Core\Router;
use GamesStore\Core\Database;
use GamesStore\Core\Response;

// Configurar headers CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Tratar requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Inicializar banco de dados
    $database = new Database();
    $database->initializeDatabase();
    
    // Inicializar roteador
    $router = new Router();
    
    // Registrar rotas da API
    require_once __DIR__ . '/../src/routes/api.php';
    
    // Processar requisição
    $router->handleRequest();
    
} catch (Exception $e) {
    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
}
