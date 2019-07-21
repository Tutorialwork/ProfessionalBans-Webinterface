<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Livechat</title>
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

  //Button functions
  if(isset($_GET["download"])){
    if(!isAdmin($_SESSION['username'])){
      showModal("ERROR", "Fehler", "Dir wurde der Zugriff auf diese Funktion verweigert.");
      exit;
    }
    //Create file and download log file
    $filename = 'log-'.date('d-m-Y_H-i',time()).'.txt';
    mkdir("log");
    $file = fopen("log/".$filename, "w") or die("Unable to open file!");
    require("./mysql.php");
    $stmt = $mysql->prepare("SELECT * FROM chat ORDER BY SENDDATE DESC");
    $stmt->execute();
    $log = array();
    $txt = "";
    while($row = $stmt->fetch()){
      $txt = $txt.UUIDResolve($row["UUID"])." -> ".$row["MESSAGE"]." | um ".date('d.m.Y H:i',$row["SENDDATE"]/1000)." auf ".$row["SERVER"]."\n";
    }
    fwrite($file, $txt);
    fclose($file);
    //Download .txt file
    header('Location: log/'.$filename);
  }

  if(isset($_GET["clean"])){
    if(!isAdmin($_SESSION['username'])){
      showModal("ERROR", "Fehler", "Dir wurde der Zugriff auf diese Funktion verweigert.");
      exit;
    }
    require("mysql.php");
    $stmt = $mysql->prepare("DELETE FROM chat");
    $stmt->execute();
    showModal("SUCCESS", "Erfolgreich", "Der komplette Chatverlauf des Netzwerkes wurde gelöscht.");
  }

  //Update function
  if(isset($_GET["update"])){
    if(!isset($_GET["server"])){
      $page = $_GET["page"];
      $sqlint = ($page*5)-5;
      require("./mysql.php");
      $stmt = $mysql->prepare("SELECT * FROM chat ORDER BY SENDDATE DESC LIMIT $sqlint,5");
      $stmt->execute();
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
      $pagestmt = $mysql->prepare("SELECT * FROM chat");
      $pagestmt->execute();
      $count = $pagestmt->rowCount();
      $pages = $count / 5;
      $pages = ceil($pages);
      if($count == 0){
        ?>
        <h3 style="color: red;">Es wurden noch keine Chatnachrichten gefunden!</h3>
        <?php
        exit;
      }
      ?>
      <ol>
      <?php
      if($page == "1"){
        echo '<li><a><i class="fas fa-arrow-left disabled"></i></a></li>';
      } else {
        $intpage = (int)$page;
        $intpage = $intpage - 1;
        echo '<li><a href="livechat.php?page='.$intpage.'"><i class="fas fa-arrow-left"></i></a></li>';
      }
      for($i=1;$i<=$pages;$i++){
        if($i == $page){
          echo '<li class="active-page"><a href="livechat.php?page='.$i.'">'.$i.'</a></li>';
        } else {
          echo '<li><a href="livechat.php?page='.$i.'">'.$i.'</a></li>';
        }
      }
      if($page == $i - 1){
        echo '<li><a><i class="fas fa-arrow-right disabled"></i></a></li>';
      } else {
        $intpage = (int)$page;
        $intpage = $intpage + 1;
        echo '<li><a href="livechat.php?page='.$intpage.'"><i class="fas fa-arrow-right"></i></a></li>';
      }
      ?>
      </ol>
      <?php
      exit;
    } else {
      $page = $_GET["page"];
      $sqlint = ($page*5)-5;
      require("./mysql.php");
      $stmt = $mysql->prepare("SELECT * FROM chat WHERE SERVER = :server ORDER BY SENDDATE DESC LIMIT $sqlint,5");
      $stmt->bindParam(":server", $_GET["server"], PDO::PARAM_STR);
      $stmt->execute();
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
      $pagestmt = $mysql->prepare("SELECT * FROM chat WHERE SERVER = :server");
      $pagestmt->bindParam(":server", $_GET["server"], PDO::PARAM_STR);
      $pagestmt->execute();
      $count = $pagestmt->rowCount();
      $pages = $count / 5;
      $pages = ceil($pages);
      ?>
      <ol>
      <?php
      if($page == "1"){
        echo '<li><a><i class="fas fa-arrow-left disabled"></i></a></li>';
      } else {
        $intpage = (int)$page;
        $intpage = $intpage - 1;
        echo '<li><a href="livechat.php?page='.$intpage.'&server='.$_GET["server"].'"><i class="fas fa-arrow-left"></i></a></li>';
      }
      for($i=1;$i<=$pages;$i++){
        if($i == $page){
          echo '<li class="active-page"><a href="livechat.php?page='.$i.'&server='.$_GET["server"].'">'.$i.'</a></li>';
        } else {
          echo '<li><a href="livechat.php?page='.$i.'&server='.$_GET["server"].'">'.$i.'</a></li>';
        }
      }
      if($page == $i - 1){
        echo '<li><a><i class="fas fa-arrow-right disabled"></i></a></li>';
      } else {
        $intpage = (int)$page;
        $intpage = $intpage + 1;
        echo '<li><a href="livechat.php?page='.$intpage.'&server='.$_GET["server"].'"><i class="fas fa-arrow-right"></a></li>';
      }
      ?>
      </ol>
      <?php
      exit;
    }
  }

   ?>
  <body>
    <div class="container">
      <div class="sidebar">
        <ul>
          <li><a href="index.php"><i class="fas fa-home"></i> Übersicht</a></li>
          <li><a href="bans.php"><i class="fas fa-ban"></i> Bans</a></li>
          <li><a href="mutes.php"><i class="fas fa-volume-mute"></i> Mutes</a></li>
          <li class="active"><a href="livechat.php"><i class="fas fa-comment"></i> Livechat</a></li>
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
              <li class="active"><a href="livechat.php">Livechat</a></li>
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
            <h1>Livechat</h1>
            <?php
            if(isset($_GET["server"])){
              ?>
              <p>Nachrichten von: <strong><?php echo $_GET["server"] ?></strong></p>
              <?php
            } else {
              ?>
              <p>Nachrichten von: <strong>Alle Server</strong></p>
              <?php
            }
             ?>
            <div id="output"></div>
            <?php
            //Update Counter function
            if(isset($_GET["server"])){
              if(isset($_GET["page"])){
                $givenpage = $_GET["page"];
              } else {
                $givenpage = 1;
              }
              ?>
              <script type="text/javascript">
              $(document).ready(function(){


            var updateDiv = function ()
          {
            $('#output').load('livechat.php?update&server=<?php echo $_GET["server"] ?>&page=<?php echo $givenpage ?>', function () {
              deinTimer = window.setTimeout(updateDiv, 250);
            });
          }
          var deinTimer = window.setTimeout(updateDiv, 250);

            });
              </script>
              <?php
            } else {
              if(isset($_GET["page"])){
                $givenpage = $_GET["page"];
              } else {
                $givenpage = 1;
              }
              ?>
              <script type="text/javascript">
              $(document).ready(function(){


            var updateDiv = function ()
          {
            $('#output').load('livechat.php?update&page=<?php echo $givenpage; ?>', function () {
              deinTimer = window.setTimeout(updateDiv, 250);
            });
          }
          var deinTimer = window.setTimeout(updateDiv, 250);

            });
              </script>
              <?php
            }
             ?>
          </div>
          <div class="flex item-2 sidebox">
            <select name="server" onChange="window.document.location.href=this.options[this.selectedIndex].value;">
              <?php
              if(!isset($_GET["server"])){
                echo '<option value="livechat.php">Alle Server</option>';
                require("./mysql.php");
                $server = array();
                $stmt = $mysql->prepare("SELECT SERVER FROM chat");
                $stmt->execute();
                while($row = $stmt->fetch()){
                  if(!in_array($row["SERVER"], $server)){
                     array_push($server, $row["SERVER"]);
                  }
                }
                foreach ($server as $value) {
                  echo '<option value="livechat.php?server='.$value.'">'.$value.'</option>';
                }
              } else {
                require("./mysql.php");
                $server = array();
                $stmt = $mysql->prepare("SELECT SERVER FROM chat");
                $stmt->execute();
                while($row = $stmt->fetch()){
                  if(!in_array($row["SERVER"], $server)){
                     array_push($server, $row["SERVER"]);
                  }
                }
                echo '<option value="livechat.php?server='.$_GET["server"].'">'.$_GET["server"].'</option>';
                foreach ($server as $value) {
                  if($value != $_GET["server"]){
                    echo '<option value="livechat.php?server='.$value.'">'.$value.'</option>';
                  }
                }
                echo '<option value="livechat.php">Alle Server</option>';
              }
               ?>
            </select>
            <div class="flex-button">
              <p></p>
              <a href="livechat.php?download" class="btn"><i class="fas fa-file-download"></i> Download</a>
              <a href="livechat.php?clean" class="btn"><i class="far fa-trash-alt"></i> Löschen</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
