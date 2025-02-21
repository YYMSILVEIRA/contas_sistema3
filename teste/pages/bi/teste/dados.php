<?php
// Simula a busca de vários usuários no banco de dados
$usuarios = [
    [
        'nome' => 'João Silva',
        'idade' => 30,
        'cidade' => 'São Paulo'
    ],
    [
        'nome' => 'Maria Oliveira',
        'idade' => 25,
        'cidade' => 'Rio de Janeiro'
    ],
    [
        'nome' => 'Carlos Souza',
        'idade' => 40,
        'cidade' => 'Belo Horizonte'
    ]
];

// Retorna os usuários em formato JSON
header('Content-Type: application/json');
echo json_encode($usuarios);
?>