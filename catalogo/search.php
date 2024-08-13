<?php
require_once 'dependencies/config.php';

$searchQuery = isset($_GET['q']) ? $_GET['q'] : '';
$livrosPorPagina = 25;
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaAtual - 1) * $livrosPorPagina;

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

$results = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $titulo = $row['titulo_livro'];
    $imagem = getBookCover($titulo);
    $results[] = [
        'titulo' => $titulo,
        'imagem' => $imagem
    ];
}

header('Content-Type: application/json');
echo json_encode($results);
?>
