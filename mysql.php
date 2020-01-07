<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/////////////////////////////////////////////////
// Stelle hier deine Datenbankverbindung ein!
/////////////////////////////////////////////////
$host = "localhost";
$name = "professionalbans";
$user = "root";
$passwort = "";
/////////////////////////////////////////////////
try {
    $mysql = new PDO("mysql:host=$host;dbname=$name;charset=utf8", $user, $passwort);
} catch (PDOException $e) {
    echo "SQL Error: " . $e->getMessage();
}

function MySQLWrapper()
{
    global $mysql;
    return $mysql;
}
