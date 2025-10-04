<?php
// models/Produto.php
require_once __DIR__ . "/Database.php";

class Produto
{
    private $conn;
    private $table = "produtos";

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function getAll()
    {
        $stmt = $this->conn->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function find($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
            (nome, descricao, preco, categoria, plataforma, desenvolvedor, publisher, data_lancamento, classificacao_etaria, estoque, ativo, imagem_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['nome'] ?? null,
            $data['descricao'] ?? null,
            $data['preco'] ?? 0,
            $data['categoria'] ?? null,
            $data['plataforma'] ?? null,
            $data['desenvolvedor'] ?? null,
            $data['publisher'] ?? null,
            $data['data_lancamento'] ?? null,
            $data['classificacao_etaria'] ?? null,
            $data['estoque'] ?? 0,
            $data['ativo'] ?? 1,
            $data['imagem_url'] ?? null
        ]);
        return $this->conn->lastInsertId();
    }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} SET
            nome = ?, descricao = ?, preco = ?, categoria = ?, plataforma = ?, desenvolvedor = ?, publisher = ?, data_lancamento = ?, classificacao_etaria = ?, estoque = ?, ativo = ?, imagem_url = ?
            WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['nome'] ?? null,
            $data['descricao'] ?? null,
            $data['preco'] ?? 0,
            $data['categoria'] ?? null,
            $data['plataforma'] ?? null,
            $data['desenvolvedor'] ?? null,
            $data['publisher'] ?? null,
            $data['data_lancamento'] ?? null,
            $data['classificacao_etaria'] ?? null,
            $data['estoque'] ?? 0,
            $data['ativo'] ?? 1,
            $data['imagem_url'] ?? null,
            $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
