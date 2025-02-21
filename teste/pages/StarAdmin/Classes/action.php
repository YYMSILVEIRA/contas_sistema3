<?php 
  session_start();
  include 'Db.php';


  //inserção dos registros

  $Nome = isset($_POST['Nome']) == true ? $_POST['Nome']:"";
  $Sobrenome = isset($_POST['Sobrenome']) == true ? $_POST['Sobrenome']:"";
  $Sexualidade = isset($_POST['Sexualidade']) == true ? $_POST['Sexualidade']:"";
  $Data_nasc = isset($_POST['Data_nasc']) == true ? $_POST['Data_nasc']:"";
  $Bairro = isset($_POST['Bairro']) == true ? $_POST['Bairro']:"";
  $Cidade = isset($_POST['Cidade']) == true ? $_POST['Cidade']:"";
  $End_mom = isset($_POST['End_mom']) == true ? $_POST['End_mom']:"";
  $CEP = isset($_POST['CEP']) == true ? $_POST['CEP']:"";
  $Cidade_mom = isset($_POST['Cidade_mom']) == true ? $_POST['Cidade_mom']:"";
  $Nacionalidade = isset($_POST['Nacionalidade']) == true ? $_POST['Nacionalidade']:"";


  $sql = "INSERT INTO atendimento(Nome, Sobrenome, Sexualidade, Data_nasc, Bairro, Cidade, End_mom, Cidade_mom, CEP, Nacionalidade) VALUES ('$Nome','$Sobrenome','$Sexualidade','$Data_nasc','$Bairro','$Cidade','$End_mom','$Cidade_mom','$CEP','$Nacionalidade');"; 

    $run_sql = mysqli_query($mysqli,$sql);
    

      if($run_sql) {
          $_SESSION['msg'] = "<p>Efetuado cadastro com sucesso </p>";
          header("Location: ../pages/formAtendimento.php");
          //
          
      }else{
       // header("Location: formAtendimento.php");
        echo "<script>alert('Necessario cadastrar')</script>";
      }

  

 

 ?>