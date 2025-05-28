# Documentação do Sistema de Loja Virtual

Este documento explica o fluxo geral do sistema e como os diferentes componentes estão interligados.

## Estrutura Geral do Sistema

O sistema de loja virtual é composto por vários módulos que trabalham juntos para fornecer uma experiência completa de e-commerce:

1. **Sistema de Autenticação**
   - `login.php` - Página de login
   - `processa_login.php` - Processamento das credenciais
   - `verifica_login.php` - Verificação de sessão ativa
   - `logout.php` - Encerramento da sessão

2. **Painel Administrativo**
   - `index.php` - Página principal com menu de opções

3. **Gerenciamento de Produtos**
   - `cadastrarproduto.php` - Cadastro de novos produtos
   - `listaproduto.php` - Listagem de produtos cadastrados
   - `update-produtos.php` - Atualização de produtos existentes

4. **Gerenciamento de Categorias**
   - `gerenciar_categorias.php` - Interface para adicionar e listar categorias
   - `update_categoria.php` - Atualização de categorias existentes
   - `delete_categoria.php` - Exclusão de categorias

5. **Loja e Carrinho**
   - `produtos.php` - Exibição de produtos para o cliente com filtro por categoria
   - `carrinho.php` - Gerenciamento do carrinho de compras
   - `finalizar.php` - Finalização do pedido

6. **Histórico de Pedidos**
   - `admin_pedidos.php` - Visualização e gerenciamento de pedidos
   - `delete_pedido.php` - Exclusão de pedidos

7. **Conexão com Banco de Dados**
   - `banco.php` - Classe para gerenciar conexões com o banco de dados

## Fluxo de Funcionamento

### 1. Autenticação

O fluxo começa com a autenticação do usuário:

1. O usuário acessa `login.php` e insere suas credenciais
2. `processa_login.php` verifica as credenciais e cria a sessão
3. Após o login bem-sucedido, o usuário é redirecionado para `index.php`
4. Todas as páginas administrativas incluem `verifica_login.php` para garantir que apenas usuários autenticados tenham acesso

### 2. Gerenciamento de Categorias

Antes de cadastrar produtos, é necessário configurar as categorias:

1. No painel administrativo (`index.php`), o usuário clica em "Gerenciar Categorias"
2. Em `gerenciar_categorias.php`, o usuário pode:
   - Adicionar novas categorias (nome e descrição)
   - Ver a lista de categorias existentes
   - Editar categorias existentes (redirecionando para `update_categoria.php`)
   - Excluir categorias (através de `delete_categoria.php`)

### 3. Gerenciamento de Produtos

Com as categorias configuradas, o usuário pode gerenciar os produtos:

1. No painel administrativo, o usuário pode:
   - Clicar em "Cadastrar Produtos" para adicionar novos produtos
   - Clicar em "Listar Produtos" para ver os produtos existentes
2. Em `cadastrarproduto.php`, o usuário preenche:
   - Nome, descrição e valor do produto
   - Seleciona uma categoria do dropdown
   - Faz upload de uma imagem
3. Em `listaproduto.php`, o usuário pode:
   - Ver todos os produtos cadastrados
   - Editar produtos existentes (redirecionando para `update-produtos.php`)
   - Excluir produtos

### 4. Loja e Processo de Compra

O fluxo de compra para o cliente funciona assim:

1. O cliente acessa `produtos.php` onde pode:
   - Ver todos os produtos disponíveis
   - Filtrar produtos por categoria
   - Adicionar produtos ao carrinho
2. Ao adicionar produtos, o cliente pode:
   - Continuar comprando
   - Acessar o carrinho clicando no botão "Ver Carrinho"
3. Em `carrinho.php`, o cliente pode:
   - Ver os produtos adicionados
   - Alterar quantidades
   - Remover produtos
   - Finalizar o pedido
4. Em `finalizar.php`, o cliente:
   - Seleciona um cliente cadastrado
   - Confirma o pedido
   - Recebe uma mensagem de confirmação e é redirecionado para a página inicial

### 5. Gerenciamento de Pedidos

Após a finalização de pedidos, o administrador pode gerenciá-los:

1. No painel administrativo, o usuário clica em "Ver Pedidos"
2. Em `admin_pedidos.php`, o usuário pode:
   - Ver todos os pedidos realizados
   - Ver detalhes de cada pedido (cliente, produtos, valores)
   - Excluir pedidos através de `delete_pedido.php`

## Integração entre Componentes

### Sessões

O sistema utiliza sessões PHP (`$_SESSION`) para:
- Manter o usuário logado entre páginas
- Armazenar itens do carrinho de compras
- Controlar o tempo de inatividade

### Banco de Dados

A classe `Banco` em `banco.php` gerencia todas as conexões com o banco de dados MySQL:
- Implementa o padrão Singleton para garantir uma única instância de conexão
- Fornece métodos para conectar e desconectar do banco
- É utilizada em todos os arquivos que precisam acessar o banco de dados

### Relacionamentos no Banco de Dados

- **Produtos e Categorias**: Cada produto pertence a uma categoria (relação N:1)
- **Pedidos e Produtos**: Um pedido pode conter vários produtos (relação N:N)
- **Pedidos e Clientes**: Cada pedido está associado a um cliente (relação N:1)

