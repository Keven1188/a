<?php

namespace GamesStore\Config;

/**
 * Configurações do banco de dados
 */
class Database
{
    const HOST = 'localhost';
    const DATABASE_NAME = 'games_store';
    const USERNAME = 'root';
    const PASSWORD = '';
    const CHARSET = 'utf8mb4';
    
    /**
     * Obter string de conexão DSN
     */
    public static function getDsn(): string
    {
        return sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            self::HOST,
            self::DATABASE_NAME,
            self::CHARSET
        );
    }
    
    /**
     * Obter opções de conexão PDO
     */
    public static function getOptions(): array
    {
        return [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }
}
