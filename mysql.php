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
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e){
    if($host == "localhost" && $name == "professionalbans" && $user == "root" && $passwort == ""){
        ?>
        <div class="error">
            <h3>Bitte stelle deine MySQL Datenbank in der mysql.php ein.</h3>
        </div>
        <?php
    } else {
        ?>
        <div class="error">
            <h3>Ein Fehler mit der MySQL Datenbank ist aufgetreten.</h3>
            <p><?php echo $e->getMessage() ?></p>
        </div>
        <?php
    }
}
 ?>
