<?php
require 'banco.php'; // Inclui o arquivo de conexão com o banco de dados

// Busca todas as categorias para o dropdown de seleção antes de processar qualquer POST
$pdo_cat = Banco::conectar(); // Conecta ao banco de dados
$sql_cat = "SELECT id, nome FROM categorias ORDER BY nome ASC"; // Query para buscar categorias ordenadas por nome
$categorias = $pdo_cat->query($sql_cat); // Executa a query
Banco::desconectar(); // Desconecta após buscar as categorias

// Inicializa variáveis para armazenar mensagens de erro
$nomeErro = null;
$descricaoErro = null;
$valorErro = null;
$categoriaErro = null; // Erro para validação da categoria
$imagemErro = null;

// Inicializa variáveis para armazenar os dados do formulário
$nome = '';
$descricao = '';
$valor = '';
$categoria_id = ''; // ID da categoria selecionada
$imagemNomeArquivo = null; // Armazena apenas o nome do arquivo de imagem, não o caminho completo

// Verifica se o formulário foi enviado (método POST)
if (!empty($_POST)) {
    // Captura os dados enviados pelo formulário
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $categoria_id = $_POST['categoria_id']; // Captura o ID da categoria selecionada

    // Validação dos campos do formulário
    $validacao = true;

    // Valida o nome do produto
    if (empty($nome)) {
        $nomeErro = 'Por favor digite o nome!';
        $validacao = false;
    }

    // Valida a descrição do produto
    if (empty($descricao)) {
        $descricaoErro = 'Por favor digite a descrição!';
        $validacao = false;
    }

    // Valida o valor do produto
    if (empty($valor)) {
        $valorErro = 'Por favor digite o valor!';
        $validacao = false;
    }

    // Valida a categoria selecionada
    if (empty($categoria_id)) {
        $categoriaErro = 'Por favor selecione a categoria!';
        $validacao = false;
    }

    // Verifica se foi enviada uma imagem
    if (!empty($_FILES['imagem']['name'])) {
        $uploadDir = 'uploads/'; // Diretório onde as imagens serão salvas
        $nomeOriginal = $_FILES['imagem']['name']; // Nome original do arquivo
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION)); // Obtém a extensão do arquivo
        $nomeImagem = uniqid('prod_') . "." . $extensao; // Gera um nome único para o arquivo
        $caminhoCompleto = $uploadDir . $nomeImagem; // Caminho completo onde o arquivo será salvo
        $permitidas = ['jpg', 'jpeg', 'png', 'gif']; // Extensões de arquivo permitidas

        // Valida a extensão do arquivo
        if (!in_array($extensao, $permitidas)) {
            $imagemErro = 'Formato de imagem inválido (permitido: jpg, jpeg, png, gif)';
            $validacao = false;
        } 
        // Tenta fazer o upload do arquivo
        elseif (!move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoCompleto)) {
            $imagemErro = "Erro ao fazer upload da imagem!";
            $validacao = false;
        } 
        else {
            // Se o upload for bem-sucedido, armazena apenas o nome do arquivo (sem o caminho)
            $imagemNomeArquivo = $nomeImagem;
        }
    } 
    // Se nenhuma imagem foi enviada, pode-se definir se é obrigatória ou não
    // Neste caso, a imagem não é obrigatória
    else {
        // $imagemErro = 'Por favor selecione uma imagem!';
        // $validacao = false;
    }

    // Se todos os dados forem válidos, insere no banco de dados
    if ($validacao) {
        $pdo = Banco::conectar(); // Conecta ao banco de dados
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Configura o modo de erro
        // Query SQL para inserção, incluindo categoria_id e imagem (apenas nome do arquivo)
        $sql = "INSERT INTO produto (nome, descricao, valor, imagem, categoria_id) VALUES (?, ?, ?, ?, ?)";
        $q = $pdo->prepare($sql); // Prepara a query
        try {
            // Executa a query com os parâmetros
            $q->execute([$nome, $descricao, $valor, $imagemNomeArquivo, $categoria_id]);
            Banco::desconectar(); // Fecha a conexão
            header("Location: index.php?status=produto_add_success"); // Redireciona com status de sucesso
            exit(); // Encerra a execução do script após o redirecionamento
        } catch (PDOException $e) {
            Banco::desconectar();
            error_log("Erro DB ao cadastrar produto: " . $e->getMessage()); // Registra o erro no log
            header("Location: cadastrarproduto.php?status=dberror"); // Redireciona com status de erro
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <!-- Carrega os estilos do Bootstrap -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <title>Adicionar Produto</title>
</head>

<body>
    <div class="container">
        <div clas="span10 offset1">
            <div class="card mt-4">
                <!-- Cabeçalho do card -->
                <div class="card-header">
                    <h3 class="well">Adicionar Produto</h3>
                </div>
                <!-- Corpo do card com o formulário -->
                <div class="card-body">
                    <?php 
                    // Exibe mensagem de erro se houver problema no banco de dados
                    if (isset($_GET['status']) && $_GET['status'] == 'dberror') {
                        echo '<div class="alert alert-danger">Ocorreu um erro ao salvar no banco de dados. Verifique se os dados estão corretos e tente novamente.</div>';
                    }
                    ?>
                    <!-- Formulário de cadastro de produto com suporte a upload de arquivos -->
                    <form class="form-horizontal" action="cadastrarproduto.php" method="post" enctype="multipart/form-data">

                        <!-- Campo para o nome do produto -->
                        <div class="mb-3 <?php echo !empty($nomeErro) ? 'has-error' : ''; ?>">
                            <label for="nome" class="form-label">Nome</label>
                            <input id="nome" class="form-control" name="nome" type="text" placeholder="Nome" value="<?php echo htmlspecialchars($nome); ?>">
                            <?php if (!empty($nomeErro)) : ?>
                                <span class="text-danger"><?php echo $nomeErro; ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Campo para a descrição do produto -->
                        <div class="mb-3 <?php echo !empty($descricaoErro) ? 'has-error' : ''; ?>">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea id="descricao" rows="3" class="form-control" name="descricao" placeholder="Descrição"><?php echo htmlspecialchars($descricao); ?></textarea>
                            <?php if (!empty($descricaoErro)) : ?>
                                <span class="text-danger"><?php echo $descricaoErro; ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Campo para o valor do produto -->
                        <div class="mb-3 <?php echo !empty($valorErro) ? 'has-error' : ''; ?>">
                            <label for="valor" class="form-label">Valor</label>
                            <input id="valor" type="number" step="0.01" class="form-control" name="valor" placeholder="Ex: 20.00" value="<?php echo htmlspecialchars($valor); ?>">
                            <?php if (!empty($valorErro)) : ?>
                                <span class="text-danger"><?php echo $valorErro; ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Dropdown para seleção da categoria -->
                        <div class="mb-3 <?php echo !empty($categoriaErro) ? 'has-error' : ''; ?>">
                            <label for="categoria_id" class="form-label">Categoria</label>
                            <select id="categoria_id" name="categoria_id" class="form-select">
                                <option value="">Selecione uma Categoria</option>
                                <?php
                                // Gera as opções do dropdown com as categorias do banco
                                if ($categorias) {
                                    foreach ($categorias as $cat) {
                                        $selected = (!empty($categoria_id) && $categoria_id == $cat['id']) ? ' selected' : '';
                                        echo '<option value="' . $cat['id'] . '"' . $selected . '>' . htmlspecialchars($cat['nome']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <?php if (!empty($categoriaErro)) : ?>
                                <span class="text-danger"><?php echo $categoriaErro; ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Campo para upload da imagem do produto -->
                        <div class="mb-3 <?php echo !empty($imagemErro) ? 'has-error' : ''; ?>">
                            <label for="imagem" class="form-label">Imagem do Produto</label>
                            <input id="imagem" type="file" class="form-control" name="imagem" accept="image/png, image/jpeg, image/gif">
                            <?php if (!empty($imagemErro)) : ?>
                                <span class="text-danger"><?php echo $imagemErro; ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Botões de ação -->
                        <div class="form-actions mt-4">
                            <button type="submit" class="btn btn-success">Adicionar</button>
                            <a href="index.php" class="btn btn-secondary">Voltar</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Carrega o JavaScript do Bootstrap -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
