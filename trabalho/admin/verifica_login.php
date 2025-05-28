<?php
// Arquivo de verificação de login que deve ser incluído no início de cada página administrativa
session_start(); // Inicia a sessão PHP para acessar as variáveis de sessão

// Verifica se o usuário está logado através das variáveis de sessão
// Se a variável 'logado' não existir ou não for verdadeira, o usuário não está autenticado
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    // Redireciona para a página de login com mensagem de erro de acesso
    header("Location: login.php?erro=acesso");
    exit; // Encerra a execução do script após o redirecionamento
}

// Sistema de timeout por inatividade (30 minutos = 1800 segundos)
// Isso aumenta a segurança, encerrando sessões inativas automaticamente
$tempo_limite = 1800; // Define o tempo limite em segundos
if (isset($_SESSION['ultimo_acesso']) && (time() - $_SESSION['ultimo_acesso'] > $tempo_limite)) {
    // Se o tempo desde o último acesso for maior que o limite, a sessão expirou
    session_unset(); // Remove todas as variáveis de sessão
    session_destroy(); // Destrói a sessão
    
    // Redireciona para o login com mensagem específica de sessão expirada
    header("Location: login.php?erro=sessao");
    exit;
}

// Atualiza o timestamp do último acesso a cada vez que uma página protegida é acessada
// Isso mantém a sessão ativa enquanto o usuário estiver navegando no sistema
$_SESSION['ultimo_acesso'] = time();
?>
