<?php
// Arquivo de verificação de login para ser incluído no início de cada página administrativa
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    // Não está logado, redireciona para a página de login
    header("Location: login.php?erro=acesso");
    exit;
}

// Opcional: Verificar tempo de inatividade (30 minutos = 1800 segundos)
$tempo_limite = 1800; // 30 minutos
if (isset($_SESSION['ultimo_acesso']) && (time() - $_SESSION['ultimo_acesso'] > $tempo_limite)) {
    // Sessão expirou, encerra a sessão
    session_unset();
    session_destroy();
    
    // Redireciona para o login com mensagem de erro
    header("Location: login.php?erro=sessao");
    exit;
}

// Atualiza o tempo do último acesso
$_SESSION['ultimo_acesso'] = time();
?>
