<?php
session_start();
require_once('conexaoBD.php');

//Se for um request de GET, obtém os dados do usuário da session
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

	if(isset($_SESSION['email'], $_SESSION['nome'])) {
		$emailUsuario = $_SESSION['email'];
		$nomeUsuario = $_SESSION['nome'];

		if(isset($_SESSION['foto'])) {
			$fotoUsuario = $_SESSION['foto'];
		}
		else {
			$fotoUsuario = './content/images/default-photo.png';
		}

		$sql = " select nome, place_id, lat, lng from unidadesPUC";

		$result = $conn->query($sql);

		mysqli_close($conn);

	}
	else {
		header('location:./index.php');
	}
}
//Se for um request de POST, remove os dados do usuario da session
else if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	if(isset($_POST['sair'])) {
		session_destroy();
		header('location:./index.php');
		exit();
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>Unidades PUC em BH</title>    

	<script src="./content/js/jquery-3.2.1.min.js"></script>
	<link rel="stylesheet" type="text/css" href="./content/css/mapaUnidadesPUC-style.css" />
	
	<!-- Materialize CSS -->
	<link rel="stylesheet" href="./content/css/materialize.min.css" />
	<script src="./content/js/materialize.min.js"></script>
	<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
</head>
<body>
	<!-- Navbar exibida no topo da tela -->
	<div class="navbar-fixed">
		<nav>
			<div class="nav-wrapper teal">
				<a href="#!" class="brand-logo" style="margin-left: 8px;">TecWeb - Mashup</a>
				<ul class="right hide-on-med-and-down">
					<li><img src="<?=$fotoUsuario?>" alt="" width="45" style="padding-top: 8px;"class="circle"></li>					
					<li><a class="dropdown-button" href="#!" data-activates="dropdown-opcoes" style="text-align: center; min-width: 250px;"><?=$nomeUsuario?><i class="material-icons right">arrow_drop_down</i></a></li>
				</ul>
			</div>
		</nav>
	</div>
			
	<div id="mapa"></div>

	<!-- FAB com sub-botões -->
	<div class="fixed-action-btn click-to-toggle">
		<a id="btn-opcoes" class="btn-large btn-floating tooltipped teal" data-position="left" data-delay="50" data-tooltip="Mostrar/Ocultar Opções">
			<i class="large material-icons">menu</i>
		</a>
		<ul>
			<li class="waves-effect waves-light">
				<a  href="#modal-localizacao" class="btn-large btn-floating tooltipped modal-trigger" data-position="left" data-delay="50" data-tooltip="Obter Localização">
					<i class="material-icons">my_location</i>
				</a>
			</li>
			<li class="waves-effect waves-light">
				<a href="#!" id="btn-marcadores" class="btn-large btn-floating tooltipped light-blue" data-position="left" data-delay="50" data-tooltip="Mostrar/Ocultar Unidades">
					<i class="material-icons">location_on</i>
				</a>
			</li>
			<li class="waves-effect waves-light">
				<a href="#!" id="btn-rota" class="btn-large btn-floating tooltipped orange" data-position="left" data-delay="50" data-tooltip="Limpar Rota">
					<i class="material-icons">delete</i>
				</a>
			</li>			
		</ul>
	</div>
	
	<!-- Hint que aparece no FAB -->
	<div class="tap-target teal lighten-2" data-activates="btn-opcoes">
		<div class="tap-target-content">
			<h5>Funções do aplicativo</h5>
			<p>Selecione esse botão para exibir as funcionalidades que utilizam a API do Google Maps!</p>
		</div>
	</div>	
	
	<div id="modal-localizacao" class="modal">
		<div class="modal-content">
		  <h4>Escolha o método de Geolocalização</h4>
		  <p>Escolha a opção API Google para fazer uma requisição a API Geolocation, e obter sua localização aproximada baseada no seu endereço de IP</p>
		  <p>Ou então, escolha a opção Browser para que seja solicitada a sua localização para o seu navegador de internet.</p>
		</div>
		<div class="modal-footer">
		  <a href="javascript:obterLocalizacaoUsuario('API');" class="modal-action modal-close waves-effect waves-green btn-flat">API Google</a>
		  <a href="javascript:obterLocalizacaoUsuario('Browser');" class="modal-action modal-close waves-effect waves-green btn-flat">Browser</a>
		</div>
	</div>

	<form method="POST" id="formLogout" action="<?=$_SERVER['PHP_SELF']?>">
		<input type="hidden" name="sair" value="Sair" />
	</form>
	<!-- Dropdown Structure -->
	<ul id="dropdown-opcoes" class="dropdown-content">
		<li><a href="#!"><?=$emailUsuario?></a></li>		
		<li class="divider"></li>
		<li><a href="#!" onclick="$('#formLogout').submit();"><i class="material-icons">power_settings_new</i>Sair</a></li>
	</ul>
	
	
	<script>
	var _mapa;
	var _marcadores = [];
	var _marcadoresVisiveis = false;
	var _marcadorUsuario;
	var _rotaTracada;
	var _infoWindow;

	function iniciarMapa() {

		_mapa = new google.maps.Map(document.getElementById("mapa"), {
			center: {lat: -19.922808, lng: -43.945176},
			zoom: 13,
			zoomControl: false,
			mapTypeControl: false,
			scaleControl: false,
			streetViewControl: false,
			rotateControl: false,
			fullscreenControl: false
		});

		//Obtendo as localizacoes do banco
		var localizacoes = [];

		<?php while($row = $result->fetch_assoc()): ?>
		
		localizacoes.push({
		nome: '<?=$row['nome'];?>',
		place_id: '<?=$row['place_id'];?>',
		localizacao: {lat : <?=$row['lat'];?>, lng : <?=$row['lng'];?>}
		});
		
		<?php endwhile;?>

		_infoWindow = new google.maps.InfoWindow();

		//Para cada localização no array
		for (var i = 0; i < localizacoes.length; i++) {

			var nome = localizacoes[i].nome;
			var posicao = localizacoes[i].localizacao;
			var identificacao = localizacoes[i].place_id;

			//Criando um marcador e colocando no mapa
			var marcador = new google.maps.Marker({
				title: nome,
				position: posicao,
				animation: google.maps.Animation.DROP,
				id: identificacao
			});

			//Salvando no array global para futura manipulação
			_marcadores.push(marcador);

			//Adicionando evento de exibição da infoWindow
			marcador.addListener('click', function() {
			obterDetalhesUnidade(this);
			});
		}//fim for localizacoesPuc
		
		
		//Adicionando evento no botão de exibir/ocultar os marcadores
		$('#btn-marcadores').click(alternarVisaoMarcadores);
		$('#btn-rota').addClass('disabled');
		
		//Ativando o modal de geolocalização
		$('.modal').modal();
		$('.tap-target').tapTarget('open');
	}//fim function iniciarMapa

	function obterLocalizacaoUsuario(modo) {
		
		//var locUsuario;
		//Se for selecionada a opção de API, faz uma requisição Ajax
		if (modo == "API") {
		
			var jsonLocalizacaoUsuario = { "considerIp": "true"}
			
			xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {

					var localizacaoUsuario = JSON.parse(this.responseText);

					if (localizacaoUsuario)  {
						
						_marcadorUsuario = new google.maps.Marker({
									map: _mapa,
									title: 'Sua Localização',
									position: localizacaoUsuario.location,
									icon: './content/images/blue-dot.png'									
									});
						_mapa.panTo(localizacaoUsuario.location);
					}
				}			
			};
			
			//Obtendo a localização do usuario atraves da API Geolocation
			xhttp.open("POST", "https://www.googleapis.com/geolocation/v1/geolocate?key=AIzaSyDZ6mZ9nbSJ_hPoaUH-7hizoT32pJQnzCk", false);
			xhttp.setRequestHeader("Content-type", "application/json");
			xhttp.send(JSON.stringify(jsonLocalizacaoUsuario));
		}
		
		//Se for selecionada a opção do navegador, solicita a permissão para o usuário
		else if (modo == "Browser") {
		
			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(function (pos) { //Em caso de sucesso					
				
				_marcadorUsuario = new google.maps.Marker({
									map: _mapa,
									title: 'Sua Localização',
									position: {lat: pos.coords.latitude, lng: pos.coords.longitude},
									icon: './content/images/blue-dot.png'									
									});				
					_mapa.panTo({lat: pos.coords.latitude, lng: pos.coords.longitude});
				}, function() { //Em caso de erro
					alert('Não foi possível obter a sua localização.');
				});
			} else {
				alert('Esse modo não é suportado!');
			}		
		}		
	}
	
	function obterDetalhesUnidade(marcador) {
		var servico = new google.maps.places.PlacesService(_mapa);

		servico.getDetails({
			placeId: marcador.id
			}, function (place, status) {

				if (status === google.maps.places.PlacesServiceStatus.OK) {

					//Setando o marcador da infoWindow para que ele não seja criado de novo
					_infoWindow.marker = marcador;

					var innerHTML = '<div style=\"max-width: 400px;\" class=\"center-align\">'
					//Se o objeto de resposta (place) possuir os dados, monta a infoWindow
					if (place.name) {
						innerHTML += '<h5>' + place.name + '</h5>';
					}
					if (place.formatted_address) {
						innerHTML += '<br><p style=\"max-width: 400px;\">' + place.formatted_address + '</p>';
					}
					if (place.formatted_phone_number) {
						innerHTML += '<br>' + place.formatted_phone_number;
					}
					if (place.website) {
						innerHTML += '<br><a href=\"' + place.website + '\" target=\"_blank\">Link para o website</a>';
					}
					if (place.photos) {
						innerHTML += '<br><br><img src=\"' + place.photos[0].getUrl({maxHeight: 250, maxWidth: 450}) + '\"' +
									 ' alt=\"Imagem da unidade\">';
					}
					if (place.geometry.location) {		
						innerHTML += '<div><a class=\"waves-effect waves-light btn\" onclick=\"mostrarRota(&quot;'+ place.formatted_address + '&quot;);\"><i class=\"material-icons left\">directions</i>Visualizar rota</a></div>';
					}
					innerHTML += '</div>';
					_infoWindow.setContent(innerHTML);
					_infoWindow.open(_mapa, marcador);

					//Remover a infoWindow do marcador ao fechá-lo
					_infoWindow.addListener('closeclick', function() {
					_infoWindow.marker = null;
					});
				}
				else {
					alert('Não foi possível obter os dados dessa unidade');
				}//fim if-else status == OK
			});
	}

	function mostrarRota(pDestino) {
		var directionsService = new google.maps.DirectionsService;

		if (_marcadorUsuario) {

			directionsService.route({
				origin: _marcadorUsuario.position,
				destination: pDestino,
				travelMode: 'DRIVING'
			}, function (response, status) {
					if (status === google.maps.DirectionsStatus.OK) {
						_rotaTracada = new google.maps.DirectionsRenderer({
							map : _mapa,
							draggable: true,
							directions: response,
							polylineOptions: {
								strokeColor: 'green'
							}
						});

						//Escondendo a infoWindow
						_infoWindow.close()
						//Ocultando os marcadores para mostrar somente as directions
						alternarVisaoMarcadores();

						//Habilitando o botão de limpar a rota
						$('#btn-rota').click(limparRotaTracada);
						$('#btn-rota').removeClass('disabled');

					} else {
						alert('Não foi possível traçar a rota devido a ' + status);
					}
				});
		} else {
			alert('Não foi possível traçar a rota, pois a sua localizaçao não foi encontrada!');
		}
	}

	function alternarVisaoMarcadores() {

		if(_marcadoresVisiveis) {
			//Se os marcadores estiverem visíveis, percorre a lista ocultando-os
			for (var i = 0; i < _marcadores.length; i++) {
				//Removendo os marcadores do mapa
				_marcadores[i].setMap(null);
			}

			//Alternando para o proximo clique
			_marcadoresVisiveis = false;
		}
		else {
			//Senão os coloca no mapa
			var limitesMapa = new google.maps.LatLngBounds();

			for (var i = 0; i < _marcadores.length; i++) {
				//Setando o mapa que o marcador pertence
				_marcadores[i].setMap(_mapa);
				//Aumenta os limites da visão do mapa para cada marcador adicionado
				limitesMapa.extend(_marcadores[i].position);
			}

			//Ajustando a visão do mapa para exibir todos os marcadores
			_mapa.fitBounds(limitesMapa);

			//Alternando para o proximo clique
			_marcadoresVisiveis = true;
		}
	}//fim function alternarVisaoMarcadores

	function limparRotaTracada () {

		if (_rotaTracada && _rotaTracada.map != null) {
			//Removendo a rotaTracada do mapa
			_rotaTracada.setMap(null);
			//Desabilitando o botão até que o usuário veja outra rota
			$('#btn-rota').addClass('disabled');
		}
	}//fim function alternarVisaoRotaCalculada
	</script>

	<script async defer
		src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDZ6mZ9nbSJ_hPoaUH-7hizoT32pJQnzCk&libraries=places,geometry&callback=iniciarMapa"></script>
</body>
</html>

