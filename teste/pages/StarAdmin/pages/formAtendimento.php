<?php  
 

  require_once '../Classes/Db.php';
   
  //require_once '../Classes/action.php';      
  
  session_start();

  //usuario logado
  $id=$_SESSION['codigo'];
  $sql = "SELECT * FROM usuario where codigo = '$id'";
  $resultado = mysqli_query($mysqli,$sql); 
  $dados = mysqli_fetch_array($resultado);


 ?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Resgitro de atendimento</title>
  <!-- plugins:css -->
  <link rel="stylesheet" href="../vendors/iconfonts/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="../vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="../vendors/css/vendor.bundle.addons.css">
  <!-- endinject -->
  <!-- plugin css for this page -->
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/styleForm.css">
  <!-- endinject -->
  <link rel="shortcut icon" href="../images/favicon.png" />
</head>

<body>
  <div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    <nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-top justify-content-center">
        <a class="navbar-brand brand-logo" href="../index.php">
          <img src="../images/logo.svg" alt="logo" />
        </a>
        <a class="navbar-brand brand-logo-mini" href="../index.php">
          <img src="../images/logo-mini.svg" alt="logo" />
        </a>
      </div>
      <div class="navbar-menu-wrapper d-flex align-items-center">

        <ul class="navbar-nav navbar-nav-right">
          <li class="nav-item dropdown">
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="messageDropdown">
              <div class="dropdown-item">
                <p class="mb-0 font-weight-normal float-left">You have 7 unread mails
                </p>
                <span class="badge badge-info badge-pill float-right">View all</span>
              </div>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <img src="../images/faces/face4.jpg" alt="image" class="profile-pic">
                </div>
                <div class="preview-item-content flex-grow">
                  <h6 class="preview-subject ellipsis font-weight-medium text-dark">David Grey
                    <span class="float-right font-weight-light small-text">1 Minutes ago</span>
                  </h6>
                  <p class="font-weight-light small-text">
                    The meeting is cancelled
                  </p>
                </div>
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <img src="../images/faces/face2.jpg" alt="image" class="profile-pic">
                </div>
                <div class="preview-item-content flex-grow">
                  <h6 class="preview-subject ellipsis font-weight-medium text-dark">Tim Cook
                    <span class="float-right font-weight-light small-text">15 Minutes ago</span>
                  </h6>
                  <p class="font-weight-light small-text">
                    New product launch
                  </p>
                </div>
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <img src="../images/faces/face3.jpg" alt="image" class="profile-pic">
                </div>
                <div class="preview-item-content flex-grow">
                  <h6 class="preview-subject ellipsis font-weight-medium text-dark"> Johnson
                    <span class="float-right font-weight-light small-text">18 Minutes ago</span>
                  </h6>
                  <p class="font-weight-light small-text">
                    Upcoming board meeting
                  </p>
                </div>
              </a>
            </div>
          </li>
          <li class="nav-item dropdown">
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
              <a class="dropdown-item">
                <p class="mb-0 font-weight-normal float-left">You have 4 new notifications
                </p>
                <span class="badge badge-pill badge-warning float-right">View all</span>
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <div class="preview-icon bg-success">
                    <i class="mdi mdi-alert-circle-outline mx-0"></i>
                  </div>
                </div>
                <div class="preview-item-content">
                  <h6 class="preview-subject font-weight-medium text-dark">Application Error</h6>
                  <p class="font-weight-light small-text">
                    Just now
                  </p>
                </div>
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <div class="preview-icon bg-warning">
                    <i class="mdi mdi-comment-text-outline mx-0"></i>
                  </div>
                </div>
                <div class="preview-item-content">
                  <h6 class="preview-subject font-weight-medium text-dark">Settings</h6>
                  <p class="font-weight-light small-text">
                    Private message
                  </p>
                </div>
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <div class="preview-icon bg-info">
                    <i class="mdi mdi-email-outline mx-0"></i>
                  </div>
                </div>
                <div class="preview-item-content">
                  <h6 class="preview-subject font-weight-medium text-dark">New user registration</h6>
                  <p class="font-weight-light small-text">
                    2 days ago
                  </p>
                </div>
              </a>
            </div>
          </li>
          <li class="nav-item dropdown d-none d-xl-inline-block">
            <a class="nav-link dropdown-toggle" id="UserDropdown" href="#" data-toggle="dropdown" aria-expanded="false">
              <span class="profile-text"> Olá, <?php echo $dados['nome']?> <?php echo $dados ['sobrenome']; ?> !</span>
              <img class="img-xs rounded-circle" src ="../images/faces/<?php echo $dados['foto_perfil']; ?>" alt="Profile image">
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
              <a class="dropdown-item p-0">
                <div class="d-flex border-bottom">
                  <div class="py-3 px-4 d-flex align-items-center justify-content-center">
                    <i class="mdi mdi-bookmark-plus-outline mr-0 text-gray"></i>
                  </div>
                  <div class="py-3 px-4 d-flex align-items-center justify-content-center border-left border-right">
                    <i class="mdi mdi-account-outline mr-0 text-gray"></i>
                  </div>
                  <div class="py-3 px-4 d-flex align-items-center justify-content-center">
                    <i class="mdi mdi-alarm-check mr-0 text-gray"></i>
                  </div>
                </div>
              </a>
             <a class="dropdown-item" href="../login.php">
                Sair
              </a>
            </div>
          </li>
        </ul>
        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
          <span class="mdi mdi-menu"></span>
        </button>
      </div>
    </nav>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_sidebar.html -->
      
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
          <li class="nav-item nav-profile">
            <div class="nav-link">
              <div class="user-wrapper">
                <div class="profile-image">
                  <img class="rounded" src ="../images/faces/<?php echo $dados['foto_perfil']; ?>" alt="Profile image">
                </div>
                <div class="text-wrapper">
                  <p class="profile-name"><?php echo $dados['nome']?></p>
                  <div>
                    <small class="designation text-muted"><?php echo $dados['funcao']; ?></small>
                    <span class="status-indicator online"></span>
                  </div>
                </div>
              </div>
             </div>
          </li>

          <li class="nav-item">
            <a class="nav-link" href="../index.php">
              <i class="menu-icon mdi mdi-television"></i>
              <span class="menu-title">Dashboard</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="formAtendimento.php">
              <i class="menu-icon mdi mdi-content-copy"></i>
              <span class="menu-title">Atendimento</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="resultgrafic.php">
              <i class="menu-icon mdi mdi-chart-line"></i>
              <span class="menu-title">Resultados graficos</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="estruturaUser.php">
              <i class="menu-icon mdi mdi-table"></i>
              <span class="menu-title">Estrutura</span>
            </a>
          </li>
         <li class="nav-item">
            <a class="nav-link" href="chamados.php">
              <i class="menu-icon mdi mdi-table"></i>
              <span class="menu-title">Chamados</span>
            </a>
         </li>
          <li class="nav-item">
            <div class="collapse" id="auth">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item">
                  <a class="nav-link" href="pages/samples/blank-page.html"> Blank Page </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="pages/samples/login.html"> Login </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="pages/samples/register.html"> Register </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="pages/samples/error-404.html"> 404 </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="pages/samples/error-500.html"> 500 </a>
                </li>
              </ul>
            </div>
          </li>
        </ul>
      </nav>
      
  


      <!-- partial -->
      <div class="main-panel">
  

      <!-- Atendimento -->
            <div class="col-12 grid-margin">
                    <div class="card">
                      <div class="card-body">
                        <h4 class="card-title">Registro de atendimento</h4>
                        <form class="form-sample" action="../Classes/action.php" method="POST">
                          <?php 
                            if (isset($_SESSION['msg'])) {
                              echo $_SESSION['msg'];
                              unset($_SESSION['msg']);
                            }
                           ?>
                          <p class="card-description">
                            Registre conforme solicitado
                          </p>
                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Primeiro nome</label>
                                <div class="col-sm-9">
                                  <input type="text" class="form-control" name="Nome" required="preencha os campos" />
                                </div>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Sobrenome</label>
                                <div class="col-sm-9">
                                  <input type="text" class="form-control" name="Sobrenome" required="preencha os campos"/>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Sexualidade</label>
                                <div class="col-sm-9">
                                  <select class="form-control" name="Sexualidade" required="preencha os campos">
                                    <option>Masculino</option>
                                    <option>Feminino</option>
                                  </select>
                                </div>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Data de nascimento</label>
                                <div class="col-sm-9">
                                  <input class="form-control" placeholder="dd/mm/yyyy" name="Data_nasc" required="preencha os campos" />
                                </div>
                              </div>
                            </div>
                          </div>
                          <p class="card-description">
                            Endereço
                          </p>
                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Bairro</label>
                                <div class="col-sm-9">
                                  <input type="text" class="form-control" name="Bairro" required="preencha os campos" />
                                </div>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Cidade</label>
                                <div class="col-sm-9">
                                  <input type="text" class="form-control" name="Cidade" required="preencha os campos" />
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Endereço da mae</label>
                                <div class="col-sm-9">
                                  <input type="text" class="form-control" name="End_mom" required="preencha os campos"/>
                                </div>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group row">
                                <label class="col-sm-3 col-form-label">CEP</label>
                                <div class="col-sm-9">
                                  <input type="text" class="form-control" name="CEP"  required="preencha os campos"/>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Cidade da mãe</label>
                                <div class="col-sm-9">
                                  <input type="text" class="form-control" name="Cidade_mom" required="preencha os campos" />
                                </div>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nacionalidade</label>
                                <div class="col-sm-9">
                                  <select class="form-control" name="Nacionalidade" required="preencha os campos">
                                    <option>Brasileiro</option>
                                    <option>Italiano</option>
                                    <option>Russo</option>
                                    <option>Japa</option>
                                  </select>
                                </div>
                              </div>
                            </div>
                          </div>
                          <input type="submit"  value="Cadastrar" class="btn btn-primary">
                          <input type="reset"  value="Limpar" class="btn btn-primary">
                        </form>
                      </div>
                    </div>
                  </div>

        <!-- content-wrapper ends -->
        <!-- partial:partials/_footer.html -->
        <footer class="footer">
          <div class="container-fluid clearfix">
            <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Gsystem © 2018
              <a href="http://salesgabriel-com-br.umbler.net/MeuCV/" target="_blank">Gabriel Sales</a>. Gsystem reserved..</span>
            <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Sistema dashbord Gsystem
              <i class="mdi mdi-heart text-danger"></i>
            </span>
          </div>
        </footer>
        <!-- partial -->
      </div>
      <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
  <!-- container-scroller -->
  <!-- plugins:js -->
  <script src="../vendors/js/vendor.bundle.base.js"></script>
  <script src="vendors/js/vendor.bundle.addons.js"></script>
  <!-- endinject -->
  <!-- Plugin js for this page-->
  <!-- End plugin js for this page-->
  <!-- inject:js -->
  <script src="../js/off-canvas.js"></script>
  <script src="../js/misc.js"></script>
  <!-- endinject -->
  <!-- Custom js for this page-->
  <!-- End custom js for this page-->
</body>

</html>
