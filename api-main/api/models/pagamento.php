<?php
// models/Pagamento.php
require_once __DIR__ . "/Database.php";

class PagamentoModel
{
    private $conn;
    private $table = "pagamentos";

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function create($pedido_id, $metodo, $valor, $dados_pagamento = null, $transacao_id = null)
    {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (pedido_id, metodo_pagamento, valor, status, data_pagamento, transacao_id, dados_pagamento) VALUES (?, ?, ?, 'pendente', CURRENT_TIMESTAMP, ?, ?)");
        $stmt->execute([$pedido_id, $metodo, $valor, $transacao_id, $dados_pagamento]);
        return $this->conn->lastInsertId();
    }

    public function find($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAll()
    {
        $stmt = $this->conn->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }
}
