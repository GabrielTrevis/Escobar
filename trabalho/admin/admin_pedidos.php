<?php
include 'banco.php';

// Consulta corrigida conforme o banco de dados
$sql = "
SELECT 
    p.id AS id_pedido,
    c.nome AS cliente_nome,
    pr.nome AS produto_nome,
    p.quantidade,
    p.data_pedido
FROM pedidos p
JOIN cliente c ON p.id_cliente = c.id
JOIN produto pr ON p.id_produto = pr.id
ORDER BY p.data_pedido DESC
";

$stmt = $pdo->query($sql);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo - Pedidos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            margin: 30px 0;
        }
        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background: #333;
            color: #fff;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        .voltar {
            text-align: center;
            margin: 20px;
        }
        .voltar a {
            background: #333;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <h1>Lista de Pedidos</h1>

    <table>
        <thead>
            <tr>
                <th>ID do Pedido</th>
                <th>Cliente</th>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Data do Pedido</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($pedidos) > 0): ?>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pedido['id_pedido']); ?></td>
                        <td><?php echo htmlspecialchars($pedido['cliente_nome']); ?></td>
                        <td><?php echo htmlspecialchars($pedido['produto_nome']); ?></td>
                        <td><?php echo htmlspecialchars($pedido['quantidade']); ?></td>
                        <td><?php echo htmlspecialchars($pedido['data_pedido']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5">Nenhum pedido encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="voltar">
        <a href="index.php">Voltar ao Painel</a>
    </div>

</body>
</html>
