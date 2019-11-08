<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Bangrund bearbeiten</title>
    <link rel="stylesheet" href="css/master.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/jquery.sweet-modal.min.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="js/jquery.sweet-modal.min.js"></script>
    <link rel="shortcut icon" type="image/x-icon" href="css/favicon.ico">
    <meta name="viewport"
      content="width=device-width,
               initial-scale=1.0,
               minimum-scale=1.0">
  </head>
  <?php

  session_start();
  if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit;
  }

  require("./datamanager.php");
  if(isInitialPassword($_SESSION['username'])){
    header("Location: resetpassword.php?name=".$_SESSION['username']);
    exit;
  }

  validateSession();

   ?>
  <body>
    <?php
    if(!isAdmin($_SESSION['username'])){
      showModalRedirect("ERROR", "Fehler", "Der Zugriff auf diese Seite wurde verweigert.", "index.php");
      exit;
    }
    ?>
    <div class="container">
      <div class="sidebar">
        <ul>
          <li><a href="index.php"><i class="fas fa-home"></i> Übersicht</a></li>
          <li><a href="bans.php"><i class="fas fa-ban"></i> Bans</a></li>
          <li><a href="mutes.php"><i class="fas fa-volume-mute"></i> Mutes</a></li>
          <li><a href="livechat.php"><i class="fas fa-comment"></i> Livechat</a></li>
          <li><a href="reports.php"><i class="fas fa-flag"></i> Reports</a></li>
          <li><a href="accounts.php"><i class="fas fa-users"></i> Accounts</a></li>
          <li class="active"><a href="reasons.php"><i class="fas fa-cogs"></i> Bangründe</a></li>
          <li><a href="unbans.php"><i class="fas fa-envelope"></i> Entbannungsanträge</a></li>
        </ul>
      </div>
      <div class="header">
        <!-- Trigger for mobile devices -->
        <i class="fas fa-bars fa-2x menu mobileicon"></i>
        <a href="logout.php"><i class="fas fa-sign-out-alt fa-2x headericon"></i></a>
      </div>
      <div class="content">
        <!-- START Mobile Menu -->
        <div class="mobilenavbar">
          <nav>
            <ul class="navbar animated bounceInDown">
              <!-- Menu for mobile devices -->
              <li><a href="index.php">Übersicht</a></li>
              <li><a href="bans.php">Bans</a></li>
              <li><a href="mutes.php">Mutes</a></li>
              <li><a href="livechat.php">Livechat</a></li>
              <li><a href="reports.php">Reports</a></li>
              <li><a href="accounts.php">Accounts</a></li>
              <li class="active"><a href="reasons.php">Bangründe</a></li>
              <li><a href="unbans.php">Entbannungsanträge</a></li>
            </ul>
          </nav>
        </div>
        <script type="text/javascript">
        $(document).ready(function(){
          $('.menu').click(function(){
            $('ul').toggleClass("navactive");
          })
        })
        </script>
        <!-- END Mobile Menu -->
        <div class="flex-container animated fadeIn">
          <div class="flex item-1 sidebox">
            <?php
            if(!isset($_GET["id"])){
              showModalRedirect("ERROR", "Fehler", "Es wurde keine Anfrage gestellt.", "reasons.php");
              exit;
            }
            if(isset($_POST["submit"]) && isset($_SESSION["CSRF"])){
              if($_POST["CSRFToken"] != $_SESSION["CSRF"]){
                showModal("ERROR", "CSRF Fehler", "Deine Sitzung ist abgelaufen. Versuche die Seite erneut zu öffnen.");
              } else {
                require("./mysql.php");
                $id = $_GET["id"];
                if(filter_var($_POST['zeit'], FILTER_VALIDATE_INT)){
                  $zeit = $_POST['zeit'];
                } else {
                  showModalRedirect("ERROR", "Fehler", "Du hast keine gültige Zahl angegeben.", "editreason.php?id=".$_GET["id"]);
                  exit;
                }

                $stmt = $mysql->prepare("SELECT * FROM reasons WHERE id = :id");
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                $stmt->execute();
                $data = 0;
                while($row = $stmt->fetch()){
                      $data++;
                }
                if($data == 1){
                  if($_POST["einheit"] == "m"){
                    $minuten = $zeit;
                  } else if($_POST["einheit"] == "s"){
                    $minuten = $zeit * 60;
                  } else if($_POST["einheit"] == "t"){
                    $minuten = $zeit * 60 * 24;
                  }
                  if($_POST["type"] == "ban"){
                    $type = 0;
                  } else if($_POST["type"] == "mute"){
                    $type = 1;
                  } else if($_POST["type"] == "permaban"){
                    $type = 0;
                    $minuten = -1;
                  } else if($_POST["type"] == "permamute"){
                    $type = 1;
                    $minuten = -1;
                  }

                    //Update Grund
                    $uhrzeit = time();
                    $stmt = $mysql->prepare("UPDATE reasons SET REASON = :grund, TIME = :min, TYPE = :type, PERMS = :perms WHERE ID = :id");
                    $stmt->bindParam(":grund", $_POST['grund'], PDO::PARAM_STR);
                    $stmt->bindParam(":min", $minuten, PDO::PARAM_INT);
                    $stmt->bindParam(":type", $type, PDO::PARAM_INT);
                    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                    if($_POST["perms"] != ""){
                      $perms = $_POST["perms"];
                    } else {
                      $perms = "null";
                    }
                    $stmt->bindParam(":perms", $perms, PDO::PARAM_STR);
                    $stmt->execute();
                    showModalRedirect("SUCCESS", "Erfolgreich", "Der Grund <strong>".htmlspecialchars($_POST["grund"])."</strong> wurde erfolgreich bearbeitet.", "editreason.php?id=".$_GET["id"]);
                } else {
                  showModal("ERROR", "Fehler", "Diese ID ist nicht registriert.");
                }
                exit;
              }
            } else {
              //Erstelle Token wenn Formular nicht abgesendet wurde
              $_SESSION["CSRF"] = generateRandomString(25);
            }
             ?>
            <h1>Bangrund bearbeiten #<strong><?php echo $_GET["id"]; ?></strong></h1>
            <form action="editreason.php?id=<?php echo $_GET["id"]; ?>" method="post">
              <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
              <p>Grund</p>
              <input type="text" name="grund" value="<?php echo getReasonByReasonID($_GET["id"]) ?>" maxlength="16" required/>
              <p>Zeit</p>
              <input type="number" name="zeit" value="<?php
              if(getTimeByReasonID($_GET["id"]) < 60){
                echo getTimeByReasonID($_GET["id"]);
              } else if(getTimeByReasonID($_GET["id"]) > 1439){
                $type = 2;
                echo getTimeByReasonID($_GET["id"]) / 60 / 24;
              } else {
                $type = 1;
                echo getTimeByReasonID($_GET["id"]) / 60;
              }
               ?>" required/>
               <p>Permission</p>
               <input type="text" name="perms" value="<?php echo getPermsByReasonID($_GET["id"]) ?>"/>
               <p>Einheit</p>
               <select name="einheit">
                 <?php
                 if($type == 0){
                   echo '<option value="m">Minuten</option>
                   <option value="s">Stunden</option>
                   <option value="t">Tage</option>';
                 } else if($type == 1){
                   echo '<option value="s">Stunden</option>
                   <option value="m">Minuten</option>
                   <option value="t">Tage</option>';
                 } else if($type == 2){
                   echo '<option value="t">Tage</option>
                   <option value="s">Stunden</option>
                   <option value="m">Minuten</option>';
                 }
                  ?>
               </select>
               <p>Typ</p>
               <select name="type">
                 <?php
                 if(isMuteByReasonID($_GET["id"]) && getTimeByReasonID($_GET["id"]) == -1){
                   echo '<option value="permamute">Permanenter Mute</option>
                   <option value="ban">Ban</option>
                   <option value="mute">Mute</option>
                   <option value="permaban">Permanenter Ban</option>';
                 } if(isMuteByReasonID($_GET["id"]) && getTimeByReasonID($_GET["id"]) != -1){
                   echo '<option value="mute">Mute</option>
                   <option value="ban">Ban</option>
                   <option value="permaban">Permanenter Ban</option>
                   <option value="permamute">Permanenter Mute</option>';
                 } else if(!isMuteByReasonID($_GET["id"]) && getTimeByReasonID($_GET["id"]) == -1){
                   echo '<option value="permaban">Permanenter Ban</option>
                   <option value="ban">Ban</option>
                   <option value="mute">Mute</option>
                   <option value="permamute">Permanenter Mute</option>';
                 } else if(!isMuteByReasonID($_GET["id"]) && getTimeByReasonID($_GET["id"]) != -1){
                   echo '<option value="ban">Ban</option>
                   <option value="mute">Mute</option>
                   <option value="permaban">Permanenter Ban</option>
                   <option value="permamute">Permanenter Mute</option>';
                 }
                  ?>
               </select>
              <button type="submit" name="submit">Speichern</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
