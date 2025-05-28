<?php
require 'banco.php';

$id = 0;

if (!empty($_GET['id'])) {
    $id = $_REQUEST['id'];
}

// Adicionar uma etapa de confirmação aqui seria ideal em um projeto real
// Ex: if (!empty($_POST['confirm'])) { ... delete logic ... } else { ... show confirmation form ... }

if (!empty($id)) {
    // Deletar Categoria
    $pdo = Banco::conectar();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "DELETE FROM categorias WHERE id = ?";
    $q = $pdo->prepare($sql);
    try {
        $q->execute(array($id));
        Banco::desconectar();
        header("Location: gerenciar_categorias.php?status=delete_success");
        exit();
    } catch (PDOException $e) {
        // Se houver erro (ex: restrição de chave estrangeira não tratada com SET NULL/CASCADE)
        Banco::desconectar();
        // Logar erro $e->getMessage()
        header("Location: gerenciar_categorias.php?status=delete_error&msg=" . urlencode('Não foi possível excluir a categoria. Verifique se existem produtos associados.'));
        exit();
    }
} else {
    header("Location: gerenciar_categorias.php?status=invalid_id");
    exit();
}
?>
