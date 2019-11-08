<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Accounts</title>
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
    if(isset($_GET["delete"]) && isset($_GET["name"])){
      if(!isset($_GET["confirmed"])){
        if(!empty($_GET["name"])){
          ?>
          <script>
            $.sweetModal.defaultSettings.confirm.yes.label = "Löschen";
            $.sweetModal.defaultSettings.confirm.cancel.label = "Abbrechen";
            $.sweetModal.confirm('Möchtest du wirklich <strong><?php echo $_GET["name"]; ?></strong> löschen?', function() {
              var xhttp = new XMLHttpRequest();
              xhttp.open("GET", "accounts.php?delete&name=<?php echo $_GET["name"]; ?>&confirmed");
              xhttp.send();
              $.sweetModal({
                content: '<strong><?php echo $_GET["name"]; ?></strong> wurde erfolgreich gelöscht.',
                icon: $.sweetModal.ICON_SUCCESS,
                onClose: function(){
                  window.location = "accounts.php";
                }
              });
            });
          </script>
          <?php
        } else {
          header("Location: accounts.php");
        }
      } else {
        require("./mysql.php");
        $stmt = $mysql->prepare("DELETE FROM accounts WHERE USERNAME = :user");
        $stmt->execute(array(":user" => $_GET["name"]));
      }
      
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
            <li class="active"><a href="accounts.php"><i class="fas fa-users"></i> Accounts</a></li>
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
            <li><a href="reports.php"><i class="fas fa-flag"></i> Reports</a></li>
            <?php
          }
          ?>
          <?php
          if(isAdmin($_SESSION["username"])){
            ?>
            <li class="active"><a href="accounts.php"><i class="fas fa-users"></i> Accounts</a></li>
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
            <h1>Accounts</h1>
            <table>
              <tr>
                <th>Username</th>
                <th>Rang</th>
                <th>Google Authenticator</th>
                <th>Aktionen</th>
              </tr>
              <?php
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM accounts");
              $stmt->execute();
              while($row = $stmt->fetch()){
                echo "<tr>";
                echo '<td><strong>'.htmlspecialchars($row["USERNAME"]).'</strong></td>';
                if($row["RANK"] == 3){
                  echo '<td>Admin</td>';
                } else if($row["RANK"] == 2){
                  echo '<td>Moderator</td>';
                } else if($row["RANK"] == 1){
                  echo '<td>Supporter</td>';
                }
                if($row["GOOGLE_AUTH"] != "null"){
                  echo '<td>Ja</td>';
                } else {
                  echo '<td>Nein</td>';
                }
                  echo '<td><a href="editaccount.php?name='.$row["USERNAME"].'""><i class="material-icons">edit</i></a> ';
                if($row["USERNAME"] != $_SESSION["username"]){
                  echo '<a href="accounts.php?delete&name='.$row["USERNAME"].'"><i class="material-icons">block</i></a></td>';
                }
                echo "</tr>";
              }
               ?>
            </table>
          </div>
          <!--
          <div class="flex item-2 sidebox">
            <h1>Account erstellen</h1>
            <form action="bans.php" method="post">
              <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
              <input type="text" name="username" placeholder="Username" required><br>
              <input type="text" name="mcusername" placeholder="Minecraft Username" required><br>
              <select name="rang">
                <option value="3">Admin</option>
                <option value="2">Moderator</option>
                <option value="1">Supporter</option>
              </select><br>
              <button type="submit" name="submit">Account erstellen</button>
            </form>
          </div>
        -->
        </div>
        <script type="text/javascript">
        function rankChangeModal(name){

        }
        </script>
      </div>
    </div>
  </body>
</html>
