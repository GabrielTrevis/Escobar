<?php
require 'banco.php';

$id = null;
if (!empty($_GET['id'])) {
    $id = $_REQUEST['id'];
}

if (null == $id) {
    header("Location: listaproduto.php");
}

$uploadDir = 'uploads/';
$imagemAtual = null;

if (!empty($_POST)) {
    $nomeErro = null;
    $descricaoErro = null;
    $valorErro = null;

    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];

    $imagem = $_FILES['imagem']['name'];
    $imagem_tmp = $_FILES['imagem']['tmp_name'];

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

    // update data
    if ($validacao) {
        $pdo = Banco::conectar();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Pega imagem atual do banco
        $sql = "SELECT imagem FROM produto WHERE id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id));
        $data = $q->fetch(PDO::FETCH_ASSOC);
        $imagemAtual = $data['imagem'];

        // Se uma nova imagem foi enviada, move e salva novo nome
        if (!empty($imagem)) {
            $ext = pathinfo($imagem, PATHINFO_EXTENSION);
            $novoNome = uniqid() . '.' . $ext;
            move_uploaded_file($imagem_tmp, $uploadDir . $novoNome);
            $imagemParaSalvar = $novoNome;
        } else {
            $imagemParaSalvar = $imagemAtual;
        }

        $sql = "UPDATE produto SET nome = ?, descricao = ?, valor = ?, imagem = ? WHERE id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($nome, $descricao, $valor, $imagemParaSalvar, $id));
        Banco::desconectar();
        header("Location: listaproduto.php");
    }
} else {
    $pdo = Banco::conectar();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT * FROM produto WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($id));
    $data = $q->fetch(PDO::FETCH_ASSOC);
    $nome = $data['nome'];
    $descricao = $data['descricao'];
    $valor = $data['valor'];
    $imagemAtual = $data['imagem'];
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
            <div class="card">
                <div class="card-header">
                    <h3 class="well">Atualizar Produto</h3>
                </div>
                <div class="card-body">

                    <form class="form-horizontal" action="update-produtos.php?id=<?php echo $id ?>" method="post" enctype="multipart/form-data">

                        <div class="control-group <?php echo !empty($nomeErro) ? 'error' : ''; ?>">
                            <label class="control-label">Nome</label>
                            <div class="controls">
                                <input name="nome" class="form-control" type="text" placeholder="Nome" value="<?php echo !empty($nome) ? $nome : ''; ?>">
                                <?php if (!empty($nomeErro)) : ?>
                                    <span class="help-inline"><?php echo $nomeErro; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="control-group <?php echo !empty($descricaoErro) ? 'error' : ''; ?>">
                            <label class="control-label">Descrição</label>
                            <div class="controls">
                                <input name="descricao" class="form-control" type="text" placeholder="Descrição" value="<?php echo !empty($descricao) ? $descricao : ''; ?>">
                                <?php if (!empty($descricaoErro)) : ?>
                                    <span class="help-inline"><?php echo $descricaoErro; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="control-group <?php echo !empty($valorErro) ? 'error' : ''; ?>">
                            <label class="control-label">Valor</label>
                            <div class="controls">
                                <input name="valor" class="form-control" type="text" placeholder="Valor" value="<?php echo !empty($valor) ? $valor : ''; ?>">
                                <?php if (!empty($valorErro)) : ?>
                                    <span class="help-inline"><?php echo $valorErro; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Imagem atual -->
                        <div class="control-group">
                            <label class="control-label">Imagem atual</label>
                            <div class="controls">
                                <?php if (!empty($imagemAtual)) : ?>
                                    <img src="uploads/<?php echo $imagemAtual; ?>" alt="Imagem do produto" style="width: 150px;">
                                <?php else : ?>
                                    <p>Nenhuma imagem cadastrada.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Input para nova imagem -->
                        <div class="control-group">
                            <label class="control-label">Alterar imagem</label>
                            <div class="controls">
                                <input type="file" name="imagem" class="form-control">
                            </div>
                        </div>

                        <br>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-warning">Atualizar</button>
                            <a href="listaproduto.php" class="btn btn-default">Voltar</a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.min.js"></script>
</body>

</html>
