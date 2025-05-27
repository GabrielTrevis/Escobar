<?php
session_start();
include 'banco.php';

// Verifica se o cliente foi selecionado
if (!isset($_POST['cliente_id']) || empty($_POST['cliente_id'])) {
    header("Location: carrinho.php?status=erro_cliente");
    exit;
}

$cliente_id = $_POST['cliente_id'];

// Verifica se o carrinho não está vazio
if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
    header("Location: carrinho.php?status=erro_carrinho");
    exit;
}

$pdo = Banco::conectar();

try {
    $pdo->beginTransaction();

    // Cria o pedido
    $sql_pedido = "INSERT INTO pedido (cliente_id, data_pedido) VALUES (?, NOW())";
    $stmt = $pdo->prepare($sql_pedido);
    $stmt->execute([$cliente_id]);
    $pedido_id = $pdo->lastInsertId();

    // Insere os itens do pedido
    foreach ($_SESSION['carrinho'] as $produto_id => $quantidade) {
        $sql_item = "INSERT INTO item_pedido (pedido_id, produto_id, quantidade) VALUES (?, ?, ?)";
        $stmt_item = $pdo->prepare($sql_item);
        $stmt_item->execute([$pedido_id, $produto_id, $quantidade]);
    }

    $pdo->commit();

    // Limpa o carrinho
    $_SESSION['carrinho'] = [];

    header("Location: carrinho.php?status=sucesso");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    header("Location: carrinho.php?status=erro");
    exit;
}
?>
