<?php
require 'banco.php'; // Garanta que o caminho para banco.php está correto

$pdo = Banco::conectar();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Consulta para buscar os itens de pedidos, juntando com cliente e produto
// Ordena para agrupar por grupo_pedido_id e depois pela data mais recente do grupo
$sql = "
SELECT 
    ped.id AS item_id, 
    ped.data_pedido, 
    ped.quantidade, 
    ped.grupo_pedido_id, -- Coluna chave para agrupamento
    cli.id AS cliente_id, 
    cli.nome AS cliente_nome, 
    cli.email AS cliente_email,
    cli.telefone AS cliente_telefone,
    cli.endereco AS cliente_endereco,
    prod.id AS produto_id, 
    prod.nome AS produto_nome,
    prod.valor AS produto_valor, -- Lembre-se que 'valor' é VARCHAR!
    prod.imagem AS produto_imagem
FROM pedidos ped
LEFT JOIN cliente cli ON ped.id_cliente = cli.id 
LEFT JOIN produto prod ON ped.id_produto = prod.id
WHERE ped.grupo_pedido_id IS NOT NULL -- Ignora itens sem grupo (pedidos antigos?)
ORDER BY ped.data_pedido DESC, ped.grupo_pedido_id ASC, ped.id ASC
";

$pedidos_agrupados = [];
$erro_db = null;
try {
    $stmt = $pdo->query($sql);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupa os itens por grupo_pedido_id
    foreach ($resultados as $item) {
        $grupo_id = $item['grupo_pedido_id'];
        
        // Se o grupo ainda não existe no array, cria a estrutura base
        if (!isset($pedidos_agrupados[$grupo_id])) {
            $pedidos_agrupados[$grupo_id] = [
                'grupo_id' => $grupo_id,
                // Usa a data do primeiro item encontrado para o grupo (já que ordenamos)
                'data_pedido' => $item['data_pedido'], 
                'cliente_id' => $item['cliente_id'],
                'cliente_nome' => $item['cliente_nome'] ?? 'Desconhecido (ID ' . ($item['cliente_id'] ?? 0) . ')',
                'cliente_email' => $item['cliente_email'] ?? '',
                'cliente_telefone' => $item['cliente_telefone'] ?? '',
                'cliente_endereco' => $item['cliente_endereco'] ?? '',
                'itens' => [],
                'valor_total_pedido' => 0, // Inicializa o total do pedido
                'total_itens' => 0 // Contador de itens no pedido
            ];
        }
        
        // Calcula o subtotal do item (tratando valor VARCHAR)
        $valor_unitario_num = floatval(str_replace(',', '.', $item['produto_valor']));
        $quantidade_num = intval($item['quantidade']);
        $subtotal_item = $valor_unitario_num * $quantidade_num;
        
        // Adiciona o item ao pedido correspondente
        $pedidos_agrupados[$grupo_id]['itens'][] = [
            'item_id' => $item['item_id'], // ID da linha na tabela pedidos
            'produto_id' => $item['produto_id'],
            'produto_nome' => $item['produto_nome'] ?? 'Produto Desconhecido (ID ' . ($item['produto_id'] ?? 'N/A') . ')',
            'produto_imagem' => $item['produto_imagem'] ?? '',
            'quantidade' => $quantidade_num,
            'valor_unitario' => $item['produto_valor'], // Mantém o valor original para exibição
            'valor_unitario_num' => $valor_unitario_num, // Valor convertido para número
            'subtotal' => $subtotal_item
        ];
        
        // Acumula o subtotal no valor total do pedido
        $pedidos_agrupados[$grupo_id]['valor_total_pedido'] += $subtotal_item;
        $pedidos_agrupados[$grupo_id]['total_itens'] += $quantidade_num;
    }

} catch (PDOException $e) {
    $erro_db = "Erro ao buscar ou agrupar pedidos: " . $e->getMessage();
    $pedidos_agrupados = []; // Garante que a variável exista mesmo em caso de erro
}

