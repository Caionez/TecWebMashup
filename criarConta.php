<?php
require_once('conexaoBD.php');

//Se for um request de POST, tenta autenticar o usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	if (isset($_POST['email'], $_POST['senha'], $_POST['nome'])) {
		
		$email = $_POST['email'];
		$senha = $_POST['senha'];
		$nome  = $_POST['nome'];
	
		$sql = " insert into usuario (email,senha,nome) values "
					." (?, ?, ?) ";

		$stmt = $conn->prepare($sql);

		if($stmt) {

			$stmt->bind_param('sss', $email, $senha, $nome);
			$stmt->execute();

			if($stmt->affected_rows > 0) {
				$msg = "Usuário cadastrado com sucesso!";
				header("location:./index.php?msg=$msg");
			}
		}
		$stmt->close();		
	}
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
	<meta charset="utf-8" />
	<title>TecWeb Mashup - Login</title>
	
	
	<meta name="google-signin-scope" content="profile email">
    <meta name="google-signin-client_id" content="897390727670-6vph0ma65dqou4oim7crpjfrsnckqrid.apps.googleusercontent.com">
    <script src="https://apis.google.com/js/platform.js" async defer></script>
	
	<script src="./content/js/jquery-3.2.1.min.js"></script>
	<!--<link rel="stylesheet" type="text/css" href="./content/css/estilo.css" />-->

	<!-- Materialize CSS -->
	<link rel="stylesheet" href="./content/css/materialize.min.css">  
	<script src="./content/js/materialize.min.js"></script>
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body>
  <div class="section"></div>
  <main>
    <center>
      <h5 class="teal-text">Cadastro de usuário</h5>
      <div class="section"></div>

      <div class="container">
        <div class="z-depth-1 grey lighten-4 row" style="display: inline-block; width: 35%; padding: 0 50px; border: 1px solid #EEE;">
          <form class="col s12" method="post" id="formCadastro" action="<?=$_SERVER['PHP_SELF']?>">
            <div class='row'>
              <div class='col s12'>
              </div>
            </div>

			<div class='row'>
              <div class='input-field col s12'>
                <input class='validate' type='text' name="nome" id="nome" required/>
                <label for='nome'>Nome</label>
              </div>              
            </div>
			
            <div class='row'>
              <div class='input-field col s12'>
                <input class='validate' type='email' name='email' id='email' required/>
                <label for='email'>Email</label>
              </div>
            </div>
			
            <div class='row'>
              <div class='input-field col s12'>
                <input class='validate' type='password' name="senha" id="senha" required/>
                <label for='password'>Senha</label>
              </div>              
            </div>
            <br />
            <center>
              <div class='row'>
                <button type='submit' name='btn_login' class='col s12 btn btn-large waves-effect teal'>Cadastrar</button>
              </div>			  
            </center>
          </form>
        </div>
      </div>      
    </center>
    <div class="section"></div>
  </main>
</body>
</html>