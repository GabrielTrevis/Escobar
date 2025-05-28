<?php
require 'banco.php';

// Buscar categorias para o dropdown antes de qualquer POST
$pdo_cat = Banco::conectar();
$sql_cat = "SELECT id, nome FROM categorias ORDER BY nome ASC";
$categorias = $pdo_cat->query($sql_cat);
Banco::desconectar(); // Desconectar após buscar categorias

$nomeErro = null;
$descricaoErro = null;
$valorErro = null;
$categoriaErro = null; // Erro para categoria
$imagemErro = null;

$nome = '';
$descricao = '';
$valor = '';
$categoria_id = ''; // ID da categoria selecionada
$imagemNomeArquivo = null; // *** Alterado: Guarda apenas o nome do arquivo ***

if (!empty($_POST)) {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $categoria_id = $_POST['categoria_id']; // Captura o ID da categoria

    // Validação dos campos
    $validacao = true;

    if (empty($nome)) {
        $nomeErro = 'Por favor digite o nome!';
        $validacao = false;
    }

    if (empty($descricao)) {
        $descricaoErro = 'Por favor digite a descrição!';
        $validacao = false;
    }

    if (empty($valor)) {
        $valorErro = 'Por favor digite o valor!';
        $validacao = false;
    }

    if (empty($categoria_id)) { // Validação da categoria
        $categoriaErro = 'Por favor selecione a categoria!';
        $validacao = false;
    }

    // Verifica se enviou imagem
    if (!empty($_FILES['imagem']['name'])) {
        $uploadDir = 'uploads/';
        $nomeOriginal = $_FILES['imagem']['name'];
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        $nomeImagem = uniqid('prod_') . "." . $extensao; // *** Nome único para o arquivo ***
        $caminhoCompleto = $uploadDir . $nomeImagem;
        $permitidas = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($extensao, $permitidas)) {
            $imagemErro = 'Formato de imagem inválido (permitido: jpg, jpeg, png, gif)';
            $validacao = false;
        } elseif (!move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoCompleto)) {
            $imagemErro = "Erro ao fazer upload da imagem!";
            $validacao = false;
        } else {
            $imagemNomeArquivo = $nomeImagem; // *** Salva APENAS o nome do arquivo ***
        }
    } else {
        // Definir se imagem é obrigatória ou não
        // $imagemErro = 'Por favor selecione uma imagem!';
        // $validacao = false;
    }

    // Inserir no banco
    if ($validacao) {
        $pdo = Banco::conectar();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Atualiza SQL para incluir categoria_id e imagem (apenas nome)
        $sql = "INSERT INTO produto (nome, descricao, valor, imagem, categoria_id) VALUES (?, ?, ?, ?, ?)";
        $q = $pdo->prepare($sql);
        try {
            // Adiciona imagemNomeArquivo ao array de execução
            $q->execute([$nome, $descricao, $valor, $imagemNomeArquivo, $categoria_id]);
            Banco::desconectar();
            header("Location: index.php?status=produto_add_success"); // Redireciona com status
            exit(); // Adiciona exit após header
        } catch (PDOException $e) {
            Banco::desconectar();
            error_log("Erro DB ao cadastrar produto: " . $e->getMessage()); // Log do erro
            header("Location: cadastrarproduto.php?status=dberror");
            exit(); // Adiciona exit após header
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <title>Adicionar Produto</title>
</head>

<body>
    <div class="container">
        <div clas="span10 offset1">
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="well">Adicionar Produto</h3>
                </div>
                <div class="card-body">
                    <?php 
                    if (isset($_GET['status']) && $_GET['status'] == 'dberror') {
                        echo '<div class="alert alert-danger">Ocorreu um erro ao salvar no banco de dados. Verifique se os dados estão corretos e tente novamente.</div>';
                    }
                    ?>
                    <form class="form-horizontal" action="cadastrarproduto.php" method="post" enctype="multipart/form-data">

                        <!-- Nome -->
                        <div class="mb-3 <?php echo !empty($nomeErro) ? 'has-error' : ''; ?>">
                            <label for="nome" class="form-label">Nome</label>
                            <input id="nome" class="form-control" name="nome" type="text" placeholder="Nome" value="<?php echo htmlspecialchars($nome); ?>">
                            <?php if (!empty($nomeErro)) : ?>
                                <span class="text-danger"><?php echo $nomeErro; ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Descrição -->
                        <div class="mb-3 <?php echo !empty($descricaoErro) ? 'has-error' : ''; ?>">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea id="descricao" rows="3" class="form-control" name="descricao" placeholder="Descrição"><?php echo htmlspecialchars($descricao); ?></textarea>
                            <?php if (!empty($descricaoErro)) : ?>
                                <span class="text-danger"><?php echo $descricaoErro; ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Valor -->
                        <div class="mb-3 <?php echo !empty($valorErro) ? 'has-error' : ''; ?>">
                            <label for="valor" class="form-label">Valor</label>
                            <input id="valor" type="number" step="0.01" class="form-control" name="valor" placeholder="Ex: 20.00" value="<?php echo htmlspecialchars($valor); ?>">
                            <?php if (!empty($valorErro)) : ?>
                                <span class="text-danger"><?php echo $valorErro; ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Categoria -->
                        <div class="mb-3 <?php echo !empty($categoriaErro) ? 'has-error' : ''; ?>">
                            <label for="categoria_id" class="form-label">Categoria</label>
                            <select id="categoria_id" name="categoria_id" class="form-select">
                                <option value="">Selecione uma Categoria</option>
                                <?php
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

                        <!-- Imagem -->
                        <div class="mb-3 <?php echo !empty($imagemErro) ? 'has-error' : ''; ?>">
                            <label for="imagem" class="form-label">Imagem do Produto</label>
                            <input id="imagem" type="file" class="form-control" name="imagem" accept="image/png, image/jpeg, image/gif">
                            <?php if (!empty($imagemErro)) : ?>
                                <span class="text-danger"><?php echo $imagemErro; ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Botões -->
                        <div class="form-actions mt-4">
                            <button type="submit" class="btn btn-success">Adicionar</button>
                            <a href="index.php" class="btn btn-secondary">Voltar</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
