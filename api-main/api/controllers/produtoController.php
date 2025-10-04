<?php
// controllers/ProdutoController.php
require_once __DIR__ . "/../models/Database.php";
require_once __DIR__ . "/../models/Produto.php";

class ProdutoController
{
    private $produtoModel;

    public function __construct()
    {
        $db = new Database();
        $this->produtoModel = new Produto($db->getConnection());
    }

    public function index()
    {
        $rows = $this->produtoModel->getAll();
        echo json_encode($rows);
    }

    public function store()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['nome']) || !isset($data['preco'])) {
            http_response_code(400);
            echo json_encode(["erro" => "nome e preco são obrigatórios"]);
            return;
        }
        try {
            $id = $this->produtoModel->create($data);
            http_response_code(201);
            echo json_encode(["status" => "Produto criado", "id" => $id]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["erro" => $e->getMessage()]);
        }
    }

    public function update()
    {
        parse_str(file_get_contents("php://input"), $data);
        if (empty($data['id'])) {
            http_response_code(400);
            echo json_encode(["erro" => "id é obrigatório"]);
            return;
        }
        $id = $data['id'];
        unset($data['id']);
        $ok = $this->produtoModel->update($id, $data);
        if ($ok) echo json_encode(["status" => "Produto atualizado"]);
        else {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao atualizar"]);
        }
    }

    public function delete()
    {
        parse_str(file_get_contents("php://input"), $data);
        if (empty($data['id'])) {
            http_response_code(400);
            echo json_encode(["erro" => "id é obrigatório"]);
            return;
        }
        $this->produtoModel->delete($data['id']);
        echo json_encode(["status" => "Produto deletado"]);
    }
}
