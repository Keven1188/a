<?php
// models/Carrinho.php
require_once __DIR__ . "/Database.php";

class Carrinho
{
    private $conn;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function getCartByUser($usuario_id)
    {
        // retorna carrinho ativo do usuario com itens
        $stmt = $this->conn->prepare("SELECT * FROM carrinhos WHERE usuario_id = ? AND ativo = 1 LIMIT 1");
        $stmt->execute([$usuario_id]);
        $carrinho = $stmt->fetch();
        if (!$carrinho) return null;

        $stmt = $this->conn->prepare("SELECT ic.id, ic.produto_id, p.nome, ic.quantidade, ic.preco_unitario
            FROM itens_carrinho ic
            JOIN produtos p ON p.id = ic.produto_id
            WHERE ic.carrinho_id = ?");
        $stmt->execute([$carrinho['id']]);
        $itens = $stmt->fetchAll();

        $carrinho['itens'] = $itens;
        return $carrinho;
    }

    public function createCart($usuario_id)
    {
        $stmt = $this->conn->prepare("INSERT INTO carrinhos (usuario_id, ativo) VALUES (?, 1)");
        $stmt->execute([$usuario_id]);
        return $this->conn->lastInsertId();
    }

    public function addItem($carrinho_id, $produto_id, $quantidade, $preco_unitario)
    {
        // se item já existe, incrementa quantidade
        $stmt = $this->conn->prepare("SELECT id, quantidade FROM itens_carrinho WHERE carrinho_id = ? AND produto_id = ?");
        $stmt->execute([$carrinho_id, $produto_id]);
        $ex = $stmt->fetch();
        if ($ex) {
            $newQ = $ex['quantidade'] + $quantidade;
            $stmt = $this->conn->prepare("UPDATE itens_carrinho SET quantidade = ?, preco_unitario = ? WHERE id = ?");
            $stmt->execute([$newQ, $preco_unitario, $ex['id']]);
            return $ex['id'];
        }

        $stmt = $this->conn->prepare("INSERT INTO itens_carrinho (carrinho_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
        $stmt->execute([$carrinho_id, $produto_id, $quantidade, $preco_unitario]);
        return $this->conn->lastInsertId();
    }

    public function updateItem($item_id, $quantidade)
    {
        $stmt = $this->conn->prepare("UPDATE itens_carrinho SET quantidade = ? WHERE id = ?");
        return $stmt->execute([$quantidade, $item_id]);
    }

    public function removeItem($item_id)
    {
        $stmt = $this->conn->prepare("DELETE FROM itens_carrinho WHERE id = ?");
        return $stmt->execute([$item_id]);
    }

    public function clearCart($carrinho_id)
    {
        $stmt = $this->conn->prepare("DELETE FROM itens_carrinho WHERE carrinho_id = ?");
        $stmt->execute([$carrinho_id]);
        // opcional: marcar carrinho como inativo
        $stmt = $this->conn->prepare("UPDATE carrinhos SET ativo = 0 WHERE id = ?");
        $stmt->execute([$carrinho_id]);
        return true;
    }
}
