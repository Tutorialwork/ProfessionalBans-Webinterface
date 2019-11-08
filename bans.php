<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Bans</title>
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
            <li class="active"><a href="bans.php"><i class="fas fa-ban"></i> Bans</a></li>
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
            <li class="active"><a href="bans.php"><i class="fas fa-ban"></i> Bans</a></li>
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
          if(!isset($_GET["ipbans"])){
            ?>
            <div class="flex-button">
              <a href="bans.php?ipbans" class="btn"><i class="fas fa-book-open"></i> IP-Bans</a>
            </div>
            <h1>Aktive Bans</h1>
            <table>
              <tr>
                <th>Spieler</th>
                <th>Grund</th>
                <th>gebannt bis</th>
                <th>gebannt von</th>
                <th>Aktionen</th>
              </tr>
              <tr>
                <?php
                require("./mysql.php");
                $stmt = $mysql->prepare("SELECT * FROM bans");
                $stmt->execute();
                while($row = $stmt->fetch()){
                  if($row["BANNED"] == 1){
                    echo "<tr>";
                    echo '<td>'.$row["NAME"].'</td>';
                    echo '<td>'.$row["REASON"].'</td>';
                    echo '<td>'.date('d.m.Y H:i',$row["END"]/1000).'</td>';
                    echo '<td>';
                    if($row["TEAMUUID"] == "KONSOLE"){
                      echo "Konsole";
                    } else {
                      echo UUIDResolve($row["TEAMUUID"]);
                    }
                    echo '</td>';
                    echo '<td><a href="bans.php?delete&name='.$row["NAME"].'"><i class="material-icons">block</i></a></td>';
                    echo "</tr>";
                  }
                }
                 ?>
              </tr>
            </table>
          <?php
          } else {
            //IP Bans
            ?>
            <div class="flex-button">
              <a href="bans.php" class="btn"><i class="fas fa-ban"></i> Bans</a>
            </div>
            <h1>Aktive IP-Bans</h1>
            <table>
              <tr>
                <th>IP</th>
                <th>Spieler</th>
                <th>Grund</th>
                <th>gebannt bis</th>
                <th>gebannt von</th>
                <th>Aktionen</th>
              </tr>
              <tr>
                <?php
                require("./mysql.php");
                $stmt = $mysql->prepare("SELECT * FROM ips");
                $stmt->execute();
                while($row = $stmt->fetch()){
                  if($row["BANNED"] == 1){
                    echo "<tr>";
                    echo '<td>'.$row["IP"].'</td>';
                    echo '<td>';
                    if($row["USED_BY"] != "null"){
                      echo UUIDResolve($row["USED_BY"]);
                    }
                    echo '</td>';
                    echo '<td>'.$row["REASON"].'</td>';
                    echo '<td>'.date('d.m.Y H:i',$row["END"]/1000).'</td>';
                    echo '<td>';
                    if($row["TEAMUUID"] == "KONSOLE"){
                      echo "Konsole";
                    } else {
                      echo UUIDResolve($row["TEAMUUID"]);
                    }
                    echo '</td>';
                    echo '<td><a href="bans.php?delete&ip='.$row["IP"].'"><i class="material-icons">block</i></a></td>';
                    echo "</tr>";
                  }
                }
                 ?>
              </tr>
            </table>
            <?php
          }
          ?>
          </div>
          <div class="flex item-2 sidebox">
            <?php
            if(isset($_POST["submit"]) && isset($_SESSION["CSRF"])){
              if($_POST["CSRFToken"] != $_SESSION["CSRF"]){
                showModal("ERROR", "CSRF Fehler", "Deine Sitzung ist abgelaufen. Versuche die Seite erneut zu öffnen.");
              } else {
                //Fetch UUID from Userinput
                $stmt = $mysql->prepare("SELECT UUID FROM bans WHERE NAME = :username");
                $stmt->bindParam(":username", $_POST["spieler"], PDO::PARAM_STR);
                $stmt->execute();
                while($row = $stmt->fetch()){
                  $uuid = $row["UUID"];
                }
                if(isPlayerExists($uuid)){
                  if(isBanned($uuid)){
                    showModalRedirect("ERROR", "Fehler", "Dieser Spieler ist bereits gebannt.", "bans.php");
                    exit;
                  }
                  $now = time();
                  if(getMinutesByReasonID($_POST["grund"]) != "-1"){ //Kein Perma Ban
                    $phpEND = $now + getMinutesByReasonID($_POST["grund"]) * 60;
                    $javaEND = $phpEND * 1000;
                  } else {
                    //PERMA BAN
                    $javaEND = -1;
                  }
                  $stmt = $mysql->prepare("UPDATE bans SET BANNED = 1, MUTED = 0, REASON = :reason, END = :end, TEAMUUID = :webUUID  WHERE UUID = :uuid");
                  $reason = getReasonByReasonID($_POST["grund"]);
                  $stmt->bindParam(":reason", $reason, PDO::PARAM_STR);
                  $stmt->bindParam(":end", $javaEND, PDO::PARAM_STR);

                  //UUID von User im Webinterface
                  $stmtUUID = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = :name");
                  $stmtUUID->bindParam(":name", $_SESSION["username"], PDO::PARAM_STR);
                  $stmtUUID->execute();
                  while($row = $stmtUUID->fetch()){
                    if($row["USERNAME"] == $_SESSION["username"]){
                      $useruuid = $row["UUID"];
                    }
                  }

                  $stmt->bindParam(":webUUID", $useruuid, PDO::PARAM_STR);
                  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
                  $stmt->execute();
                  addBanCounter($uuid);
                  showModalRedirect("SUCCESS", "Erfolgreich", "Der Spieler <strong>".htmlspecialchars($_POST["spieler"])."</strong> wurde erfolgreich gebannt.", "bans.php");
                } else {
                  showModal("ERROR", "Fehler", "Dieser Spieler hat das Netzwerk noch nie betreten.");
                }
              }
            } else {
              //Erstelle Token wenn Formular nicht abgesendet wurde
              $_SESSION["CSRF"] = generateRandomString(25);
            }
            if(isset($_GET["delete"]) && isset($_GET["name"])){
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :username");
              $stmt->bindParam(":username", $_GET['name'], PDO::PARAM_STR);
              $stmt->execute();
              $data = 0;
              while($row = $stmt->fetch()){
                $data++;
              }
              if($data == 1){
                $stmt = $mysql->prepare("UPDATE bans SET BANNED = 0 WHERE NAME = :username");
                $stmt->bindParam(":username", $_GET['name'], PDO::PARAM_STR);
                $stmt->execute();
                showModalRedirect("SUCCESS", "Erfolgreich", "<strong>".$_GET["name"]."</strong> wurde erfolgreich entbannt.", "bans.php");
              } else {
                showModal("ERROR", "Fehler", "Der angeforderte Benutzer wurde nicht gefunden.");
              }
            }
            if(isset($_GET["delete"]) && isset($_GET["ip"])){
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM ips WHERE IP = :ip");
              $stmt->bindParam(":ip", $_GET['ip'], PDO::PARAM_STR);
              $stmt->execute();
              $data = 0;
              while($row = $stmt->fetch()){
                $data++;
              }
              if($data == 1){
                $stmt = $mysql->prepare("UPDATE ips SET BANNED = 0 WHERE IP = :ip");
                $stmt->bindParam(":ip", $_GET['ip'], PDO::PARAM_STR);
                $stmt->execute();
                showModalRedirect("SUCCESS", "Erfolgreich", "Die IP-Adresse <strong>".$_GET["ip"]."</strong> wurde erfolgreich entbannt.", "bans.php?ipbans");
              } else {
                showModal("ERROR", "Fehler", "Die angeforderte IP-Adresse wurde nicht gefunden.");
              }
            }
             ?>
            <h1>Spieler bannen</h1>
            <form action="bans.php" method="post">
              <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
              <input type="text" name="spieler" placeholder="Spieler" required><br>
              <select name="grund">
                <?php
                require("./mysql.php");
                $stmt = $mysql->prepare("SELECT * FROM reasons");
                $stmt->execute();
                while($row = $stmt->fetch()){
                  if($row["TYPE"] == 0){
                      echo '<option value="'.$row["ID"].'">'.$row["REASON"].'</option>';
                  }
                }
                 ?>
              </select><br>
              <button type="submit" name="submit">Spieler bannen</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
