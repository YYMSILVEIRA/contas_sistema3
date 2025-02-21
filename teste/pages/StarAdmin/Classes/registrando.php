<?php 
  session_start();
  include 'Db.php';


  //inserção dos registros

  $nome = isset($_POST['nome']) == true ? $_POST['nome']:"";
  $sobrenome = isset($_POST['sobrenome']) == true ? $_POST['sobrenome']:"";
  $email = isset($_POST['email']) == true ? $_POST['email']:"";
  $senha = isset($_POST['senha']) == true ? $_POST['senha']:"";
  $funcao = isset($_POST['funcao']) == true ? $_POST['funcao']:"";
  //$foto_perfil = isset($_FILES['foto_perfil']) == true ? $_FILES['foto_perfil']:"";

  $extensao = substr($_FILES['foto_perfil']['name'], -4); //pega a extensão do arquivo
  $novo_nome = md5(time()) . $extensao; //define o nome do diretorio
  $diretorio = "../images/faces/"; // define o diretorio para onde será enviado as imagens

  move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $diretorio . $novo_nome);

      $sql = "INSERT INTO `usuario`(`nome`, `sobrenome`, `email`, `senha`, `data_cadastro`, `funcao`, `foto_perfil`) VALUES ('$nome','$sobrenome','$email','$senha',NOW(),'$funcao','$novo_nome');"; 

    $run_sql = mysqli_query($mysqli,$sql);


 

      if($run_sql) {
          $_SESSION['msg'] = "<p>Efetuado cadastro com sucesso </p>";
          header("Location: ../pages/registre.php");
          //
          
      }else{
     
         $_SESSION['msg'] = "<p>Erro ao cadastrar conta </p>";
       //  header("Location: ../pages/registre.php");

      }

  

 

 ?>