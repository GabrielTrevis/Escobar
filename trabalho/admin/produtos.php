<?php
session_start();
include 'banco.php';
$pdo = Banco::conectar();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// --- Lógica do Carrinho (mantida) ---
if (!isset($_SESSION['carrinho']) || !is_array($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}
if (isset($_GET['add']) && is_numeric($_GET['add'])) {
    $idProduto = $_GET['add'];
    if (isset($_SESSION['carrinho'][$idProduto]) && is_numeric($_SESSION['carrinho'][$idProduto])) {
        $_SESSION['carrinho'][$idProduto]++;
    } else {
        $_SESSION['carrinho'][$idProduto] = 1;
    }
    // Remove 'add' da URL para evitar re-adicionar ao recarregar
    // Redireciona para a mesma página, preservando o filtro de categoria se existir
    $redirect_url = 'produtos.php';
    if (isset($_GET['categoria']) && is_numeric($_GET['categoria'])) {
        $redirect_url .= '?categoria=' . $_GET['categoria'];
    }
    header("Location: " . $redirect_url);
    exit;
}
// --- Fim da Lógica do Carrinho ---

// --- Lógica de Filtro por Categoria ---
$categoria_filtrada_id = null;
$categoria_filtrada_nome = "Todos os Produtos"; // Título padrão

// Verifica se uma categoria foi selecionada via GET
if (isset($_GET['categoria']) && is_numeric($_GET['categoria'])) {
    $categoria_filtrada_id = intval($_GET['categoria']);
}

// Busca todas as categorias para exibir os filtros
$sql_categorias = "SELECT id, nome FROM categorias ORDER BY nome ASC";
$categorias = $pdo->query($sql_categorias)->fetchAll(PDO::FETCH_ASSOC);

// Monta a consulta SQL base para produtos
$sql_produtos = "SELECT p.id, p.nome, p.valor, p.imagem, c.nome as categoria_nome 
                 FROM produto p 
                 LEFT JOIN categorias c ON p.categoria_id = c.id";

// Adiciona o filtro WHERE se uma categoria foi selecionada
if ($categoria_filtrada_id !== null) {
    $sql_produtos .= " WHERE p.categoria_id = :categoria_id";
    // Busca o nome da categoria filtrada para o título
    $sql_nome_cat = "SELECT nome FROM categorias WHERE id = :id";
    $stmt_nome = $pdo->prepare($sql_nome_cat);
    $stmt_nome->bindParam(':id', $categoria_filtrada_id, PDO::PARAM_INT);
    $stmt_nome->execute();
    $cat_nome_result = $stmt_nome->fetch(PDO::FETCH_ASSOC);
    if ($cat_nome_result) {
        $categoria_filtrada_nome = "Produtos da Categoria: " . htmlspecialchars($cat_nome_result['nome']);
    }
}

$sql_produtos .= " ORDER BY p.nome ASC";

// Prepara e executa a consulta de produtos
$stmt_produtos = $pdo->prepare($sql_produtos);
if ($categoria_filtrada_id !== null) {
    $stmt_produtos->bindParam(':categoria_id', $categoria_filtrada_id, PDO::PARAM_INT);
}
$stmt_produtos->execute();
$produtos_listados = $stmt_produtos->fetchAll(PDO::FETCH_ASSOC);
// --- Fim da Lógica de Filtro ---

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Produtos</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .produto-imagem {
            width: 80px; /* Imagem um pouco maior */
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .categoria-badge {
            font-size: 0.75em;
            font-weight: 500;
        }
        .filtros-categorias .nav-link {
            padding: 0.5rem 1rem;
            margin-bottom: 5px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            color: #0d6efd;
            text-align: center;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .filtros-categorias .nav-link.active {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
            border-color: #0d6efd;
        }
         .filtros-categorias .nav-link:hover {
            background-color: #e7f1ff;
            border-color: #cfe2ff;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <!-- Coluna de Filtros -->
            <div class="col-md-3">
                <h4><i class="bi bi-filter"></i> Categorias</h4>
                <nav class="nav flex-column filtros-categorias">
                    <a class="nav-link <?php echo ($categoria_filtrada_id === null) ? 'active' : ''; ?>" href="produtos.php">
                        <i class="bi bi-list-ul"></i> Todas
                    </a>
                    <?php foreach ($categorias as $cat): ?>
                        <a class="nav-link <?php echo ($categoria_filtrada_id === $cat['id']) ? 'active' : ''; ?>" href="produtos.php?categoria=<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['nome']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <hr>
                 <a href="carrinho.php" class="btn btn-primary w-100 mt-3">
                     <i class="bi bi-cart"></i> Ver Carrinho 
                     <?php 
                     $totalItensCarrinho = 0;
                     foreach($_SESSION['carrinho'] as $qtd) { $totalItensCarrinho += $qtd; }
                     if ($totalItensCarrinho > 0) {
                         echo '<span class="badge bg-danger ms-2">' . $totalItensCarrinho . '</span>';
                     }
                     ?>
                 </a>
                 <a href="index.php" class="btn btn-secondary w-100 mt-2"><i class="bi bi-arrow-left"></i> Voltar ao Painel</a>
            </div>

            <!-- Coluna de Produtos -->
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center mb-3">
                     <h2 class="mb-0"><?php echo $categoria_filtrada_nome; ?></h2>
                     <!-- Pode adicionar ordenação ou outros controles aqui -->
                </div>

                <?php if (empty($produtos_listados)): ?>
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                        Nenhum produto encontrado nesta categoria.
                    </div>
                <?php else: ?>
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 55%;">Produto</th>
                                <th style="width: 15%;" class="text-center">Categoria</th>
                                <th style="width: 15%;" class="text-end">Preço</th>
                                <th style="width: 15%;" class="text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($produtos_listados as $produto): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php 
                                            $imgPath = (!empty($produto['imagem']) && file_exists('uploads/' . $produto['imagem'])) 
                                                       ? 'uploads/' . htmlspecialchars($produto['imagem']) 
                                                       : 'img/placeholder.png'; // Placeholder
                                            ?>
                                            <img src="<?php echo $imgPath; ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>" class="produto-imagem me-3">
                                            <span><?php echo htmlspecialchars($produto['nome']); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($produto['categoria_nome'])): ?>
                                            <span class="badge bg-secondary categoria-badge">
                                                <?php echo htmlspecialchars($produto['categoria_nome']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark categoria-badge">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-bold">R$ <?php echo number_format(floatval(str_replace(',', '.', $produto['valor'])), 2, ',', '.'); ?></td>
                                   <td class="text-center">
                                        <!-- Usando link GET para adicionar ao carrinho -->
                                        <a href="produtos.php?add=<?php echo $produto['id']; ?><?php echo ($categoria_filtrada_id !== null) ? '&categoria=' . $categoria_filtrada_id : ''; ?>" class="btn btn-success btn-sm">
                                            <i class="bi bi-cart-plus"></i> Add
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
