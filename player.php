<?php
require("./inc/header.inc.php");
require("./mysql.php");
if(isset($_GET["id"]) && !empty($_GET["id"]) && isPlayerExists($_GET["id"])){
    ?>
    <div class="flex-container animated fadeIn">
          <div class="flex item-1">
            <h1><?php echo UUIDResolve($_GET["id"]) ?></h1>
            <?php
            $stmt = $mysql->prepare("SELECT * FROM bans WHERE UUID = :id");
            $stmt->execute(array(":id" => $_GET["id"]));
            $row = $stmt->fetch();

            $datetime1 = new DateTime();
            $datetime1->setTimestamp(time());
            $datetime2 = new DateTime();
            $datetime2->setTimestamp(time() - $row["ONLINE_TIME"] / 1000);
            $interval = $datetime1->diff($datetime2);
            ?>
            <p><?php echo $messages["onlinetime"] . ": " . $interval->format('<strong>%a</strong> '.$messages["days"].', <strong>%h</strong> '.$messages["hours"].', <strong>%i</strong> '.$messages["minutes"].''); ?></p>
            <?php
            if($row["ONLINE_STATUS"] == 1){
                ?>
                <p><?php echo UUIDResolve($_GET["id"]) . " " . $messages["player_online_msg"] ?></p>
                <p>Sein erster Login auf dem Netzwerk war am <strong><?php echo date('d.m.Y H:i',$row["FIRSTLOGIN"]/1000) ?></strong></p>
                <?php
            } else {
                ?>
                <p><?php echo UUIDResolve($_GET["id"])  . " " . str_replace("%date%", date($messages["date_format"],$row["LASTLOGIN"]/1000), $messages["player_offline_msg"]) ?></p>
                <?php
            }
            ?>
            <table>
              <tr>
                <th><?php echo $messages["event"] ?></th>
                <th><?php echo $messages["from"] ?></th>
                <th><?php echo $messages["at"] ?></th>
              </tr>
              <?php
              $stmt = $mysql->prepare("SELECT * FROM log WHERE UUID = :id ORDER BY DATE DESC");
              $stmt->execute(array(":id" => $_GET["id"]));
              while($row = $stmt->fetch()){
                  if($row["ACTION"] == "BAN" || $row["ACTION"] == "UNBAN_BAN" || $row["ACTION"] == "MUTE" || $row["ACTION"] == "UNBAN_MUTE" 
                  || $row["ACTION"] == "AUTOMUTE_BLACKLIST" || $row["ACTION"] == "AUTOMUTE_ADBLACKLIST"){
                    echo "<tr>";
                    echo '<td><strong>';
                    switch($row["ACTION"]){
                      case "BAN":
                          echo str_replace("%text%", htmlspecialchars(getReasonByReasonID($row["NOTE"])), $messages["event_BAN"]);
                          break;
                      case "UNBAN_BAN":
                          echo str_replace("%text%", "", $messages["event_UNBAN_BAN"]);
                        break;
                      case "MUTE":
                          echo str_replace("%text%", htmlspecialchars(getReasonByReasonID($row["NOTE"])), $messages["event_MUTE"]);
                        break;
                      case "UNBAN_MUTE":
                          echo str_replace("%text%", "", $messages["event_UNBAN_MUTE"]);
                        break;
                      case "AUTOMUTE_BLACKLIST":
                          echo str_replace("%text%", $row["NOTE"], $messages["event_AUTOMUTE_BLACKLIST"]);
                        break;
                      case "AUTOMUTE_ADBLACKLIST":
                          echo str_replace("%text%", $row["NOTE"], $messages["event_AUTOMUTE_ADBLACKLIST"]);
                        break;
                    }
                    echo '</strong></td>';
                    if($row["BYUUID"] != "KONSOLE"){
                      echo '<td>'.UUIDResolve($row["BYUUID"]).'</td>';
                    } else {
                      echo '<td>Konsole</td>';
                    }
                    echo '<td>'.date($messages["date_format"],$row["DATE"]/1000).'</td>';
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