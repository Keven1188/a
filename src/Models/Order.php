<?php

namespace GamesStore\Models;

/**
 * Modelo para gerenciar pedidos
 */
class Order extends BaseModel
{
    protected string $table = 'pedidos';
    
    /**
     * Buscar pedidos de um usuário
     */
    public function findByUser(int $userId, int $limit = 50, int $offset = 0): array
    {
        return $this->findWhere('usuario_id = :user_id', [':user_id' => $userId], $limit, $offset);
    }
    
    /**
     * Buscar pedidos por status
     */
    public function findByStatus(string $status, int $limit = 50, int $offset = 0): array
    {
        return $this->findWhere('status = :status', [':status' => $status], $limit, $offset);
    }
    
    /**
     * Buscar pedido com itens
     */
    public function findWithItems(int $orderId): ?array
    {
        $sql = "
            SELECT 
                p.*,
                pi.id as item_id,
                pi.quantidade,
                pi.preco_unitario,
                pi.subtotal,
                prod.nome as produto_nome,
                prod.categoria as produto_categoria
            FROM {$this->table} p
            LEFT JOIN itens_pedido pi ON p.id = pi.pedido_id
            LEFT JOIN produtos prod ON pi.produto_id = prod.id
            WHERE p.id = :order_id
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':order_id', $orderId);
        $stmt->execute();
        
        $results = $stmt->fetchAll();
        
        if (empty($results)) {
            return null;
        }
        
        // Organizar dados
        $order = [
            'id' => $results[0]['id'],
            'usuario_id' => $results[0]['usuario_id'],
            'status' => $results[0]['status'],
            'total' => $results[0]['total'],
            'data_pedido' => $results[0]['data_pedido'],
            'data_atualizacao' => $results[0]['data_atualizacao'],
            'endereco_entrega' => $results[0]['endereco_entrega'],
            'observacoes' => $results[0]['observacoes'],
            'itens' => []
        ];
        
        foreach ($results as $row) {
            if ($row['item_id']) {
                $order['itens'][] = [
                    'id' => $row['item_id'],
                    'produto_nome' => $row['produto_nome'],
                    'produto_categoria' => $row['produto_categoria'],
                    'quantidade' => $row['quantidade'],
                    'preco_unitario' => $row['preco_unitario'],
                    'subtotal' => $row['subtotal']
                ];
            }
        }
        
        return $order;
    }
    
    /**
     * Criar pedido com itens
     */
    public function createOrder(int $userId, array $items, array $orderData = []): int
    {
        $this->db->beginTransaction();
        
        try {
            // Validar itens
            if (empty($items)) {
                throw new \InvalidArgumentException('Pedido deve conter pelo menos um item');
            }
            
            $productModel = new Product();
            $total = 0;
            
            // Validar disponibilidade e calcular total
            foreach ($items as $item) {
                if (!isset($item['produto_id']) || !isset($item['quantidade'])) {
                    throw new \InvalidArgumentException('Item deve conter produto_id e quantidade');
                }
                
                $product = $productModel->findById($item['produto_id']);
                if (!$product) {
                    throw new \InvalidArgumentException("Produto {$item['produto_id']} não encontrado");
                }
                
                if (!$productModel->checkStock($item['produto_id'], $item['quantidade'])) {
                    throw new \InvalidArgumentException("Estoque insuficiente para o produto {$product['nome']}");
                }
                
                $total += $product['preco'] * $item['quantidade'];
            }
            
            // Criar pedido
            $orderData['usuario_id'] = $userId;
            $orderData['total'] = $total;
            $orderData['status'] = $orderData['status'] ?? 'pendente';
            
            $orderId = $this->create($orderData);
            
            // Criar itens do pedido
            foreach ($items as $item) {
                $product = $productModel->findById($item['produto_id']);
                $subtotal = $product['preco'] * $item['quantidade'];
                
                $this->createOrderItem($orderId, $item['produto_id'], $item['quantidade'], $product['preco'], $subtotal);
                
                // Reduzir estoque
                $productModel->updateStock($item['produto_id'], -$item['quantidade']);
            }
            
            $this->db->commit();
            return $orderId;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Criar item do pedido
     */
    private function createOrderItem(int $orderId, int $productId, int $quantity, float $unitPrice, float $subtotal): void
    {
        $sql = "INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario, subtotal) VALUES (:pedido_id, :produto_id, :quantidade, :preco_unitario, :subtotal)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':pedido_id', $orderId);
        $stmt->bindValue(':produto_id', $productId);
        $stmt->bindValue(':quantidade', $quantity);
        $stmt->bindValue(':preco_unitario', $unitPrice);
        $stmt->bindValue(':subtotal', $subtotal);
        
        $stmt->execute();
    }
    
    /**
     * Atualizar status do pedido
     */
    public function updateStatus(int $orderId, string $status): bool
    {
        $validStatuses = ['pendente', 'processando', 'enviado', 'entregue', 'cancelado'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException('Status inválido');
        }
        
        return $this->update($orderId, ['status' => $status]);
    }
    
    /**
     * Cancelar pedido
     */
    public function cancelOrder(int $orderId): bool
    {
        $this->db->beginTransaction();
        
        try {
            $order = $this->findWithItems($orderId);
            if (!$order) {
                throw new \InvalidArgumentException('Pedido não encontrado');
            }
            
            if ($order['status'] === 'cancelado') {
                throw new \InvalidArgumentException('Pedido já está cancelado');
            }
            
            if (in_array($order['status'], ['enviado', 'entregue'])) {
                throw new \InvalidArgumentException('Não é possível cancelar pedido já enviado ou entregue');
            }
            
            // Restaurar estoque
            $productModel = new Product();
            foreach ($order['itens'] as $item) {
                $productModel->updateStock($item['produto_id'], $item['quantidade']);
            }
            
            // Atualizar status
            $this->updateStatus($orderId, 'cancelado');
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    /**
     * Obter estatísticas de pedidos
     */
    public function getStats(): array
    {
        $sql = "
            SELECT 
                COUNT(*) as total_pedidos,
                COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes,
                COUNT(CASE WHEN status = 'processando' THEN 1 END) as processando,
                COUNT(CASE WHEN status = 'enviado' THEN 1 END) as enviados,
                COUNT(CASE WHEN status = 'entregue' THEN 1 END) as entregues,
                COUNT(CASE WHEN status = 'cancelado' THEN 1 END) as cancelados,
                SUM(CASE WHEN status != 'cancelado' THEN total ELSE 0 END) as receita_total,
                AVG(CASE WHEN status != 'cancelado' THEN total ELSE NULL END) as ticket_medio
            FROM {$this->table}
        ";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
}
