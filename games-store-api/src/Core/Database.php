<?php

namespace GamesStore\Core;

use GamesStore\Config\Database as DatabaseConfig;
use PDO;
use PDOException;

/**
 * Classe principal para gerenciamento do banco de dados
 */
class Database
{
    private static ?PDO $connection = null;
    
    /**
     * Obter conexão com o banco de dados (Singleton)
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                self::$connection = new PDO(
                    DatabaseConfig::getDsn(),
                    DatabaseConfig::USERNAME,
                    DatabaseConfig::PASSWORD,
                    DatabaseConfig::getOptions()
                );
            } catch (PDOException $e) {
                throw new \Exception('Erro na conexão com o banco de dados: ' . $e->getMessage());
            }
        }
        
        return self::$connection;
    }
    
    /**
     * Inicializar estrutura do banco de dados
     */
    public function initializeDatabase(): void
    {
        $connection = self::getConnection();
        
        // Criar tabela de usuários
        $this->createUsersTable($connection);
        
        // Criar tabela de produtos
        $this->createProductsTable($connection);
        
        // Criar tabela de pedidos
        $this->createOrdersTable($connection);
        
        // Criar tabela de itens do pedido
        $this->createOrderItemsTable($connection);
        
        // Inserir dados de exemplo
        $this->insertSampleData($connection);
    }
    
    /**
     * Criar tabela de usuários
     */
    private function createUsersTable(PDO $connection): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(120) UNIQUE NOT NULL,
            senha VARCHAR(255) NOT NULL,
            nome_completo VARCHAR(200),
            telefone VARCHAR(20),
            data_nascimento DATE,
            tipo_usuario ENUM('cliente', 'admin') DEFAULT 'cliente',
            ativo BOOLEAN DEFAULT TRUE,
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            ultimo_login DATETIME,
            INDEX idx_username (username),
            INDEX idx_email (email),
            INDEX idx_tipo_usuario (tipo_usuario)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $connection->exec($sql);
    }
    
    /**
     * Criar tabela de produtos
     */
    private function createProductsTable(PDO $connection): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS produtos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(200) NOT NULL,
            descricao TEXT,
            preco DECIMAL(10,2) NOT NULL,
            categoria VARCHAR(100) NOT NULL,
            plataforma VARCHAR(100) NOT NULL,
            desenvolvedor VARCHAR(150) NOT NULL,
            publisher VARCHAR(150) NOT NULL,
            data_lancamento DATE,
            classificacao_etaria VARCHAR(10),
            estoque INT DEFAULT 0,
            ativo BOOLEAN DEFAULT TRUE,
            imagem_url VARCHAR(500),
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_categoria (categoria),
            INDEX idx_plataforma (plataforma),
            INDEX idx_preco (preco),
            INDEX idx_ativo (ativo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $connection->exec($sql);
    }
    
    /**
     * Criar tabela de pedidos
     */
    private function createOrdersTable(PDO $connection): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS pedidos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            status ENUM('pendente', 'processando', 'enviado', 'entregue', 'cancelado') DEFAULT 'pendente',
            total DECIMAL(10,2) NOT NULL,
            data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
            data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            endereco_entrega TEXT,
            observacoes TEXT,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            INDEX idx_usuario_id (usuario_id),
            INDEX idx_status (status),
            INDEX idx_data_pedido (data_pedido)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $connection->exec($sql);
    }
    
    /**
     * Criar tabela de itens do pedido
     */
    private function createOrderItemsTable(PDO $connection): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS itens_pedido (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pedido_id INT NOT NULL,
            produto_id INT NOT NULL,
            quantidade INT NOT NULL DEFAULT 1,
            preco_unitario DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
            FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
            INDEX idx_pedido_id (pedido_id),
            INDEX idx_produto_id (produto_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $connection->exec($sql);
    }
    
    /**
     * Inserir dados de exemplo
     */
    private function insertSampleData(PDO $connection): void
    {
        // Verificar se já existem dados
        $stmt = $connection->query("SELECT COUNT(*) FROM usuarios");
        if ($stmt->fetchColumn() > 0) {
            return; // Dados já existem
        }
        
        // Inserir usuário administrador
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (username, email, senha, nome_completo, tipo_usuario) VALUES 
                ('admin', 'admin@gamesstore.com', ?, 'Administrador do Sistema', 'admin')";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$adminPassword]);
        
        // Inserir usuário cliente de exemplo
        $clientPassword = password_hash('cliente123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (username, email, senha, nome_completo, tipo_usuario) VALUES 
                ('cliente_teste', 'cliente@email.com', ?, 'Cliente de Teste', 'cliente')";
        $stmt = $connection->prepare($sql);
        $stmt->execute([$clientPassword]);
        
        // Inserir produtos de exemplo
        $produtos = [
            ['The Witcher 3: Wild Hunt', 'RPG épico em mundo aberto', 59.99, 'RPG', 'PC', 'CD Projekt Red', 'CD Projekt', '2015-05-19', 'M', 100],
            ['Cyberpunk 2077', 'RPG futurístico em Night City', 79.99, 'RPG', 'PC', 'CD Projekt Red', 'CD Projekt', '2020-12-10', 'M', 50],
            ['Red Dead Redemption 2', 'Aventura no Velho Oeste', 69.99, 'Ação', 'PC', 'Rockstar Games', 'Rockstar Games', '2018-10-26', 'M', 75],
            ['Grand Theft Auto V', 'Ação em mundo aberto', 49.99, 'Ação', 'PC', 'Rockstar Games', 'Rockstar Games', '2013-09-17', 'M', 200],
            ['Minecraft', 'Jogo de construção e sobrevivência', 29.99, 'Sandbox', 'PC', 'Mojang Studios', 'Microsoft', '2011-11-18', 'E', 500]
        ];
        
        $sql = "INSERT INTO produtos (nome, descricao, preco, categoria, plataforma, desenvolvedor, publisher, data_lancamento, classificacao_etaria, estoque) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        
        foreach ($produtos as $produto) {
            $stmt->execute($produto);
        }
    }
}
