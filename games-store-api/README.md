# Games Store API

Uma API RESTful completa para sistema de loja de jogos, desenvolvida em PHP com arquitetura MVC e banco de dados MySQL.

## Características

- **Arquitetura MVC**: Separação clara entre Models, Views e Controllers
- **API RESTful**: Endpoints padronizados seguindo convenções REST
- **Banco de Dados**: MySQL com estrutura otimizada e relacionamentos
- **Validações**: Validação de dados de entrada e tratamento de erros
- **Paginação**: Suporte a paginação em listagens
- **CORS**: Configurado para permitir requisições de diferentes origens
- **Autoloader**: Sistema de carregamento automático de classes

## Estrutura do Projeto

```
games-store-api/
├── public/
│   ├── index.php          # Ponto de entrada da API
│   └── .htaccess          # Configurações do Apache
├── src/
│   ├── Config/
│   │   └── Database.php   # Configurações do banco
│   ├── Core/
│   │   ├── Database.php   # Classe principal do banco
│   │   ├── Router.php     # Sistema de roteamento
│   │   └── Response.php   # Padronização de respostas
│   ├── Models/
│   │   ├── BaseModel.php  # Modelo base com CRUD
│   │   ├── User.php       # Modelo de usuários
│   │   ├── Product.php    # Modelo de produtos
│   │   └── Order.php      # Modelo de pedidos
│   ├── Controllers/
│   │   ├── UserController.php     # Controller de usuários
│   │   ├── ProductController.php  # Controller de produtos
│   │   └── OrderController.php    # Controller de pedidos
│   ├── routes/
│   │   └── api.php        # Definição das rotas
│   └── autoload.php       # Autoloader personalizado
├── composer.json          # Configurações do Composer
└── README.md              # Documentação
```

## Banco de Dados

### Tabelas

1. **usuarios**
   - Gerenciamento de usuários (clientes e administradores)
   - Autenticação com senha hash
   - Controle de status ativo/inativo

2. **produtos**
   - Catálogo de jogos
   - Informações detalhadas (categoria, plataforma, desenvolvedor, etc.)
   - Controle de estoque

3. **pedidos**
   - Gerenciamento de pedidos
   - Status de acompanhamento
   - Relacionamento com usuários

4. **itens_pedido**
   - Itens individuais de cada pedido
   - Relacionamento com produtos
   - Controle de quantidade e preços

## Endpoints da API

### Informações Gerais
- `GET /` - Informações da API
- `GET /api/status` - Status da API

### Usuários
- `GET /api/users` - Listar usuários
- `GET /api/users/{id}` - Buscar usuário por ID
- `POST /api/users` - Criar usuário
- `PUT /api/users/{id}` - Atualizar usuário
- `DELETE /api/users/{id}` - Deletar usuário
- `POST /api/users/login` - Login de usuário
- `GET /api/users/type/{type}` - Buscar usuários por tipo

### Produtos
- `GET /api/products` - Listar produtos
- `GET /api/products/{id}` - Buscar produto por ID
- `POST /api/products` - Criar produto
- `PUT /api/products/{id}` - Atualizar produto
- `DELETE /api/products/{id}` - Remover produto
- `GET /api/products/search?q={term}` - Pesquisar produtos
- `GET /api/products/category/{category}` - Produtos por categoria
- `GET /api/products/platform/{platform}` - Produtos por plataforma
- `GET /api/products/categories` - Listar categorias
- `GET /api/products/platforms` - Listar plataformas
- `GET /api/products/bestsellers` - Produtos mais vendidos
- `GET /api/products/onsale` - Produtos em promoção

### Pedidos
- `GET /api/orders` - Listar pedidos
- `GET /api/orders/{id}` - Buscar pedido por ID
- `POST /api/orders` - Criar pedido
- `PUT /api/orders/{id}/status` - Atualizar status do pedido
- `PUT /api/orders/{id}/cancel` - Cancelar pedido
- `GET /api/orders/user/{userId}` - Pedidos de um usuário
- `GET /api/orders/status/{status}` - Pedidos por status
- `GET /api/orders/stats` - Estatísticas de pedidos

## Instalação

1. **Clonar o projeto**
   ```bash
   git clone <repository-url>
   cd games-store-api
   ```

2. **Configurar servidor web**
   - Apontar DocumentRoot para a pasta `public/`
   - Habilitar mod_rewrite no Apache

3. **Configurar banco de dados**
   - Criar banco MySQL: `games_store`
   - Ajustar credenciais em `src/Config/Database.php`

4. **Inicializar aplicação**
   - Acessar qualquer endpoint para criar tabelas automaticamente
   - Dados de exemplo serão inseridos automaticamente

## Configuração do Servidor

### Apache
```apache
<VirtualHost *:80>
    DocumentRoot /path/to/games-store-api/public
    ServerName games-store-api.local
    
    <Directory /path/to/games-store-api/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx
```nginx
server {
    listen 80;
    server_name games-store-api.local;
    root /path/to/games-store-api/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Exemplos de Uso

### Criar Usuário
```bash
curl -X POST http://localhost/api/users \
  -H "Content-Type: application/json" \
  -d '{
    "username": "joao123",
    "email": "joao@email.com",
    "senha": "senha123",
    "nome_completo": "João Silva"
  }'
```

### Login
```bash
curl -X POST http://localhost/api/users/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "joao123",
    "senha": "senha123"
  }'
```

### Criar Pedido
```bash
curl -X POST http://localhost/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "usuario_id": 1,
    "itens": [
      {
        "produto_id": 1,
        "quantidade": 2
      },
      {
        "produto_id": 3,
        "quantidade": 1
      }
    ],
    "endereco_entrega": "Rua das Flores, 123"
  }'
```

## Dados de Exemplo

A API vem com dados de exemplo pré-configurados:

### Usuários
- **Admin**: username: `admin`, senha: `admin123`
- **Cliente**: username: `cliente_teste`, senha: `cliente123`

### Produtos
- The Witcher 3: Wild Hunt
- Cyberpunk 2077
- Red Dead Redemption 2
- Grand Theft Auto V
- Minecraft

## Tecnologias Utilizadas

- **PHP 7.4+**
- **MySQL 5.7+**
- **Apache/Nginx**
- **PDO** para acesso ao banco de dados
- **JSON** para comunicação da API

## Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## Licença

Este projeto está sob a licença MIT.
