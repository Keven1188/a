<?php
// models/Usuario.php
require_once __DIR__ . "/Database.php";

class Usuario
{
    private $conn;
    private $table = "usuarios";

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function getAll()
    {
        $stmt = $this->conn->query("SELECT id, username, email, nome_completo, telefone, tipo_usuario, ativo, data_criacao, ultimo_login FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function find($id)
    {
        $stmt = $this->conn->prepare("SELECT id, username, email, nome_completo, telefone, tipo_usuario, ativo, data_criacao, ultimo_login FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (username, email, senha, nome_completo, telefone, data_nascimento, tipo_usuario, ativo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $senha_hash = password_hash($data['senha'], PASSWORD_BCRYPT);
        $stmt->execute([
            $data['username'],
            $data['email'],
            $senha_hash,
            $data['nome_completo'] ?? null,
            $data['telefone'] ?? null,
            $data['data_nascimento'] ?? null,
            $data['tipo_usuario'] ?? 'cliente',
            $data['ativo'] ?? 1
        ]);
        return $this->conn->lastInsertId();
    }

    public function update($id, $data)
    {
        // não atualiza senha aqui (crie endpoint específico se necessário)
        $sql = "UPDATE {$this->table} SET username = ?, email = ?, nome_completo = ?, telefone = ?, data_nascimento = ?, tipo_usuario = ?, ativo = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $data['username'] ?? null,
            $data['email'] ?? null,
            $data['nome_completo'] ?? null,
            $data['telefone'] ?? null,
            $data['data_nascimento'] ?? null,
            $data['tipo_usuario'] ?? 'cliente',
            $data['ativo'] ?? 1,
            $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}