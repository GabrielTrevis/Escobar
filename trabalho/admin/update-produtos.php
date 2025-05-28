<?php
require 'banco.php'; // Inclui o arquivo de conexão com o banco de dados

$id = null; // Inicializa a variável de ID
if (!empty($_GET['id'])) {
    $id = $_REQUEST['id']; // Obtém o ID do produto a ser atualizado da URL
}

// Verifica se foi fornecido um ID válido
if (null == $id) {
    header("Location: listaproduto.php"); // Redireciona se não houver ID
    exit();
}

// Busca todas as categorias para o dropdown de seleção
$pdo_cat = Banco::conectar();
$sql_cat = "SELECT id, nome FROM categorias ORDER BY nome ASC";
$categorias = $pdo_cat->query($sql_cat);
Banco::desconectar();

// Configurações para manipulação de imagens
$uploadDir = 'uploads/'; // Diretório onde as imagens são armazenadas
$imagemAtualNome = null; // Armazena apenas o nome do arquivo de imagem atual
$categoriaAtualId = null; // Armazena o ID da categoria atual do produto

// Inicializa variáveis para armazenar mensagens de erro
$nomeErro = null;
$descricaoErro = null;
$valorErro = null;
$categoriaErro = null;
$imagemErro = null;

// Inicializa variáveis para armazenar os dados do formulário
$nome = '';
$descricao = '';
$valor = '';
$categoria_id = '';

