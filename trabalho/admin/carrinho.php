<?php
session_start(); // Inicia a sessão para gerenciar o carrinho de compras
include 'banco.php'; // Inclui o arquivo de conexão com o banco de dados
$pdo = Banco::conectar(); // Estabelece a conexão com o banco
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Configura o PDO para lançar exceções em caso de erros

// Inicializa o carrinho como um array vazio se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Processa ações de alteração de quantidade no carrinho
if (isset($_GET['acao']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id']; // ID do produto a ser manipulado
    
    switch ($_GET['acao']) {
        case 'add': // Adicionar uma unidade
            $_SESSION['carrinho'][$id] = ($_SESSION['carrinho'][$id] ?? 0) + 1;
            break;
        case 'del': // Remover uma unidade
            if (isset($_SESSION['carrinho'][$id])) {
                $_SESSION['carrinho'][$id]--;
                // Se a quantidade chegar a zero ou menos, remove o item do carrinho
                if ($_SESSION['carrinho'][$id] <= 0) {
                    unset($_SESSION['carrinho'][$id]);
                }
            }
            break;
        case 'remover': // Remover o item completamente
            unset($_SESSION['carrinho'][$id]);
            break;
    }
    // Redireciona para atualizar a página sem os parâmetros GET
    header("Location: carrinho.php");
    exit;
}

// Buscar informações dos produtos que estão no carrinho
$ids = array_keys($_SESSION['carrinho']); // Obtém os IDs dos produtos no carrinho
$produtos = []; // Array para armazenar os dados dos produtos

if (count($ids) > 0) {
    // Prepara a consulta SQL para buscar vários produtos de uma vez
    // Garante que os IDs são inteiros para segurança na query IN
    $ids_seguros = array_map('intval', $ids);
    $placeholders = implode(',', array_fill(0, count($ids_seguros), '?')); // Cria placeholders para a query preparada
    $sql = "SELECT id, nome, valor, imagem FROM produto WHERE id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids_seguros); // Executa a consulta com os IDs como parâmetros
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtém todos os produtos encontrados
}

Banco::desconectar(); // Fecha a conexão com o banco de dados
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Carrinho</title>
    <!-- Carrega os estilos do Bootstrap e ícones -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* Estilização dos controles de quantidade */
        .quantidade-box {
            display: flex;
            align-items: center;
            justify-content: center; /* Centraliza os botões */
            gap: 10px;
        }
        /* Estilização das imagens de produtos */
        .produto-imagem {
            width: 70px;
            height: 70px;
            object-fit: cover; /* Mantém a proporção da imagem */
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        /* Alinhamento vertical e horizontal das células da tabela */
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
            <!-- Mensagem de alerta quando o carrinho fica vazio antes de finalizar -->
            <div class="alert alert-warning">Seu carrinho ficou vazio antes de finalizar. Adicione itens novamente.</div>
        <?php endif; ?>

        <?php if (empty($produtos)): ?>
            <!-- Exibe mensagem quando o carrinho está vazio -->
            <div class="alert alert-info text-center">
                <i class="bi bi-cart-x fs-1 d-block mb-3"></i>
                <h4>Seu carrinho está vazio.</h4>
                <a href="produtos.php" class="btn btn-primary mt-2">Ver Produtos</a>
            </div>
        <?php else:
            // Cria um mapa de produtos indexado por ID para facilitar o acesso
            $produtos_map = [];
            foreach ($produtos as $p) {
                $produtos_map[$p['id']] = $p;
            }
        ?>
            <!-- Tabela de itens do carrinho -->
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
                    $totalGeral = 0; // Inicializa o total geral do carrinho
                    // Itera sobre os itens do carrinho na sessão
                    foreach ($_SESSION['carrinho'] as $id => $quantidade):
                        // Verifica se o produto existe no mapa de produtos
                        if (!isset($produtos_map[$id])) continue; // Pula se o produto não foi encontrado no banco
                        $produto = $produtos_map[$id];
                        
                        // Monta o caminho da imagem, usando um placeholder se necessário
                        $imgPath = (!empty($produto['imagem']) && file_exists('uploads/' . $produto['imagem']))
                                   ? 'uploads/' . htmlspecialchars($produto['imagem'])
                                   : 'img/placeholder.png';

                        // Calcula o subtotal do item (preço unitário * quantidade)
                        $valor_unitario_num = floatval(str_replace(',', '.', $produto['valor']));
                        $subtotal = $quantidade * $valor_unitario_num;
                        $totalGeral += $subtotal; // Adiciona ao total geral
                    ?>
                        <tr>
                            <!-- Imagem do produto -->
                            <td><img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>" class="produto-imagem"></td>
                            <!-- Nome do produto -->
                            <td class="text-start"><?php echo htmlspecialchars($produto['nome']); ?></td>
                            <!-- Preço unitário formatado -->
                            <td>R$ <?php echo number_format($valor_unitario_num, 2, ',', '.'); ?></td>
                            <!-- Controles de quantidade (diminuir, quantidade atual, aumentar) -->
                            <td>
                                <div class="quantidade-box">
                                    <a href="carrinho.php?acao=del&id=<?php echo $id; ?>" class="btn btn-outline-danger btn-sm"><i class="bi bi-dash-lg"></i></a>
                                    <span class="fw-bold mx-1"><?php echo $quantidade; ?></span>
                                    <a href="carrinho.php?acao=add&id=<?php echo $id; ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-plus-lg"></i></a>
                                </div>
                            </td>
                            <!-- Subtotal do item formatado -->
                            <td class="fw-bold">R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                            <!-- Botão para remover o item completamente -->
                            <td>
                                <a href="carrinho.php?acao=remover&id=<?php echo $id; ?>" class="btn btn-danger btn-sm" title="Remover Item">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <!-- Rodapé da tabela com o total geral -->
                <tfoot>
                    <tr class="table-light">
                        <th colspan="4" class="text-end">Total Geral:</th>
                        <th colspan="2" class="fs-5 fw-bold">R$ <?php echo number_format($totalGeral, 2, ',', '.'); ?></th>
                    </tr>
                </tfoot>
            </table>

            <!-- Botão para finalizar o pedido -->
            <div class="d-flex justify-content-end mt-4">
                <a href="finalizar.php" class="btn btn-success btn-lg"><i class="bi bi-check-circle"></i> Finalizar Pedido</a>
            </div>
        <?php endif; ?>

    </div>
    <!-- Carrega o JavaScript do Bootstrap -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
