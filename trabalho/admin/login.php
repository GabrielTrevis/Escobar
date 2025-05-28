<?php
session_start();

// Se já estiver logado, redireciona para o painel
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header("Location: index.php");
    exit;
}

// Mensagem de erro (se houver)
$erro = '';
if (isset($_GET['erro'])) {
    switch ($_GET['erro']) {
        case 'credenciais':
            $erro = 'Usuário ou senha incorretos.';
            break;
        case 'sessao':
            $erro = 'Sua sessão expirou. Por favor, faça login novamente.';
            break;
        case 'acesso':
            $erro = 'Você precisa fazer login para acessar esta página.';
            break;
        default:
            $erro = 'Ocorreu um erro. Por favor, tente novamente.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f4f7f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #eaeaea;
            padding: 20px;
            text-align: center;
        }
        .card-header h3 {
            margin-bottom: 0;
            color: #333;
            font-weight: 600;
        }
        .card-body {
            padding: 30px;
        }
        .form-control {
            height: 50px;
            border-radius: 6px;
            box-shadow: none;
            border: 1px solid #dce7f1;
            padding: 10px 15px;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .btn-primary {
            height: 50px;
            border-radius: 6px;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(13, 110, 253, 0.2);
        }
        .login-icon {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 15px;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        .alert {
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-shield-lock login-icon"></i>
                <h3>Painel Administrativo</h3>
                <p class="text-muted mb-0">Faça login para acessar o sistema</p>
            </div>
            <div class="card-body">
                <?php if (!empty($erro)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($erro); ?>
                    </div>
                <?php endif; ?>

                <form action="processa_login.php" method="post">
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuário</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Digite seu usuário" required autofocus>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="senha" class="form-label">Senha</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite sua senha" required>
                        </div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Entrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="../index.php" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Voltar para o site
            </a>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
