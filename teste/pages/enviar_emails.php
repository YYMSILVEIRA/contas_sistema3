<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../padroes/arquivos_de_padrao.php';

// Função para enviar e-mail
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Caminho para o autoload do Composer
require_once '../includes/logs.php';
// Exemplo de uso: registrar o acesso à página atual
$pagina_acessada = $_SERVER['REQUEST_URI']; // Captura a URL acessada
registrarLog($pagina_acessada);

function enviarEmail($destinatario, $assunto, $mensagem) {
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8'; // Define a codificação como UTF-8
    try {
        // Configurações do servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Servidor SMTP do Gmail
        $mail->SMTPAuth = true;
        $mail->Username = 'cryptsec.ib2s@gmail.com'; // Seu e-mail
        $mail->Password = 'mqjb lkav btcp oddf'; // Sua senha ou senha de app
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
        $mail->Port = 587; // Porta do Gmail

        // Remetente e destinatário
        $mail->setFrom('yurimadridsilveira2002@outlook.com', 'Contas que estão prestes a vencer | Contas Sistema');
        $mail->addAddress($destinatario);

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body = $mensagem;

        // Envia o e-mail
        $mail->send();
        echo "E-mail enviado para $destinatario com sucesso.<br>";
    } catch (Exception $e) {
        echo "Falha ao enviar e-mail para $destinatario. Erro: {$mail->ErrorInfo}<br>";
    }
}

// Verificar contas a receber
$stmt = $pdo->query("
    SELECT cr.id, cr.descricao, cr.valor, cr.data, cr.cliente, u.email 
    FROM contas_receber cr
    JOIN usuarios u ON cr.id_empresa = u.id_empresa
    WHERE cr.id_situacao = 8 
    AND DATEDIFF(cr.data, CURDATE()) = 3
");
$contasReceber = $stmt->fetchAll();

foreach ($contasReceber as $conta) {
    $assunto = "Lembrete: Conta a Receber Próxima do Vencimento";
    $mensagem = "
        <h2>Lembrete de Conta a Receber</h2>
        <p><strong>Descrição:</strong> {$conta['descricao']}</p>
        <p><strong>Valor:</strong> R$ " . number_format($conta['valor'], 2, ',', '.') . "</p>
        <p><strong>Data de Vencimento:</strong> " . date('d/m/Y', strtotime($conta['data'])) . "</p>
        <p><strong>Cliente:</strong> {$conta['cliente']}</p>
        <p>Faltam 3 dias para o vencimento desta conta.</p>
    ";
    enviarEmail($conta['email'], $assunto, $mensagem);
}

// Verificar contas a pagar
$stmt = $pdo->query("
    SELECT cp.id, cp.descricao, cp.valor, cp.data, cp.fornecedor, u.email 
    FROM contas_pagar cp
    JOIN usuarios u ON cp.id_empresa = u.id_empresa
    WHERE cp.id_situacao = 8 
    AND DATEDIFF(cp.data, CURDATE()) = 3
");
$contasPagar = $stmt->fetchAll();

foreach ($contasPagar as $conta) {
    $assunto = "Lembrete: Conta a Pagar Próxima do Vencimento";
    $mensagem = "
        <h2>Lembrete de Conta a Pagar</h2>
        <p><strong>Descrição:</strong> {$conta['descricao']}</p>
        <p><strong>Valor:</strong> R$ " . number_format($conta['valor'], 2, ',', '.') . "</p>
        <p><strong>Data de Vencimento:</strong> " . date('d/m/Y', strtotime($conta['data'])) . "</p>
        <p><strong>Fornecedor:</strong> {$conta['fornecedor']}</p>
        <p>Faltam 3 dias para o vencimento desta conta.</p>
    ";
    enviarEmail($conta['email'], $assunto, $mensagem);
}

echo "Verificação de vencimentos concluída.";
?>