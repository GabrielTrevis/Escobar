<?php
session_start(); // Inicia a sessão PHP para processar os dados de login

// Verifica se o formulário foi enviado via método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os dados do formulário e remove espaços em branco no início e fim
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $senha = isset($_POST['senha']) ? trim($_POST['senha']) : '';
    
    // Credenciais fixas para demonstração
    // Em um sistema real, essas credenciais viriam do banco de dados
    $usuario_correto = 'admin';
    $senha_correta = 'admin';
    
    // Verifica se as credenciais informadas correspondem às corretas
    if ($usuario === $usuario_correto && $senha === $senha_correta) {
        // Se as credenciais estiverem corretas, cria variáveis de sessão
        $_SESSION['logado'] = true; // Marca o usuário como logado
        $_SESSION['usuario'] = $usuario; // Guarda o nome do usuário
        $_SESSION['ultimo_acesso'] = time(); // Registra o momento do login
        
        // Redireciona para o painel administrativo
        header("Location: index.php");
        exit; // Encerra a execução do script após o redirecionamento
    } else {
        // Se as credenciais estiverem incorretas, redireciona para o login com mensagem de erro
        header("Location: login.php?erro=credenciais");
        exit;
    }
} else {
    // Se alguém tentar acessar este arquivo diretamente sem enviar o formulário
    // Redireciona para a página de login
    header("Location: login.php");
    exit;
}
?>
