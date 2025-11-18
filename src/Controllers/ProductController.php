<?php

namespace GamesStore\Controllers;

use GamesStore\Models\Product;
use GamesStore\Core\Response;

/**
 * Controller para gerenciar produtos
 */
class ProductController
{
    private Product $productModel;
    
    public function __construct()
    {
        $this->productModel = new Product();
    }
    
    /**
     * Listar todos os produtos
     */
    public function index(): void
    {
        try {
            $page = (int) ($_GET['page'] ?? 1);
            $limit = (int) ($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            
            $products = $this->productModel->findActive($limit, $offset);
            $total = $this->productModel->count('ativo = 1');
            
            Response::paginated($products, $page, $limit, $total, 'Produtos recuperados com sucesso');
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar produtos: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Buscar produto por ID
     */
    public function show(string $id): void
    {
        try {
            $productId = (int) $id;
            $product = $this->productModel->findById($productId);
            
            if (!$product || !$product['ativo']) {
                Response::error('Produto não encontrado', 404);
                return;
            }
            
            Response::success($product, 'Produto encontrado');
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar produto: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Criar novo produto
     */
    public function create(): void
    {
        try {
            $data = Response::getRequestData();
            $data = Response::sanitize($data);
            
            // Validar campos obrigatórios
            $errors = Response::validateRequired($data, ['nome', 'preco', 'categoria', 'plataforma', 'desenvolvedor', 'publisher']);
            if (!empty($errors)) {
                Response::error('Dados inválidos', 400, $errors);
                return;
            }
            
            $productId = $this->productModel->createProduct($data);
            $product = $this->productModel->findById($productId);
            
            Response::success($product, 'Produto criado com sucesso', 201);
            
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            Response::error('Erro ao criar produto: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Atualizar produto
     */
    public function update(string $id): void
    {
        try {
            $productId = (int) $id;
            $data = Response::getRequestData();
            $data = Response::sanitize($data);
            
            $success = $this->productModel->updateProduct($productId, $data);
            
            if ($success) {
                $product = $this->productModel->findById($productId);
                Response::success($product, 'Produto atualizado com sucesso');
            } else {
                Response::error('Erro ao atualizar produto', 500);
            }
            
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            Response::error('Erro ao atualizar produto: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Deletar produto (desativar)
     */
    public function delete(string $id): void
    {
        try {
            $productId = (int) $id;
            
            if (!$this->productModel->findById($productId)) {
                Response::error('Produto não encontrado', 404);
                return;
            }
            
            // Desativar ao invés de deletar
            $success = $this->productModel->update($productId, ['ativo' => false]);
            
            if ($success) {
                Response::success(null, 'Produto removido com sucesso');
            } else {
                Response::error('Erro ao remover produto', 500);
            }
            
        } catch (\Exception $e) {
            Response::error('Erro ao remover produto: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Pesquisar produtos
     */
    public function search(): void
    {
        try {
            $term = $_GET['q'] ?? '';
            if (empty($term)) {
                Response::error('Termo de pesquisa é obrigatório', 400);
                return;
            }
            
            $page = (int) ($_GET['page'] ?? 1);
            $limit = (int) ($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            
            $products = $this->productModel->search($term, $limit, $offset);
            
            // Contar total de resultados (usando escape para aspas simples)
            $searchTermEscaped = str_replace("'", "''", $term);
            $total = $this->productModel->count("(nome LIKE '%{$searchTermEscaped}%' OR descricao LIKE '%{$searchTermEscaped}%') AND ativo = 1");
            
            Response::paginated($products, $page, $limit, $total, "Resultados da pesquisa por '{$term}'");
            
        } catch (\Exception $e) {
            Response::error('Erro na pesquisa: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Buscar produtos por categoria
     */
    public function byCategory(string $category): void
    {
        try {
            $page = (int) ($_GET['page'] ?? 1);
            $limit = (int) ($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            
            $products = $this->productModel->findByCategory($category, $limit, $offset);
            $total = $this->productModel->count("categoria = '{$category}' AND ativo = 1");
            
            Response::paginated($products, $page, $limit, $total, "Produtos da categoria '{$category}' recuperados com sucesso");
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar produtos: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Buscar produtos por plataforma
     */
    public function byPlatform(string $platform): void
    {
        try {
            $page = (int) ($_GET['page'] ?? 1);
            $limit = (int) ($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            
            $products = $this->productModel->findByPlatform($platform, $limit, $offset);
            $total = $this->productModel->count("plataforma = '{$platform}' AND ativo = 1");
            
            Response::paginated($products, $page, $limit, $total, "Produtos da plataforma '{$platform}' recuperados com sucesso");
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar produtos: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obter categorias disponíveis
     */
    public function categories(): void
    {
        try {
            $categories = $this->productModel->getCategories();
            Response::success($categories, 'Categorias recuperadas com sucesso');
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar categorias: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obter plataformas disponíveis
     */
    public function platforms(): void
    {
        try {
            $platforms = $this->productModel->getPlatforms();
            Response::success($platforms, 'Plataformas recuperadas com sucesso');
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar plataformas: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obter produtos mais vendidos
     */
    public function bestSellers(): void
    {
        try {
            $limit = (int) ($_GET['limit'] ?? 10);
            $products = $this->productModel->getBestSellers($limit);
            
            Response::success($products, 'Produtos mais vendidos recuperados com sucesso');
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar produtos: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Obter produtos em promoção
     */
    public function onSale(): void
    {
        try {
            $limit = (int) ($_GET['limit'] ?? 10);
            $products = $this->productModel->getOnSale($limit);
            
            Response::success($products, 'Produtos em promoção recuperados com sucesso');
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar produtos: ' . $e->getMessage(), 500);
        }
    }
}
