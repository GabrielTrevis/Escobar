<?php
session_start();
include 'banco.php'; // Garante que o caminho está correto
$pdo = Banco::conectar();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Garante que há itens no carrinho
if (!isset($_SESSION['carrinho']) || count($_SESSION['carrinho']) === 0) {
    // Redireciona para o carrinho com mensagem de erro
    header("Location: carrinho.php?status=empty"); 
    exit;
}

// Processa o envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cliente'])) {
    $idCliente = filter_input(INPUT_POST, 'cliente', FILTER_VALIDATE_INT);
    
    // Validação básica do ID do cliente
    if (!$idCliente) {
        // Se o cliente for inválido, redireciona de volta com erro
        // Idealmente, passaria uma mensagem de erro mais específica
        header("Location: finalizar.php?error=invalid_client");
        exit;
    }

    $dataPedido = date('Y-m-d H:i:s');
    // Gera um ID único para agrupar todos os itens desta compra específica
    // Usando uniqid com prefixo para mais segurança e timestamp para ordenação
    $grupoPedidoId = uniqid('pedido_', true) . '_' . time(); 

    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO pedidos (id_produto, quantidade, data_pedido, id_cliente, grupo_pedido_id)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        foreach ($_SESSION['carrinho'] as $idProduto => $quantidade) {
            // Valida se idProduto e quantidade são numéricos antes de inserir
            if (is_numeric($idProduto) && is_numeric($quantidade) && $quantidade > 0) {
                 $stmt->execute([$idProduto, $quantidade, $dataPedido, $idCliente, $grupoPedidoId]);
            } else {
                // Logar erro ou pular item inválido?
                // Por segurança, vamos lançar uma exceção para cancelar a transação
                throw new Exception("Item inválido encontrado no carrinho: Produto ID {$idProduto}, Quantidade {$quantidade}");
            }
        }

        $pdo->commit();

        // Limpa o carrinho após sucesso
        unset($_SESSION['carrinho']);

        // Redireciona para o carrinho ou uma página de sucesso
        header("Location: carrinho.php?status=sucesso"); 
        exit;

    } catch (Exception $e) {
        // Desfaz a transação em caso de erro
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Erro ao finalizar pedido: " . $e->getMessage());
        // Redireciona de volta com mensagem de erro genérica
        header("Location: finalizar.php?error=processing");
        exit;
    }

} else {
     // Se não for POST ou cliente não selecionado, busca clientes para exibir o formulário
     try {
        $clientes = $pdo->query("SELECT id, nome FROM cliente ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
     } catch (PDOException $e) {
         $clientes = [];
         $erro_db_clientes = "Erro ao buscar clientes: " . $e->getMessage();
     }
}

Banco::desconectar(); // Desconecta após buscar clientes ou processar
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Pedido</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css"> <!-- Verifique o caminho -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 600px; background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-top: 50px; }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4"><i class="bi bi-check2-circle"></i> Finalizar Pedido</h2>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php 
            switch ($_GET['error']) {
                case 'invalid_client': echo "Cliente selecionado inválido."; break;
                case 'processing': echo "Ocorreu um erro ao processar seu pedido. Tente novamente."; break;
                default: echo "Ocorreu um erro desconhecido."; break;
            }
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($erro_db_clientes)): ?>
         <div class="alert alert-warning">Não foi possível carregar a lista de clientes.</div>
    <?php elseif (isset($clientes)): ?>
        <form method="POST" action="finalizar.php">
            <div class="mb-3">
                <label for="cliente" class="form-label"><strong>Selecione o Cliente:</strong></label>
                <select name="cliente" id="cliente" class="form-select" required>
                    <option value="">-- Selecione um Cliente --</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                 <a href="carrinho.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar ao Carrinho</a>
                 <button type="submit" class="btn btn-success"><i class="bi bi-bag-check-fill"></i> Confirmar e Finalizar Pedido</button>
            </div>
        </form>
    <?php endif; ?>
</div>
<script src="../assets/js/bootstrap.bundle.min.js"></script> <!-- Verifique o caminho -->
</body>
</html>

