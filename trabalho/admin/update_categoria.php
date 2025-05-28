<?php
require 'banco.php'; // Inclui o arquivo de conexão com o banco de dados

$id = null; // Inicializa a variável de ID
// Verifica se foi fornecido um ID na URL
if (!empty($_GET['id'])) {
    $id = $_REQUEST['id'];
}

// Se não houver ID, redireciona para a página de gerenciamento com mensagem de erro
if (null == $id) {
    header("Location: gerenciar_categorias.php?status=invalid_id");
    exit();
}

// Inicializa variáveis para armazenar erros e dados do formulário
$nomeErro = null;
$descricaoErro = null;
$nome = '';
$descricao = '';

// Verifica se o formulário foi enviado (método POST)
if (!empty($_POST)) {
    // Captura os dados enviados pelo formulário
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];

    // Validação dos dados
    $validacao = true;
    if (empty($nome)) {
        $nomeErro = 'Por favor digite o nome da categoria!';
        $validacao = false;
    }

    // Se os dados forem válidos, atualiza no banco de dados
    if ($validacao) {
        $pdo = Banco::conectar(); // Conecta ao banco de dados
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Configura o modo de erro
        $sql = "UPDATE categorias SET nome = ?, descricao = ? WHERE id = ?"; // Query SQL para atualização
        $q = $pdo->prepare($sql); // Prepara a query
        try {
            $q->execute(array($nome, $descricao, $id)); // Executa a query com os parâmetros
            Banco::desconectar(); // Fecha a conexão
            header("Location: gerenciar_categorias.php?status=update_success"); // Redireciona com status de sucesso
            exit();
        } catch (PDOException $e) {
            Banco::desconectar();
            // Redireciona com mensagem de erro em caso de falha
            header("Location: update_categoria.php?id=$id&status=update_error&msg=" . urlencode($e->getMessage()));
            exit();
        }
    }
} else {
    // Se não for POST, busca os dados da categoria no banco para exibir no formulário
    $pdo = Banco::conectar();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT nome, descricao FROM categorias WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($id));
    $data = $q->fetch(PDO::FETCH_ASSOC); // Obtém os dados da categoria
    
    // Verifica se a categoria existe
    if ($data) {
        $nome = $data['nome'];
        $descricao = $data['descricao'];
    } else {
        // Se a categoria não for encontrada, redireciona com mensagem de erro
        Banco::desconectar();
        header("Location: gerenciar_categorias.php?status=notfound");
        exit();
    }
    Banco::desconectar(); // Fecha a conexão com o banco
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <!-- Carrega os estilos do Bootstrap -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <title>Atualizar Categoria</title>
</head>
<body>
    <div class="container">
        <div class="span10 offset1">
            <div class="card">
                <!-- Cabeçalho do card -->
                <div class="card-header">
                    <h3 class="well">Atualizar Categoria</h3>
                </div>
                <!-- Corpo do card com o formulário -->
                <div class="card-body">
                    <?php
                    // Exibe mensagem de erro se houver
                    if (isset($_GET['status']) && $_GET['status'] == 'update_error') {
                        echo '<div class="alert alert-danger">Erro ao atualizar categoria: ' . htmlspecialchars($_GET['msg'] ?? 'Erro desconhecido') . '</div>';
                    }
                    ?>
                    <!-- Formulário de atualização da categoria -->
                    <form class="form-horizontal" action="update_categoria.php?id=<?php echo $id; ?>" method="post">
                        <!-- Campo para o nome da categoria -->
                        <div class="control-group <?php echo !empty($nomeErro) ? 'error' : ''; ?>">
                            <label class="control-label">Nome</label>
                            <div class="controls">
                                <input name="nome" class="form-control" type="text" placeholder="Nome da Categoria" value="<?php echo htmlspecialchars($nome); ?>">
                                <?php if (!empty($nomeErro)): ?>
                                    <span class="text-danger"><?php echo $nomeErro; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- Campo para a descrição da categoria -->
                        <div class="control-group">
                            <label class="control-label">Descrição</label>
                            <div class="controls">
                                <textarea name="descricao" class="form-control" placeholder="Descrição (opcional)"><?php echo htmlspecialchars($descricao); ?></textarea>
                            </div>
                        </div>
                        <!-- Botões de ação -->
                        <div class="form-actions">
                            <br/>
                            <button type="submit" class="btn btn-warning">Atualizar</button>
                            <a href="gerenciar_categorias.php" class="btn btn-default">Voltar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Carrega os scripts JavaScript necessários -->
    <script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <!-- JavaScript do Bootstrap -->
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
