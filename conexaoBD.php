<?php
  $bdhost = "localhost";
  $bdusuario = "root";
  $bdsenha = "";
  $baseDados = "mashupBD";

  // Cria a conexão
  $conn = new mysqli($bdhost, $bdusuario, $bdsenha, $baseDados);
  $conn->set_charset('utf8');

  // Verifica conexão
  if ($conn->connect_error) {
    die("Conexão ao MySQL falhou: " . $conn->connect_error);
  }

?>