<?php
session_start();
include 'banco.php';
$pdo = Banco::conectar();
$sql = 'SELECT * FROM produto ORDER BY nome ASC';

// Garante que o carrinho está sempre como array
if (!isset($_SESSION['carrinho']) || !is_array($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Verifica se algum produto foi adicionado via GET
if (isset($_GET['add']) && is_numeric($_GET['add'])) {
    $idProduto = $_GET['add'];

    // Verifica se o produto já está no carrinho e se é um número
    if (isset($_SESSION['carrinho'][$idProduto]) && is_numeric($_SESSION['carrinho'][$idProduto])) {
        $_SESSION['carrinho'][$idProduto]++;
    } else {
        $_SESSION['carrinho'][$idProduto] = 1;
    }

    header("Location: produtos.php"); // Redireciona para evitar reenvio
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Produtos</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="jumbotron mt-4">
            <h2>Produtos Disponíveis</h2>
        </div>

        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Preço</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pdo->query($sql) as $row): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="<?php echo $row['imagem']; ?>" alt="<?php echo htmlspecialchars($row['nome']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                <span><?php echo htmlspecialchars($row['nome']); ?></span>
                            </div>
                        </td>
                        <td>R$ <?php echo number_format($row['valor'], 2, ',', '.'); ?></td>
                       <td>
                            <form action="adicionar_ao_carrinho.php" method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-success btn-sm">Adicionar ao Carrinho</button>
                            </form>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="carrinho.php" class="btn btn-primary">Ir para o Carrinho</a>
        <a href="index.php" class="btn btn-secondary mt-3">Voltar</a>
    </div>
</body>
</html>
