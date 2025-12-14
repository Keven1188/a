<?php

namespace GamesStore\Core;

/**
 * Roteador principal da API
 */
class Router
{
    private array $routes = [];
    private string $basePath;
    
    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }
    
    /**
     * Registrar rota GET
     */
    public function get(string $path, callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }
    
    /**
     * Registrar rota POST
     */
    public function post(string $path, callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }
    
    /**
     * Registrar rota PUT
     */
    public function put(string $path, callable $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }
    
    /**
     * Registrar rota DELETE
     */
    public function delete(string $path, callable $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    /**
     * Adicionar rota ao registro
     */
    private function addRoute(string $method, string $path, callable $handler): void
    {
        $path = $this->basePath . '/' . ltrim($path, '/');
        $pattern = $this->convertToRegex($path);
        
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
            'path' => $path
        ];
    }
    
    /**
     * Converter caminho para regex
     */
    private function convertToRegex(string $path): string
    {
        // Normalizar o caminho (remover barras duplas e normalizar)
        $path = trim($path, '/');
        if (empty($path)) {
            return '/^\/?$/';
        }
        
        // Dividir o caminho em partes (separadas por /)
        $parts = explode('/', $path);
        $patternParts = [];
        
        foreach ($parts as $part) {
            if (empty($part)) {
                continue;
            }
            
            if (preg_match('/^\{([^}]+)\}$/', $part, $matches)) {
                // É um parâmetro {id}
                $patternParts[] = '([^\/]+)';
            } else {
                // É uma parte literal - escapar caracteres especiais (mas não a barra)
                $patternParts[] = preg_quote($part, '/');
            }
        }
        
        // Construir o padrão com barras escapadas corretamente
        // As barras devem ser escapadas como \/ na regex
        $pattern = '/^\/' . implode('\/', $patternParts) . '$/';
        
        return $pattern;
    }
    
    /**
     * Processar requisição atual
     */
    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover index.php do caminho se presente
        $uri = preg_replace('/\/index\.php/', '', $uri);
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                // Remover o primeiro elemento (match completo)
                array_shift($matches);
                
                // Executar handler com parâmetros capturados
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }
        
        // Rota não encontrada
        Response::error('Endpoint não encontrado', 404);
    }
    
    /**
     * Listar todas as rotas registradas (para debug)
     */
    public function getRoutes(): array
    {
        return array_map(function($route) {
            return [
                'method' => $route['method'],
                'path' => $route['path']
            ];
        }, $this->routes);
    }
}
