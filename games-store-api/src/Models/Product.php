<?php

namespace GamesStore\Models;

/**
 * Modelo para gerenciar produtos
 */
class Product extends BaseModel
{
    protected string $table = 'produtos';
    
    /**
     * Buscar produtos ativos
     */
    public function findActive(int $limit = 50, int $offset = 0): array
    {
        return $this->findWhere('ativo = 1', [], $limit, $offset);
    }
    
    /**
     * Buscar produtos por categoria
     */
    public function findByCategory(string $category, int $limit = 50, int $offset = 0): array
    {
        return $this->findWhere('categoria = :category AND ativo = 1', [':category' => $category], $limit, $offset);
    }
    
    /**
     * Buscar produtos por plataforma
     */
    public function findByPlatform(string $platform, int $limit = 50, int $offset = 0): array
    {
        return $this->findWhere('plataforma = :platform AND ativo = 1', [':platform' => $platform], $limit, $offset);
    }
    
    /**
     * Buscar produtos por faixa de preço
     */
    public function findByPriceRange(float $minPrice, float $maxPrice, int $limit = 50, int $offset = 0): array
    {
        return $this->findWhere(
            'preco BETWEEN :min_price AND :max_price AND ativo = 1',
            [':min_price' => $minPrice, ':max_price' => $maxPrice],
            $limit,
            $offset
        );
    }
    
    /**
     * Pesquisar produtos por nome ou descrição
     */
    public function search(string $term, int $limit = 50, int $offset = 0): array
    {
        $searchTerm = '%' . $term . '%';
        return $this->findWhere(
            '(nome LIKE :term OR descricao LIKE :term) AND ativo = 1',
            [':term' => $searchTerm],
            $limit,
            $offset
        );
    }
    
    /**
     * Obter categorias disponíveis
     */
    public function getCategories(): array
    {
        $sql = "SELECT DISTINCT categoria FROM {$this->table} WHERE ativo = 1 ORDER BY categoria";
        $stmt = $this->db->query($sql);
        
        return array_column($stmt->fetchAll(), 'categoria');
    }
    
    /**
     * Obter plataformas disponíveis
     */
    public function getPlatforms(): array
    {
        $sql = "SELECT DISTINCT plataforma FROM {$this->table} WHERE ativo = 1 ORDER BY plataforma";
        $stmt = $this->db->query($sql);
        
        return array_column($stmt->fetchAll(), 'plataforma');
    }
    
    /**
     * Obter produtos mais vendidos (simulado por estoque baixo)
     */
    public function getBestSellers(int $limit = 10): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1 ORDER BY estoque ASC, nome ASC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obter produtos em promoção (simulado por preço baixo)
     */
    public function getOnSale(int $limit = 10): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE ativo = 1 AND preco < 50 ORDER BY preco ASC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Verificar disponibilidade em estoque
     */
    public function checkStock(int $productId, int $quantity = 1): bool
    {
        $sql = "SELECT estoque FROM {$this->table} WHERE id = :id AND ativo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $productId);
        $stmt->execute();
        
        $stock = $stmt->fetchColumn();
        return $stock !== false && $stock >= $quantity;
    }
    
    /**
     * Atualizar estoque
     */
    public function updateStock(int $productId, int $quantity): bool
    {
        $sql = "UPDATE {$this->table} SET estoque = estoque + :quantity WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $productId);
        $stmt->bindValue(':quantity', $quantity);
        
        return $stmt->execute();
    }
    
    /**
     * Criar produto com validações
     */
    public function createProduct(array $data): int
    {
        // Validar dados obrigatórios
        $required = ['nome', 'preco', 'categoria', 'plataforma', 'desenvolvedor', 'publisher'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Campo '{$field}' é obrigatório");
            }
        }
        
        // Validar preço
        if (!is_numeric($data['preco']) || $data['preco'] < 0) {
            throw new \InvalidArgumentException('Preço deve ser um valor numérico positivo');
        }
        
        // Definir valores padrão
        $data['estoque'] = $data['estoque'] ?? 0;
        $data['ativo'] = $data['ativo'] ?? true;
        
        return $this->create($data);
    }
    
    /**
     * Atualizar produto com validações
     */
    public function updateProduct(int $id, array $data): bool
    {
        // Verificar se produto existe
        if (!$this->findById($id)) {
            throw new \InvalidArgumentException('Produto não encontrado');
        }
        
        // Validar preço se fornecido
        if (isset($data['preco']) && (!is_numeric($data['preco']) || $data['preco'] < 0)) {
            throw new \InvalidArgumentException('Preço deve ser um valor numérico positivo');
        }
        
        return $this->update($id, $data);
    }
}
