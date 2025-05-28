<?php
require 'banco.php';

$id = null;
if (!empty($_GET['id'])) {
    $id = $_REQUEST['id'];
}

if (null == $id) {
    header("Location: gerenciar_categorias.php?status=invalid_id");
    exit();
}

// Variáveis de erro e dados
$nomeErro = null;
$descricaoErro = null;
$nome = '';
$descricao = '';

if (!empty($_POST)) {
    // Manter dados do POST
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];

    // Validação
    $validacao = true;
    if (empty($nome)) {
        $nomeErro = 'Por favor digite o nome da categoria!';
        $validacao = false;
    }

    // Atualizar no banco
    if ($validacao) {
        $pdo = Banco::conectar();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "UPDATE categorias SET nome = ?, descricao = ? WHERE id = ?";
        $q = $pdo->prepare($sql);
        try {
            $q->execute(array($nome, $descricao, $id));
            Banco::desconectar();
            header("Location: gerenciar_categorias.php?status=update_success");
            exit();
        } catch (PDOException $e) {
            Banco::desconectar();
            // Tratar erro (ex: nome duplicado)
            header("Location: update_categoria.php?id=$id&status=update_error&msg=" . urlencode($e->getMessage()));
            exit();
        }
    }
} else {
    // Carregar dados da categoria para exibir no formulário
    $pdo = Banco::conectar();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT nome, descricao FROM categorias WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($id));
    $data = $q->fetch(PDO::FETCH_ASSOC);
    if ($data) {
        $nome = $data['nome'];
        $descricao = $data['descricao'];
    } else {
        Banco::desconectar();
        header("Location: gerenciar_categorias.php?status=notfound");
        exit();
    }
    Banco::desconectar();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <title>Atualizar Categoria</title>
</head>
<body>
    <div class="container">
        <div class="span10 offset1">
            <div class="card">
                <div class="card-header">
                    <h3 class="well">Atualizar Categoria</h3>
                </div>
                <div class="card-body">
                    <?php
                    if (isset($_GET['status']) && $_GET['status'] == 'update_error') {
                        echo '<div class="alert alert-danger">Erro ao atualizar categoria: ' . htmlspecialchars($_GET['msg'] ?? 'Erro desconhecido') . '</div>';
                    }
                    ?>
                    <form class="form-horizontal" action="update_categoria.php?id=<?php echo $id; ?>" method="post">
                        <div class="control-group <?php echo !empty($nomeErro) ? 'error' : ''; ?>">
                            <label class="control-label">Nome</label>
                            <div class="controls">
                                <input name="nome" class="form-control" type="text" placeholder="Nome da Categoria" value="<?php echo htmlspecialchars($nome); ?>">
                                <?php if (!empty($nomeErro)): ?>
                                    <span class="text-danger"><?php echo $nomeErro; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">Descrição</label>
                            <div class="controls">
                                <textarea name="descricao" class="form-control" placeholder="Descrição (opcional)"><?php echo htmlspecialchars($descricao); ?></textarea>
                            </div>
                        </div>
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
    <script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
