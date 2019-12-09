<?php
require("./inc/header.inc.php");
?>
<div class="flex-container animated fadeIn">
  <div class="flex item-1">
    <h1>Aktive Mutes</h1>
    <table>
      <tr>
        <th>Spieler</th>
        <th>Grund</th>
        <th>gemutet bis</th>
        <th>gemutet von</th>
        <th>Aktionen</th>
      </tr>
      <tr>
        <?php

        $stmt = MySQLWrapper()->prepare("SELECT * FROM bans");
        $stmt->execute();
        while ($row = $stmt->fetch()) {
          if ($row["MUTED"] == 1) {
            echo "<tr>";
            echo '<td>' . $row["NAME"] . '</td>';
            echo '<td>';
            if (isMuteAutoMute($row["UUID"])) {
              echo htmlspecialchars($row["REASON"]) . " (<strong>" . htmlspecialchars(getAutoMuteMessage($row["UUID"])) . "</strong>)";
            } else {
              echo htmlspecialchars($row["REASON"]);
            }
            echo '</td>';
            echo '<td>' . date('d.m.Y H:i', $row["END"] / 1000) . '</td>';
            echo '<td>';
            if ($row["TEAMUUID"] == "KONSOLE") {
              echo "Konsole";
            } else {
              echo UUIDResolve($row["TEAMUUID"]);
            }
            echo '</td>';
            echo '<td><a href="mutes.php?delete&name=' . $row["NAME"] . '"><i class="material-icons">block</i></a></td>';
            echo "</tr>";
          }
        }
        ?>
      </tr>
    </table>
  </div>
  <div class="flex item-2 sidebox">
    <?php
    if (isset($_POST["submit"]) && isset($_SESSION["CSRF"])) {
      if ($_POST["CSRFToken"] != $_SESSION["CSRF"]) {
        showModal("ERROR", "CSRF Fehler", "Deine Sitzung ist abgelaufen. Versuche die Seite erneut zu öffnen.");
      } else {
        //Fetch UUID from Userinput
        $stmt = MySQLWrapper()->prepare("SELECT UUID FROM bans WHERE NAME = :username");
        $stmt->bindParam(":username", $_POST["spieler"], PDO::PARAM_STR);
        $stmt->execute();
        if ($row = $stmt->fetch()) {
          $uuid = $row["UUID"];
        }
        if (isPlayerExists($uuid)) {
          if (isMuted($uuid)) {
            showModalRedirect("ERROR", "Fehler", "Dieser Spieler ist bereits gemutet.", "mutes.php");
            exit;
          }
          $now = time();
          if (getMinutesByReasonID($_POST["grund"]) != "-1") { //Kein Perma Ban
            $phpEND = $now + getMinutesByReasonID($_POST["grund"]) * 60;
            $javaEND = $phpEND * 1000;
          } else {
            //PERMA BAN
            $javaEND = -1;
          }
          $stmt = MySQLWrapper()->prepare("UPDATE bans SET MUTED = 1, REASON = :reason, END = :end, TEAMUUID = :webUUID  WHERE UUID = :uuid");
          $reason = getReasonByReasonID($_POST["grund"]);
          $stmt->bindParam(":reason", $reason, PDO::PARAM_STR);
          $stmt->bindParam(":end", $javaEND, PDO::PARAM_STR);

          //UUID von User im Webinterface
          $stmtUUID = MySQLWrapper()->prepare("SELECT * FROM accounts WHERE USERNAME = :name");
          $stmtUUID->bindParam(":name", $_SESSION["username"], PDO::PARAM_STR);
          $stmtUUID->execute();
          while ($row = $stmtUUID->fetch()) {
            if ($row["USERNAME"] == $_SESSION["username"]) {
              $useruuid = $row["UUID"];
            }
          }

          $stmt->bindParam(":webUUID", $useruuid, PDO::PARAM_STR);
          $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
          $stmt->execute();
          addMuteCounter($uuid);
          showModalRedirect("SUCCESS", "Erfolgreich", "Der Spieler <strong>" . htmlspecialchars($_POST["spieler"]) . "</strong> wurde erfolgreich gemutet.", "mutes.php");
        } else {
          showModal("ERROR", "Fehler", "Dieser Spieler hat das Netzwerk noch nie betreten.");
        }
      }
    } else {
      //Erstelle Token wenn Formular nicht abgesendet wurde
      $_SESSION["CSRF"] = generateRandomString(25);
    }
    if (isset($_GET["delete"]) && isset($_GET["name"])) {

      $stmt = MySQLWrapper()->prepare("SELECT * FROM bans WHERE NAME = :username");
      $stmt->bindParam(":username", $_GET['name'], PDO::PARAM_STR);
      $stmt->execute();
      if ($row = $stmt->fetch()) {
        $stmt = MySQLWrapper()->prepare("UPDATE bans SET MUTED = 0 WHERE NAME = :username");
        $stmt->bindParam(":username", $_GET['name'], PDO::PARAM_STR);
        $stmt->execute();
        showModalRedirect("SUCCESS", "Erfolgreich", "<strong>" . $_GET["name"] . "</strong> wurde erfolgreich entmutet.", "mutes.php");
      } else {
        showModal("ERROR", "Fehler", "Der angeforderte Benutzer wurde nicht gefunden.");
      }
    }
    ?>
    <h1>Spieler muten</h1>
    <form action="mutes.php" method="post">
      <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
      <input type="text" name="spieler" placeholder="Spieler" required><br>
      <select name="grund">
        <?php

        $stmt = MySQLWrapper()->prepare("SELECT * FROM reasons");
        $stmt->execute();
        while ($row = $stmt->fetch()) {
          if ($row["TYPE"] == 1) {
            echo '<option value="' . $row["ID"] . '">' . htmlspecialchars($row["REASON"]) . '</option>';
          }
        }
        ?>
      </select><br>
      <button type="submit" name="submit">Spieler muten</button>
    </form>
  </div>
</div>
</div>
</div>
</body>

</html>