// Verifica se o formulário foi enviado (método POST)
if (!empty($_POST)) {
    // Captura os dados enviados pelo formulário
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $categoria_id = $_POST['categoria_id'];

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

    // Processamento da imagem
    $imagemParaSalvar = null;
    $pdo = Banco::conectar();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Busca o nome da imagem atual do produto no banco de dados
    $sqlImg = "SELECT imagem FROM produto WHERE id = ?";
    $qImg = $pdo->prepare($sqlImg);
    $qImg->execute(array($id));
    $dataImg = $qImg->fetch(PDO::FETCH_ASSOC);
    $imagemAtualNome = $dataImg['imagem']; // Nome do arquivo atual
    $imagemParaSalvar = $imagemAtualNome; // Assume a imagem atual por padrão (se não for enviada nova)

    // Verifica se uma nova imagem foi enviada
    if (!empty($_FILES['imagem']['name'])) {
        $nomeOriginal = $_FILES['imagem']['name'];
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));
        $novoNomeArquivo = uniqid('prod_') . '.' . $extensao; // Gera um nome único para o arquivo
        $caminhoCompleto = $uploadDir . $novoNomeArquivo;
        $permitidas = ['jpg', 'jpeg', 'png', 'gif']; // Extensões permitidas

        // Valida a extensão do arquivo
        if (!in_array($extensao, $permitidas)) {
            $imagemErro = 'Formato de imagem inválido (permitido: jpg, jpeg, png, gif)';
            $validacao = false;
        } 
        // Tenta fazer o upload do arquivo
        elseif (!move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoCompleto)) {
            $imagemErro = "Erro ao fazer upload da nova imagem!";
            $validacao = false;
        } 
        else {
            // Se o upload for bem-sucedido, armazena o novo nome do arquivo
            $imagemParaSalvar = $novoNomeArquivo;
            
            // Opcional: exclui a imagem antiga se existir e for diferente da nova
            if ($imagemAtualNome && $imagemAtualNome != $novoNomeArquivo && file_exists($uploadDir . $imagemAtualNome)) {
                unlink($uploadDir . $imagemAtualNome);
            }
        }
    } // Se não enviou imagem nova, $imagemParaSalvar continua sendo $imagemAtualNome

    // Atualiza os dados no banco se a validação for bem-sucedida
    if ($validacao) {
        // Query SQL para atualização, incluindo o nome da imagem
        $sql = "UPDATE produto SET nome = ?, descricao = ?, valor = ?, imagem = ?, categoria_id = ? WHERE id = ?";
        $q = $pdo->prepare($sql);
        try {
            // Executa a query com os parâmetros
            $q->execute(array($nome, $descricao, $valor, $imagemParaSalvar, $categoria_id, $id));
            Banco::desconectar();
            header("Location: listaproduto.php?status=update_success"); // Redireciona com status de sucesso
            exit();
        } catch (PDOException $e) {
            Banco::desconectar();
            error_log("Erro DB ao atualizar produto: " . $e->getMessage()); // Registra o erro no log
            header("Location: update-produtos.php?id=$id&status=dberror"); // Redireciona com status de erro
            exit();
        }
    } else {
        // Se a validação falhou, busca os dados atuais novamente para exibir o formulário
        // (exceto a imagem, que já foi buscada)
        $sql = "SELECT nome, descricao, valor, categoria_id FROM produto WHERE id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id));
        $data = $q->fetch(PDO::FETCH_ASSOC);
        $categoriaAtualId = $data['categoria_id']; // Pega o ID atual para pré-selecionar no dropdown
        // Mantém os dados do POST se a validação falhou, para o usuário corrigir
        Banco::desconectar();
    }
} else {
    // Se não for POST (primeira visita à página), carrega os dados do produto para exibir no formulário
    $pdo = Banco::conectar();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT * FROM produto WHERE id = ?";
    $q = $pdo->prepare($sql);
    $q->execute(array($id));
    $data = $q->fetch(PDO::FETCH_ASSOC);
    
    // Verifica se o produto existe
    if ($data) {
        // Preenche as variáveis com os dados do produto
        $nome = $data['nome'];
        $descricao = $data['descricao'];
        $valor = $data['valor'];
        $imagemAtualNome = $data['imagem']; // Nome do arquivo de imagem atual
        $categoria_id = $data['categoria_id'];
        $categoriaAtualId = $data['categoria_id'];
    } else {
        // Se o produto não for encontrado, redireciona com mensagem de erro
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
    <!-- Carrega os estilos do Bootstrap -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <title>Atualizar Produto</title>
</head>

<body>
    <div class="container">
        <div class="span10 offset1">
            <div class="card mt-4">
                <!-- Cabeçalho do card -->
                <div class="card-header">
                    <h3 class="well">Atualizar Produto</h3>
                </div>
                <!-- Corpo do card com o formulário -->
                <div class="card-body">
                    <?php
                    // Exibe mensagem de erro se houver problema no banco de dados
                    if (isset($_GET['status']) && $_GET['status'] == 'dberror') {
                        echo '<div class="alert alert-danger">Ocorreu um erro ao atualizar o produto. Verifique os dados e tente novamente.</div>';
                    }
                    ?>
                    <!-- Formulário de atualização do produto com suporte a upload de arquivos -->
                    <form class="form-horizontal" action="update-produtos.php?id=<?php echo $id ?>" method="post" enctype="multipart/form-data">

                        <!-- Campo para o nome do produto -->
                        <div class="mb-3 <?php echo !empty($nomeErro) ? 'has-error' : ''; ?>">
                            <label for="nome" class="form-label">Nome</label>
                            <input id="nome" name="nome" class="form-control" type="text" placeholder="Nome" value="<?php echo htmlspecialchars($nome); ?>">
                            <?php if (!empty($nomeErro)) : ?>
                                <span class="text-danger"><?php echo $nomeErro; ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Campo para a descrição do produto -->
                        <div class="mb-3 <?php echo !empty($descricaoErro) ? 'has-error' : ''; ?>">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea id="descricao" name="descricao" class="form-control" placeholder="Descrição"><?php echo htmlspecialchars($descricao); ?></textarea>
                            <?php if (!empty($descricaoErro)) : ?>
                                <span class="text-danger"><?php echo $descricaoErro; ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Campo para o valor do produto -->
                        <div class="mb-3 <?php echo !empty($valorErro) ? 'has-error' : ''; ?>">
                            <label for="valor" class="form-label">Valor</label>
                            <input id="valor" name="valor" class="form-control" type="number" step="0.01" placeholder="Valor" value="<?php echo htmlspecialchars($valor); ?>">
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
                                        // Determina qual ID usar para pré-selecionar (do POST ou do banco)
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

                        <!-- Exibe a imagem atual do produto -->
                        <div class="mb-3">
                            <label class="form-label">Imagem atual</label>
                            <div>
                                <?php 
                                // Monta o caminho completo para exibir a imagem, usando um placeholder se necessário
                                $imgPath = (!empty($imagemAtualNome) && file_exists($uploadDir . $imagemAtualNome)) 
                                           ? $uploadDir . htmlspecialchars($imagemAtualNome) 
                                           : 'img/placeholder.png';
                                ?>
                                <img src="<?php echo $imgPath; ?>" alt="Imagem atual do produto" style="width: 150px; height: 150px; object-fit: cover; border-radius: 5px; border: 1px solid #dee2e6;">
                                <?php if (empty($imagemAtualNome)) : ?>
                                    <p class="text-muted mt-1"><small>Nenhuma imagem cadastrada.</small></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Campo para upload de uma nova imagem (opcional) -->
                        <div class="mb-3 <?php echo !empty($imagemErro) ? 'has-error' : ''; ?>">
                            <label for="imagem" class="form-label">Alterar imagem (opcional)</label>
                            <input id="imagem" type="file" name="imagem" class="form-control" accept="image/png, image/jpeg, image/gif">
                            <?php if (!empty($imagemErro)) : ?>
                                <span class="text-danger"><?php echo $imagemErro; ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Botões de ação -->
                        <div class="form-actions mt-4">
                            <button type="submit" class="btn btn-warning">Atualizar</button>
                            <a href="listaproduto.php" class="btn btn-secondary">Voltar</a>
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
