<?php
/////////////////////////////////////////////////
// Stelle hier deine Datenbankverbindung ein!
/////////////////////////////////////////////////
$host = "localhost";
$name = "professionalbans";
$user = "root";
$passwort = "";
/////////////////////////////////////////////////
try{
    $mysql = new PDO("mysql:host=$host;dbname=$name", $user, $passwort);
} catch (PDOException $e){
    echo "SQL Error: ".$e->getMessage();
}
 ?>
