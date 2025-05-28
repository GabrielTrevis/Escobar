<?php
session_start(); // Inicia a sessão PHP

// Processo de logout (encerramento da sessão)
session_unset(); // Remove todas as variáveis de sessão
session_destroy(); // Destrói completamente a sessão

// Redireciona o usuário para a página de login
header("Location: login.php");
exit; // Encerra a execução do script após o redirecionamento
?>
