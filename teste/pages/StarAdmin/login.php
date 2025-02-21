<?php
 //Conexão
  require_once'Classes/Db.php';

  //sessão
  session_start();
  
    //verificação
    //session_start();
    // if(isset($_SESSION["Logado"]) || !isset($_SESSION["Logado"])){
    // header("Location:Login.php");
    // //exit;
    // }

  //botão enviar
  if (isset($_POST['btn-entrar'])) {
      $erros = array();
        $email = mysqli_escape_string($mysqli,$_POST['email']);
         $senha = mysqli_escape_string($mysqli,$_POST['senha']); 
      
      if (empty($email) or empty($senha)) {
        $erros[] = "<li> O campo email e senha precisam ser preechidos</li>";
      }else{
           $sql= "SELECT email, senha FROM usuario WHERE email = '$email' ";
           $resultado = mysqli_query($mysqli,$sql);
           
           if (mysqli_num_rows($resultado) > 0 ) {
                
               $sql= "SELECT * FROM usuario where email = '$email' and senha ='$senha' ";
               $resultado=mysqli_query($mysqli,$sql);
               
                if (mysqli_num_rows($resultado) == 1) {
                    $dados=mysqli_fetch_array($resultado);
                    mysqli_close($mysqli);
                    $_SESSION['Logado'] = true;
                    $_SESSION['codigo'] = $dados['codigo'];
                    header('location:index.php');
                    
                }else{
                      $erros[] = "<li>Usuario e senhas não conferem</li>";
                }
            
             }else{
                    $erros[] = "<li>Usuario inexistente</li>";
            }
      }
    }
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Star Admin Free Bootstrap Admin Dashboard Template</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="vendors/iconfonts/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="vendors/css/vendor.bundle.addons.css">
  <!-- endinject -->
  <!-- plugin css for this page -->
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link rel="stylesheet" href="css/style.css">
  <!-- endinject -->
  <link rel="shortcut icon" href="images/favicon.png" />
</head>

<body>
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper auth-page">
      <div class="content-wrapper d-flex align-items-center auth auth-bg-1 theme-one">
        <div class="row w-100">
          <div class="col-lg-4 mx-auto">
            <div class="auto-form-wrapper">
              <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method ="POST">
           
                  <?php if(!empty($erros)){
                           foreach($erros as $erro):
                                echo $erro;
                           endforeach;
                      }
                  
                  ?>
                <div class="form-group">
                  <label class="label">Email</label>
                  <div class="input-group">
                    <input type="text" name="email" class="form-control" id="exampleInputEmail1" placeholder="EMAIL">
                    <div class="input-group-append">
                      <span class="input-group-text">
                        <i class="mdi mdi-check-circle-outline"></i>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="label">Password</label>
                  <div class="input-group">
                    <input type="password" name="senha" class="form-control" id="exampleInputPassword1" placeholder="******">
                    <div class="input-group-append">
                      <span class="input-group-text">
                        <i class="mdi mdi-check-circle-outline"></i>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <button name="btn-entrar" type="submit" class="btn btn-primary btn-lg btn-block">ENTRAR</button>
                  <button  type="reset" class="btn btn-primary btn-lg btn-block" onClick=parent.location="javascript:location.reload()">LIMPAR</button>
                </div>
                <div class="form-group d-flex justify-content-between">
                  <a href="pages/ForgotPassword.html" class="text-small forgot-password text-black">Esqueceu a senha</a>
                  
                </div>

                <div class="text-block text-center my-3">
                  <span class="text-small font-weight-semibold">Não possui conta ?</span>
                  <a href="pages/registre.php" class="text-black text-small">Crie uma conta</a>
                </div>
              </form>
            </div>
            <p class="footer-text text-center">Gsystem © 2018.</p>
          </div>
        </div>
      </div>
      <!-- content-wrapper ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
  <!-- container-scroller -->
  <!-- plugins:js -->
  <script src="vendors/js/vendor.bundle.base.js"></script>
  <script src="vendors/js/vendor.bundle.addons.js"></script>
  <!-- endinject -->
  <!-- inject:js -->
  <script src="js/off-canvas.js"></script>
  <script src="js/misc.js"></script>
  <!-- endinject -->
</body>

</html>