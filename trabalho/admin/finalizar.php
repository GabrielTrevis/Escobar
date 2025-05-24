<?php
session_start();
include 'banco.php';
$pdo = Banco::conectar();

// Garante que há itens no carrinho
if (!isset($_SESSION['carrinho']) || count($_SESSION['carrinho']) === 0) {
    echo "<p>O carrinho está vazio. <a href='produtos.php'>Voltar para produtos</a></p>";
    exit;
}

// Processa o envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cliente'])) {
    $idCliente = (int) $_POST['cliente'];
    $dataPedido = date('Y-m-d H:i:s');

    foreach ($_SESSION['carrinho'] as $idProduto => $quantidade) {
        $sql = "INSERT INTO pedidos (id_produto, quantidade, data_pedido, id_cliente)
                VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$idProduto, $quantidade, $dataPedido, $idCliente]);
    }

    // Limpa o carrinho
    unset($_SESSION['carrinho']);

    echo "<p>Pedido finalizado com sucesso!</p>";
    echo "<a href='produtos.php'>Voltar para os produtos</a>";
    exit;
}

// Busca os clientes cadastrados para o select
$clientes = $pdo->query("SELECT id, nome FROM cliente ORDER BY nome ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Finalizar Pedido</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Finalizar Pedido</h2>

    <form method="POST" action="finalizar.php" class="mt-4">
        <div class="form-group">
            <label for="cliente">Selecione o Cliente:</label>
            <select name="cliente" id="cliente" class="form-control" required>
                <option value="">-- Selecione --</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success mt-3">Finalizar Pedido</button>
        <a href="carrinho.php" class="btn btn-secondary mt-3">Voltar ao Carrinho</a>
    </form>
</div>
</body>
</html>
