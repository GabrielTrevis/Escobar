<?php
require 'banco.php'; // Garanta que o caminho para banco.php está correto

$pdo = Banco::conectar();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// --- Lógica para Exclusão (se houver feedback) ---
$delete_status = null;
if (isset($_GET['delete_status'])) {
    if ($_GET['delete_status'] == 'success') {
        $delete_status = ['type' => 'success', 'message' => 'Pedido excluído com sucesso!'];
    } elseif ($_GET['delete_status'] == 'error') {
        $delete_status = ['type' => 'danger', 'message' => 'Erro ao excluir o pedido.'];
    } elseif ($_GET['delete_status'] == 'notfound') {
        $delete_status = ['type' => 'warning', 'message' => 'Pedido não encontrado para exclusão.'];
    }
}

// --- Consulta e Agrupamento de Pedidos ---
$sql = "
SELECT 
    ped.id AS item_id, 
    ped.data_pedido, 
    ped.quantidade, 
    ped.grupo_pedido_id, 
    cli.id AS cliente_id, 
    cli.nome AS cliente_nome, 
    cli.email AS cliente_email,
    cli.telefone AS cliente_telefone,
    cli.endereco AS cliente_endereco,
    prod.id AS produto_id, 
    prod.nome AS produto_nome,
    prod.valor AS produto_valor, 
    prod.imagem AS produto_imagem
FROM pedidos ped
LEFT JOIN cliente cli ON ped.id_cliente = cli.id 
LEFT JOIN produto prod ON ped.id_produto = prod.id
WHERE ped.grupo_pedido_id IS NOT NULL
ORDER BY ped.data_pedido DESC, ped.grupo_pedido_id ASC, ped.id ASC
";

$pedidos_agrupados = [];
$erro_db = null;
try {
    $stmt = $pdo->query($sql);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $item) {
        $grupo_id = $item['grupo_pedido_id'];
        
        if (!isset($pedidos_agrupados[$grupo_id])) {
            $pedidos_agrupados[$grupo_id] = [
                'grupo_id' => $grupo_id,
                'data_pedido' => $item['data_pedido'], 
                'cliente_id' => $item['cliente_id'],
                'cliente_nome' => $item['cliente_nome'] ?? 'Desconhecido (ID ' . ($item['cliente_id'] ?? 0) . ')',
                'cliente_email' => $item['cliente_email'] ?? '',
                'cliente_telefone' => $item['cliente_telefone'] ?? '',
                'cliente_endereco' => $item['cliente_endereco'] ?? '',
                'itens' => [],
                'valor_total_pedido' => 0,
                'total_itens' => 0
            ];
        }
        
        $valor_unitario_num = floatval(str_replace(',', '.', $item['produto_valor']));
        $quantidade_num = intval($item['quantidade']);
        $subtotal_item = $valor_unitario_num * $quantidade_num;
        
        $pedidos_agrupados[$grupo_id]['itens'][] = [
            'item_id' => $item['item_id'],
            'produto_id' => $item['produto_id'],
            'produto_nome' => $item['produto_nome'] ?? 'Produto Desconhecido (ID ' . ($item['produto_id'] ?? 'N/A') . ')',
            'produto_imagem' => $item['produto_imagem'] ?? '',
            'quantidade' => $quantidade_num,
            'valor_unitario' => $item['produto_valor'],
            'valor_unitario_num' => $valor_unitario_num,
            'subtotal' => $subtotal_item
        ];
        
        $pedidos_agrupados[$grupo_id]['valor_total_pedido'] += $subtotal_item;
        $pedidos_agrupados[$grupo_id]['total_itens'] += $quantidade_num;
    }

} catch (PDOException $e) {
    $erro_db = "Erro ao buscar ou agrupar pedidos: " . $e->getMessage();
    $pedidos_agrupados = [];
}

