<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Chatlogs</title>
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
  if(!isMod($_SESSION['username'])){
    showModalRedirect("ERROR", "Fehler", "Der Zugriff auf diese Seite wurde verweigert.", "index.php");
    exit;
  }

  if(isInitialPassword($_SESSION['username'])){
    header("Location: resetpassword.php?name=".$_SESSION['username']);
    exit;
  }

   ?>
  <body>
    <div class="container">
      <div class="sidebar">
        <ul>
          <li><a href="index.php"><i class="fas fa-home"></i> Übersicht</a></li>
          <li><a href="bans.php"><i class="fas fa-ban"></i> Bans</a></li>
          <li><a href="mutes.php"><i class="fas fa-volume-mute"></i> Mutes</a></li>
          <li><a href="livechat.php"><i class="fas fa-comment"></i> Livechat</a></li>
          <li class="active"><a href="reports.php"><i class="fas fa-flag"></i> Reports</a></li>
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
              <li class="active"><a href="reports.php">Reports</a></li>
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
            if(isset($_GET["del"])){
              require("./mysql.php");
              $stmt = $mysql->prepare("DELETE FROM chatlog WHERE LOGID = :id");
              $stmt->bindParam(":id", $_GET["del"], PDO::PARAM_STR);
              $stmt->execute();
              showModal("SUCCESS", "Erfolgreich", "Der Chatlog wurde erfolgreich gelöscht.");
            }
             ?>
             <div class="flex-button">
               <a href="reports.php" class="btn"><i class="fas fa-flag"></i> Reports</a>
             </div>
             <h1>Chatlogs</h1>
             <input type="text" name="username" id="username" placeholder="Nach ID oder Server suchen..." required>
             <div id="result"></div>
             <script>
           $(document).ready(function(){
            load_data();
            function load_data(query)
            {
              $.ajax({
                url:"fetch.php?type=CLOG",
                method:"post",
                data:{query:query},
                success:function(data)
                {
                  $('#result').html(data);
                }
              });
            }

            $('#username').keyup(function(){
              var search = $(this).val();
              if(search != '')
              {
                load_data(search);
              }
              else
              {
                load_data();
              }
            });
           });
           </script>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
