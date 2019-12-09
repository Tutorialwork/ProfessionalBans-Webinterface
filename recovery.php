<?php
require("./datamanager.php");
$error = "";
$success = "";
if (isset($_POST["submit"])) {
  $stmt = MySQLWrapper()->prepare("SELECT * FROM accounts WHERE USERNAME = :user");
  $stmt->bindParam(":user", $_POST["mcusername"], PDO::PARAM_STR);
  $stmt->execute();
  $count = $stmt->rowCount();
  if ($count == 1) {
    setAuthCodeByMCName($_POST["mcusername"], generateRandomString(25));
    header("Location: recovery.php?verify=" . htmlspecialchars($_POST["mcusername"]));
  } else {
    $error = "Es gibt kein Account mit diesem Minecraft Username.";
  }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="utf-8">
  <title>Passwort vergessen</title>
  <link rel="stylesheet" href="css/login.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" type="image/x-icon" href="css/favicon.ico">
</head>

<body>

  <?php
  if (isset($_GET["verify"])) {
    ?>
    <form class="login" action="recovery.php?verify=<?php echo $_GET["verify"]; ?>&token=<?php echo getAuthCodeByMCName($_GET["verify"]); ?>" method="post">
      <?php
        if (isset($_POST["pwsubmit"])) {
          if (isAuth($_GET["verify"])) {
            if (getAuthCodeByMCName($_GET["verify"]) == $_GET["token"]) {
              if ($_POST["pw"] == $_POST["pw2"]) {
                resetAuthStatus($_GET["verify"]);
                updatePasswordByMCName($_GET["verify"], $_POST["pw"]);
                ?>
              <div class="success">
                <h4>Das Passwort wurde erfolgreich geändert. Du kannst dich jetzt <a href="login.php">hier</a> einloggen.</h4>
              </div>
            <?php
                    } else {
                      ?>
              <div class="error">
                <h4>Die Passwörter stimmen nicht überein.</h4>
              </div>
            <?php
                    }
                  } else {
                    ?>
            <div class="error">
              <h4>Ein Fehler ist aufgetreten.</h4>
            </div>
          <?php
                }
              } else {
                ?>
          <div class="error">
            <h4>Du musst zuerst deine Identität bestätigen.</h4>
          </div>
      <?php
          }
        }
        ?>
      <h1><i class="fas fa-user-check"></i> Bestätigen</h1>
      <p>Bitte bestätige jetzt deine Identität, indem du auf dem Minecraft Netzwerk folgenden Befehl eingibst. Anschließend kannst du unten ein neues Passwort festlegen.</p>
      <input type="text" name="cmd" placeholder="Befehl" value="/webverify <?php echo getAuthCodeByMCName($_GET["verify"]); ?>"><br>
      <p>Neues Passwort festlegen</p>
      <input type="password" name="pw" placeholder="Neues Passwort" minlength="6" required>
      <input type="password" name="pw2" placeholder="Neues Passwort bestätigen" minlength="6" required>
      <button type="submit" name="pwsubmit">Passwort ändern</button>
    </form>
  <?php
  } else {
    //Warte auf Anfrage...
    ?>
    <form class="login" action="recovery.php" method="post">
      <?php if (!empty($error)) { ?>
        <div class="error">
          <h4><?= $error; ?></h4>
        </div>
      <?php } ?>
      <h1 class="small-heading"><i class="fas fa-key"></i> Passwort vergessen</h1>
      <p>Gebe deinen Minecraft Username ein. Dieser kann sich möglicherweise von deinem Username im Webinterface unterscheiden.</p>
      <input type="text" name="mcusername" placeholder="Minecraft Username" maxlength="16" minlength="3" required><br>
      <button type="submit" name="submit">Bestätigen</button><br><br><br>
      <a href="login.php"><i class="fas fa-arrow-left"></i> Zurück</a>
    </form>
  <?php
  }
  ?>

</body>

</html>