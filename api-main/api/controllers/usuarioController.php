<?php
require_once __DIR__ . "/../models/Database.php";

class usuarioController
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    // GET /api/usuario  -> lista todos
    public function index()
    {
        $stmt = $this->conn->query("SELECT id, username, email, nome_completo, telefone, tipo_usuario, ativo FROM usuarios");
        $rows = $stmt->fetchAll();
        echo json_encode($rows);
    }

    // POST /api/usuario  -> cria
    public function store()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['username']) || empty($data['email']) || empty($data['senha'])) {
            http_response_code(400);
            echo json_encode(["erro" => "username, email e senha são obrigatórios"]);
            return;
        }

        $sql = "INSERT INTO usuarios (username, email, senha, nome_completo, telefone)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $senha_hash = password_hash($data['senha'], PASSWORD_BCRYPT);

        try {
            $stmt->execute([
                $data['username'],
                $data['email'],
                $senha_hash,
                $data['nome_completo'] ?? null,
                $data['telefone'] ?? null
            ]);

            http_response_code(201);
            echo json_encode(["status" => "Usuário criado", "id" => $this->conn->lastInsertId()]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["erro" => $e->getMessage()]);
        }
    }

    // PUT /api/usuario  -> atualiza (envia id, nome, email etc)
    public function update()
    {
        parse_str(file_get_contents("php://input"), $data);

        if (empty($data['id'])) {
            http_response_code(400);
            echo json_encode(["erro" => "id é obrigatório"]);
            return;
        }

        $sql = "UPDATE usuarios SET username = ?, email = ?, nome_completo = ?, telefone = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $data['username'] ?? null,
            $data['email'] ?? null,
            $data['nome_completo'] ?? null,
            $data['telefone'] ?? null,
            $data['id']
        ]);

        echo json_encode(["status" => "Usuário atualizado"]);
    }

    // DELETE /api/usuario -> deletar (envia id)
    public function delete()
    {
        parse_str(file_get_contents("php://input"), $data);

        if (empty($data['id'])) {
            http_response_code(400);
            echo json_encode(["erro" => "id é obrigatório"]);
            return;
        }

        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$data['id']]);

        echo json_encode(["status" => "Usuário deletado"]);
    }
}
