<?php 
  session_start();
  include 'Db.php';

//armazena names em variaveis
 $nome = isset($_POST['nome']) == true ? $_POST['nome']:"";
 $setor = isset($_POST['setor']) == true ? $_POST['setor']:"";
 $solicitacao = isset($_POST['solicitacao']) == true ? $_POST['solicitacao']:"";
 $sla = isset($_POST['sla']) == true ? $_POST['sla']:"";




 //Query para inserir dados
  $sql = "INSERT INTO `Chamados`(`nome`, `setor`, `solicitacao`, `sla`) VALUES('$nome','$setor','$solicitacao','$sla');"; 


//Chama função para executar query, onde recebe variavel com query e variavel de conexão com bd 
$run_sql = mysqli_query($mysqli,$sql);


//valida processo
      if($run_sql) {
          $_SESSION['msg'] = "<p><b>Chamado aberto</b></p>";
          header("Location: ../pages/chamados.php");
          //
          
      }else{
       // header("Location: formAtendimento.php");

        echo "<script>alert('Necessario cadastrar')</script>";
      }

 ?>