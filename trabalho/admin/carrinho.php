<?php
session_start();
include 'banco.php';
$pdo = Banco::conectar();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
    // Garante que os IDs são inteiros para segurança na query IN
    $ids_seguros = array_map('intval', $ids);
    $placeholders = implode(',', array_fill(0, count($ids_seguros), '?'));
    $sql = "SELECT id, nome, valor, imagem FROM produto WHERE id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids_seguros);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

Banco::desconectar();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Carrinho</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .quantidade-box {
            display: flex;
            align-items: center;
            justify-content: center; /* Centraliza os botões */
            gap: 10px;
        }
        .produto-imagem {
            width: 70px; /* Aumenta um pouco a imagem */
            height: 70px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
        .table td:first-child { /* Alinha nome à esquerda */
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <h2 class="mb-0"><i class="bi bi-cart3"></i> Carrinho de Compras</h2>
            <a href="produtos.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Continuar Comprando</a>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'empty_on_submit'): ?>
            <div class="alert alert-warning">Seu carrinho ficou vazio antes de finalizar. Adicione itens novamente.</div>
        <?php endif; ?>

        <?php if (empty($produtos)): ?>
            <div class="alert alert-info text-center">
                <i class="bi bi-cart-x fs-1 d-block mb-3"></i>
                <h4>Seu carrinho está vazio.</h4>
                <a href="produtos.php" class="btn btn-primary mt-2">Ver Produtos</a>
            </div>
        <?php else:
            // Mapeia produtos por ID para fácil acesso
            $produtos_map = [];
            foreach ($produtos as $p) {
                $produtos_map[$p['id']] = $p;
            }
        ?>
            <table class="table table-bordered table-hover align-middle mt-4">
                <thead class="table-light">
                    <tr>
                        <th style="width: 15%;">Imagem</th>
                        <th style="width: 35%;">Produto</th>
                        <th style="width: 15%;">Preço Unit.</th>
                        <th style="width: 15%;">Quantidade</th>
                        <th style="width: 15%;">Subtotal</th>
                        <th style="width: 5%;">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalGeral = 0;
                    // Itera sobre a SESSÃO para garantir a ordem e quantidades corretas
                    foreach ($_SESSION['carrinho'] as $id => $quantidade):
                        // Pega os dados do produto do mapa
                        if (!isset($produtos_map[$id])) continue; // Pula se o produto não foi encontrado no banco
                        $produto = $produtos_map[$id];
                        
                        // *** CORREÇÃO AQUI: Monta o caminho da imagem ***
                        $imgPath = (!empty($produto['imagem']) && file_exists('uploads/' . $produto['imagem']))
                                   ? 'uploads/' . htmlspecialchars($produto['imagem'])
                                   : 'img/placeholder.png'; // Placeholder

                        // Calcula subtotal (garantindo que valor é numérico)
                        $valor_unitario_num = floatval(str_replace(',', '.', $produto['valor']));
                        $subtotal = $quantidade * $valor_unitario_num;
                        $totalGeral += $subtotal;
                    ?>
                        <tr>
                            <td><img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>" class="produto-imagem"></td>
                            <td class="text-start"><?php echo htmlspecialchars($produto['nome']); ?></td>
                            <td>R$ <?php echo number_format($valor_unitario_num, 2, ',', '.'); ?></td>
                            <td>
                                <div class="quantidade-box">
                                    <a href="carrinho.php?acao=del&id=<?php echo $id; ?>" class="btn btn-outline-danger btn-sm"><i class="bi bi-dash-lg"></i></a>
                                    <span class="fw-bold mx-1"><?php echo $quantidade; ?></span>
                                    <a href="carrinho.php?acao=add&id=<?php echo $id; ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-plus-lg"></i></a>
                                </div>
                            </td>
                            <td class="fw-bold">R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                            <td>
                                <a href="carrinho.php?acao=remover&id=<?php echo $id; ?>" class="btn btn-danger btn-sm" title="Remover Item">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <th colspan="4" class="text-end">Total Geral:</th>
                        <th colspan="2" class="fs-5 fw-bold">R$ <?php echo number_format($totalGeral, 2, ',', '.'); ?></th>
                    </tr>
                </tfoot>
            </table>

            <div class="d-flex justify-content-end mt-4">
                <a href="finalizar.php" class="btn btn-success btn-lg"><i class="bi bi-check-circle"></i> Finalizar Pedido</a>
            </div>
        <?php endif; ?>

    </div>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
