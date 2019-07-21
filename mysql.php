<?php
/////////////////////////////////////////////////
// Stelle hier deine Datenbankverbindung ein!
/////////////////////////////////////////////////
$host = "dathost1.de";
$name = "developer";
$user = "developer";
$passwort = "46YTwwDVe4kL";
/////////////////////////////////////////////////
try{
    $mysql = new PDO("mysql:host=$host;dbname=$name", $user, $passwort);
} catch (PDOException $e){
    echo "SQL Error: ".$e->getMessage();
}
 ?>
