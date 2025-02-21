<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
// Função para registrar o log


// Função para detectar o sistema operacional
function detectarSistemaOperacional($user_agent) {
    $user_agent = strtolower($user_agent);

    if (strpos($user_agent, 'windows') !== false) {
        return 'Windows';
    } elseif (strpos($user_agent, 'macintosh') !== false || strpos($user_agent, 'mac os') !== false) {
        return 'Mac OS';
    } elseif (strpos($user_agent, 'linux') !== false) {
        return 'Linux';
    } elseif (strpos($user_agent, 'android') !== false) {
        return 'Android';
    } elseif (strpos($user_agent, 'iphone') !== false || strpos($user_agent, 'ipad') !== false) {
        return 'iOS';
    } else {
        return 'Desconhecido';
    }
}

// Função para registrar o log
function registrarLog($page_accessed) {
    global $pdo; // Use a conexão PDO já existente

    // Captura o endereço IP do usuário
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Captura a data e hora atual
    $access_time = date('Y-m-d H:i:s');

    // Verifica se o usuário está logado e captura o ID
    $user_id = 0; // Valor padrão para usuários não logados
    if (isLoggedIn()) { // Verifica se o usuário está logado
        $user_id = $_SESSION['user_id']; // Captura o ID do usuário da sessão
    }

    // Captura o User-Agent para detectar o sistema operacional
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido';
    $operating_system = detectarSistemaOperacional($user_agent);

    // Prepara a query para inserir o log no banco de dados
    $sql = "INSERT INTO logs (ip_address, access_time, page_accessed, user_id, operating_system) 
            VALUES (:ip_address, :access_time, :page_accessed, :user_id, :operating_system)";
    $stmt = $pdo->prepare($sql);

    // Executa a query com os valores capturados
    $stmt->execute([
        ':ip_address' => $ip_address,
        ':access_time' => $access_time,
        ':page_accessed' => $page_accessed,
        ':user_id' => $user_id,
        ':operating_system' => $operating_system
    ]);
}


// Exemplo de uso: registrar o acesso à página atual
/*$pagina_acessada = $_SERVER['REQUEST_URI']; // Captura a URL acessada
registrarLog($pagina_acessada);*/
?>