<?php
session_start();
require_once('conexaoBD.php');

if (isset($_GET['msg']))
	$msg = htmlspecialchars($_GET['msg']);

//Se for um request de POST, tenta autenticar o usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	if (isset($_POST['tipoLogin'])) {
		if ($_POST['tipoLogin'] == "BD") {
			
			if (isset($_POST['email'])) {

				$email = $_POST['email'];

				$sql = " select nome, senha from usuario "
						." where email = ? ";

				$stmt = $conn->prepare($sql);

				if($stmt) {
					$stmt->bind_param('s', $email);
					$stmt->execute();

					$result = $stmt->get_result();
					$row = $result->fetch_assoc();

					if ($row) {
						if ($row['senha'] == $_POST['senha']) {
							$_SESSION['email'] = $email;
							$_SESSION['nome'] = $row['nome'];														
							header('location:./mapaUnidadesPUC.php');
						}
						else {
							$msg = 'Usuário ou senha incorretos';
						}
					}
					else {
						$msg = 'Usuário não cadastrado';
					}
					$stmt->close();
				}
			}//Fim if (isset($_POST['email']))
		}
		elseif ($_POST['tipoLogin'] == "google") {
			$_SESSION['email'] = $_POST['email_google'];
			$_SESSION['nome'] = $_POST['nome_google'];
			$_SESSION['foto'] = $_POST['foto_google'];
			header('location:./mapaUnidadesPUC.php');
		}
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
      <h5 class="teal-text">Faça login para acessar o Aplicativo</h5>
      <div class="section"></div>

      <div class="container">
        <div class="z-depth-1 grey lighten-4 row" style="display: inline-block; width: 35%; padding: 0 50px; border: 1px solid #EEE;">
          <form class="col s12" method="post" id="formLogin" action="<?=$_SERVER['PHP_SELF']?>">
            <div class='row'>
              <div class='col s12'>
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
                <button type='submit' name='btn_login' class='col s12 btn btn-large waves-effect teal'>Login</button>
              </div>
			  <div class='row'>
                <div class="g-signin2" data-onsuccess="onSignIn" data-width="226" data-height="54" data-longtitle="true"></div>
              </div>
            </center>

			<!--Campos usados para autenticação com API -->
			<input type="hidden" name="tipoLogin" id="tipoLogin" value="BD" />
			<!-- Se o value do hidden tipoLogin for alterado para google
			os dados dos hiddens abaixo serão utilizados para a autenticação -->
			<input type="hidden" name="email_google" id="email_google" />
			<input type="hidden" name="nome_google" id="nome_google" />
			<input type="hidden" name="foto_google" id="foto_google" />
          </form>
        </div>
      </div>
      <a href="./criarConta.php">Criar conta</a>
    </center>
    <div class="section"></div>
  </main>
		
		
	<script>
	
	<?php if(!empty($msg)): ?>
		alert('<?= $msg ?>');	
	<?php endif; ?>
	function onSignIn(googleUser) {
	  	  
        //Obtendo o perfil do usuário
        var profile = googleUser.getBasicProfile();

		$('#tipoLogin').val('google');
		$('#email_google').val(profile.getEmail());
		$('#nome_google').val(profile.getName());
		$('#foto_google').val(profile.getImageUrl());

		$('#email').val(profile.getEmail());
		$('#senha').val('google');
		
		//Após obter os dados, desconecta para evitar que ao sair o usuário acabe logando novamente		
		gapi.auth2.getAuthInstance().disconnect();
		
		$('#formLogin').submit();
	}
	</script>
</body>
</html>