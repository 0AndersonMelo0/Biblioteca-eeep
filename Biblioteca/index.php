<?php
require_once 'dependencies/config.php';

// Função para buscar a URL da capa do livro usando a Google Books API com cURL
function getBookCover($titulo)
{
    $titulo = urlencode($titulo);
    $url = "https://www.googleapis.com/books/v1/volumes?q=intitle:" . $titulo;

    // Inicia cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (isset($data['items'][0]['volumeInfo']['imageLinks']['thumbnail'])) {
        return $data['items'][0]['volumeInfo']['imageLinks']['thumbnail'];
    } else {
        // Retorna URL para imagem "Sem foto"
        return 'img/sem-foto.png';  // Atualizado para .png
    }
}

// Defina o número de livros por página
$livrosPorPagina = 25;

// Obtenha a página atual a partir do parâmetro GET, padrão é 1
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaAtual - 1) * $livrosPorPagina;

// Obtenha o texto de busca a partir do parâmetro GET
$searchQuery = isset($_GET['q']) ? $_GET['q'] : '';

// Consultar livros com limite e deslocamento
if ($searchQuery) {
    $sql = "SELECT DISTINCT titulo_livro FROM livros WHERE titulo_livro LIKE :query LIMIT :limite OFFSET :offset";
    $queryParam = '%' . $searchQuery . '%';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':query', $queryParam, PDO::PARAM_STR);
} else {
    $sql = "SELECT DISTINCT titulo_livro FROM livros LIMIT :limite OFFSET :offset";
    $stmt = $conn->prepare($sql);
}

$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limite', $livrosPorPagina, PDO::PARAM_INT);
$stmt->execute();

// Contar o total de livros para a paginação
if ($searchQuery) {
    $sqlTotal = "SELECT COUNT(DISTINCT titulo_livro) as total FROM livros WHERE titulo_livro LIKE :query";
    $stmtTotal = $conn->prepare($sqlTotal);
    $stmtTotal->bindParam(':query', $queryParam, PDO::PARAM_STR);
} else {
    $sqlTotal = "SELECT COUNT(DISTINCT titulo_livro) as total FROM livros";
    $stmtTotal = $conn->prepare($sqlTotal);
}

$stmtTotal->execute();
$totalLivros = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalLivros / $livrosPorPagina);
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
            <form action="" method="get" id="search-form">
                <div id="divBusca">
                    <input type="text" id="txtBusca" name="q" placeholder="Buscar..." value="<?php echo htmlspecialchars($searchQuery); ?>" />
                    <button id="btnBusca" type="submit">
                        <img src="https://s3-alpha-sig.figma.com/img/2fbc/cd73/5f61f04407b960f9f22ea475bc2a6622?Expires=1724025600&Key-Pair-Id=APKAQ4GOSFWCVNEHN3O4&Signature=PHaJAoNOMUlkVfRi3HpcnO9RJMN0kOv5RZKQy-5GYOGobxnW-w6M6JJhE-9x2eYJ8v8hXr82pqOla5RaiZ0ASX3OaMXike1yLP1Gn2NG8-on1MJNnttlODlc3OMYHYv968JQvY-iA9d36RbcI7jJwAnfAhTmkZkuLyAjd4RYBt1phUHQ5rHAwdjMf9-0QYMNjiQVSG23kReG1qEG~1AiYRBR-iDXozK1v9~KRe0x-0B6jpNk3xPbscoLEf7Cueht5vrUMn0HGvnJP2Mnza6x-gumYqlch9EDRSgctOGIdS1p6kGd93j1y-CWrW0Ggtto43oKMAunWQxNInldzmFsTQ__" />
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
                    $imagem = getBookCover($titulo);

                    echo '<div class="shop-link">
                        <h3>' . $titulo . '</h3>
                        <img src="' . $imagem . '" alt="capa do livro" style="max-width: 100px; max-height: 150px;" loading="lazy">
                        <button class="botao-verde">+ informações</button>
                      </div>';
                }
            } else {
                echo '<p>Nenhum livro encontrado.</p>';
            }
            ?>
        </div>
        <!-- Paginação -->
        <div class="pagination">
            <?php if ($paginaAtual > 1): ?>
                <a href="?q=<?php echo urlencode($searchQuery); ?>&pagina=<?php echo $paginaAtual - 1; ?>">Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <a href="?q=<?php echo urlencode($searchQuery); ?>&pagina=<?php echo $i; ?>" <?php if ($i == $paginaAtual) echo 'class="active"'; ?>>
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($paginaAtual < $totalPaginas): ?>
                <a href="?q=<?php echo urlencode($searchQuery); ?>&pagina=<?php echo $paginaAtual + 1; ?>">Próximo</a>
            <?php endif; ?>
        </div>
    </section>

    <script>
    // Atualiza a pesquisa sem recarregar a página
    document.getElementById('txtBusca').addEventListener('input', function() {
        const query = this.value;
        if (query.length < 3) {
            return;
        }
        fetch(`search.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                let resultsHtml = '';
                if (data.length > 0) {
                    data.forEach(book => {
                        resultsHtml += `
                            <div class="shop-link">
                                <h3>${book.titulo}</h3>
                                <img src="${book.imagem}" alt="capa do livro" style="max-width: 100px; max-height: 150px;" loading="lazy">
                            </div>
                        `;
                    });
                } else {
                    resultsHtml = '<p>Nenhum livro encontrado.</p>';
                }
                document.getElementById('search-results').innerHTML = resultsHtml;
            })
            .catch(error => {
                console.error('Erro ao buscar livros:', error);
            });
    });
    </script>
    <script type="text/javascript" src="scripts.js"></script>
</body>
</html>