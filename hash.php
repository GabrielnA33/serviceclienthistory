<?php
$password = "gnajoa01"; // La contrase単a en texto plano
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
echo "Hash generado: <br> $hashed_password";
?>
