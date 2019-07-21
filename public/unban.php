<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Entbannungsantrag stellen</title>
    <link rel="stylesheet" href="../css/public.css">
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
    <link rel="shortcut icon" type="image/x-icon" href="css/favicon.ico">
    <meta name="viewport"
      content="width=device-width,
               initial-scale=1.0,
               minimum-scale=1.0">
  </head>
  <body>
    <div class="container">
      <?php
      if(isset($_POST["submit"])){
        require("./../mysql.php");
        $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :name");
        $stmt->bindParam(":name", $_POST["player"], PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch();
        $count = $stmt->rowCount();
        if($count != 0){
          if($row["BANNED"] == 1){
            if(!empty($_POST["message"])){
              $stmt = $mysql->prepare("INSERT INTO unbans (UUID, FAIR, MESSAGE, DATE, STATUS) VALUES (:uuid, :fair, :msg, :now, 0)");
              $now = time();
              $stmt->bindParam(":uuid", $row["UUID"], PDO::PARAM_STR);
              $stmt->bindParam(":fair", $_POST["fair"], PDO::PARAM_STR);
              $stmt->bindParam(":msg", $_POST["message"], PDO::PARAM_STR);
              $stmt->bindParam(":now", $now, PDO::PARAM_STR);
              $stmt->execute();
              ?>
              <div class="success">
                Dein Entbannungsantrag wurde abgesendet.
              </div>
              <?php
            } else {
              ?>
              <div class="error">
                Das Formular ist unvollständig.
              </div>
              <?php
            }
          } else {
            ?>
            <div class="error">
              Du wurdest nicht gebannt.
            </div>
            <?php
          }
        } else {
          ?>
          <div class="error">
            Du wurdest nicht gebannt.
          </div>
          <?php
        }
      }
       ?>
      <form action="unban.php" method="post">
        <h1><i class="fas fa-paper-plane"></i> Entbannungsantrag</h1>
        <input type="text" name="player" placeholder="Dein Spielername" required><br>
        <label for="fair">Glaubst du der Ban war gerechtfertigt?</label><br>
        <select name="fair">
          <option value="1">Ja, aber ich sehe meinen Fehler ein</option>
          <option value="0">Nein, ich habe nichts getan</option>
        </select><br>
        <textarea name="message" rows="10" cols="80" placeholder="Was willst du uns über deinen Ban mitteilen?" required></textarea><br>
        <button type="submit" name="submit">Antrag stellen</button>
      </form>
    </div>
  </body>
</html>
