<?php

namespace GamesStore\Models;

/**
 * Modelo para gerenciar usuários
 */
class User extends BaseModel
{
    protected string $table = 'usuarios';
    
    /**
     * Buscar usuário por username
     */
    public function findByUsername(string $username): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Buscar usuário por email
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Verificar se username já existe
     */
    public function usernameExists(string $username, int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE username = :username";
        $params = [':username' => $username];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Verificar se email já existe
     */
    public function emailExists(string $email, int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email";
        $params = [':email' => $email];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Atualizar último login
     */
    public function updateLastLogin(int $userId): bool
    {
        $sql = "UPDATE {$this->table} SET ultimo_login = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $userId);
        
        return $stmt->execute();
    }
    
    /**
     * Buscar usuários ativos
     */
    public function findActive(int $limit = 50, int $offset = 0): array
    {
        return $this->findWhere('ativo = 1', [], $limit, $offset);
    }
    
    /**
     * Buscar usuários por tipo
     */
    public function findByType(string $type, int $limit = 50, int $offset = 0): array
    {
        return $this->findWhere('tipo_usuario = :type', [':type' => $type], $limit, $offset);
    }
    
    /**
     * Criar usuário com validações
     */
    public function createUser(array $data): int
    {
        // Validar dados obrigatórios
        $required = ['username', 'email', 'senha'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Campo '{$field}' é obrigatório");
            }
        }
        
        // Verificar se username já existe
        if ($this->usernameExists($data['username'])) {
            throw new \InvalidArgumentException('Username já está em uso');
        }
        
        // Verificar se email já existe
        if ($this->emailExists($data['email'])) {
            throw new \InvalidArgumentException('Email já está em uso');
        }
        
        // Hash da senha
        $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        
        // Definir valores padrão
        $data['tipo_usuario'] = $data['tipo_usuario'] ?? 'cliente';
        $data['ativo'] = $data['ativo'] ?? true;
        
        return $this->create($data);
    }
    
    /**
     * Atualizar usuário com validações
     */
    public function updateUser(int $id, array $data): bool
    {
        // Verificar se usuário existe
        if (!$this->findById($id)) {
            throw new \InvalidArgumentException('Usuário não encontrado');
        }
        
        // Verificar username único (se fornecido)
        if (isset($data['username']) && $this->usernameExists($data['username'], $id)) {
            throw new \InvalidArgumentException('Username já está em uso');
        }
        
        // Verificar email único (se fornecido)
        if (isset($data['email']) && $this->emailExists($data['email'], $id)) {
            throw new \InvalidArgumentException('Email já está em uso');
        }
        
        // Hash da senha se fornecida
        if (isset($data['senha']) && !empty($data['senha'])) {
            $data['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        } else {
            unset($data['senha']); // Não atualizar senha se não fornecida
        }
        
        return $this->update($id, $data);
    }
}
