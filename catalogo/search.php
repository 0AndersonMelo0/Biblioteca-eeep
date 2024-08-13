<?php
require_once 'dependencies/config.php';

$query = isset($_GET['q']) ? $_GET['q'] : '';
$limit = 25;  // Limite de livros por página

if ($query) {
    $sql = "SELECT DISTINCT titulo_livro FROM livros WHERE titulo_livro LIKE :query LIMIT :limit";
    $queryParam = '%' . $query . '%';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':query', $queryParam, PDO::PARAM_STR);
} else {
    $sql = "SELECT DISTINCT titulo_livro FROM livros LIMIT :limit";
    $stmt = $conn->prepare($sql);
}

$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();

$books = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $titulo = htmlspecialchars($row["titulo_livro"]);
    $imagem = getBookCover($titulo);
    $books[] = ['titulo' => $titulo, 'imagem' => $imagem];
}

header('Content-Type: application/json');
echo json_encode($books);

function getBookCover($titulo)
{
    $titulo = urlencode($titulo);
    $url = "https://www.googleapis.com/books/v1/volumes?q=intitle:" . $titulo;

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
        return 'img/sem-foto.png';
    }
}
?>
