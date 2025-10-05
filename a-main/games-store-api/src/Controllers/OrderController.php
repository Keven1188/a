<?php

namespace GamesStore\Controllers;

use GamesStore\Models\Order;
use GamesStore\Core\Response;

/**
 * Controller para gerenciar pedidos
 */
class OrderController
{
    private Order $orderModel;
    
    public function __construct()
    {
        $this->orderModel = new Order();
    }
    
    /**
     * Listar todos os pedidos
     */
    public function index(): void
    {
        try {
            $page = (int) ($_GET['page'] ?? 1);
            $limit = (int) ($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            
            $orders = $this->orderModel->findAll($limit, $offset);
            $total = $this->orderModel->count();
            
            Response::paginated($orders, $page, $limit, $total, 'Pedidos recuperados com sucesso');
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar pedidos: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Buscar pedido por ID com itens
     */
    public function show(string $id): void
    {
        try {
            $orderId = (int) $id;
            $order = $this->orderModel->findWithItems($orderId);
            
            if (!$order) {
                Response::error('Pedido não encontrado', 404);
                return;
            }
            
            Response::success($order, 'Pedido encontrado');
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar pedido: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Criar novo pedido
     */
    public function create(): void
    {
        try {
            $data = Response::getRequestData();
            $data = Response::sanitize($data);
            
            // Validar campos obrigatórios
            $errors = Response::validateRequired($data, ['usuario_id', 'itens']);
            if (!empty($errors)) {
                Response::error('Dados inválidos', 400, $errors);
                return;
            }
            
            if (!is_array($data['itens']) || empty($data['itens'])) {
                Response::error('Lista de itens deve ser um array não vazio', 400);
                return;
            }
            
            // Validar itens
            foreach ($data['itens'] as $item) {
                if (!isset($item['produto_id']) || !isset($item['quantidade'])) {
                    Response::error('Cada item deve conter produto_id e quantidade', 400);
                    return;
                }
                
                if (!is_numeric($item['quantidade']) || $item['quantidade'] <= 0) {
                    Response::error('Quantidade deve ser um número positivo', 400);
                    return;
                }
            }
            
            $orderData = [
                'endereco_entrega' => $data['endereco_entrega'] ?? null,
                'observacoes' => $data['observacoes'] ?? null
            ];
            
            $orderId = $this->orderModel->createOrder($data['usuario_id'], $data['itens'], $orderData);
            $order = $this->orderModel->findWithItems($orderId);
            
            Response::success($order, 'Pedido criado com sucesso', 201);
            
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            Response::error('Erro ao criar pedido: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Atualizar status do pedido
     */
    public function updateStatus(string $id): void
    {
        try {
            $orderId = (int) $id;
            $data = Response::getRequestData();
            
            if (!isset($data['status'])) {
                Response::error('Status é obrigatório', 400);
                return;
            }
            
            $success = $this->orderModel->updateStatus($orderId, $data['status']);
            
            if ($success) {
                $order = $this->orderModel->findWithItems($orderId);
                Response::success($order, 'Status do pedido atualizado com sucesso');
            } else {
                Response::error('Erro ao atualizar status do pedido', 500);
            }
            
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            Response::error('Erro ao atualizar pedido: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Cancelar pedido
     */
    public function cancel(string $id): void
    {
        try {
            $orderId = (int) $id;
            
            $success = $this->orderModel->cancelOrder($orderId);
            
            if ($success) {
                $order = $this->orderModel->findWithItems($orderId);
                Response::success($order, 'Pedido cancelado com sucesso');
            } else {
                Response::error('Erro ao cancelar pedido', 500);
            }
            
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            Response::error('Erro ao cancelar pedido: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Buscar pedidos de um usuário
     */
    public function byUser(string $userId): void
    {
        try {
            $userIdInt = (int) $userId;
            $page = (int) ($_GET['page'] ?? 1);
            $limit = (int) ($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            
            $orders = $this->orderModel->findByUser($userIdInt, $limit, $offset);
            $total = $this->orderModel->count("usuario_id = {$userIdInt}");
            
            Response::paginated($orders, $page, $limit, $total, 'Pedidos do usuário recuperados com sucesso');
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar pedidos: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Buscar pedidos por status
     */
    public function byStatus(string $status): void
    {
        try {
            $validStatuses = ['pendente', 'processando', 'enviado', 'entregue', 'cancelado'];
            if (!in_array($status, $validStatuses)) {
                Response::error('Status inválido', 400);
                return;
            }
            
            $page = (int) ($_GET['page'] ?? 1);
            $limit = (int) ($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            
            $orders = $this->orderModel->findByStatus($status, $limit, $offset);
            $total = $this->orderModel->count("status = '{$status}'");
            
            Response::paginated($orders, $page, $limit, $total, "Pedidos com status '{$status}' recuperados com sucesso");
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar pedidos: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obter estatísticas de pedidos
     */
    public function stats(): void
    {
        try {
            $stats = $this->orderModel->getStats();
            Response::success($stats, 'Estatísticas de pedidos recuperadas com sucesso');
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar estatísticas: ' . $e->getMessage(), 500);
        }
    }
}
