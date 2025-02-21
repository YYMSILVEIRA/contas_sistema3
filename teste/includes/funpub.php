<?php

function lerContasEmail($pdo) {
    $contas = [];

    try {
        // Prepara a query SQL
        $stmt = $pdo->prepare("SELECT endereco_email, senha FROM contas_sistema.emails_disparar");
        
        // Executa a query
        $stmt->execute();
        
        // Obtém todos os resultados
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Itera sobre os resultados e adiciona ao array
        foreach ($results as $row) {
            $contas[] = [
                'endereco_email' => $row['endereco_email'],
                'senha' => $row['senha']
            ];
        }
    } catch (PDOException $e) {
        // Em caso de erro, exibe a mensagem
        die("Erro ao buscar contas de e-mail: " . $e->getMessage());
    }

    return $contas;
}


?>