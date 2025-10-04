<?php
// controllers/CarrinhoController.php
require_once __DIR__ . "/../models/Database.php";
require_once __DIR__ . "/../models/Carrinho.php";
require_once __DIR__ . "/../models/Produto.php";

class CarrinhoController
{
    private $db;
    private $carrinhoModel;
    private $produtoModel;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->carrinhoModel = new Carrinho($this->db);
        $this->produtoModel = new Produto($this->db);
    }

    // GET /api/carrinho?usuario_id=1
    public function index()
    {
        $usuario_id = $_GET['usuario_id'] ?? null;
        if (!$usuario_id) {
            http_response_code(400);
            echo json_encode(["erro" => "usuario_id é obrigatório"]);
            return;
        }
        $cart = $this->carrinhoModel->getCartByUser($usuario_id);
        if (!$cart) {
            echo json_encode(["mensagem" => "Carrinho vazio"]);
            return;
        }
        echo json_encode($cart);
    }

    // POST /api/carrinho  -> adicionar item (JSON: usuario_id, produto_id, quantidade)
    public function store()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['usuario_id']) || empty($data['produto_id'])) {
            http_response_code(400);
            echo json_encode(["erro" => "usuario_id e produto_id são obrigatórios"]);
            return;
        }
        $usuario_id = $data['usuario_id'];
        $produto_id = $data['produto_id'];
        $qtd = $data['quantidade'] ?? 1;

        // garantir carrinho
        $cart = $this->carrinhoModel->getCartByUser($usuario_id);
        if (!$cart) {
            $cart_id = $this->carrinhoModel->createCart($usuario_id);
        } else {
            $cart_id = $cart['id'];
        }

        // obter preco do produto
        $produto = $this->produtoModel->find($produto_id);
        if (!$produto) {
            http_response_code(404);
            echo json_encode(["erro" => "Produto não encontrado"]);
            return;
        }
        $preco = $produto['preco'];

        $item_id = $this->carrinhoModel->addItem($cart_id, $produto_id, $qtd, $preco);
        http_response_code(201);
        echo json_encode(["status" => "Item adicionado", "item_id" => $item_id, "carrinho_id" => $cart_id]);
    }

    // PUT /api/carrinho -> atualizar item (item_id, quantidade)
    public function update()
    {
        parse_str(file_get_contents("php://input"), $data);
        if (empty($data['item_id']) || !isset($data['quantidade'])) {
            http_response_code(400);
            echo json_encode(["erro" => "item_id e quantidade são obrigatórios"]);
            return;
        }
        $ok = $this->carrinhoModel->updateItem($data['item_id'], (int)$data['quantidade']);
        if ($ok) echo json_encode(["status" => "Quantidade atualizada"]);
        else {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao atualizar"]);
        }
    }

    // DELETE /api/carrinho -> remover item (item_id) ou limpar carrinho (carrinho_id)
    public function delete()
    {
        parse_str(file_get_contents("php://input"), $data);
        if (!empty($data['item_id'])) {
            $this->carrinhoModel->removeItem($data['item_id']);
            echo json_encode(["status" => "Item removido"]);
            return;
        }
        if (!empty($data['carrinho_id'])) {
            $this->carrinhoModel->clearCart($data['carrinho_id']);
            echo json_encode(["status" => "Carrinho limpo"]);
            return;
        }
        http_response_code(400);
        echo json_encode(["erro" => "item_id ou carrinho_id é obrigatório"]);
    }

    // opcional: checkout simples (gera pedido e limpa carrinho)
    public function checkout()
    {
        // uso interno: chamar manualmente com POST JSON { usuario_id: x, action: "checkout" }
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['usuario_id'])) {
            http_response_code(400);
            echo json_encode(["erro" => "usuario_id é obrigatório"]);
            return;
        }
        $usuario_id = $data['usuario_id'];
        $cart = $this->carrinhoModel->getCartByUser($usuario_id);
        if (!$cart || empty($cart['itens'])) {
            http_response_code(400);
            echo json_encode(["erro" => "Carrinho vazio"]);
            return;
        }

        // criar pedido
        $this->db->beginTransaction();
        try {
            $total = 0;
            foreach ($cart['itens'] as $it) {
                $total += $it['preco_unitario'] * $it['quantidade'];
            }
            $stmt = $this->db->prepare("INSERT INTO pedidos (usuario_id, valor_total, status) VALUES (?, ?, 'pendente')");
            $stmt->execute([$usuario_id, $total]);
            $pedido_id = $this->db->lastInsertId();

            $stmtItem = $this->db->prepare("INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
            foreach ($cart['itens'] as $it) {
                $stmtItem->execute([$pedido_id, $it['produto_id'], $it['quantidade'], $it['preco_unitario']]);
            }

            // limpar carrinho (remover itens e inativar)
            $this->carrinhoModel->clearCart($cart['id']);

            $this->db->commit();
            echo json_encode(["status" => "Pedido criado", "pedido_id" => $pedido_id, "valor_total" => $total]);
        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(["erro" => $e->getMessage()]);
        }
    }
}
