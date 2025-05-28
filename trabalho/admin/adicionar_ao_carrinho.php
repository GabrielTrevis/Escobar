<?php
session_start();

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    if (isset($_SESSION['carrinho'][$id])) {
        $_SESSION['carrinho'][$id] += 1;
    } else {
        $_SESSION['carrinho'][$id] = 1;
    }
}

// Redireciona automaticamente para o carrinho
header('Location: carrinho.php');
exit;
