<?php
$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
if($lang == "de" || $lang == "ch" || $lang == "at"){
    require("./languages/de.php");
} else {
    require("./languages/en.php");
}
?>
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
    <meta name="viewport" content="width=device-width,
            initial-scale=1.0,
            minimum-scale=1.0">
</head>
<?php

    session_start();
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit;
    }

    require("./datamanager.php");
    if (isInitialPassword($_SESSION['username'])) {
        header("Location: resetpassword.php?name=" . $_SESSION['username']);
        exit;
    }

    validateSession();
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
      $txt = $txt.UUIDResolve($row["UUID"])." -> ".$row["MESSAGE"]." | ".date($messages["date_format"],$row["SENDDATE"]/1000)." @ ".$row["SERVER"]."\n";
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
            <p><?php echo UUIDResolve($row["UUID"]); ?> | <?php echo date($messages["time_format"], $row["SENDDATE"] / 1000); ?></p>
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
            <!-- START 
            Navbar for desktop devices -->
            <ul>
                <li <?php activeItem("index.php") ?>><a href="index.php"><i class="fas fa-home"></i> <?php echo $messages["overview"] ?></a></li>
                <li <?php activeItem("search.php") ?>><a href="search.php"><i class="fas fa-search"></i> <?php echo $messages["search"] ?></a></li>
                <?php
                    if (isMod($_SESSION["username"])) {
                        ?>
                    <li <?php activeItem("bans.php") ?>><a href="bans.php"><i class="fas fa-ban"></i> Bans</a></li>
                <?php
                    }
                    ?>
                <li <?php activeItem("mutes.php") ?>><a href="mutes.php"><i class="fas fa-volume-mute"></i> Mutes</a></li>
                <li <?php activeItem("livechat.php") ?>><a href="livechat.php"><i class="fas fa-comment"></i> Livechat</a></li>
                <?php
                    if (isMod($_SESSION["username"])) {
                        ?>
                    <li <?php activeItem("reports.php") ?>><a href="reports.php"><i class="fas fa-flag"></i> Reports</a></li>
                <?php
                    }
                    ?>
                <?php
                    if (isAdmin($_SESSION["username"])) {
                        ?>
                    <li <?php activeItem("accounts.php") ?>><a href="accounts.php"><i class="fas fa-users"></i> Accounts</a></li>
                    <li <?php activeItem("reasons.php") ?>><a href="reasons.php"><i class="fas fa-cogs"></i> <?php echo $messages["banreasons"] ?></a></li>
                <?php
                    }
                    ?>
                <?php
                    if (isMod($_SESSION["username"])) {
                        ?>
                        <li <?php activeItem("unbans.php") ?>><a href="unbans.php"><i class="fas fa-envelope"></i> <?php echo $messages["unbanrequests"] ?></a></li>
                        <?php
                    }
                    ?>
            </ul>
            <!-- END 
            Navbar for desktop devices -->
        </div>
        <div class="header">
            <!-- Trigger for mobile devices -->
            <i class="fas fa-bars fa-2x menu mobileicon"></i>
            <a href="logout.php"><i class="fas fa-sign-out-alt fa-2x headericon"></i></a>
        </div>
        <div class="content">
            <div class="mobilenavbar">
                <nav>
                    <!-- START 
                    Navbar for mobile devices -->
                    <ul class="navbar animated bounceInDown">
                        <li <?php activeItem("index.php") ?>><a href="index.php"><i class="fas fa-home"></i> <?php echo $messages["overview"] ?></a></li>
                        <li <?php activeItem("search.php") ?>><a href="search.php"><i class="fas fa-search"></i> <?php echo $messages["search"] ?></a></li>
                        <?php
                        if (isMod($_SESSION["username"])) {
                            ?>
                            <li <?php activeItem("bans.php") ?>><a href="bans.php"><i class="fas fa-ban"></i> Bans</a></li>
                            <?php
                        }
                        ?>
                        <li><a href="mutes.php"><i class="fas fa-volume-mute"></i> Mutes</a></li>
                        <li><a href="livechat.php"><i class="fas fa-comment"></i> Livechat</a></li>
                        <?php
                        if (isMod($_SESSION["username"])) {
                            ?>
                            <li <?php activeItem("reports.php") ?>><a href="reports.php"><i class="fas fa-flag"></i> Reports</a></li>
                            <?php
                        }
                        ?>
                        <?php
                        if (isAdmin($_SESSION["username"])) {
                            ?>
                            <li <?php activeItem("accounts.php") ?>><a href="accounts.php"><i class="fas fa-users"></i> Accounts</a></li>
                            <li <?php activeItem("reasons.php") ?>><a href="reasons.php"><i class="fas fa-cogs"></i> <?php echo $messages["banreasons"] ?></a></li>
                            <?php
                        }
                        ?>
                        <?php
                        if (isMod($_SESSION["username"])) {
                            ?>
                            <li <?php activeItem("unbans.php") ?>><a href="unbans.php"><i class="fas fa-envelope"></i> <?php echo $messages["unbanrequests"] ?></a></li>
                            <?php
                        }
                        ?>
                    </ul>
                    <!-- END 
                    Navbar for mobile devices -->
                </nav>
            </div>
            <script type="text/javascript">
                $(document).ready(function() {
                    $('.menu').click(function() {
                        $('ul').toggleClass("navactive");
                    })
                })
            </script>

<?php

function activeItem($file){
    $request = explode("/", $_SERVER['REQUEST_URI']);
    $request_file = end($request);
    if($request_file == $file){
        echo 'class="active"';
    }
}