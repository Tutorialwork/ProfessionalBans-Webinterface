<?php
require("./inc/header.inc.php");
if (isset($_GET["id"]) && !empty($_GET["id"]) && isPlayerExists($_GET["id"])) {
  ?>
  <div class="flex-container animated fadeIn">
    <div class="flex item-1">
      <h1><?php echo UUIDResolve($_GET["id"]) ?></h1>
      <?php
        $stmt = MySQLWrapper()->prepare("SELECT * FROM bans WHERE UUID = :id");
        $stmt->execute(array(":id" => $_GET["id"]));
        $row = $stmt->fetch();
        if ($row["ONLINE_STATUS"] == 1) {
          ?>
        <p><?php echo UUIDResolve($_GET["id"]) ?> ist zur Zeit <strong>ONLINE</strong></p>
        <p>Sein erster Login auf dem Netzwerk war am <strong><?php echo date('d.m.Y H:i', $row["FIRSTLOGIN"] / 1000) ?></strong></p>
      <?php
        } else {
          ?>
        <p><?php echo UUIDResolve($_GET["id"]) ?> wurde zuletzt am <strong><?php echo date('d.m.Y H:i', $row["LASTLOGIN"] / 1000) ?></strong> gesehen.</p>
        <p>Sein erster Login auf dem Netzwerk war am <strong><?php echo date('d.m.Y H:i', $row["FIRSTLOGIN"] / 1000) ?></strong></p>
      <?php
        }
        ?>
      <table>
        <tr>
          <th>Aktion</th>
          <th>Von</th>
          <th>Am</th>
        </tr>
        <?php
          $stmt = MySQLWrapper()->prepare("SELECT * FROM log WHERE UUID = :id ORDER BY DATE DESC");
          $stmt->execute(array(":id" => $_GET["id"]));
          $row = $stmt->fetch();
          if (!empty($row)) {
            if (
              $row["ACTION"] == "BAN" || $row["ACTION"] == "UNBAN_BAN" || $row["ACTION"] == "MUTE" || $row["ACTION"] == "UNBAN_MUTE"
              || $row["ACTION"] == "AUTOMUTE_BLACKLIST" || $row["ACTION"] == "AUTOMUTE_ADBLACKLIST"
            ) {
              echo "<tr>";
              echo '<td><strong>';
              switch ($row["ACTION"]) {
                case "BAN":
                  echo "wurde gebannt wegen " . htmlspecialchars(getReasonByReasonID($row["NOTE"]));
                  break;
                case "UNBAN_BAN":
                  echo "wurde entbannt";
                  break;
                case "MUTE":
                  echo "wurde gemutet wegen " . htmlspecialchars(getReasonByReasonID($row["NOTE"]));
                  break;
                case "UNBAN_BAN":
                  echo "wurde entmutet";
                  break;
                case "AUTOMUTE_BLACKLIST":
                  echo "wurde automatisch gemutet wegen seinem Verhalten (<strong>" . $row["NOTE"] . "</strong>)";
                  break;
                case "AUTOMUTE_ADBLACKLIST":
                  echo "wurde automatisch gemutet wegen Werbung (<strong>" . $row["NOTE"] . "</strong>)";
                  break;
              }
              echo '</strong></td>';
              if ($row["BYUUID"] != "KONSOLE") {
                echo '<td>' . UUIDResolve($row["BYUUID"]) . '</td>';
              } else {
                echo '<td>Konsole</td>';
              }
              echo '<td>' . date('d.m.Y H:i', $row["DATE"] / 1000) . '</td>';
              echo "</tr>";
            }
          }
          ?>
      </table>
    </div>
  </div>
  </div>
  </div>
  </body>

  </html>
<?php
} else {
  showModalRedirect("ERROR", "Fehler", "Der Link ist ungültig", "search.php");
}
