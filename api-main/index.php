<?php
header("Content-Type: application/json");

// Ex.: /sprint3/api/usuario  ou /api/usuario
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$segments = array_values(array_filter(explode('/', $path))); // remove vazios

// localizar 'api' e pegar o próximo segmento
$apiPos = array_search('api', $segments);
$endpoint = ($apiPos !== false && isset($segments[$apiPos + 1])) ? $segments[$apiPos + 1] : null;

$method = $_SERVER['REQUEST_METHOD'];

switch ($endpoint) {
    case "usuario":
        require_once __DIR__ . "/controllers/UsuarioController.php";
        $controller = new UsuarioController();
        break;
    case "produto":
        require_once __DIR__ . "/controllers/ProdutoController.php";
        $controller = new ProdutoController();
        break;
    case "carrinho":
        require_once __DIR__ . "/controllers/CarrinhoController.php";
        $controller = new CarrinhoController();
        break;
    case "pagamentos":
        require_once __DIR__ . "/controllers/PagamentoController.php";
        $controller = new PagamentoController();
        break;
    default:
        http_response_code(404);
        echo json_encode(["erro" => "Endpoint não encontrado"]);
        exit;
}

switch ($method) {
    case "GET":
        $controller->index();
        break;
    case "POST":
        $controller->store();
        break;
    case "PUT":
        $controller->update();
        break;
    case "DELETE":
        $controller->delete();
        break;
    default:
        http_response_code(405);
        echo json_encode(["erro" => "Método não permitido"]);
        break;
}
