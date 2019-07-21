<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Chatlog</title>
    <link rel="stylesheet" href="../css/master.css">
    <link rel="stylesheet" href="../css/login.css">
    <style media="screen">
      .flex-chat p{
        color: white;
      }
      hr{
        border: 0;
        background: white;
        height: 1px;
        width: 180px;
      }
    </style>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="css/favicon.ico">
  </head>
  <body>
    <form class="login" action="login.php" method="post">
      <?php
      if(!isset($_GET["id"])){
        ?>
        <div class="error">
          <h4>Es wurde kein Chatlog angefodert.</h4>
        </div>
        <?php
        exit;
      } else {
        //VALIDATE the given CHATLOG ID
        require '../mysql.php';
        $stmt = $mysql->prepare("SELECT * FROM chatlog WHERE LOGID = :id");
        $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_STR);
        $stmt->execute();
        $val = 0;
        while ($row = $stmt->fetch()) {
          $val++;
          //is chatlog in database?
        }
        if($val == 0){
          //BAD REQUEST
          ?>
          <div class="error">
            <h4>Der angefoderte Chatlog wurde nicht gefunden.</h4>
          </div>
          <?php
      exit;
        }
        //All okay loading page...
      }

      function UUIDResolve($uuid){
        require("../mysql.php");
        $stmt = $mysql->prepare("SELECT NAME, UUID FROM bans WHERE UUID = :uuid");
        $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
        $stmt->execute();
        while($row = $stmt->fetch()){
          if($row["UUID"] == $uuid){
            return $row["NAME"];
          }
        }
      }
      function getCreator($id){
        require("../mysql.php");
        $stmt = $mysql->prepare("SELECT * FROM chatlog WHERE LOGID = :id");
        $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_STR);
        $stmt->execute();
        while ($row = $stmt->fetch()){
          if($row["CREATOR_UUID"] != "KONSOLE"){
            return UUIDResolve($row["CREATOR_UUID"]);
          } else {
            return "KONSOLE";
          }
        }
      }
      function getPlayer($id){
        require("../mysql.php");
        $stmt = $mysql->prepare("SELECT * FROM chatlog WHERE LOGID = :id");
        $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_STR);
        $stmt->execute();
        while ($row = $stmt->fetch()){
          return UUIDResolve($row["UUID"]);
        }
      }
      function getDateRAW($id){
        require("../mysql.php");
        $stmt = $mysql->prepare("SELECT * FROM chatlog WHERE LOGID = :id");
        $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_STR);
        $stmt->execute();
        while ($row = $stmt->fetch()){
          return $row["CREATED_AT"];
        }
      }
      function getServer($id){
        require("../mysql.php");
        $stmt = $mysql->prepare("SELECT * FROM chatlog WHERE LOGID = :id");
        $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_STR);
        $stmt->execute();
        while ($row = $stmt->fetch()){
          return $row["SERVER"];
        }
      }
       ?>
      <h1>Chatlog</h1>
      <p>Dieser Chatlog ist ein gültiger Beweis bei einem Report.</p>
      <p></p>
      <p>Weitere Details zu diesem Chatlog:<br><br>
        Spieler: <?php echo getPlayer($_GET["id"]); ?><br>
        Ersteller: <?php echo getCreator($_GET["id"]); ?><br>
        Datum: <?php echo date('d.m.Y H:i',getDateRAW($_GET["id"])/1000); ?><br>
        Server: <?php echo getServer($_GET["id"]); ?></p>
      <hr>
      <?php
      $stmt = $mysql->prepare("SELECT * FROM chatlog WHERE LOGID = :id ORDER BY SENDDATE DESC");
      $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_STR);
      $stmt->execute();
      $count = $stmt->rowCount();
      ?>
      <p>Es wurden <strong><?php echo $count; ?> Nachrichten</strong> geloggt.</p>
      <?php
      while($row = $stmt->fetch()){
        ?>
        <div class="flex-chat">
          <img src="https://minotar.net/helm/<?php echo UUIDResolve($row["UUID"]); ?>/64.png" alt="head" class="mchead">
          <p><?php echo htmlspecialchars($row["MESSAGE"]); ?></p>
          <div class="chat-info">
            <p><?php echo UUIDResolve($row["UUID"]); ?> | <?php echo date("H:i", $row["SENDDATE"] / 1000); ?></p>
          </div>
        </div>
        <?php
      }
       ?>
    </form>
  </body>
</html>
