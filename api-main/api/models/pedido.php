<?php
// models/Pedido.php
require_once __DIR__ . "/Database.php";

class Pedido
{
    private $conn;
    private $table = "pedidos";

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function find($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getItems($pedido_id)
    {
        $stmt = $this->conn->prepare("SELECT ip.*, p.nome FROM itens_pedido ip JOIN produtos p ON p.id = ip.produto_id WHERE ip.pedido_id = ?");
        $stmt->execute([$pedido_id]);
        return $stmt->fetchAll();
    }

    public function setStatus($pedido_id, $status)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $pedido_id]);
    }
}
