<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Übersicht</title>
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
    <div class="container">
      <div class="sidebar">
        <ul>
          <li><a href="index.php"><i class="fas fa-home"></i> Übersicht</a></li>
          <li><a href="bans.php"><i class="fas fa-ban"></i> Bans</a></li>
          <li><a href="mutes.php"><i class="fas fa-volume-mute"></i> Mutes</a></li>
          <li><a href="livechat.php"><i class="fas fa-comment"></i> Livechat</a></li>
          <li><a href="reports.php"><i class="fas fa-flag"></i> Reports</a></li>
          <li><a href="accounts.php"><i class="fas fa-users"></i> Accounts</a></li>
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
              <li><a href="accounts.php">Accounts</a></li>
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
          <div class="flex item-1">
            <?php
            if(isset($_POST["gdisable"])){
              require("mysql.php");
              $stmt = $mysql->prepare("UPDATE accounts SET GOOGLE_AUTH = 'null' WHERE USERNAME = :user");
              $stmt->bindParam(":user", $_SESSION["username"], PDO::PARAM_STR);
              $stmt->execute();
            }
            if(hasGoogleAuth($_SESSION["username"])){
              ?>
              <h1>Google Authenticator</h1>
              <p>Du schütze derzeit deinen Account mit der 2-Faktor Authentifizierung.</p>
              <br>
              <form action="google-auth.php" method="post">
                <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                <button type="submit" name="gdisable"><i class="fas fa-lock-open"></i> Deaktivieren</button>
              </form>
              <?php
            } else {
              ?>
              <h1>Google Authenticator</h1>
              <p>Schütze deinen Account mit der 2-Faktor Authentifizierung.</p>
              <?php
              //Erstelle Token
              require("./authmanager.php");
              $ga = new PHPGangsta_GoogleAuthenticator();
              $secret = $ga->createSecret();
              //POST Abfragen
              if(!isset($_POST["gactivate"])){
                ?>
                <a href='https://itunes.apple.com/de/app/google-authenticator/id388497605?mt=8'><img height="125" width="323" alt='Jetzt im App Store' src='https://delta.chat/assets/home/get-it-on-ios.png'/></a>
                <a href='https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=de&pcampaignid=MKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1'><img height="125" width="323" alt='Jetzt bei Google Play' src='https://play.google.com/intl/en_us/badges/images/generic/de_badge_web_generic.png'/></a>
                <?php
              } else {
                require("mysql.php");
                $stmt = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = :user");
                $stmt->bindParam(":user", $_SESSION["username"], PDO::PARAM_STR);
                $stmt->execute();
                if($row = $stmt->fetch()){
                  if(!hasGoogleAuth($_SESSION["username"])){
                    $qrCodeUrl = $ga->getQRCodeGoogleUrl('ProfessionalBans', $secret);
                    echo '<img src="'.$qrCodeUrl.'" class="normal">';
                    $_SESSION["gtoken"] = $secret;
                  }
                }
              }
              ?>
              <form action="google-auth.php" method="post">
                <?php
                if(!isset($_POST["gactivate"])){
                  ?>
                  <button type="submit" name="gactivate"><i class="fas fa-mobile-alt"></i> Aktivieren</button>
                  <?php
                } else {
                  ?>
                  <button type="submit" name="gfinish"><i class="fas fa-lock"></i> Einrichtung abschließen</button>
                  <?php
                }
                ?>
              </form>
              <?php
              if(isset($_POST["gfinish"])){
                require("mysql.php");
                $stmt = $mysql->prepare("UPDATE accounts SET GOOGLE_AUTH = :auth WHERE USERNAME = :user");
                $stmt->bindParam(":user", $_SESSION["username"], PDO::PARAM_STR);
                $stmt->bindParam(":auth", $_SESSION["gtoken"], PDO::PARAM_STR);
                $stmt->execute();
                unset($_SESSION["gtoken"]);
                echo '<meta http-equiv="refresh" content="0; URL=google-auth.php">';
              }
            }
             ?>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
