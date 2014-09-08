<?php
include "../sql/sql.php";
$IdVotos =$_POST['IdVotos'];
$sql =  new sql();
$query = $sql->sql_i("update tracks set Votos = Votos + 1 where id ='$IdVotos'");
?>