Banco::desconectar();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Histórico de Pedidos</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #eef2f5; }
        .container { margin-top: 30px; }
        .page-header {
            background-color: #ffffff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.06);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-header h1 { margin-bottom: 0; font-size: 1.75rem; color: #343a40; }
        .summary-cards .card { border: none; border-radius: 8px; box-shadow: 0 3px 8px rgba(0,0,0,0.06); margin-bottom: 20px; transition: transform 0.2s ease; }
        .summary-cards .card:hover { transform: translateY(-3px); }
        .summary-cards .card-body { padding: 1.5rem; display: flex; align-items: center; }
        .summary-cards .card-icon { font-size: 2rem; margin-right: 1rem; padding: 0.8rem; border-radius: 50%; color: #fff; }
        .icon-pedidos { background-color: #0d6efd; }
        .icon-itens { background-color: #fd7e14; }
        .icon-valor { background-color: #198754; }
        .summary-cards .card-title { font-size: 0.9rem; font-weight: 600; color: #6c757d; text-transform: uppercase; margin-bottom: 0.2rem; }
        .summary-cards .card-text { font-size: 1.6rem; font-weight: 700; color: #343a40; }
        .accordion-item { border: 1px solid #dee2e6; border-radius: 8px; margin-bottom: 15px; box-shadow: 0 3px 8px rgba(0,0,0,0.06); overflow: hidden; }
        .accordion-button { font-weight: 600; background-color: #ffffff; padding: 1rem 1.5rem; border-bottom: 1px solid #dee2e6; }
        .accordion-button:not(.collapsed) { color: #0a58ca; background-color: #f8f9fa; box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.125); }
        .accordion-button:focus { box-shadow: none; }
        .accordion-body { padding: 1.5rem; background-color: #ffffff; }
        .pedido-info span { margin-right: 15px; font-size: 0.9em; color: #6c757d; }
        .pedido-info strong { color: #495057; }
        .valor-total-header { font-weight: bold; font-size: 1.1em; color: #198754; }
        .cliente-details { background-color: #f8f9fa; padding: 20px; border-radius: 6px; margin-bottom: 25px; border: 1px solid #e9ecef; }
        .cliente-details h5 { margin-bottom: 15px; font-size: 1.1rem; color: #495057; border-bottom: 1px solid #dee2e6; padding-bottom: 10px; }
        .cliente-details p { margin-bottom: 8px; font-size: 0.95em; }
        .itens-pedido-header { margin-bottom: 15px; font-size: 1.1rem; color: #495057; border-bottom: 1px solid #dee2e6; padding-bottom: 10px; }
        
        /* Estilo Aprimorado para Itens do Pedido */
        .list-group-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e9ecef; /* Linha divisória mais suave */
        }
        .list-group-item:last-child { border-bottom: none; }
        .item-info {
            display: flex;
            align-items: center;
            flex-grow: 1; /* Ocupa espaço disponível */
            margin-right: 1rem;
        }
        .item-imagem {
            width: 65px; /* Imagem um pouco maior */
            height: 65px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 15px;
            border: 1px solid #dee2e6;
        }
        .item-nome {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.2rem;
        }
        .item-preco-unit {
            font-size: 0.85em;
            color: #6c757d;
        }
        .item-qtd-subtotal {
            text-align: right;
            min-width: 120px; /* Espaço para qtd e subtotal */
        }
        .item-quantidade {
            font-size: 0.9em;
            color: #6c757d;
            margin-bottom: 0.2rem;
        }
        .item-subtotal {
            font-weight: bold;
            color: #20c997; /* Verde mais vibrante */
        }
        .pedido-total-footer {
            text-align: right;
            padding: 1rem 1.25rem;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            font-size: 1.1em;
            font-weight: bold;
        }
        .pedido-actions {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px dashed #ced4da;
            text-align: right;
        }
        .sem-pedidos { text-align: center; padding: 60px 20px; background-color: white; border-radius: 8px; box-shadow: 0 3px 8px rgba(0,0,0,0.06); }
        .sem-pedidos i { font-size: 4em; color: #adb5bd; margin-bottom: 25px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><i class="bi bi-receipt-cutoff"></i> Histórico de Pedidos</h1>
            <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left-circle"></i> Voltar ao Painel</a> 
        </div>

        <!-- Feedback de Exclusão -->
        <?php if ($delete_status): ?>
            <div class="alert alert-<?php echo $delete_status['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($delete_status['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($erro_db)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> 
                Erro ao conectar ou buscar dados: <?php echo htmlspecialchars($erro_db); ?>
            </div>
        <?php elseif (empty($pedidos_agrupados)): ?>
            <div class="sem-pedidos">
                <i class="bi bi-cart-x"></i>
                <h3>Nenhum pedido encontrado</h3>
                <p class="text-muted">Ainda não há pedidos registrados no sistema.</p>
                <a href="index.php" class="btn btn-primary mt-3">Voltar ao Painel</a>
            </div>
        <?php else: ?>
            <!-- Resumo Geral -->
            <?php 
                $total_geral = 0;
                $total_pedidos = count($pedidos_agrupados);
                $total_itens_geral = 0;
                foreach ($pedidos_agrupados as $pedido) {
                    $total_geral += $pedido['valor_total_pedido'];
                    $total_itens_geral += $pedido['total_itens'];
                }
            ?>
            <div class="row summary-cards">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-icon icon-pedidos"><i class="bi bi-cart-check"></i></div>
                            <div>
                                <h5 class="card-title">Pedidos</h5>
                                <p class="card-text"><?php echo $total_pedidos; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                             <div class="card-icon icon-itens"><i class="bi bi-box-seam"></i></div>
                             <div>
                                <h5 class="card-title">Itens Vendidos</h5>
                                <p class="card-text"><?php echo $total_itens_geral; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-icon icon-valor"><i class="bi bi-cash-coin"></i></div>
                            <div>
                                <h5 class="card-title">Valor Total</h5>
                                <p class="card-text">R$ <?php echo number_format($total_geral, 2, ',', '.'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Pedidos em Accordion -->
            <div class="accordion" id="accordionPedidos">
                <?php foreach ($pedidos_agrupados as $grupo_id => $pedido): ?>
                    <?php $collapse_id = 'collapse_' . preg_replace('/[^a-zA-Z0-9]/', '', $grupo_id); ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading_<?php echo $collapse_id; ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapse_id; ?>" aria-expanded="false" aria-controls="<?php echo $collapse_id; ?>">
                                <div class="d-flex justify-content-between w-100 align-items-center">
                                    <div class="pedido-info">
                                        <span><i class="bi bi-hash"></i> <strong>Pedido:</strong> <?php echo htmlspecialchars(substr($grupo_id, 0, 15)); ?>...</span>
                                        <span><i class="bi bi-calendar3"></i> <strong>Data:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($pedido['data_pedido']))); ?></span>
                                        <span><i class="bi bi-person"></i> <strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['cliente_nome']); ?></span>
                                    </div>
                                    <span class="valor-total-header">R$ <?php echo number_format($pedido['valor_total_pedido'], 2, ',', '.'); ?></span>
                                </div>
                            </button>
                        </h2>
                        <div id="<?php echo $collapse_id; ?>" class="accordion-collapse collapse" aria-labelledby="heading_<?php echo $collapse_id; ?>" data-bs-parent="#accordionPedidos">
                            <div class="accordion-body">
                                <div class="row">
                                    <!-- Detalhes do Cliente (Esquerda) -->
                                    <div class="col-md-4">
                                        <div class="cliente-details">
                                            <h5><i class="bi bi-person-badge"></i> Detalhes do Cliente</h5>
                                            <p><strong>Nome:</strong> <?php echo htmlspecialchars($pedido['cliente_nome']); ?></p>
                                            <?php if (!empty($pedido['cliente_email'])): ?>
                                                <p><strong>Email:</strong> <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($pedido['cliente_email']); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($pedido['cliente_telefone'])): ?>
                                                <p><strong>Telefone:</strong> <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($pedido['cliente_telefone']); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($pedido['cliente_endereco'])): ?>
                                                <p><strong>Endereço:</strong> <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($pedido['cliente_endereco']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Itens do Pedido (Direita) -->
                                    <div class="col-md-8">
                                        <h5 class="itens-pedido-header"><i class="bi bi-list-ul"></i> Itens Comprados (<?php echo $pedido['total_itens']; ?>)</h5>
                                        <div class="list-group mb-3">
                                            <?php foreach ($pedido['itens'] as $item_pedido): ?>
                                                <div class="list-group-item">
                                                    <div class="item-info">
                                                        <?php 
                                                        $imgPath = (!empty($item_pedido['produto_imagem']) && file_exists('uploads/' . $item_pedido['produto_imagem'])) 
                                                                   ? 'uploads/' . htmlspecialchars($item_pedido['produto_imagem']) 
                                                                   : 'img/placeholder.png';
                                                        ?>
                                                        <img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($item_pedido['produto_nome']); ?>" class="item-imagem">
                                                        <div>
                                                            <div class="item-nome"><?php echo htmlspecialchars($item_pedido['produto_nome']); ?></div>
                                                            <div class="item-preco-unit">R$ <?php echo number_format($item_pedido['valor_unitario_num'], 2, ',', '.'); ?> / un.</div>
                                                        </div>
                                                    </div>
                                                    <div class="item-qtd-subtotal">
                                                        <div class="item-quantidade">Qtd: <?php echo $item_pedido['quantidade']; ?></div>
                                                        <div class="item-subtotal">R$ <?php echo number_format($item_pedido['subtotal'], 2, ',', '.'); ?></div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="pedido-total-footer">
                                            <strong>Total do Pedido: R$ <?php echo number_format($pedido['valor_total_pedido'], 2, ',', '.'); ?></strong>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Ações do Pedido (Excluir) -->
                                <div class="pedido-actions">
                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete('<?php echo htmlspecialchars($grupo_id); ?>')">
                                        <i class="bi bi-trash"></i> Excluir Pedido
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função para confirmar exclusão
        function confirmDelete(grupoId) {
            if (confirm("Tem certeza que deseja excluir todos os itens deste pedido (" + grupoId + ")? Esta ação não pode ser desfeita.")) {
                // Redireciona para o script de exclusão
                window.location.href = 'delete_pedido.php?grupo_id=' + encodeURIComponent(grupoId);
            }
        }
    </script>
</body>
</html>
