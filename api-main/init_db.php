<?php
// init_db.php - execute a partir da raiz do projeto (sprint3)
// Localiza automaticamente o Database.php dentro de api/models e cria a pasta database se precisar.

$base = __DIR__; // caminho para sprint3
$dbModelPath = $base .  '/api/config/database.php';
$dbFolder = $base . '/api/database';

// Confere se o arquivo existe
if (!file_exists($dbModelPath)) {
    echo "Arquivo Database.php não encontrado em: $dbModelPath\n";
    exit(1);
}

require_once $dbModelPath;

if (!is_dir($dbFolder)) {
    if (!mkdir($dbFolder, 0777, true)) {
        echo "Falha ao criar pasta database em: $dbFolder\n";
        exit(1);
    }
    chmod($dbFolder, 0777);
}

$db = new Database();
if ($db->initDatabase()) {
    echo "Banco inicializado com sucesso em: " . $dbFolder . "/games_store.db\n";
} else {
    echo "Falha ao inicializar o banco\n";
}
