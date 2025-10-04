<?php
/**
 * Autoloader personalizado para o projeto Games Store API
 */

spl_autoload_register(function ($className) {
    // Namespace base do projeto
    $baseNamespace = 'GamesStore\\';
    
    // Verificar se a classe pertence ao nosso namespace
    if (strpos($className, $baseNamespace) !== 0) {
        return;
    }
    
    // Remover o namespace base
    $relativeClassName = substr($className, strlen($baseNamespace));
    
    // Substituir barras invertidas por barras normais
    $relativeClassName = str_replace('\\', '/', $relativeClassName);
    
    // Construir o caminho do arquivo
    $filePath = __DIR__ . '/' . $relativeClassName . '.php';
    
    // Carregar o arquivo se existir
    if (file_exists($filePath)) {
        require_once $filePath;
    }
});
