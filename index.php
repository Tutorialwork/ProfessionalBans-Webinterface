        <?php
        require("./inc/header.inc.php");
        ?>
        <div class="flex-container animated fadeIn">
          <div class="flex item-1">
            <h1><?php echo $messages["welcome"] . " " . htmlspecialchars($_SESSION["username"]); ?></h1>
            <p><i class="fas fa-lock"></i> <a href="google-auth.php">Google Authenticator</a>:
            <?php
            if (hasGoogleAuth($_SESSION["username"])) {
              echo $messages["enabled"];
            } else {
              echo $messages["disabled"];
            }
            ?>
            </p>
            <p><i class="fas fa-user-clock"></i> <?php
            echo $messages["lastlogin"] . ": " . getLastlogin(getUUID());
            ?></p>
            <p><i class="fas fa-mobile-alt"></i> App QR-Code: <a onclick="showQR()"><?php echo $messages["show"] ?></a></p>
            <?php
            $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $url = str_replace("index.php", "", $url);
            $url = $url . "app/login.php";
            ?>
            <script>
            function showQR(){
              $.sweetModal({
                title: 'ProfessionalBans App',
                content: '<?php echo $messages['app_popup'] ?><br><img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=<?php echo $url ?>&choe=UTF-8" title="ProfessionalBans App QR-Code" /><br><h3>App Download</h3><a href="https://play.google.com/store/apps/details?id=de.tutorialwork.professionalbansreloaded&pcampaignid=pcampaignidMKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1"><img alt="Jetzt bei Google Play" src="https://play.google.com/intl/en_us/badges/static/images/badges/de_badge_web_generic.png" width="150"/></a>'
              });
            }
            </script>
          </div>
        </div>
        <?php
        require("./mysql.php");
        $pstmt = $mysql->prepare("SELECT * FROM bans");
        $pstmt->execute();

        $mstmt = $mysql->prepare("SELECT * FROM bans WHERE MUTED = 1");
        $mstmt->execute();

        $bstmt = $mysql->prepare("SELECT * FROM bans WHERE BANNED = 1");
        $bstmt->execute();

        $onstmt = $mysql->prepare("SELECT * FROM bans WHERE ONLINE_STATUS = 1");
        $onstmt->execute();

        $punishstmt = $mysql->prepare("SELECT * FROM log WHERE ACTION = 'BAN' OR ACTION = 'MUTE'");
        $punishstmt->execute();

        $total_ontime = 0;
        while ($row = $pstmt->fetch()){
            $total_ontime += $row["ONLINE_TIME"];
        }

        $datetime1 = new DateTime();
        $datetime1->setTimestamp(time());
        $datetime2 = new DateTime();
        $datetime2->setTimestamp(time() - $total_ontime / 1000);
        $interval = $datetime1->diff($datetime2);

         ?>
        <div class="flex-container animated fadeIn">
          <div class="flex item-1">
            <h1><?php echo $messages["players"] ?>
              <div class="flex-icon">
                <i class="fas fa-users fa-2x"></i>
              </div>
            </h1>
            <h1 class="count"><?php echo $pstmt->rowCount(); ?></h1>
          </div>
          <div class="flex item-2">
            <h1><?php echo $messages["bans"] ?>
              <div class="flex-icon">
                <i class="fas fa-ban fa-2x"></i>
              </div>
            </h1>
            <h1 class="count"><?php echo $bstmt->rowCount(); ?></h1>
          </div>
          <div class="flex item-3">
            <h1><?php echo $messages["mutes"] ?>
              <div class="flex-icon">
                <i class="fas fa-volume-mute fa-2x"></i>
              </div>
            </h1>
            <h1 class="count"><?php echo $mstmt->rowCount(); ?></h1>
          </div>
        </div>
        <div class="flex-container animated fadeIn">
            <div class="flex item-1">
                <h1><?php echo $messages["online_player"] ?>
                    <div class="flex-icon">
                        <i class="fas fa-user-alt fa-2x"></i>
                    </div>
                </h1>
                <h1 class="count"><?php echo $onstmt->rowCount(); ?></h1>
            </div>
            <div class="flex item-2">
                <h1><?php echo $messages["punished_today"] ?>
                    <div class="flex-icon">
                        <i class="fas fa-user-lock fa-2x"></i>
                    </div>
                </h1>
                <h1 class="count"><?php echo $punishstmt->rowCount() ?></h1>
            </div>
            <div class="flex item-3">
                <h1><?php echo $messages["ontime_total"] ?>
                    <div class="flex-icon">
                        <i class="fas fa-hourglass-half fa-2x"></i>
                    </div>
                </h1>
                <h1 class="count"><?php echo $interval->format('<strong>%a</strong> '.$messages["days"].' und <strong>%h</strong> '.$messages["hours"].''); ?></h1>
            </div>
        </div>
        <div class="flex-container animated fadeIn">
          <div class="flex item-1">
            <h1><?php echo $messages["last_activities"] ?></h1>
            <table>
              <tr>
                <th><?php echo $messages["players"] ?></th>
                <th><?php echo $messages["from"] ?></th>
                <th><?php echo $messages["event"] ?></th>
                <th><?php echo $messages["date"] ?></th>
              </tr>
              <?php
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM log ORDER BY DATE DESC LIMIT 4");
              $stmt->execute();
              while ($row = $stmt->fetch()) {
                ?>
                <tr>
                  <td><?php
                  if($row["UUID"] == "KONSOLE"){
                    echo "Console";
                  } else if($row["UUID"] == "null"){
                      echo "───";
                  } else {
                      echo UUIDResolve($row["UUID"]);
                  }
                  ?></td>
                  <td><?php
                  if($row["BYUUID"] == "KONSOLE"){
                    echo "Console";
                  } else if($row["BYUUID"] == "null"){
                      echo "───";
                  } else {
                      echo UUIDResolve($row["BYUUID"]);
                  }
                   ?></td>
                  <td><?php
                  //Verfügbare Action Codes (Stand: 25.05.2019)
                  //BAN, MUTE, ADD_WORD_BLACKLIST, DEL_WORD_BLACKLIST, CREATE_CHATLOG, IPBAN_IP, IPBAN_PLAYER, KICK, REPORT, REPORT_OFFLINE, REPORT_ACCEPT, UNBAN_IP, UNBAN_BAN, UNBAN_MUTE,
                  //ADD_WEBACCOUNT, DEL_WEBACCOUNT, AUTOMUTE_ADBLACKLIST, AUTOMUTE_BLACKLIST
                  $action = $row["ACTION"];
                  $note = htmlspecialchars($row["NOTE"]);
                  if($action == "BAN"){
                      echo str_replace("%text%", htmlspecialchars(getReasonByReasonID($note)), $messages["event_BAN"]);
                  } else if($action == "MUTE"){
                      echo str_replace("%text%", htmlspecialchars(getReasonByReasonID($note)), $messages["event_MUTE"]);
                  } else if($action == "ADD_WORD_BLACKLIST"){
                      echo str_replace("%text%", $note, $messages["event_ADD_WORD_BLACKLIST"]);
                  } else if($action == "DEL_WORD_BLACKLIST"){
                      echo str_replace("%text%", $note, $messages["event_DEL_WORD_BLACKLIST"]);
                  } else if($action == "CREATE_CHATLOG"){
                    //Prepare Chatlog URL
                    $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
           					$finish_url = str_replace("index.php", "public/chatlog.php?id=", $url);
                      echo str_replace("%text%", $finish_url.$note, $messages["event_CREATE_CHATLOG"]);
                  } else if($action == "IPBAN_IP"){
                      echo str_replace("%text%", $note, $messages["event_IPBAN_IP"]);
                  } else if($action == "IPBAN_PLAYER"){
                      echo str_replace("%text%", htmlspecialchars(getReasonByReasonID($note)), $messages["event_IPBAN_PLAYER"]);
                  } else if($action == "KICK"){
                      echo str_replace("%text%", $note, $messages["event_KICK"]);
                  } else if($action == "REPORT"){
                      echo str_replace("%text%", $note, $messages["event_REPORT"]);
                  } else if($action == "REPORT_OFFLINE"){
                      echo str_replace("%text%", $note, $messages["event_REPORT_OFFLINE"]);
                  } else if($action == "REPORT_ACCEPT"){
                      echo str_replace("%text%", $note, $messages["event_REPORT_ACCEPT"]);
                  } else if($action == "UNBAN_IP"){
                      echo str_replace("%text%", $note, $messages["event_UNBAN_IP"]);
                  } else if($action == "UNBAN_BAN"){
                      echo str_replace("%text%", "", $messages["event_UNBAN_BAN"]);
                  } else if($action == "UNBAN_MUTE"){
                      echo str_replace("%text%", "", $messages["event_UNBAN_MUTE"]);
                  } else if($action == "ADD_WEBACCOUNT"){
                      echo str_replace("%text%", $note, $messages["event_ADD_WEBACCOUNT"]);
                  } else if($action == "DEL_WEBACCOUNT"){
                      echo str_replace("%text%", "", $messages["event_DEL_WEBACCOUNT"]);
                  } else if($action == "AUTOMUTE_ADBLACKLIST"){
                      echo str_replace("%text%", $note, $messages["event_AUTOMUTE_ADBLACKLIST"]);
                  } else if($action == "AUTOMUTE_BLACKLIST"){
                      echo str_replace("%text%", $note, $messages["event_AUTOMUTE_BLACKLIST"]);
                  }
                  ?></td>
                  <td><?php echo date($messages["date_format"],$row["DATE"]/1000); ?></td>
                </tr>
                <?php
              }
               ?>
            </table>
          </div>
          <div class="flex item-2">
            <?php
            //Form submit
            if(isset($_POST["submit"]) && isset($_SESSION["CSRF"])){
              if($_POST["CSRFToken"] != $_SESSION["CSRF"]){
                showModal("ERROR", $messages["error"], $messages["csrf_err"]);
              } else {
                $stmt = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = :username");
                $stmt->bindParam(":username", $_SESSION['username'], PDO::PARAM_STR);
                $stmt->execute();
                while($row = $stmt->fetch()){
                  if(password_verify($_POST['currentpw'], $row["PASSWORD"])){
                    if($_POST["newpw"] == $_POST["newpw2"]){
                      $hash = password_hash($_POST["newpw"], PASSWORD_BCRYPT);
                      $stmt = $mysql->prepare("UPDATE accounts SET PASSWORD = :pw WHERE USERNAME = :username");
                      $stmt->bindParam(":pw", $hash, PDO::PARAM_STR);
                      $stmt->bindParam(":username", $_SESSION['username'], PDO::PARAM_STR);
                      $stmt->execute();
                      showModal("SUCCESS", $messages["success"], $messages["pw_change_success"]);
                    } else {
                      showModal("ERROR", $messages["error"], $messages["pw_change_err_2"]);
                    }
                  } else {
                    showModal("ERROR", $messages["error"], $messages["pw_change_err_1"]);
                  }
                }
              }
            } else {
              //Erstelle Token wenn Formular nicht abgesendet wurde
              $_SESSION["CSRF"] = generateRandomString(25);
            }
             ?>
            <h1><?php echo $messages["change_password"] ?></h1>
            <form action="index.php" method="post">
              <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
              <input type="password" name="currentpw" placeholder="<?php echo $messages["old_password"] ?>" autocomplete="current-password" required><br>
              <input type="password" name="newpw" placeholder="<?php echo $messages["new_password"] ?>" autocomplete="new-password" required><br>
              <input type="password" name="newpw2" placeholder="<?php echo $messages["new_password2"] ?>" autocomplete="new-password" required><br>
              <button type="submit" name="submit"><?php echo $messages["change_password"] ?></button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