Banco::desconectar();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Histórico de Pedidos</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css"> <!-- Verifique o caminho -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-bottom: 30px;
        }
        .container {
            margin-top: 30px;
        }
        .page-title {
            margin-bottom: 30px;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 15px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .accordion-button {
            font-weight: 600;
        }
        .accordion-button:not(.collapsed) {
            color: #0d6efd; /* Cor do Bootstrap primary */
            background-color: #e7f1ff; /* Fundo levemente azulado quando aberto */
        }
        .accordion-body {
            padding: 1.5rem;
            background-color: #ffffff;
        }
        .cliente-info-header {
            font-size: 0.9em;
            color: #6c757d;
            margin-left: 15px;
        }
        .total-pedido-header {
             font-weight: bold;
             color: #198754; /* Cor do Bootstrap success */
        }
        .table-itens th {
            background-color: #f8f9fa;
            font-size: 0.9em;
            text-align: center;
        }
        .table-itens td {
            font-size: 0.95em;
            text-align: center;
            vertical-align: middle;
        }
        .table-itens td:first-child { /* Nome do produto */
             text-align: left;
        }
        .cliente-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }
        .badge-total-itens {
            background-color: #0d6efd;
            color: white;
            font-size: 0.8em;
            padding: 5px 8px;
            border-radius: 50px;
            margin-left: 10px;
        }
        .produto-imagem {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 10px;
        }
        .produto-nome-col {
            display: flex;
            align-items: center;
        }
        .sem-pedidos {
            text-align: center;
            padding: 50px 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .sem-pedidos i {
            font-size: 3em;
            color: #6c757d;
            margin-bottom: 15px;
        }
        .filtro-pedidos {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .resumo-pedidos {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-left: 4px solid #198754;
        }
        .resumo-valor {
            font-size: 1.2em;
            font-weight: bold;
            color: #198754;
        }
        .resumo-quantidade {
            font-size: 1.2em;
            font-weight: bold;
            color: #0d6efd;
        }
        .status-pedido {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #28a745;
            color: white;
        }
        .status-pedido.pendente {
            background-color: #ffc107;
            color: #212529;
        }
        .status-pedido.concluido {
            background-color: #28a745;
        }
        .status-pedido.cancelado {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center page-title">
            <h1 class="h2 mb-0"><i class="bi bi-receipt-cutoff"></i> Histórico de Pedidos</h1>
            <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left-circle"></i> Voltar ao Painel</a> 
        </div>

        <?php if (isset($erro_db)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> 
                Erro ao conectar ou buscar dados: <?php echo htmlspecialchars($erro_db); ?>
            </div>
        <?php elseif (empty($pedidos_agrupados)): ?>
            <div class="sem-pedidos">
                <i class="bi bi-cart-x"></i>
                <h3>Nenhum pedido encontrado</h3>
                <p class="text-muted">Não há pedidos registrados no sistema ou a coluna 'grupo_pedido_id' não foi configurada.</p>
                <a href="index.php" class="btn btn-primary mt-3">Voltar ao Painel</a>
            </div>
        <?php else: ?>
            <!-- Resumo de Pedidos -->
            <?php 
                $total_geral = 0;
                $total_pedidos = count($pedidos_agrupados);
                $total_itens_geral = 0;
                
                foreach ($pedidos_agrupados as $pedido) {
                    $total_geral += $pedido['valor_total_pedido'];
                    $total_itens_geral += $pedido['total_itens'];
                }
            ?>
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="resumo-pedidos">
                        <h5><i class="bi bi-cart-check"></i> Total de Pedidos</h5>
                        <div class="resumo-quantidade"><?php echo $total_pedidos; ?> pedidos</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="resumo-pedidos">
                        <h5><i class="bi bi-box-seam"></i> Total de Itens</h5>
                        <div class="resumo-quantidade"><?php echo $total_itens_geral; ?> itens</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="resumo-pedidos">
                        <h5><i class="bi bi-cash-coin"></i> Valor Total</h5>
                        <div class="resumo-valor">R$ <?php echo number_format($total_geral, 2, ',', '.'); ?></div>
                    </div>
                </div>
            </div>

            <div class="accordion" id="accordionPedidos">
                <?php foreach ($pedidos_agrupados as $grupo_id => $pedido): ?>
                    <?php 
                        // Cria um ID único para o collapse baseado no grupo_id (removendo caracteres não permitidos)
                        $collapse_id = 'collapse_' . preg_replace('/[^a-zA-Z0-9]/', '', $grupo_id);
                        
                        // Define um status para o pedido (simulado - você pode implementar lógica real)
                        $status_pedido = 'concluido'; // Valores possíveis: pendente, concluido, cancelado
                    ?>
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header" id="heading_<?php echo $collapse_id; ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapse_id; ?>" aria-expanded="false" aria-controls="<?php echo $collapse_id; ?>">
                                <div class="d-flex align-items-center justify-content-between w-100">
                                    <div>
                                        <span class="me-3">
                                            <i class="bi bi-calendar3"></i> 
                                            <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($pedido['data_pedido']))); ?>
                                        </span>
                                        <span class="cliente-info-header">
                                            <i class="bi bi-person"></i> 
                                            <?php echo htmlspecialchars($pedido['cliente_nome']); ?>
                                            <span class="badge-total-itens">
                                                <?php echo $pedido['total_itens']; ?> itens
                                            </span>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="status-pedido <?php echo $status_pedido; ?> me-3">
                                            <?php if ($status_pedido === 'pendente'): ?>
                                                <i class="bi bi-hourglass-split"></i> Pendente
                                            <?php elseif ($status_pedido === 'concluido'): ?>
                                                <i class="bi bi-check-circle"></i> Concluído
                                            <?php elseif ($status_pedido === 'cancelado'): ?>
                                                <i class="bi bi-x-circle"></i> Cancelado
                                            <?php endif; ?>
                                        </span>
                                        <span class="total-pedido-header">
                                            R$ <?php echo number_format($pedido['valor_total_pedido'], 2, ',', '.'); ?>
                                        </span>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="<?php echo $collapse_id; ?>" class="accordion-collapse collapse" aria-labelledby="heading_<?php echo $collapse_id; ?>" data-bs-parent="#accordionPedidos">
                            <div class="accordion-body">
                                <!-- Informações do Cliente -->
                                <div class="cliente-card">
                                    <h5><i class="bi bi-person-circle"></i> Informações do Cliente</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1">
                                                <strong>Nome:</strong> <?php echo htmlspecialchars($pedido['cliente_nome']); ?>
                                            </p>
                                            <?php if (!empty($pedido['cliente_email'])): ?>
                                                <p class="mb-1">
                                                    <strong>Email:</strong> <i class="bi bi-envelope"></i> 
                                                    <?php echo htmlspecialchars($pedido['cliente_email']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php if (!empty($pedido['cliente_telefone'])): ?>
                                                <p class="mb-1">
                                                    <strong>Telefone:</strong> <i class="bi bi-telephone"></i> 
                                                    <?php echo htmlspecialchars($pedido['cliente_telefone']); ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if (!empty($pedido['cliente_endereco'])): ?>
                                                <p class="mb-1">
                                                    <strong>Endereço:</strong> <i class="bi bi-geo-alt"></i> 
                                                    <?php echo htmlspecialchars($pedido['cliente_endereco']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Detalhes do Pedido -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0"><i class="bi bi-box2"></i> Itens do Pedido</h5>
                                            <small class="text-muted">Pedido #<?php echo htmlspecialchars($grupo_id); ?></small>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <?php if (!empty($pedido['itens'])): ?>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped table-hover mb-0 table-itens">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 50%">Produto</th>
                                                            <th style="width: 15%"><i class="bi bi-123"></i> Qtd.</th>
                                                            <th style="width: 15%"><i class="bi bi-tag"></i> Valor Unit.</th>
                                                            <th style="width: 20%"><i class="bi bi-cash"></i> Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($pedido['itens'] as $item_pedido): ?>
                                                            <tr>
                                                                <td class="produto-nome-col">
                                                                    <?php if (!empty($item_pedido['produto_imagem'])): ?>
                                                                        <img src="../assets/img/<?php echo htmlspecialchars($item_pedido['produto_imagem']); ?>" 
                                                                             alt="<?php echo htmlspecialchars($item_pedido['produto_nome']); ?>" 
                                                                             class="produto-imagem">
                                                                    <?php else: ?>
                                                                        <div class="produto-imagem bg-light d-flex align-items-center justify-content-center">
                                                                            <i class="bi bi-image text-muted"></i>
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <?php echo htmlspecialchars($item_pedido['produto_nome']); ?>
                                                                </td>
                                                                <td><?php echo htmlspecialchars($item_pedido['quantidade']); ?></td>
                                                                <td>R$ <?php echo htmlspecialchars($item_pedido['valor_unitario']); ?></td>
                                                                <td class="fw-bold">R$ <?php echo number_format($item_pedido['subtotal'], 2, ',', '.'); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                    <tfoot>
                                                        <tr>
                                                            <th colspan="3" class="text-end">Valor Total do Pedido:</th>
                                                            <th class="text-center">R$ <?php echo number_format($pedido['valor_total_pedido'], 2, ',', '.'); ?></th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted p-3">Nenhum item encontrado para este pedido.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Informações Adicionais e Ações -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> Data do Pedido: 
                                            <?php echo htmlspecialchars(date('d/m/Y H:i:s', strtotime($pedido['data_pedido']))); ?>
                                        </small>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-primary me-2">
                                            <i class="bi bi-printer"></i> Imprimir
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-check-circle"></i> Marcar como Concluído
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script> <!-- Verifique o caminho -->
</body>
</html>
