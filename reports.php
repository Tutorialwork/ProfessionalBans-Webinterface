<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Reports</title>
    <link rel="stylesheet" href="css/master.css">
    <link rel="stylesheet" href="css/animate.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <link rel="stylesheet" href="css/jquery.sweet-modal.min.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
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
    if(!isMod($_SESSION['username'])){
      showModalRedirect("ERROR", "Fehler", "Der Zugriff auf diese Seite wurde verweigert.", "index.php");
      exit;
    }
    ?>
    <div class="container">
      <div class="sidebar">
        <ul>
        <li><a href="index.php"><i class="fas fa-home"></i> Übersicht</a></li>
          <?php
          if(isMod($_SESSION["username"])){
            ?>
            <li><a href="bans.php"><i class="fas fa-ban"></i> Bans</a></li>
            <?php
          }
          ?>
          <li><a href="mutes.php"><i class="fas fa-volume-mute"></i> Mutes</a></li>
          <li><a href="livechat.php"><i class="fas fa-comment"></i> Livechat</a></li>
          <?php
          if(isMod($_SESSION["username"])){
            ?>
            <li class="active"><a href="reports.php"><i class="fas fa-flag"></i> Reports</a></li>
            <?php
          }
          ?>
          <?php
          if(isAdmin($_SESSION["username"])){
            ?>
            <li><a href="accounts.php"><i class="fas fa-users"></i> Accounts</a></li>
            <li><a href="reasons.php"><i class="fas fa-cogs"></i> Bangründe</a></li>
            <?php
          }
          ?>
          <?php
          if(isMod($_SESSION["username"])){
            ?>
            <li><a href="unbans.php"><i class="fas fa-envelope"></i> Entbannungsanträge</a></li>
            <?php
          }
          ?>
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
              <li><a href="index.php"><i class="fas fa-home"></i> Übersicht</a></li>
          <?php
          if(isMod($_SESSION["username"])){
            ?>
            <li><a href="bans.php"><i class="fas fa-ban"></i> Bans</a></li>
            <?php
          }
          ?>
          <li><a href="mutes.php"><i class="fas fa-volume-mute"></i> Mutes</a></li>
          <li><a href="livechat.php"><i class="fas fa-comment"></i> Livechat</a></li>
          <?php
          if(isMod($_SESSION["username"])){
            ?>
            <li class="active"><a href="reports.php"><i class="fas fa-flag"></i> Reports</a></li>
            <?php
          }
          ?>
          <?php
          if(isAdmin($_SESSION["username"])){
            ?>
            <li><a href="accounts.php"><i class="fas fa-users"></i> Accounts</a></li>
            <li><a href="reasons.php"><i class="fas fa-cogs"></i> Bangründe</a></li>
            <?php
          }
          ?>
          <?php
          if(isMod($_SESSION["username"])){
            ?>
            <li><a href="unbans.php"><i class="fas fa-envelope"></i> Entbannungsanträge</a></li>
            <?php
          }
          ?>
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
            if(!isset($_GET["archiv"])){
              ////////////////////////////////////////
              // Show only reports flagged as unedited
              ////////////////////////////////////////
              if(isset($_GET["done"]) && isset($_GET["id"])){
                require("mysql.php");
                $uuid = "null";
                $stmt2 = $mysql->prepare("SELECT NAME, UUID FROM bans WHERE NAME = :name");
                $name = $_SESSION["username"];
                $stmt2->bindParam(":name", $name, PDO::PARAM_STR);
                $stmt2->execute();
                $row = $stmt2->fetch();
                $uuid = $row["UUID"];
                $stmt = $mysql->prepare("UPDATE reports SET status = 1, TEAM = :webuser WHERE ID = :id");
                $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_INT);
                $stmt->bindParam(":webuser", $uuid, PDO::PARAM_STR);
                $stmt->execute();
                showModalRedirect("SUCCESS", "Erfolgreich", "Der Report wurde erfolgreich als <strong>bearbeitet</strong> makiert.", "reports.php");
              }
              ?>
              <div class="flex-button">
                <a href="reports.php?archiv" class="btn"><i class="fas fa-book-open"></i> Archiv</a>
                <a href="chatlogs.php" class="btn"><i class="fas fa-comments"></i> Chatlogs</a>
              </div>
              <h1>Offene Reports</h1>
              <table>
                <tr>
                  <th>Spieler</th>
                  <th>Grund</th>
                  <th>erstellt am</th>
                  <th>erstellt von</th>
                  <th>Aktionen</th>
                </tr>
                <tr>
                  <?php
                  require("./mysql.php");
                  $stmt = $mysql->prepare("SELECT * FROM reports");
                  $stmt->execute();
                  while($row = $stmt->fetch()){
                    if($row["STATUS"] == 0){
                      echo "<tr>";
                      echo '<td>'.UUIDResolve($row["UUID"]).'</td>';
                      echo '<td>'.$row["REASON"].'</td>';
                      echo '<td>'.date('d.m.Y H:i',$row["CREATED_AT"]/1000).'</td>';
                      if($row["REPORTER"] != "KONSOLE"){
                        echo '<td>'.UUIDResolve($row["REPORTER"]).'</td>';
                      } else {
                        echo "<td>KONSOLE</td>";
                      }
                      echo '<td><a class="btn" href="reports.php?done&id='.$row["ID"].'"><i class="material-icons">done</i></a></td>';
                      echo "</tr>";
                    }
                  }
                   ?>
                </tr>
              </table>
              <?php
            } else {
              ////////////////////////////////////////
              // Show all reports
              ////////////////////////////////////////
              ?>
              <div class="flex-button">
                <a href="reports.php" class="btn"><i class="fas fa-eye-slash"></i> Offene Reports</a>
              </div>
              <h1>Alle Reports</h1>
              <table>
                <tr>
                  <th>Spieler</th>
                  <th>Grund</th>
                  <th>erstellt am</th>
                  <th>erstellt von</th>
                  <th>bearbeitet von</th>
                  <th>Status</th>
                </tr>
                <tr>
                  <?php
                  require("./mysql.php");
                  $stmt = $mysql->prepare("SELECT * FROM reports");
                  $stmt->execute();
                  while($row = $stmt->fetch()){
                    echo "<tr>";
                    echo '<td>'.UUIDResolve($row["UUID"]).'</td>';
                    echo '<td>'.$row["REASON"].'</td>';
                    echo '<td>'.date('d.m.Y H:i',$row["CREATED_AT"]/1000).'</td>';
                    if($row["REPORTER"] != "KONSOLE"){
                      echo '<td>'.UUIDResolve($row["REPORTER"]).'</td>';
                    } else {
                      echo "<td>KONSOLE</td>";
                    }
                    echo '<td>'.UUIDResolve($row["TEAM"]).'</td>';
                    if($row["STATUS"] == 0){
                      echo '<td><p style="color: red;">Offen</td>';
                    } else {
                      echo '<td><p style="color: green;">Erledigt</td>';
                    }
                    echo "</tr>";
                  }
                   ?>
                </tr>
              </table>
              <?php
            }
             ?>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
