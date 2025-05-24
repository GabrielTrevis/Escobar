<?php
require 'banco.php';

if (!empty($_POST)) {
    $nomeErro = null;
    $descricaoErro = null;
    $valorErro = null;

    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $imagem = null;

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

    // Verifica se enviou imagem
    if (!empty($_FILES['imagem']['name'])) {
        $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $nomeImagem = uniqid() . "." . $extensao;
        $caminhoImagem = 'uploads/' . $nomeImagem;

        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoImagem)) {
            echo "Erro ao fazer upload da imagem!";
            $validacao = false;
        } else {
            $imagem = $caminhoImagem;
        }
    }

    // Inserir no banco
    if ($validacao) {
        $pdo = Banco::conectar();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "INSERT INTO produto (nome, descricao, valor, imagem) VALUES (?, ?, ?, ?)";
        $q = $pdo->prepare($sql);
        $q->execute([$nome, $descricao, $valor, $imagem]);
        Banco::desconectar();
        header("Location: index.php");
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
            <div class="card">
                <div class="card-header">
                    <h3 class="well">Adicionar Produto</h3>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="cadastrarproduto.php" method="post" enctype="multipart/form-data">

                        <!-- Nome -->
                        <div class="control-group <?php echo !empty($nomeErro) ? 'error ' : ''; ?>">
                            <label class="control-label">Nome</label>
                            <div class="controls">
                                <input size="50" class="form-control" name="nome" type="text" placeholder="Nome" required value="<?php echo !empty($nome) ? $nome : ''; ?>">
                                <?php if (!empty($nomeErro)) : ?>
                                    <span class="help-inline"><?php echo $nomeErro; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Descrição -->
                        <div class="control-group <?php echo !empty($descricaoErro) ? 'error ' : ''; ?>">
                            <label class="control-label">Descrição</label>
                            <div class="controls">
                                <input size="80" class="form-control" name="descricao" type="text" placeholder="Descrição" required value="<?php echo !empty($descricao) ? $descricao : ''; ?>">
                                <?php if (!empty($descricaoErro)) : ?>
                                    <span class="help-inline"><?php echo $descricaoErro; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Valor -->
                        <div class="control-group <?php echo !empty($valorErro) ? 'error ' : ''; ?>">
                            <label class="control-label">Valor</label>
                            <div class="controls">
                                <input type="number" step="0.01" class="form-control" name="valor" placeholder="R$ 20.00" required value="<?php echo !empty($valor) ? $valor : ''; ?>">
                                <?php if (!empty($valorErro)) : ?>
                                    <span class="help-inline"><?php echo $valorErro; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Imagem -->
                        <div class="control-group">
                            <label class="control-label">Imagem do Produto</label>
                            <div class="controls">
                                <input type="file" class="form-control" name="imagem" accept="image/*">
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="form-actions"><br>
                            <button type="submit" class="btn btn-success">Adicionar</button>
                            <a href="index.php" class="btn btn-default">Voltar</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.min.js"></script>
</body>

</html>