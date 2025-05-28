<?php
include('banco.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);

    if (!empty($nome)) {
        $stmt = $conn->prepare("INSERT INTO categoria (nome) VALUES (?)");
        $stmt->bind_param("s", $nome);
        
        if ($stmt->execute()) {
            echo "<script>alert('Categoria cadastrada com sucesso!'); window.location.href='cadastrar_categoria.php';</script>";
        } else {
            echo "Erro ao cadastrar: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Por favor, preencha o nome da categoria.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Categoria</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>Cadastrar Categoria</h2>
    <form action="" method="post">
        <div class="mb-3">
            <label for="nome" class="form-label">Nome da Categoria:</label>
            <input type="text" name="nome" id="nome" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Cadastrar</button>
        <a href="../admin/index.php" class="btn btn-secondary">Voltar</a>
    </form>
</body>
</html>
