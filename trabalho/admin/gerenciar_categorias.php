<?php
require 'banco.php'; // Inclui o arquivo de conexão com o banco de dados

// Processamento do formulário para adicionar nova categoria
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = null;
    $descricao = null;

    // Obtém os dados do formulário
    if (!empty($_POST['nome'])) {
        $nome = $_POST['nome'];
    }
    if (!empty($_POST['descricao'])) {
        $descricao = $_POST['descricao'];
    }

    // Validação dos dados do formulário
    $validacao = true;
    if (empty($nome)) {
        $nomeErro = 'Por favor digite o nome da categoria!';
        $validacao = false;
    }

    // Se os dados forem válidos, insere no banco de dados
    if ($validacao) {
        $pdo = Banco::conectar(); // Conecta ao banco de dados
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Configura o modo de erro
        $sql = "INSERT INTO categorias (nome, descricao) VALUES(?, ?)"; // Query SQL para inserção
        $q = $pdo->prepare($sql); // Prepara a query
        try {
            $q->execute(array($nome, $descricao)); // Executa a query com os parâmetros
            Banco::desconectar(); // Fecha a conexão
            header("Location: gerenciar_categorias.php?status=add_success"); // Redireciona com status de sucesso
        } catch (PDOException $e) {
            // Tratamento de erros (como nome duplicado)
            Banco::desconectar();
            header("Location: gerenciar_categorias.php?status=add_error&msg=" . urlencode($e->getMessage()));
        }
    }
}

// As ações de deletar e editar são implementadas em arquivos separados:
// - delete_categoria.php: para excluir uma categoria
// - update_categoria.php: para editar uma categoria existente
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <!-- Carrega os estilos do Bootstrap -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <title>Gerenciar Categorias</title>
</head>

<body>
    <div class="container">
        <!-- Cabeçalho da página -->
        <div class="jumbotron">
            <div class="row">
                <h2>Gerenciamento de Categorias</h2>
            </div>
        </div>
        </br>
        <?php
        // Exibe mensagens de feedback baseadas no parâmetro status da URL
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'add_success') {
                echo '<div class="alert alert-success" role="alert">Categoria adicionada com sucesso!</div>';
            } elseif ($_GET['status'] == 'add_error') {
                echo '<div class="alert alert-danger" role="alert">Erro ao adicionar categoria: ' . htmlspecialchars($_GET['msg']) . '</div>';
            } elseif ($_GET['status'] == 'delete_success') {
                echo '<div class="alert alert-success" role="alert">Categoria excluída com sucesso!</div>';
            } elseif ($_GET['status'] == 'delete_error') {
                echo '<div class="alert alert-danger" role="alert">Erro ao excluir categoria.</div>';
            } elseif ($_GET['status'] == 'update_success') {
                echo '<div class="alert alert-success" role="alert">Categoria atualizada com sucesso!</div>';
            } elseif ($_GET['status'] == 'update_error') {
                echo '<div class="alert alert-danger" role="alert">Erro ao atualizar categoria.</div>';
            }
        }
        ?>
        <!-- Formulário para adicionar nova categoria -->
        <div class="row">
            <h4>Nova Categoria</h4>
            <form class="form-horizontal" action="gerenciar_categorias.php" method="post">
                <!-- Campo para o nome da categoria -->
                <div class="control-group <?php echo !empty($nomeErro) ? 'error' : ''; ?>">
                    <label class="control-label"></label>
                    <div class="controls">
                        <input name="nome" class="form-control" type="text" placeholder="Nome da Categoria" value="<?php echo !empty($nome) ? $nome : ''; ?>">
                        <?php if (!empty($nomeErro)): ?>
                            <span class="help-inline"><?php echo $nomeErro; ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Campo para a descrição da categoria (opcional) -->
                <div class="control-group">
                    <label class="control-label"></label>
                    <div class="controls">
                        <textarea name="descricao" class="form-control" placeholder="Descrição (opcional)"><?php echo !empty($descricao) ? $descricao : ''; ?></textarea>
                    </div>
                </div>

                <!-- Botão de submissão do formulário -->
                <div class="form-actions">
                    <br/>
                    <button type="submit" class="btn btn-success">Adicionar</button>
                </div>
            </form>
        </div>
        <hr>
        <!-- Tabela de categorias existentes -->
        <div class="row">
            <h4>Categorias Existentes</h4>
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Busca todas as categorias do banco de dados
                    $pdo = Banco::conectar();
                    $sql = 'SELECT * FROM categorias ORDER BY nome ASC';
                    foreach($pdo->query($sql)as $row) {
                        echo '<tr>';
                        echo '<td>'. $row['id'] . '</td>';
                        echo '<td>'. htmlspecialchars($row['nome']) . '</td>';
                        echo '<td>'. htmlspecialchars($row['descricao']) . '</td>';
                        echo '<td width=250>';
                        // Links para editar e excluir cada categoria
                        echo '<a class="btn btn-warning btn-sm" href="update_categoria.php?id='.$row['id'].'">Editar</a>';
                        echo ' ';
                        echo '<a class="btn btn-danger btn-sm" href="delete_categoria.php?id='.$row['id'].'">Excluir</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    Banco::desconectar(); // Fecha a conexão com o banco
                    ?>
                </tbody>
            </table>
        </div>
    </div> <!-- /container -->
    <!-- Carrega os scripts JavaScript necessários -->
    <script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <!-- JavaScript do Bootstrap -->
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
