<?php
require_once("./funcoes.php");


// Exemplo de uso da classe
$db = new Database();

// Consulta SQL com placeholders para filtros
$sqlTabIndicadores = "SELECT t.nome_tabela as nomeTabela, t.descricao_tabela as descricaoTabela, i.id as idIndicador, i.nome_indicador as nomeIndicador, i.descricao_indicador as descricaoIndicador
    FROM tabelas_indicadores t
    LEFT JOIN indicadores i ON t.id = i.id_tabela
    ORDER BY t.id, i.id;
";


// Executa a consulta que retorna json e converte para lista
$ListaResultados = json_decode($db->query($sqlTabIndicadores), true);
header('Content-Type: application/json');
echo json_encode($ListaResultados);


?>