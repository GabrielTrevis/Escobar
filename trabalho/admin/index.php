<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <title>Admin Painel</title>
</head>

<body>
    <div class="container">
        <!-- cabecalho-->
        <div class="jumbotron">
            <div class="col">
                <h1> Loja Virtual </h1>
            </div>
            
            <table class="table table-striped">
                <tr>
                    <th scope="col"> <a href="cadastrarcliente.php" class="btn btn-success">Cadastrar Clientes</a></th>
                    <th scope="col"> <a href="listacliente.php" class="btn btn-outline-info">Listar Clientes</a></th>

                </tr>
                <tr>
                    <th scope="col"> <a href="cadastrarproduto.php" class="btn btn-success">Cadastrar Productos</a></th>
                    <th scope="col"><a href="listaproduto.php" class="btn btn-outline-info">Listar Productos</a></th>
                </tr>

            </table>
        </div>


        <div class="row">
            <div class=" col-md-12 text-center">
                <img class="img-responsive" src="img/logo-inicio.png" alt="Imagem" />
            </div>
        </div>
    </div>


    <!--             <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">Id</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Endereço</th>
                        <th scope="col">Telefone</th>
                        <th scope="col">Email</th>
                        <th scope="col">Sexo</th>
                        <th scope="col">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        include 'banco.php';
                        $pdo = Banco::conectar();
                        $sql = 'SELECT * FROM cliente ORDER BY id DESC';

                        foreach($pdo->query($sql)as $row)
                        {
                            echo '<tr>';
			                      echo '<th scope="row">'. $row['id'] . '</th>';
                            echo '<td>'. $row['nome'] . '</td>';
                            echo '<td>'. $row['endereco'] . '</td>';
                            echo '<td>'. $row['telefone'] . '</td>';
                            echo '<td>'. $row['email'] . '</td>';
                            echo '<td>'. $row['sexo'] . '</td>';
                            echo '<td width=250>';
                            echo '<a class="btn btn-primary" href="read-clientes.php?id='.$row['id'].'">Info</a>';
                            echo ' ';
                            echo '<a class="btn btn-warning" href="update-clientes.php?id='.$row['id'].'">Atualizar</a>';
                            echo ' ';
                            echo '<a class="btn btn-danger" href="deletecliente.php?id='.$row['id'].'">Excluir</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        Banco::desconectar();
                        ?>
                </tbody>
            </table> -->
    </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.3.1.js"
        integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous">
    </script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="assets/js/bootstrap.min.js"></script>

    <!-- Botão flutuante do carrinho -->
<a href="produtos.php" class="btn btn-danger rounded-circle"
   style="position: fixed; bottom: 20px; right: 20px; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; z-index: 999;">
    🛒
</a>
</body>

</html>