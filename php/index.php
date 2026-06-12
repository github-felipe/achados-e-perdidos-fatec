<?php
    require_once("guardinha.php");
    restricao(999);

    function getNumItensDisponiveis(){
        
        //Aqui será implementado a lógica de pegar o número de itens achados porém não recuperados quando tivermos o banco de dados.
        
        $itensAchados = 89;
        return $itensAchados;
    }
    function getNumItensRecuperados(){
    
        //Aqui será implementado a lógica de pegar o número de itens devolvidos para o dono até hoje quando tivermos o banco de dados.

        $itensRecuperados = 210;
        return $itensRecuperados;
    }

    //Exemplo de uso dessas funções:
    $itensDisponiveis = getNumItensDisponiveis();
    $itensRecuperados = getNumItensRecuperados();

    echo "<p>Itens encontrados mas ainda sem dono: $itensDisponiveis";
    echo "<p>Número de itens recuperados pelo achados e perdidos: $itensRecuperados";

    if($_SESSION['nivel']<=1){
        echo "
            <form action='itens.php' method='post'>
                <input type='submit' value='Itens'>
            </form>

            <form action='cadastroPessoas.php' method='get'>
                <input type='submit' value='Cadastrar Pessoa'>
            </form>
        ";
    }
    echo "
        <form action='perfil.php' method='post'>
            <input type='submit' value='Perfil'>
        </form>

        <form action='chat.php' method='post'>
            <input type='submit' value='Procurar item'>
        </form>

        <form action='novoItem.php' method='post'>
            <input type='submit' value='Encontrei um item'>
        </form>

        <form action='logout.php' method='get'>
            <input type='submit' value='logout'>
        </form>
    ";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>

</body>
</html>