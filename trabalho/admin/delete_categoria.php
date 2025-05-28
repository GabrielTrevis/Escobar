<?php
require 'banco.php'; // Inclui o arquivo de conexão com o banco de dados

$id = 0; // Inicializa a variável de ID

// Verifica se foi fornecido um ID na URL
if (!empty($_GET['id'])) {
    $id = $_REQUEST['id'];
}

// Observação: Em um projeto real, seria ideal adicionar uma etapa de confirmação
// Por exemplo, exibir um formulário perguntando se o usuário realmente deseja excluir
// e só então processar a exclusão quando o formulário for enviado

if (!empty($id)) {
    // Processo de exclusão da categoria
    $pdo = Banco::conectar(); // Conecta ao banco de dados
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Configura o modo de erro
    $sql = "DELETE FROM categorias WHERE id = ?"; // Query SQL para exclusão
    $q = $pdo->prepare($sql); // Prepara a query
    try {
        $q->execute(array($id)); // Executa a query com o ID como parâmetro
        Banco::desconectar(); // Fecha a conexão
        header("Location: gerenciar_categorias.php?status=delete_success"); // Redireciona com status de sucesso
        exit();
    } catch (PDOException $e) {
        // Tratamento de erros na exclusão
        // Comum quando há restrições de chave estrangeira (produtos associados à categoria)
        Banco::desconectar();
        // Em um ambiente de produção, seria ideal registrar o erro em um log
        // Redireciona com mensagem de erro
        header("Location: gerenciar_categorias.php?status=delete_error&msg=" . urlencode('Não foi possível excluir a categoria. Verifique se existem produtos associados.'));
        exit();
    }
} else {
    // Se não foi fornecido um ID válido, redireciona com mensagem de erro
    header("Location: gerenciar_categorias.php?status=invalid_id");
    exit();
}
?>
