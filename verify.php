<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Verifizieren</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>
  <body>
    <?php
    session_start();
    if(!isset($_SESSION["username_unauth"])){
      header("Location: index.php");
      exit;
    }
     ?>
    <form class="login" action="verify.php" method="post">
      <?php
      if(isset($_POST["submit"])){
        require("./mysql.php");
        require("./datamanager.php");
        require("./authmanager.php");
        $ga = new PHPGangsta_GoogleAuthenticator();
        $res = $ga->verifyCode(getGToken($_SESSION["username_unauth"]), $_POST["code"], 2);
        if($res){
          $username = $_SESSION["username_unauth"];
          session_destroy();
          session_start();
          $_SESSION["username"] = $username;
          header("Location: index.php");
          exit;
        } else {
          ?>
          <div class="error">
            <h4>Der eingegebene Code ist nicht korrekt.</h4>
          </div>
          <?php
        }
      }
       ?>
      <h1><i class="fas fa-mobile-alt"></i> Verifizieren</h1>
      <input type="number" name="code" placeholder="Code" required><br>
      <button type="submit" name="submit">Login</button><br><br><br>
    </form>
  </body>
</html>
