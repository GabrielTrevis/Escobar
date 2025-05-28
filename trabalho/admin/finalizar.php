<?php
session_start(); // Inicia a sessão para gerenciar o carrinho e o processo de finalização
include 'banco.php'; // Inclui o arquivo de conexão com o banco de dados

$pdo = Banco::conectar(); // Estabelece a conexão com o banco
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Configura o PDO para lançar exceções em caso de erros

$showSuccessMessage = false; // Controla a exibição da mensagem de sucesso

// Processa o envio do formulário quando o método é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cliente'])) {
    // Verifica se o carrinho ainda tem itens antes de processar o pedido
    // Isso evita finalizar um pedido vazio se o usuário abrir duas abas
    if (!isset($_SESSION['carrinho']) || count($_SESSION['carrinho']) === 0) {
        Banco::desconectar();
        header("Location: carrinho.php?status=empty_on_submit"); 
        exit;
    }

    // Obtém e valida o ID do cliente selecionado
    $idCliente = filter_input(INPUT_POST, 'cliente', FILTER_VALIDATE_INT);
    
    // Verifica se o ID do cliente é válido
    if (!$idCliente) {
        Banco::desconectar();
        header("Location: finalizar.php?error=invalid_client");
        exit;
    }

    // Verifica se o cliente existe no banco de dados
    $sqlCheckClient = "SELECT id FROM cliente WHERE id = ?";
    $stmtCheckClient = $pdo->prepare($sqlCheckClient);
    $stmtCheckClient->execute([$idCliente]);
    if ($stmtCheckClient->rowCount() == 0) {
        Banco::desconectar();
        header("Location: finalizar.php?error=client_not_found");
        exit;
    }

    // Prepara os dados para inserção do pedido
    $dataPedido = date('Y-m-d H:i:s'); // Data e hora atual
    $grupoPedidoId = uniqid('pedido_', true) . '_' . time(); // Gera um ID único para agrupar os itens do pedido

    try {
        // Inicia uma transação para garantir que todos os itens sejam salvos ou nenhum
        $pdo->beginTransaction();

        // Prepara a query para inserir cada item do pedido
        $sql = "INSERT INTO pedidos (id_produto, quantidade, data_pedido, id_cliente, grupo_pedido_id)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        // Insere cada item do carrinho como um registro na tabela pedidos
        foreach ($_SESSION['carrinho'] as $idProduto => $item) {
            // Determina a quantidade do item (compatível com diferentes formatos de carrinho)
            $quantidade = isset($item['quantidade']) ? $item['quantidade'] : (is_numeric($item) ? $item : 0); 
            
            // Valida os dados antes de inserir
            if (is_numeric($idProduto) && is_numeric($quantidade) && $quantidade > 0) {
                 $stmt->execute([$idProduto, $quantidade, $dataPedido, $idCliente, $grupoPedidoId]);
            } else {
                // Se encontrar dados inválidos, lança uma exceção
                throw new Exception("Item inválido encontrado no carrinho: Produto ID {$idProduto}, Quantidade {$quantidade}");
            }
        }

        // Confirma a transação se tudo ocorreu bem
        $pdo->commit();
        unset($_SESSION['carrinho']); // Limpa o carrinho após finalizar o pedido
        Banco::desconectar();

        // Redireciona para a página de sucesso
        header("Location: finalizar.php?status=success"); 
        exit;

    } catch (Exception $e) {
        // Em caso de erro, desfaz a transação
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // Registra o erro no log do servidor
        error_log("Erro ao finalizar pedido: " . $e->getMessage());
        Banco::desconectar();
        // Redireciona com mensagem de erro
        header("Location: finalizar.php?error=processing&msg=" . urlencode($e->getMessage()));
        exit;
    }

} else {
    // Se não for um POST (acesso normal à página)
    
    // Verifica se é para mostrar a mensagem de sucesso (veio do redirect após finalizar)
    if (isset($_GET['status']) && $_GET['status'] === 'success') {
        $showSuccessMessage = true;
    } else {
        // Se não for POST e não for sucesso, verifica se carrinho está vazio
        if (!isset($_SESSION['carrinho']) || count($_SESSION['carrinho']) === 0) {
            Banco::desconectar();
            header("Location: carrinho.php?status=empty"); 
            exit;
        }
        
        // Busca a lista de clientes para exibir no formulário de seleção
        try {
            $clientes = $pdo->query("SELECT id, nome FROM cliente ORDER BY nome ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $clientes = [];
            $erro_db_clientes = "Erro ao buscar clientes: " . $e->getMessage();
        }
    }
    Banco::desconectar(); // Desconecta após buscar clientes ou verificar sucesso
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Pedido</title>
    <!-- Carrega os estilos do Bootstrap e ícones -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* Estilização da página de finalização */
        body { background-color: #f8f9fa; }
        .container { max-width: 600px; background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-top: 50px; }
    </style>
    <?php if ($showSuccessMessage): ?>
        <!-- Meta refresh para redirecionar automaticamente após 5 segundos -->
        <meta http-equiv="refresh" content="5;url=index.php">
    <?php endif; ?>
</head>
<body>
<div class="container">
    
    <?php if ($showSuccessMessage): ?>
        <!-- Mensagem de Sucesso após finalizar o pedido -->
        <div class="text-center">
            <h2 class="text-success mb-4"><i class="bi bi-check-circle-fill"></i> Pedido Finalizado com Sucesso!</h2>
            <p>Obrigado pela sua compra!</p>
            <p>Você será redirecionado para a página inicial em 5 segundos.</p>
            <p><a href="index.php" class="btn btn-primary">Voltar para a Página Inicial Agora</a></p>
            <p><a href="admin_pedidos.php" class="btn btn-secondary btn-sm">Ver Meus Pedidos</a></p> 
        </div>
    <?php else: ?>
        <!-- Formulário de Finalização do Pedido -->
        <h2 class="text-center mb-4"><i class="bi bi-check2-circle"></i> Finalizar Pedido</h2>

        <?php if (isset($_GET['error'])): ?>
            <!-- Exibe mensagens de erro específicas -->
            <div class="alert alert-danger">
                <?php 
                switch ($_GET['error']) {
                    case 'invalid_client': echo "ID do cliente selecionado é inválido."; break;
                    case 'client_not_found': echo "Cliente selecionado não encontrado no banco de dados."; break;
                    case 'processing': 
                        echo "Ocorreu um erro ao processar seu pedido. Tente novamente."; 
                        if (isset($_GET['msg'])) { echo "<br><small>Detalhe: " . htmlspecialchars($_GET['msg']) . "</small>"; }
                        break;
                    default: echo "Ocorreu um erro desconhecido."; break;
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($erro_db_clientes)): ?>
             <!-- Mensagem quando não foi possível carregar a lista de clientes -->
             <div class="alert alert-warning">Não foi possível carregar a lista de clientes. Detalhes: <?php echo htmlspecialchars($erro_db_clientes); ?></div>
        <?php elseif (isset($clientes)): ?>
            <?php if (empty($clientes)): ?>
                <!-- Mensagem quando não há clientes cadastrados -->
                <div class="alert alert-info">Não há clientes cadastrados. <a href="cadastrarcliente.php">Cadastre um cliente</a> antes de finalizar o pedido.</div>
            <?php else: ?>
                <!-- Formulário para selecionar o cliente e finalizar o pedido -->
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
        <?php endif; ?>
    <?php endif; // Fim do else para exibir formulário ou sucesso ?>
</div>
<!-- Carrega o JavaScript do Bootstrap -->
<script src="assets/js/bootstrap.bundle.min.js"></script> 
</body>
</html>
