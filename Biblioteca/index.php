<?php
require_once 'dependencies/config.php';

// Função para buscar a URL da capa do livro usando a Google Books API
function getBookCover($titulo)
{
    $titulo = urlencode($titulo); // Codifica o título para a URL
    $url = "https://www.googleapis.com/books/v1/volumes?q=intitle:" . $titulo;

    // Faz a requisição à API do Google Books
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    // Verifica se há resultados e retorna a URL da capa
    if (isset($data['items'][0]['volumeInfo']['imageLinks']['thumbnail'])) {
        return $data['items'][0]['volumeInfo']['imageLinks']['thumbnail'];
    } else {
        return 'path/to/default/image.jpg'; // Imagem padrão se não encontrar
    }
}

// Consultar livros
$sql = "SELECT titulo_livro, numero_registro FROM livros";
$stmt = $conn->prepare($sql);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca</title>
    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/style-bibli.css">
    
</head>

<body>

    <header>
        
        <nav>
            <aside id="menu-Oculto" class="menu-Oculto">
                <div class="imagemMenu">
                    <img src="img/logoMenu.png" alt="" class="logoMenu">
                    <button class="fechar" href="" onclick="fecharMenu()">&times;</button>

                </div>

                <div class="linha"></div>
                <div class="opcoes">

                    <a href="">Cadastrar Livro</a>
                    <a href="">Cadastrar Empréstimo</a>
                    <a href="">Banco de Livros</a>
                    <a href="">Empréstimos</a>
                    <a href="">Adicionar Turma</a>
                    <a href="">Pedidos</a>
                    <a href="">Relatório</a>
                    <a href="" class="sair">Sair</a>
                </div>

            </aside>
            <section id="principal">
                <span style="font-size:43px;cursor:pointer" onclick="abrirMenu()">&#9776;</span>
                <div class="nav-logo">
                    <img src="img/logoEEEP.png" alt="logo" class="logo_eeep" />
                    <div class="ret"></div>
                    <img src="img/logoNav.png" alt="logo" class="library" />
                </div>

            </section>
        </nav>

    </header>

    <section class="shop-section">
        <div class="header-biblioteca">
            <div class="restrito">
                <span class="ClasseTexto">BIBLIOTECA DE LIVROS</span>
            </div>
            <form action="" method="get">
                <div id="divBusca">
                    <input type="text" id="txtBusca" placeholder="Buscar..." />
                    <button id="btnBusca">
                        <img
                            src="https://s3-alpha-sig.figma.com/img/2fbc/cd73/5f61f04407b960f9f22ea475bc2a6622?Expires=1724025600&Key-Pair-Id=APKAQ4GOSFWCVNEHN3O4&Signature=PHaJAoNOMUlkVfRi3HpcnO9RJMN0kOv5RZKQy-5GYOGobxnW-w6M6JJhE-9x2eYJ8v8hXr82pqOla5RaiZ0ASX3OaMXike1yLP1Gn2NG8-on1MJNnttlODlc3OMYHYv968JQvY-iA9d36RbcI7jJwAnfAhTmkZkuLyAjd4RYBt1phUHQ5rHAwdjMf9-0QYMNjiQVSG23kReG1qEG~1AiYRBR-iDXozK1v9~KRe0x-0B6jpNk3xPbscoLEf7Cueht5vrUMn0HGvnJP2Mnza6x-gumYqlch9EDRSgctOGIdS1p6kGd93j1y-CWrW0Ggtto43oKMAunWQxNInldzmFsTQ__" />
                    </button>
                </div>
            </form>
        </div>
        <div class="barra-branca"></div>
        <div class="shop-images">
            <?php
            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $titulo = htmlspecialchars($row["titulo_livro"]);

                    // Obtém a URL da imagem da Google Books API
                    $imagem = getBookCover($titulo);

                    echo '<div class="shop-link">
                        <h3>' . $titulo . '</h3>
                        <img src="' . $imagem . '" alt="card" style="max-width: 100px; max-height: 150px;">
                        <button class="botao-verde">+ informações</button>
                      </div>';
                }
            } else {
                echo '<p>Nenhum livro encontrado.</p>';
            }
            ?>
        </div>
    </section>

    <script type="text/javascript"  src="scripts.js"></script>
</body>

</html>