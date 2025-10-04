<?php
// controllers/PagamentoController.php
require_once __DIR__ . "/../models/Database.php";
require_once __DIR__ . "/../models/Pagamento.php";
require_once __DIR__ . "/../models/Pedido.php";

class PagamentoController
{
    private $pagamentoModel;
    private $pedidoModel;

    public function __construct()
    {
        $db = new Database();
        $conn = $db->getConnection();
        $this->pagamentoModel = new PagamentoModel($conn);
        $this->pedidoModel = new Pedido($conn);
    }

    // GET /api/pagamentos or /api/pagamentos?id=1
    public function index()
    {
        if (!empty($_GET['id'])) {
            $p = $this->pagamentoModel->find($_GET['id']);
            echo json_encode($p);
            return;
        }
        echo json_encode($this->pagamentoModel->getAll());
    }

    // POST -> criar pagamento (pedido_id, metodo_pagamento, valor, dados_pagamento)
    public function store()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['pedido_id']) || empty($data['metodo_pagamento']) || !isset($data['valor'])) {
            http_response_code(400);
            echo json_encode(["erro" => "pedido_id, metodo_pagamento e valor são obrigatórios"]);
            return;
        }
        $id = $this->pagamentoModel->create(
            $data['pedido_id'],
            $data['metodo_pagamento'],
            $data['valor'],
            $data['dados_pagamento'] ?? null,
            $data['transacao_id'] ?? null
        );
        http_response_code(201);
        echo json_encode(["status" => "Pagamento criado", "id" => $id]);
    }

    // PUT -> atualizar status (id, status)
    public function update()
    {
        parse_str(file_get_contents("php://input"), $data);
        if (empty($data['id']) || empty($data['status'])) {
            http_response_code(400);
            echo json_encode(["erro" => "id e status são obrigatórios"]);
            return;
        }
        $this->pagamentoModel->updateStatus($data['id'], $data['status']);

        // se aprovado, marca pedido como 'pago' (exemplo)
        if ($data['status'] === 'aprovado' && !empty($data['pedido_id'])) {
            $this->pedidoModel->setStatus($data['pedido_id'], 'pago');
        }

        echo json_encode(["status" => "Pagamento atualizado"]);
    }

    // DELETE -> remover pagamento (id)
    public function delete()
    {
        parse_str(file_get_contents("php://input"), $data);
        if (empty($data['id'])) {
            http_response_code(400);
            echo json_encode(["erro" => "id é obrigatório"]);
            return;
        }
        $this->pagamentoModel->delete($data['id']);
        echo json_encode(["status" => "Pagamento deletado"]);
    }
}
