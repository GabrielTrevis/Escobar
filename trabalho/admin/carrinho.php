<?php
session_start();
include 'banco.php';
$pdo = Banco::conectar();

// Inicializa o carrinho
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Ações de quantidade
if (isset($_GET['acao']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    switch ($_GET['acao']) {
        case 'add':
            $_SESSION['carrinho'][$id] = ($_SESSION['carrinho'][$id] ?? 0) + 1;
            break;
        case 'del':
            if (isset($_SESSION['carrinho'][$id])) {
                $_SESSION['carrinho'][$id]--;
                if ($_SESSION['carrinho'][$id] <= 0) {
                    unset($_SESSION['carrinho'][$id]);
                }
            }
            break;
        case 'remover':
            unset($_SESSION['carrinho'][$id]);
            break;
    }
    header("Location: carrinho.php");
    exit;
}

// Buscar produtos do carrinho
$ids = array_keys($_SESSION['carrinho']);
$produtos = [];

if (count($ids) > 0) {
    $sql = "SELECT * FROM produto WHERE id IN (" . implode(',', $ids) . ")";
    $produtos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Carrinho</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        .quantidade-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="jumbotron mt-4">
            <h2>Carrinho de Compras</h2>
        </div>

        <?php if (count($produtos) == 0): ?>
            <div class="alert alert-info">Seu carrinho está vazio.</div>
            <a href="produtos.php" class="btn btn-secondary">Voltar para Produtos</a>
        <?php else: ?>
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Imagem</th>
                        <th>Preço</th>
                        <th>Quantidade</th>
                        <th>Total</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalGeral = 0;
                    foreach ($produtos as $produto):
                        $id = $produto['id'];
                        $quantidade = $_SESSION['carrinho'][$id];
                        $subtotal = $quantidade * $produto['valor'];
                        $totalGeral += $subtotal;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                            <td><img src="<?php echo $produto['imagem']; ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>"></td>
                            <td>R$ <?php echo number_format($produto['valor'], 2, ',', '.'); ?></td>
                            <td>
                                <div class="quantidade-box">
                                    <a href="carrinho.php?acao=del&id=<?php echo $id; ?>" class="btn btn-danger btn-sm">-</a>
                                    <span><?php echo $quantidade; ?></span>
                                    <a href="carrinho.php?acao=add&id=<?php echo $id; ?>" class="btn btn-success btn-sm">+</a>
                                </div>
                            </td>
                            <td>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                            <td><a href="carrinho.php?acao=remover&id=<?php echo $id; ?>" class="btn btn-outline-danger btn-sm">Remover</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4">Total Geral:</th>
                        <th colspan="2">R$ <?php echo number_format($totalGeral, 2, ',', '.'); ?></th>
                    </tr>
                </tfoot>
            </table>

            <a href="finalizar.php" class="btn btn-success mt-3">Finalizar Pedido</a>
            <a href="produtos.php" class="btn btn-secondary mt-3">Voltar para Produtos</a>
        <?php endif; ?>

        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] === 'sucesso'): ?>
                <div class="alert alert-success mt-3">Pedido finalizado com sucesso!</div>
            <?php else: ?>
                <div class="alert alert-danger mt-3">Erro ao processar o pedido.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
