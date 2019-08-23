<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Account bearbeiten</title>
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
          <li class="active"><a href="accounts.php"><i class="fas fa-users"></i> Accounts</a></li>
          <li><a href="reasons.php"><i class="fas fa-cogs"></i> Bangründe</a></li>
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
              <li class="active"><a href="accounts.php">Accounts</a></li>
              <li><a href="reasons.php">Bangründe</a></li>
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
            if(!isset($_GET["name"])){
              showModalRedirect("ERROR", "Fehler", "Es wurde keine Anfrage gestellt.", "accounts.php");
              exit;
            }
            if(isset($_POST["submit"]) && isset($_SESSION["CSRF"])){
              if($_POST["CSRFToken"] != $_SESSION["CSRF"]){
                showModal("ERROR", "CSRF Fehler", "Deine Sitzung ist abgelaufen. Versuche die Seite erneut zu öffnen.");
              } else {
                require("./mysql.php");
                //Fetch UUID from Userinput
                $stmt = $mysql->prepare("SELECT UUID FROM bans WHERE NAME = :username");
                $stmt->bindParam(":username", $_POST["mcusername"], PDO::PARAM_STR);
                $stmt->execute();
                while($row = $stmt->fetch()){
                  $uuid = $row["UUID"];
                }
                if(isPlayerExists($uuid)){
                  $rankint = (int) $_POST["rang"];
                  if(!empty($_POST["pw"])){
                    $hash = password_hash($_POST["pw"], PASSWORD_BCRYPT);
                    $stmt = $mysql->prepare("UPDATE accounts SET UUID = :uuid, RANK = :rank, PASSWORD = :pw WHERE USERNAME = :user");
                    $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
                    $stmt->bindParam(":rank", $rankint, PDO::PARAM_INT);
                    $stmt->bindParam(":user", $_GET["name"], PDO::PARAM_STR);
                    $stmt->bindParam(":pw", $hash, PDO::PARAM_STR);
                    $stmt->execute();
                  } else {
                    $stmt = $mysql->prepare("UPDATE accounts SET UUID = :uuid, RANK = :rank WHERE USERNAME = :user");
                    $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
                    $stmt->bindParam(":rank", $rankint, PDO::PARAM_INT);
                    $stmt->bindParam(":user", $_GET["name"], PDO::PARAM_STR);
                    $stmt->execute();
                  }
                  if(hasGoogleAuth($_GET["name"])){
                    $statusint = (int) $_POST["gauth"];
                    if($statusint == 0){
                      $stmt = $mysql->prepare("UPDATE accounts SET GOOGLE_AUTH = 'null' WHERE USERNAME = :user");
                      $stmt->bindParam(":user", $_GET["name"], PDO::PARAM_STR);
                      $stmt->execute();
                    }
                  }
                  showModalRedirect("SUCCESS", "Erfolgreich", "Der Benutzer wurde erfolgreich aktualisiert.", "editaccount.php?name=".$_GET["name"]);
                } else {
                  showModal("ERROR", "Fehler", "Der eingegebene Minecraft Account hat das Netzwerk noch nie betreten.");
                }
              }
            } else {
              //Erstelle Token wenn Formular nicht abgesendet wurde
              $_SESSION["CSRF"] = generateRandomString(25);
            }
             ?>
            <h1>Account bearbeiten von <?php echo $_GET["name"]; ?></h1>
            <form action="editaccount.php?name=<?php echo $_GET["name"]; ?>" method="post">
              <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
              <?php
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = :username");
              $stmt->bindParam(":username", $_GET['name'], PDO::PARAM_STR);
              $stmt->execute();
              while($row = $stmt->fetch()){
                ?>
                <p>Verknüpfter Minecraft Account</p>
                <input type="text" name="mcusername" placeholder="Verknüpfter Minecraft Account" value="<?php echo UUIDResolve($row["UUID"]); ?>" required><br>
                <p>Neues Passwort festlegen</p>
                <input type="password" name="pw" placeholder="Neues Passwort festlegen"><br>
                <p>Rang</p>
                <select name="rang">
                  <?php
                  if(isAdmin($_GET["name"])){
                    ?>
                    <option value="3">Admin</option>
                    <option value="2">Moderator</option>
                    <option value="1">Supporter</option>
                    <?php
                  } else if(isMod($_GET["name"])){
                    ?>
                    <option value="2">Moderator</option>
                    <option value="3">Admin</option>
                    <option value="1">Supporter</option>
                    <?php
                  } else if(isSup($_GET["name"])){
                    ?>
                    <option value="1">Supporter</option>
                    <option value="3">Admin</option>
                    <option value="2">Moderator</option>
                    <?php
                  }
                   ?>
                </select>
                <?php
                if(hasGoogleAuth($_GET["name"])){
                  ?>
                  <p>Google Authenticator</p>
                  <select name="gauth">
                    <option value="1">Aktiviert</option>
                    <option value="0">Deaktiviert</option>
                  </select>
                  <?php
                }
              }
               ?>
              <button type="submit" name="submit">Speichern</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
