<?php
include 'banco.php';
$pdo = Banco::conectar();

// Consulta para trazer todos os pedidos realizados com nome do cliente e produto
$sql = "
    SELECT 
        c.nome AS cliente_nome,
        p.nome AS produto_nome,
        ped.quantidade,
        p.valor,
        (ped.quantidade * p.valor) AS subtotal,
    FROM pedidos ped
    INNER JOIN cliente c ON ped.id_cliente = c.id
    INNER JOIN produto p ON ped.id_produto = p.id
    ORDER BY ped.data_hora DESC
";

$pedidos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

Banco::desconectar();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Todos os Pedidos</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Todos os Pedidos Realizados</h2>

    <?php if (empty($pedidos)): ?>
        <div class="alert alert-info">Nenhum pedido foi registrado ainda.</div>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Cliente</th>
                    <th>Produto</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário</th>
                    <th>Subtotal</th>
                    <th>Data/Hora</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pedido['cliente_nome']); ?></td>
                        <td><?php echo htmlspecialchars($pedido['produto_nome']); ?></td>
                        <td><?php echo $pedido['quantidade']; ?></td>
                        <td>R$ <?php echo number_format($pedido['valor'], 2, ',', '.'); ?></td>
                        <td>R$ <?php echo number_format($pedido['subtotal'], 2, ',', '.'); ?></td>
                        <td>
                            <?php 
                                echo isset($pedido['data_hora']) 
                                    ? date('d/m/Y H:i', strtotime($pedido['data_hora'])) 
                                    : '—';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="index.php" class="btn btn-secondary">Voltar ao Início</a>
</div>
</body>
</html>
