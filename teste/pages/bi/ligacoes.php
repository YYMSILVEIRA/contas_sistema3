<?php
// Inclui a classe Database
require_once './funcoes.php';

// Cria uma instância da classe Database
$db = new Database();

try {
    // Consulta para obter as tabelas
    $sqlTabelas = "select concat(' ',
				lgc.tabela_esquerda,
				' as ',
				lgc.apelido_esquerda,
				' ',
				lgc.tipo_ligacao,
				' ',
				lgc.tabela_direita,
				' as ',
				lgc.apelido_direita,
				' ON ',
				'( ',
				lgc.apelido_esquerda,
				'.',
				lgc.indicador_esquerdo,
				' ',
				lgc.operador,
				' ',
				lgc.apelido_direita,
				'.',
				lgc.indicador_direito,
				' ) '
			) as ligacao
from (
	select
		tcn.tabela_esquerda,
		tcn.apelido_esquerda,
		tcn.tipo_ligacao,
		tcn.tabela_direita,
		tcn.apelido_direita,
		tcn.operador,
		tcn.indicador_esquerdo,
		tcn.indicador_direito
	from
		(
		select
			(
			select
				lower(nome_tabela)
			from
				tabelas_indicadores
			where
				id = blt.id_tabela_esquerda) as tabela_esquerda,
			blt.apelido_esquerda as apelido_esquerda,
			blt.tipo_ligacao as tipo_ligacao,
			(
			select
				lower(nome_tabela)
			from
				tabelas_indicadores
			where
				id = blt.id_tabela_direita) as tabela_direita,
			blt.apelido_direita as apelido_direita,
			(
			select
				lower(nome_indicador)
			from
				indicadores
			where
				id = blt.id_indicador_esquerda) as indicador_esquerdo,
			operador,
			(
			select
				lower(nome_indicador)
			from
				indicadores
			where
				id = blt.id_indicador_direita) as indicador_direito
		from
			bi_ligar_tabelas blt
	) tcn
	where 
		lower(tcn.tabela_esquerda) = lower('contas_pagar')
		and lower(tcn.tabela_direita) = lower('situacoes')
) lgc;";
    $tabelas = json_decode($db->query($sqlTabelas), true);

    // Retorna os dados em formato JSON
    header('Content-Type: application/json');
    echo json_encode($tabelas, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    // Em caso de erro, retorna uma mensagem de erro em JSON
    http_response_code(500);
    header('Content-Type: application/json');
    error_log($e->getMessage().__DIR__);
    echo json_encode(['error' => $e->getMessage()]);
}
?>