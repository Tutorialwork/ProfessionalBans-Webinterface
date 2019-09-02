<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Bangründe</title>
    <link rel="stylesheet" href="css/master.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/jquery.sweet-modal.min.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="js/jquery.sweet-modal.min.js"></script>
    <link href="css/jquery-ui.min.css" rel="stylesheet">
    <script src="js/jquery-ui.min.js"></script>
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
            <li class="active"><a href="reasons.php"><i class="fas fa-cogs"></i> Bangründe</a></li>
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
            <li><a href="reports.php"><i class="fas fa-flag"></i> Reports</a></li>
            <?php
          }
          ?>
          <?php
          if(isAdmin($_SESSION["username"])){
            ?>
            <li><a href="accounts.php"><i class="fas fa-users"></i> Accounts</a></li>
            <li class="active"><a href="reasons.php"><i class="fas fa-cogs"></i> Bangründe</a></li>
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
            if(isset($_GET["delete"]) && isset($_GET["id"])){
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM reasons WHERE ID = :id");
              $stmt->bindParam(":id", $_GET['id'], PDO::PARAM_INT);
              $stmt->execute();
              $data = 0;
              while($row = $stmt->fetch()){
                $data++;
              }
              if($data == 1){
                $stmt = $mysql->prepare("DELETE FROM reasons WHERE ID = :id");
                $stmt->bindParam(":id", $_GET['id'], PDO::PARAM_INT);
                $stmt->execute();
                $idstmt = $mysql->prepare("SELECT * FROM reasons");
                $idstmt->execute();
                $id = 0;
                while($row = $idstmt->fetch()){
                  $id++;
                  $stmt = $mysql->prepare("UPDATE reasons SET ID = :id WHERE ID = :dbid");
                  $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                  $stmt->bindParam(":dbid", $row["ID"], PDO::PARAM_INT);
                  $stmt->execute();
                }
                showModalRedirect("SUCCESS", "Erfolgreich", "Der Bangrund wurde erfolgreich gelöscht.", "reasons.php");
              } else {
                showModal("ERROR", "Fehler", "Der angeforderte Bangrund wurde nicht gefunden.");
              }
            }

             ?>
             <script>
               $(function() {
                  $( "tbody" ).sortable({
                    axis: 'y',
                    update: function (event, ui) {
                      var data = $(this).sortable('serialize');

                      // POST to server using $.post or $.ajax
                      $.ajax({
                        data: data,
                        type: 'POST',
                        url: 'fetch.php?type=SORTINDEX'
                      });
                    }

                  }
                  );
                });
             </script>
            <h1>Bangründe</h1>
            <table> 
              <tr>
                <th>ID</th>
                <th>Bangrund</th>
                <th>Zeit</th>
                <th>Type</th>
                <th>hinzugefügt am</th>
                <th>Bans</th>
                <th>Permission</th>
                <th>Aktionen</th>
              </tr>
              <tbody>
                <?php
                require("./mysql.php");
                $stmt = $mysql->prepare("SELECT * FROM reasons ORDER BY SORTINDEX ASC");
                $stmt->execute();
                while($row = $stmt->fetch()){
                  echo '<tr id="item-'.$row["ID"].'">';
                  echo '<td>'.$row["ID"].'</td>';
                  echo '<td>'.htmlspecialchars($row["REASON"]).'</td>';
                  if($row["TIME"] == -1){
                    echo "<td>Permanent</td>";
                  } else if($row["TIME"] < 60){
                    echo '<td>'.$row["TIME"].' Minuten</td>';
                  } else if($row["TIME"] < 1440){
                    $stunden = $row["TIME"] / 60;
                    echo '<td>'.$stunden.' Stunden</td>';
                  } else {
                    $tage = $row["TIME"] / 1440;
                    echo '<td>'.$tage.' Tage</td>';
                  }
                  if($row["TYPE"] == 0){
                    echo '<td>BAN</td>';
                  } else {
                    echo '<td>MUTE</td>';
                  }
                  echo '<td>'.date('d.m.Y H:i',$row["ADDED_AT"]).'</td>';
                  echo '<td>'.$row["BANS"].'</td>';
                  if($row["PERMS"] == "null"){
                    echo '<td>Keine</td>';
                  } else {
                    echo '<td>'.$row["PERMS"].'</td>';
                  }
                  echo '<td><a href="editreason.php?id='.$row["ID"].'"><i class="material-icons">edit</i></a> ';
                  echo '<a href="reasons.php?delete&id='.$row["ID"].'"><i class="material-icons">block</i></a></td>';
                  echo '</tr>';
                }
                 ?>
              </tbody>
              
            </table>
          </div>
          <div class="flex item-2 sidebox">
            <?php
            if(isset($_POST["submit"]) && isset($_SESSION["CSRF"])){
              if($_POST["CSRFToken"] != $_SESSION["CSRF"]){
                showModal("ERROR", "CSRF Fehler", "Deine Sitzung ist abgelaufen. Versuche die Seite erneut zu öffnen.");
              } else {
                require("./mysql.php");
                $idstmt = $mysql->prepare("SELECT * FROM reasons");
                $idstmt->execute();
                $id = 1;
                while($row = $idstmt->fetch()){
                      $id++;
                }
                if(filter_var($_POST['zeit'], FILTER_VALIDATE_INT)){
                  $zeit = $_POST['zeit'];
                } else {
                  showModalRedirect("ERROR", "Fehler", "Du hast keine gültige Zahl angegeben.", "reasons.php");
                  exit;
                }

                $stmt = $mysql->prepare("SELECT * FROM reasons WHERE id = :id");
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                $stmt->execute();
                $data = 0;
                while($row = $stmt->fetch()){
                      $data++;
                }
                if($data == 0){
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
                  $stmt = $mysql->prepare("SELECT * FROM reasons WHERE REASON = :grund");
                  $stmt->bindParam(":grund", $_POST['grund'], PDO::PARAM_STR);
                  $stmt->execute();
                  while($row = $stmt->fetch()){
                    $data++;
                  }
                  if($data == 0){
                    $uhrzeit = time();
                    $stmt = $mysql->prepare("INSERT INTO reasons (ID, REASON, TIME, TYPE, ADDED_AT, BANS, PERMS, SORTINDEX) VALUES (:id, :grund, :min, :type, :now, 0, :perms, :id)");
                    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                    $stmt->bindParam(":grund", $_POST['grund'], PDO::PARAM_STR);
                    $stmt->bindParam(":min", $minuten, PDO::PARAM_INT);
                    $stmt->bindParam(":type", $type, PDO::PARAM_INT);
                    $stmt->bindParam(":now", $uhrzeit, PDO::PARAM_INT);
                    if($_POST["perms"] != ""){
                      $perms = $_POST["perms"];
                    } else {
                      $perms = "null";
                    }
                    $stmt->bindParam(":perms", $perms, PDO::PARAM_STR);
                    $stmt->execute();
                    showModalRedirect("SUCCESS", "Erfolgreich", "Der Grund <strong>".htmlspecialchars($_POST["grund"])."</strong> wurde erfolgreich hinzugefügt.", "reasons.php");
              } else {
                showModal("ERROR", "Fehler", "Dieser Grund ist bereits registriert.");
              }
                } else {
                  showModal("ERROR", "Fehler", "Diese ID ist bereits registriert.");
                }
              }
            } else {
              //Erstelle Token wenn Formular nicht abgesendet wurde
              $_SESSION["CSRF"] = generateRandomString(25);
            }
             ?>
            <h1>Bangrund erstellen</h1>
            <form action="reasons.php" method="post">
              <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
              <input type="text" name="grund" placeholder="Grund" maxlength="16" required><br>
              <input type="number" name="zeit" placeholder="Dauer" required><br>
              <input type="text" name="perms" placeholder="Permission (optional)"><br>
              <select name="einheit">
                <option value="m">Minuten</option>
                <option value="s">Stunden</option>
                <option value="t">Tage</option>
              </select><br>
              <select name="type">
                <option value="ban">Ban</option>
                <option value="mute">Mute</option>
                <option value="permaban">Permanenter Ban</option>
                <option value="permamute">Permanenter Mute</option>
              </select><br>
              <button type="submit" name="submit">Grund hinzufügen</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
