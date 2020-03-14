        <?php
        require("./inc/header.inc.php");
        if(!isMod($_SESSION['username'])){
          showModalRedirect("ERROR", $messages["error"], $messages["perms_err"], "index.php");
          exit;
        }
        ?>
        <div class="flex-container animated fadeIn">
          <div class="flex item-1">
          <?php
          if(!isset($_GET["ipbans"])){
            ?>
            <div class="flex-button">
              <a href="bans.php?ipbans" class="btn"><i class="fas fa-book-open"></i> IP-Bans</a>
            </div>
            <h1><?php echo $messages["bans"] ?></h1>
            <table>
              <tr>
                <th><?php echo $messages["player"] ?></th>
                <th><?php echo $messages["reason"] ?></th>
                <th><?php echo $messages["banned_to"] ?></th>
                <th><?php echo $messages["punisher_ban"] ?></th>
                <th><?php echo $messages["event"] ?></th>
              </tr>
              <tr>
                <?php
                require("./mysql.php");
                $stmt = $mysql->prepare("SELECT * FROM bans");
                $stmt->execute();
                while($row = $stmt->fetch()){
                  if($row["BANNED"] == 1){
                    echo "<tr>";
                    echo '<td><a href="player.php?id='.$row["UUID"].'">'.$row["NAME"].'<a></td>';
                    echo '<td>'.htmlspecialchars($row["REASON"]).'</td>';
                    echo '<td>'.date('d.m.Y H:i',$row["END"]/1000).'</td>';
                    echo '<td>';
                    if($row["TEAMUUID"] == "KONSOLE"){
                      echo "Konsole";
                    } else {
                      echo UUIDResolve($row["TEAMUUID"]);
                    }
                    echo '</td>';
                    echo '<td><a href="bans.php?delete&name='.$row["NAME"].'"><i class="material-icons">block</i></a></td>';
                    echo "</tr>";
                  }
                }
                 ?>
              </tr>
            </table>
          <?php
          } else {
            //IP Bans
            ?>
            <div class="flex-button">
              <a href="bans.php" class="btn"><i class="fas fa-ban"></i> Bans</a>
            </div>
            <h1><?php echo $messages["ipbans"] ?></h1>
            <table>
              <tr>
                <th>IP</th>
                  <th><?php echo $messages["player"] ?></th>
                  <th><?php echo $messages["reason"] ?></th>
                  <th><?php echo $messages["banned_to"] ?></th>
                  <th><?php echo $messages["punisher_ban"] ?></th>
                  <th><?php echo $messages["event"] ?></th>
              </tr>
              <tr>
                <?php
                require("./mysql.php");
                $stmt = $mysql->prepare("SELECT * FROM ips");
                $stmt->execute();
                while($row = $stmt->fetch()){
                  if($row["BANNED"] == 1){
                    echo "<tr>";
                    echo '<td>'.$row["IP"].'</td>';
                    echo '<td>';
                    if($row["USED_BY"] != "null"){
                      echo UUIDResolve($row["USED_BY"]);
                    }
                    echo '</td>';
                    echo '<td>'.htmlspecialchars($row["REASON"]).'</td>';
                    echo '<td>'.date('d.m.Y H:i',$row["END"]/1000).'</td>';
                    echo '<td>';
                    if($row["TEAMUUID"] == "KONSOLE"){
                      echo "Konsole";
                    } else {
                      echo UUIDResolve($row["TEAMUUID"]);
                    }
                    echo '</td>';
                    echo '<td><a href="bans.php?delete&ip='.$row["IP"].'"><i class="material-icons">block</i></a></td>';
                    echo "</tr>";
                  }
                }
                 ?>
              </tr>
            </table>
            <?php
          }
          ?>
          </div>
          <div class="flex item-2 sidebox">
            <?php
            if(isset($_POST["submit"]) && isset($_SESSION["CSRF"])){
              if($_POST["CSRFToken"] != $_SESSION["CSRF"]){
                showModal("ERROR", $messages["error"], $messages["csrf_err"]);
              } else {
                //Fetch UUID from Userinput
                $stmt = $mysql->prepare("SELECT UUID FROM bans WHERE NAME = :username");
                $stmt->bindParam(":username", $_POST["spieler"], PDO::PARAM_STR);
                $stmt->execute();
                while($row = $stmt->fetch()){
                  $uuid = $row["UUID"];
                }
                if(isPlayerExists($uuid)){
                  if(isBanned($uuid)){
                    showModalRedirect("ERROR", $messages["error"], $messages["already_banned"], "bans.php");
                    exit;
                  }
                  if(hasWebAccount($_POST["spieler"])){
                      showModalRedirect("ERROR", $messages["error"], $messages["not_bannable"], "bans.php");
                      exit;
                  }
                  $now = time();
                  if(getMinutesByReasonID($_POST["grund"]) != "-1"){ //Kein Perma Ban
                    $phpEND = $now + getMinutesByReasonID($_POST["grund"]) * 60;
                    $javaEND = $phpEND * 1000;
                  } else {
                    //PERMA BAN
                    $javaEND = -1;
                  }
                  $stmt = $mysql->prepare("UPDATE bans SET BANNED = 1, MUTED = 0, REASON = :reason, END = :end, TEAMUUID = :webUUID  WHERE UUID = :uuid");
                  $reason = getReasonByReasonID($_POST["grund"]);
                  $stmt->bindParam(":reason", $reason, PDO::PARAM_STR);
                  $stmt->bindParam(":end", $javaEND, PDO::PARAM_STR);

                  //UUID von User im Webinterface
                  $stmtUUID = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = :name");
                  $stmtUUID->bindParam(":name", $_SESSION["username"], PDO::PARAM_STR);
                  $stmtUUID->execute();
                  while($row = $stmtUUID->fetch()){
                    if($row["USERNAME"] == $_SESSION["username"]){
                      $useruuid = $row["UUID"];
                    }
                  }

                  $stmt->bindParam(":webUUID", $useruuid, PDO::PARAM_STR);
                  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
                  $stmt->execute();
                  addBanCounter($uuid);
                  showModalRedirect("SUCCESS", $messages["success"], str_replace("%username%", htmlspecialchars($_POST["spieler"]), $messages["player_banned"]), "bans.php");
                } else {
                  showModal("ERROR", $messages["error"], $messages["player_404"]);
                }
              }
            } else {
              //Erstelle Token wenn Formular nicht abgesendet wurde
              $_SESSION["CSRF"] = generateRandomString(25);
            }
            if(isset($_GET["delete"]) && isset($_GET["name"])){
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :username");
              $stmt->bindParam(":username", $_GET['name'], PDO::PARAM_STR);
              $stmt->execute();
              $data = 0;
              while($row = $stmt->fetch()){
                $data++;
              }
              if($data == 1){
                $stmt = $mysql->prepare("UPDATE bans SET BANNED = 0 WHERE NAME = :username");
                $stmt->bindParam(":username", $_GET['name'], PDO::PARAM_STR);
                $stmt->execute();
                showModalRedirect("SUCCESS", "Erfolgreich", "<strong>".$_GET["name"]."</strong> ".$messages["unbanned"], "bans.php");
              } else {
                showModal("ERROR", "Fehler", "Der angeforderte Benutzer wurde nicht gefunden.");
              }
            }
            if(isset($_GET["delete"]) && isset($_GET["ip"])){
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM ips WHERE IP = :ip");
              $stmt->bindParam(":ip", $_GET['ip'], PDO::PARAM_STR);
              $stmt->execute();
              $data = 0;
              while($row = $stmt->fetch()){
                $data++;
              }
              if($data == 1){
                $stmt = $mysql->prepare("UPDATE ips SET BANNED = 0 WHERE IP = :ip");
                $stmt->bindParam(":ip", $_GET['ip'], PDO::PARAM_STR);
                $stmt->execute();
                showModalRedirect("SUCCESS", "Erfolgreich", "<strong>".$_GET["ip"]."</strong> ".$messages["unbanned"], "bans.php?ipbans");
              } else {
                showModal("ERROR", "Fehler", "Die angeforderte IP-Adresse wurde nicht gefunden.");
              }
            }
             ?>
            <h1><?php echo $messages["player_punish_ban"] ?></h1>
            <form action="bans.php" method="post">
              <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
              <input type="text" name="spieler" placeholder="<?php echo $messages["player"] ?>" required><br>
              <select name="grund">
                <?php
                require("./mysql.php");
                $stmt = $mysql->prepare("SELECT * FROM reasons");
                $stmt->execute();
                while($row = $stmt->fetch()){
                  if($row["TYPE"] == 0){
                      echo '<option value="'.$row["ID"].'">'.htmlspecialchars($row["REASON"]).'</option>';
                  }
                }
                 ?>
              </select><br>
              <button type="submit" name="submit"><?php echo $messages["player_punish_ban"] ?></button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
