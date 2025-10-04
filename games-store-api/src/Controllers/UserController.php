<?php

namespace GamesStore\Controllers;

use GamesStore\Models\User;
use GamesStore\Core\Response;

/**
 * Controller para gerenciar usuários
 */
class UserController
{
    private User $userModel;
    
    public function __construct()
    {
        $this->userModel = new User();
    }
    
    /**
     * Listar todos os usuários
     */
    public function index(): void
    {
        try {
            $page = (int) ($_GET['page'] ?? 1);
            $limit = (int) ($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            
            $users = $this->userModel->findAll($limit, $offset);
            $total = $this->userModel->count();
            
            // Remover senhas dos resultados
            $users = array_map(function($user) {
                unset($user['senha']);
                return $user;
            }, $users);
            
            Response::paginated($users, $page, $limit, $total, 'Usuários recuperados com sucesso');
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar usuários: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Buscar usuário por ID
     */
    public function show(string $id): void
    {
        try {
            $userId = (int) $id;
            $user = $this->userModel->findById($userId);
            
            if (!$user) {
                Response::error('Usuário não encontrado', 404);
                return;
            }
            
            unset($user['senha']);
            Response::success($user, 'Usuário encontrado');
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar usuário: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Criar novo usuário
     */
    public function create(): void
    {
        try {
            $data = Response::getRequestData();
            $data = Response::sanitize($data);
            
            // Validar campos obrigatórios
            $errors = Response::validateRequired($data, ['username', 'email', 'senha']);
            if (!empty($errors)) {
                Response::error('Dados inválidos', 400, $errors);
                return;
            }
            
            // Validar email
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                Response::error('Email inválido', 400);
                return;
            }
            
            // Validar senha
            if (strlen($data['senha']) < 6) {
                Response::error('Senha deve ter pelo menos 6 caracteres', 400);
                return;
            }
            
            $userId = $this->userModel->createUser($data);
            
            $user = $this->userModel->findById($userId);
            unset($user['senha']);
            
            Response::success($user, 'Usuário criado com sucesso', 201);
            
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            Response::error('Erro ao criar usuário: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Atualizar usuário
     */
    public function update(string $id): void
    {
        try {
            $userId = (int) $id;
            $data = Response::getRequestData();
            $data = Response::sanitize($data);
            
            // Validar email se fornecido
            if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                Response::error('Email inválido', 400);
                return;
            }
            
            // Validar senha se fornecida
            if (isset($data['senha']) && !empty($data['senha']) && strlen($data['senha']) < 6) {
                Response::error('Senha deve ter pelo menos 6 caracteres', 400);
                return;
            }
            
            $success = $this->userModel->updateUser($userId, $data);
            
            if ($success) {
                $user = $this->userModel->findById($userId);
                unset($user['senha']);
                Response::success($user, 'Usuário atualizado com sucesso');
            } else {
                Response::error('Erro ao atualizar usuário', 500);
            }
            
        } catch (\InvalidArgumentException $e) {
            Response::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            Response::error('Erro ao atualizar usuário: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Deletar usuário
     */
    public function delete(string $id): void
    {
        try {
            $userId = (int) $id;
            
            if (!$this->userModel->findById($userId)) {
                Response::error('Usuário não encontrado', 404);
                return;
            }
            
            $success = $this->userModel->delete($userId);
            
            if ($success) {
                Response::success(null, 'Usuário deletado com sucesso');
            } else {
                Response::error('Erro ao deletar usuário', 500);
            }
            
        } catch (\Exception $e) {
            Response::error('Erro ao deletar usuário: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Login de usuário
     */
    public function login(): void
    {
        try {
            $data = Response::getRequestData();
            $data = Response::sanitize($data);
            
            // Validar campos obrigatórios
            $errors = Response::validateRequired($data, ['username', 'senha']);
            if (!empty($errors)) {
                Response::error('Dados inválidos', 400, $errors);
                return;
            }
            
            $user = $this->userModel->findByUsername($data['username']);
            
            if (!$user || !password_verify($data['senha'], $user['senha'])) {
                Response::error('Credenciais inválidas', 401);
                return;
            }
            
            if (!$user['ativo']) {
                Response::error('Usuário inativo', 403);
                return;
            }
            
            // Atualizar último login
            $this->userModel->updateLastLogin($user['id']);
            
            unset($user['senha']);
            Response::success($user, 'Login realizado com sucesso');
            
        } catch (\Exception $e) {
            Response::error('Erro no login: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Buscar usuários por tipo
     */
    public function byType(string $type): void
    {
        try {
            $validTypes = ['cliente', 'admin'];
            if (!in_array($type, $validTypes)) {
                Response::error('Tipo de usuário inválido', 400);
                return;
            }
            
            $page = (int) ($_GET['page'] ?? 1);
            $limit = (int) ($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            
            $users = $this->userModel->findByType($type, $limit, $offset);
            $total = $this->userModel->count("tipo_usuario = '{$type}'");
            
            // Remover senhas dos resultados
            $users = array_map(function($user) {
                unset($user['senha']);
                return $user;
            }, $users);
            
            Response::paginated($users, $page, $limit, $total, "Usuários do tipo '{$type}' recuperados com sucesso");
            
        } catch (\Exception $e) {
            Response::error('Erro ao buscar usuários: ' . $e->getMessage(), 500);
        }
    }
}
