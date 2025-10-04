<?php
class Database
{
    private $host = 'localhost';
    private $db_name = 'games_store';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection()
    {

        $this->conn = null;

        try {

            $this->conn = new PDO("sqlite:" . __DIR__ . "/../database/games_store.db");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {

            echo "Erro de conexão: " . $exception->getMessage();
        }

        return $this->conn;
    }

    public function initDatabase()
    {

        $conn = $this->getConnection();

        $sql = "CREATE TABLE IF NOT EXISTS usuarios (

            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(80) UNIQUE NOT NULL,
            email VARCHAR(120) UNIQUE NOT NULL,
            senha VARCHAR(255) NOT NULL,
            nome_completo VARCHAR(200),
            telefone VARCHAR(20),
            data_nascimento DATE,
            tipo_usuario VARCHAR(20) DEFAULT 'cliente',
            ativo BOOLEAN DEFAULT 1,
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            ultimo_login DATETIME

        )";
        $conn->exec($sql);


        $sql = "CREATE TABLE IF NOT EXISTS produtos (
        
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome VARCHAR(200) NOT NULL,
            descricao TEXT,
            preco DECIMAL(10,2) NOT NULL,
            categoria VARCHAR(100) NOT NULL,
            plataforma VARCHAR(100) NOT NULL,
            desenvolvedor VARCHAR(150) NOT NULL,
            publisher VARCHAR(150) NOT NULL,
            data_lancamento DATE,
            classificacao_etaria VARCHAR(10),
            estoque INTEGER DEFAULT 0,
            ativo BOOLEAN DEFAULT 1,
            imagem_url VARCHAR(500)

        )";

        $conn->exec($sql);

        $sql = "CREATE TABLE IF NOT EXISTS carrinhos (
        
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
            ativo BOOLEAN DEFAULT 1,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        
        )";
        $conn->exec($sql);

        $sql = "CREATE TABLE IF NOT EXISTS itens_carrinho (
        
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            carrinho_id INTEGER NOT NULL,
            produto_id INTEGER NOT NULL,
            quantidade INTEGER NOT NULL DEFAULT 1,
            preco_unitario DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (carrinho_id) REFERENCES carrinhos(id),
            FOREIGN KEY (produto_id) REFERENCES produtos(id)
       
        )";

        $conn->exec($sql);

        $sql = "CREATE TABLE IF NOT EXISTS pedidos (

            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER NOT NULL,
            data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
            valor_total DECIMAL(10,2) NOT NULL,
            status VARCHAR(20) DEFAULT 'pendente',
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id)

        )";

        $conn->exec($sql);

        $sql = "CREATE TABLE IF NOT EXISTS itens_pedido (

            id INTEGER PRIMARY KEY AUTOINCREMENT,
            pedido_id INTEGER NOT NULL,
            produto_id INTEGER NOT NULL,
            quantidade INTEGER NOT NULL,
            preco_unitario DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
            FOREIGN KEY (produto_id) REFERENCES produtos(id)

        )";
        $conn->exec($sql);

        $sql = "CREATE TABLE IF NOT EXISTS pagamentos (

            id INTEGER PRIMARY KEY AUTOINCREMENT,
            pedido_id INTEGER NOT NULL,
            metodo_pagamento VARCHAR(50) NOT NULL,
            valor DECIMAL(10,2) NOT NULL,
            status VARCHAR(20) DEFAULT 'pendente',
            data_pagamento DATETIME DEFAULT CURRENT_TIMESTAMP,
            transacao_id VARCHAR(200),
            dados_pagamento TEXT,
            FOREIGN KEY (pedido_id) REFERENCES pedidos(id)

        )";

        $conn->exec($sql);

        return true;
    }
}
