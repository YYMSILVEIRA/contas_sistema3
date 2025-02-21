<?php
// Verifica se o caminho do arquivo foi passado
if (!isset($_GET['caminho'])) {
    die("Caminho do arquivo não especificado.");
}

$caminhoArquivo = urldecode($_GET['caminho']);

// Verifica se o arquivo existe
if (!file_exists($caminhoArquivo)) {
    die("Arquivo não encontrado.");
}

// Lê o conteúdo do arquivo
$conteudo = file_get_contents($caminhoArquivo);

// Retorna o conteúdo do arquivo
echo $conteudo;
?>