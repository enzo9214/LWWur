<?php
include "../sql/sql.php";
$IdVotos =$_POST['IdVotos'];
$sql =  new sql();
$nombre=$_POST['Nombre'];
$tipo=$_POST['Tipo'];
if($tipo == "Cancion")
    $query = $sql->sql_s("SELECT * FROM tracks WHERE title LIKE '".$nombre."%'");
if($tipo=="Artista")
    $query = $sql->sql_s("SELECT * FROM artists WHERE name LIKE '".$nombre."%'");

echo json_encode($query);
?>