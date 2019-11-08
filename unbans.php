<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Entbannungsanträge</title>
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
            <li><a href="reports.php"><i class="fas fa-flag"></i> Reports</a></li>
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
            <li class="active"><a href="unbans.php"><i class="fas fa-envelope"></i> Entbannungsanträge</a></li>
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
            <li><a href="reports.php"><i class="fas fa-flag"></i> Reports</a></li>
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
            <li class="active"><a href="unbans.php"><i class="fas fa-envelope"></i> Entbannungsanträge</a></li>
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
        <?php
        if(!isset($_GET["id"])){
          ?>
          <div class="flex-container animated fadeIn">
            <div class="flex item-1">
              <h1>Offene Entbannungsanträge</h1>
              <table>
                <tr>
                  <th>Spieler</th>
                  <th>Datum</th>
                  <th>Aktionen</th>
                </tr>
                <tr>
                  <?php
                  require("./mysql.php");
                  $stmt = $mysql->prepare("SELECT * FROM unbans WHERE STATUS = 0");
                  $stmt->execute();
                  while($row = $stmt->fetch()){
                    echo "<tr>";
                    echo '<td>'.UUIDResolve($row["UUID"]).'</td>';
                    echo '<td>'.date('d.m.Y H:i',$row["DATE"]).'</td>';
                    echo '<td><a href="unbans.php?id='.$row["ID"].'""><i class="fas fa-eye"></i></a> ';
                    echo "</tr>";
                  }
                   ?>
                </tr>
              </table>
            </div>
            <div class="flex item-1">
              <h1>Alle Entbannungsanträge</h1>
              <table>
                <tr>
                  <th>Spieler</th>
                  <th>Datum</th>
                  <th>Entscheidung</th>
                  <th>Aktionen</th>
                </tr>
                <tr>
                  <?php
                  require("./mysql.php");
                  $stmt = $mysql->prepare("SELECT * FROM unbans");
                  $stmt->execute();
                  while($row = $stmt->fetch()){
                    echo "<tr>";
                    echo '<td>'.UUIDResolve($row["UUID"]).'</td>';
                    echo '<td>'.date('d.m.Y H:i',$row["DATE"]).'</td>';
                    echo '<td>';
                    if($row["STATUS"] == 1){
                      echo "Ban aufgehoben";
                    } else if($row["STATUS"] == 2){
                      echo "Ban verkürzt";
                    } else if($row["STATUS"] == 3){
                      echo "Abgelehnt";
                    } else if($row["STATUS"] == 0){
                      echo "Ausstehend";
                    }
                    echo '</td>';
                    echo '<td><a href="unbans.php?id='.$row["ID"].'""><i class="fas fa-eye"></i></a> ';
                    echo "</tr>";
                  }
                   ?>
                </tr>
              </table>
            </div>
          </div>
          <?php
        } else {
          if(isset($_POST["submit"])){
            function getUUID($id){
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM unbans WHERE ID = :id");
              $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_INT);
              $stmt->execute();
              $row = $stmt->fetch();
              return $row["UUID"];
            }
            require("./mysql.php");
            $status = (int) $_POST["choose"];
            $stmt = $mysql->prepare("UPDATE unbans SET STATUS = :status WHERE ID = :id");
            $stmt->bindParam(":status", $status, PDO::PARAM_INT);
            $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_INT);
            $stmt->execute();
            if($status == 1){
              $uuid = getUUID($_GET["id"]);
              $stmt = $mysql->prepare("UPDATE bans SET BANNED = 0 WHERE UUID = :uuid");
              $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
              $stmt->execute();
            } else if($status == 2){
              //Verkürzen auf 3 Tage
              $uuid = getUUID($_GET["id"]);
              $time = 259200 * 1000;
              $javatime = round(time() * 1000) + round($time);
              $stmt = $mysql->prepare("UPDATE bans SET END = :end WHERE UUID = :uuid");
              $stmt->bindParam(":end", $javatime, PDO::PARAM_STR);
              $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
              $stmt->execute();
            }
            header("Location: unbans.php");
          }
          ?>
          <div class="flex-container animated fadeIn">
            <div class="flex item-1 sidebox">
              <h1>Entbannungsantrag anschauen</h1>
              <?php
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM unbans WHERE ID = :id");
              $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_INT);
              $stmt->execute();
              while($row = $stmt->fetch()){
                ?>
                <h5>Spieler</h5>
                <p><?php echo UUIDResolve($row["UUID"]); ?></p>
                <h5>Glaubst du der Ban war gerechtfertigt?</h5>
                <p><?php
                if($row["FAIR"] == 1){
                  ?>
                  Ja, aber ich sehe meinen Fehler ein
                  <?php
                } if($row["FAIR"] == 0){
                  ?>
                  Nein, ich habe nichts getan
                  <?php
                }
                 ?></p>
                 <h5>Nachricht</h5>
                 <p><?php echo $row["MESSAGE"]; ?></p>
                 <h5>Entbannungsantrag erstellt am</h5>
                 <p><?php echo date('d.m.Y H:i',$row["DATE"]); ?></p>
                 <?php
                 if($row["STATUS"] == 0){
                   ?>
                   <form action="unbans.php?id=<?php echo $_GET["id"]; ?>" method="post">
                     <select name="choose">
                       <option value="1">Akzeptieren und Ban aufheben</option>
                       <option value="2">Akzeptieren und Ban verkürzen</option>
                       <option value="3">Ablehnen</option>
                     </select>
                     <button type="submit" name="submit">Speichern</button>
                   </form>
                   <?php
                 }
                  ?>
                <?php
              }
               ?>
            </div>
          </div>
          <?php
        }
         ?>
      </div>
    </div>
  </body>
</html>
