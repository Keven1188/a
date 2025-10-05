<?php

namespace GamesStore\Core;

/**
 * Classe para padronizar respostas da API
 */
class Response
{
    /**
     * Enviar resposta de sucesso
     */
    public static function success($data = null, string $message = 'Sucesso', int $statusCode = 200): void
    {
        self::send([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
    
    /**
     * Enviar resposta de erro
     */
    public static function error(string $message = 'Erro', int $statusCode = 400, $errors = null): void
    {
        self::send([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
    
    /**
     * Enviar resposta paginada
     */
    public static function paginated(array $data, int $page, int $limit, int $total, string $message = 'Dados recuperados com sucesso'): void
    {
        $totalPages = ceil($total / $limit);
        
        self::send([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ], 200);
    }
    
    /**
     * Enviar resposta JSON
     */
    private static function send(array $data, int $statusCode): void
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }
    
    /**
     * Obter dados do corpo da requisição
     */
    public static function getRequestData(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }
        
        return $data ?? [];
    }
    
    /**
     * Validar campos obrigatórios
     */
    public static function validateRequired(array $data, array $required): array
    {
        $errors = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $errors[] = "O campo '{$field}' é obrigatório";
            } elseif (is_string($data[$field]) && empty(trim($data[$field]))) {
                $errors[] = "O campo '{$field}' é obrigatório";
            } elseif (is_array($data[$field]) && empty($data[$field])) {
                $errors[] = "O campo '{$field}' é obrigatório";
            }
        }
        
        return $errors;
    }
    
    /**
     * Sanitizar dados de entrada
     */
    public static function sanitize(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
}
