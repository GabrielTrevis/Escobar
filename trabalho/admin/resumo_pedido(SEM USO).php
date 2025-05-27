<?php
include 'banco.php';
$pdo = Banco::conectar();

// Buscar todos os pedidos com os dados do cliente e produto
$sql = "
    SELECT 
        c.nome AS cliente_nome, 
        p.nome AS produto_nome, 
        p.valor, 
        ped.quantidade,
        (p.valor * ped.quantidade) AS subtotal
    FROM pedidos ped
    INNER JOIN cliente c ON ped.id_cliente = c.id
    INNER JOIN produto p ON ped.id_produto = p.id
    ORDER BY c.nome ASC
";

$pedidos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por cliente
$agrupado = [];
foreach ($pedidos as $pedido) {
    $cliente = $pedido['cliente_nome'];
    if (!isset($agrupado[$cliente])) {
        $agrupado[$cliente] = ['pedidos' => [], 'total' => 0];
    }
    $agrupado[$cliente]['pedidos'][] = $pedido;
    $agrupado[$cliente]['total'] += $pedido['subtotal'];
}

Banco::desconectar();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Resumo de Pedidos</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Resumo de Todos os Pedidos</h2>

    <?php if (empty($agrupado)): ?>
        <div class="alert alert-info">Nenhum pedido encontrado.</div>
    <?php else: ?>
        <?php foreach ($agrupado as $cliente => $dados): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <strong>Cliente: <?php echo htmlspecialchars($cliente); ?></strong>
                </div>
                <div class="card-body">
                    <table class="table table-bordered mb-3">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Preço Unitário</th>
                                <th>Quantidade</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dados['pedidos'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['produto_nome']); ?></td>
                                    <td>R$ <?php echo number_format($item['valor'], 2, ',', '.'); ?></td>
                                    <td><?php echo $item['quantidade']; ?></td>
                                    <td>R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <h5>Total: R$ <?php echo number_format($dados['total'], 2, ',', '.'); ?></h5>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <a href="carrinho.php" class="btn btn-secondary">Voltar ao Carrinho</a>
</div>
</body>
</html>
