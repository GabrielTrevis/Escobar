<?php
session_start();

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os dados do formulário
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $senha = isset($_POST['senha']) ? trim($_POST['senha']) : '';
    
    // Credenciais fixas para demonstração
    // Em um ambiente real, você buscaria essas informações do banco de dados
    $usuario_correto = 'admin';
    $senha_correta = 'admin';
    
    // Verifica se as credenciais estão corretas
    if ($usuario === $usuario_correto && $senha === $senha_correta) {
        // Credenciais corretas, inicia a sessão
        $_SESSION['logado'] = true;
        $_SESSION['usuario'] = $usuario;
        $_SESSION['ultimo_acesso'] = time();
        
        // Redireciona para o painel administrativo
        header("Location: index.php");
        exit;
    } else {
        // Credenciais incorretas, redireciona para o login com mensagem de erro
        header("Location: login.php?erro=credenciais");
        exit;
    }
} else {
    // Se alguém tentar acessar este arquivo diretamente sem enviar o formulário
    header("Location: login.php");
    exit;
}
?>
