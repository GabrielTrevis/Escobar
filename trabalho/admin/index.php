<?php
// Incluir verificação de login para proteger esta página
require_once 'verifica_login.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <!-- Carrega os estilos do Bootstrap e ícones -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Admin Painel</title>
    <style>
        /* Estilização da barra de informações do usuário e botão de logout */
        .user-info {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-info .welcome {
            display: flex;
            align-items: center;
        }
        .user-info .welcome i {
            font-size: 1.5rem;
            margin-right: 10px;
            color: #0d6efd;
        }
        .logout-btn {
            color: #dc3545;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        .logout-btn:hover {
            text-decoration: underline;
        }
        .logout-btn i {
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Barra superior com informações do usuário e botão de logout -->
        <div class="user-info mt-3">
            <div class="welcome">
                <i class="bi bi-person-circle"></i>
                <span>Bem-vindo, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></span>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="bi bi-box-arrow-right"></i> Sair
            </a>
        </div>
        
        <!-- Cabeçalho da página -->
        <div class="jumbotron">
            <div class="col">
                <h1> Loja Virtual - Painel Administrativo </h1>
            </div>
            
            <!-- Tabela de navegação com links para as funcionalidades do sistema -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Gerenciamento</th>
                        <th>Listagem</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td> <a href="cadastrarcliente.php" class="btn btn-success">Cadastrar Clientes</a></td>
                        <td> <a href="listacliente.php" class="btn btn-outline-info">Listar Clientes</a></td>
                    </tr>
                    <tr>
                        <td> <a href="cadastrarproduto.php" class="btn btn-success">Cadastrar Produtos</a></td>
                        <td><a href="listaproduto.php" class="btn btn-outline-info">Listar Produtos</a></td>
                    </tr>
                    <tr>
                        <td> <a href="gerenciar_categorias.php" class="btn btn-success">Gerenciar Categorias</a></td>
                        <td> <!-- Espaço para futura listagem de categorias, se necessário --> </td>
                    </tr>
                     <tr>
                        <td> <a href="admin_pedidos.php" class="btn btn-primary">Ver Pedidos</a></td>
                        <td> <!-- Espaço adicional --> </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Logo da loja -->
        <div class="row">
            <div class=" col-md-12 text-center">
                <img class="img-responsive" src="img/logo-inicio.png" alt="Imagem" />
            </div>
        </div>
    </div>

    <!-- Carrega os scripts JavaScript necessários -->
    <script src="https://code.jquery.com/jquery-3.3.1.js"
        integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous">
    </script>
    <!-- JavaScript do Bootstrap -->
    <script src="assets/js/bootstrap.min.js"></script>

    <!-- Botão flutuante para acessar a loja (área de produtos) -->
    <a href="produtos.php" class="btn btn-danger rounded-circle"
       style="position: fixed; bottom: 20px; right: 20px; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; z-index: 999;" title="Ver Loja">
        🛒
    </a>
</body>

</html>
