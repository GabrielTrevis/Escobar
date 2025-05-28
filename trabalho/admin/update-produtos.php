<?php
require 'banco.php';

$id = null;
if (!empty($_GET['id'])) {
    $id = $_REQUEST['id'];
}

if (null == $id) {
    header("Location: listaproduto.php");
    exit();
}

// Buscar categorias para o dropdown
$pdo_cat = Banco::conectar();
$sql_cat = "SELECT id, nome FROM categorias ORDER BY nome ASC";
$categorias = $pdo_cat->query($sql_cat);
Banco::desconectar();

$uploadDir = 'uploads/';
$imagemAtualNome = null; // *** Guarda apenas o nome do arquivo atual ***
$categoriaAtualId = null;

// Variáveis de erro
$nomeErro = null;
$descricaoErro = null;
$valorErro = null;
$categoriaErro = null;
$imagemErro = null;

// Variáveis de dados
$nome = '';
$descricao = '';
$valor = '';
$categoria_id = '';

if (!empty($_POST)) {
    // Manter os dados enviados pelo POST
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $categoria_id = $_POST['categoria_id'];

    // Validação
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
    if (empty($categoria_id)) {
        $categoriaErro = 'Por favor selecione a categoria!';
        $validacao = false;
    }

    // Processamento da imagem
    $imagemParaSalvar = null;
    $pdo = Banco::conectar();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Busca apenas o nome da imagem atual
    $sqlImg = "SELECT imagem FROM produto WHERE id = ?";
    $qImg = $pdo->prepare($sqlImg);
    $qImg->execute(array($id));
    $dataImg = $qImg->fetch(PDO::FETCH_ASSOC);
    $imagemAtualNome = $dataImg['imagem']; // *** Nome do arquivo atual ***
    $imagemParaSalvar = $imagemAtualNome; // Assume imagem atual por padrão

    // Se uma nova imagem foi enviada
    if (!empty($_FILES['imagem']['name'])) {
        $nomeOriginal = $_FILES['imagem']['name'];
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        $novoNomeArquivo = uniqid('prod_') . '.' . $extensao; // *** Novo nome único ***
        $caminhoCompleto = $uploadDir . $novoNomeArquivo;
        $permitidas = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($extensao, $permitidas)) {
            $imagemErro = 'Formato de imagem inválido (permitido: jpg, jpeg, png, gif)';
            $validacao = false;
        } elseif (!move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoCompleto)) {
            $imagemErro = "Erro ao fazer upload da nova imagem!";
            $validacao = false;
        } else {
            $imagemParaSalvar = $novoNomeArquivo; // *** Salva APENAS o novo nome do arquivo ***
            // Opcional: deletar imagem antiga se existir e for diferente da nova
            if ($imagemAtualNome && $imagemAtualNome != $novoNomeArquivo && file_exists($uploadDir . $imagemAtualNome)) {
                unlink($uploadDir . $imagemAtualNome);
            }
        }
    } // Se não enviou imagem nova, $imagemParaSalvar continua sendo $imagemAtualNome

    // update data
    if ($validacao) {
        // *** Atualiza SQL para salvar apenas o nome da imagem ***
        $sql = "UPDATE produto SET nome = ?, descricao = ?, valor = ?, imagem = ?, categoria_id = ? WHERE id = ?";
        $q = $pdo->prepare($sql);
        try {
            $q->execute(array($nome, $descricao, $valor, $imagemParaSalvar, $categoria_id, $id));
            Banco::desconectar();
            header("Location: listaproduto.php?status=update_success");
            exit();
        } catch (PDOException $e) {
            Banco::desconectar();
            error_log("Erro DB ao atualizar produto: " . $e->getMessage());
            header("Location: update-produtos.php?id=$id&status=dberror");
            exit();
        }
    } else {
        // Se a validação falhou, busca os dados atuais novamente para exibir o formulário
        // (exceto a imagem, que já foi buscada)
        $sql = "SELECT nome, descricao, valor, categoria_id FROM produto WHERE id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id));
        $data = $q->fetch(PDO::FETCH_ASSOC);
        $categoriaAtualId = $data['categoria_id']; // Pega o ID atual para pre-selecionar
        // Mantém os dados do POST se a validação falhou, para o usuário corrigir
        Banco::desconectar();
    }
} else {
    // Carregar dados do produto para exibir no formulário pela primeira vez
    $pdo = Banco::conectar();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT * FROM produto WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($id));
    $data = $q->fetch(PDO::FETCH_ASSOC);
    if ($data) {
        $nome = $data['nome'];
        $descricao = $data['descricao'];
        $valor = $data['valor'];
        $imagemAtualNome = $data['imagem']; // *** Pega o nome do arquivo atual ***
        $categoria_id = $data['categoria_id'];
        $categoriaAtualId = $data['categoria_id'];
    } else {
        Banco::desconectar();
        header("Location: listaproduto.php?status=notfound");
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
    <title>Atualizar Produto</title>
</head>

<body>
    <div class="container">
        <div class="span10 offset1">
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="well">Atualizar Produto</h3>
                </div>
                <div class="card-body">
                    <?php
                    if (isset($_GET['status']) && $_GET['status'] == 'dberror') {
                        echo '<div class="alert alert-danger">Ocorreu um erro ao atualizar o produto. Verifique os dados e tente novamente.</div>';
                    }
                    ?>
                    <form class="form-horizontal" action="update-produtos.php?id=<?php echo $id ?>" method="post" enctype="multipart/form-data">

                        <!-- Nome -->
                        <div class="mb-3 <?php echo !empty($nomeErro) ? 'has-error' : ''; ?>">
                            <label for="nome" class="form-label">Nome</label>
                            <input id="nome" name="nome" class="form-control" type="text" placeholder="Nome" value="<?php echo htmlspecialchars($nome); ?>">
                            <?php if (!empty($nomeErro)) : ?>
                                <span class="text-danger"><?php echo $nomeErro; ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Descrição -->
                        <div class="mb-3 <?php echo !empty($descricaoErro) ? 'has-error' : ''; ?>">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea id="descricao" name="descricao" class="form-control" placeholder="Descrição"><?php echo htmlspecialchars($descricao); ?></textarea>
                            <?php if (!empty($descricaoErro)) : ?>
                                <span class="text-danger"><?php echo $descricaoErro; ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Valor -->
                        <div class="mb-3 <?php echo !empty($valorErro) ? 'has-error' : ''; ?>">
                            <label for="valor" class="form-label">Valor</label>
                            <input id="valor" name="valor" class="form-control" type="number" step="0.01" placeholder="Valor" value="<?php echo htmlspecialchars($valor); ?>">
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
                                        $selectedId = !empty($categoria_id) ? $categoria_id : $categoriaAtualId;
                                        $selected = ($selectedId == $cat['id']) ? ' selected' : '';
                                        echo '<option value="' . $cat['id'] . '"' . $selected . '>' . htmlspecialchars($cat['nome']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <?php if (!empty($categoriaErro)) : ?>
                                <span class="text-danger"><?php echo $categoriaErro; ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Imagem atual -->
                        <div class="mb-3">
                            <label class="form-label">Imagem atual</label>
                            <div>
                                <?php 
                                // *** Monta o caminho completo para exibir a imagem ***
                                $imgPath = (!empty($imagemAtualNome) && file_exists($uploadDir . $imagemAtualNome)) 
                                           ? $uploadDir . htmlspecialchars($imagemAtualNome) 
                                           : 'img/placeholder.png'; // Placeholder se não houver ou não encontrar
                                ?>
                                <img src="<?php echo $imgPath; ?>" alt="Imagem atual do produto" style="width: 150px; height: 150px; object-fit: cover; border-radius: 5px; border: 1px solid #dee2e6;">
                                <?php if (empty($imagemAtualNome)) : ?>
                                    <p class="text-muted mt-1"><small>Nenhuma imagem cadastrada.</small></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Input para nova imagem -->
                        <div class="mb-3 <?php echo !empty($imagemErro) ? 'has-error' : ''; ?>">
                            <label for="imagem" class="form-label">Alterar imagem (opcional)</label>
                            <input id="imagem" type="file" name="imagem" class="form-control" accept="image/png, image/jpeg, image/gif">
                            <?php if (!empty($imagemErro)) : ?>
                                <span class="text-danger"><?php echo $imagemErro; ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-actions mt-4">
                            <button type="submit" class="btn btn-warning">Atualizar</button>
                            <a href="listaproduto.php" class="btn btn-secondary">Voltar</a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
