        <?php
        require("./inc/header.inc.php");
        ?>
        <div class="flex-container animated fadeIn">
          <div class="flex item-1">
            <h1>Willkommen, <?php echo htmlspecialchars($_SESSION["username"]); ?></h1>
            <p><i class="fas fa-lock"></i> <a href="google-auth.php">Google Authenticator</a>:
            <?php
            if (hasGoogleAuth($_SESSION["username"])) {
              echo "Aktiviert";
            } else {
              echo "Deaktiviert";
            }
            ?>
            </p>
            <p><i class="fas fa-user-clock"></i> Letzter Login: <?php 
            echo getLastlogin(getUUID());
            ?></p>
            <p><i class="fas fa-mobile-alt"></i> App QR-Code: <a onclick="showQR()">Anzeigen</a></p>
            <?php
            $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $url = str_replace("index.php", "", $url);
            $url = $url . "app/login.php";
            ?>
            <script>
            function showQR(){
              $.sweetModal({
                title: 'ProfessionalBans App',
                content: 'Scanne diesen QR-Code um die App mit diesem Server zu verbinden.<br><img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=<?php echo $url ?>&choe=UTF-8" title="ProfessionalBans App QR-Code" /><br><h3>App Download</h3><a href="https://play.google.com/store/apps/details?id=de.tutorialwork.professionalbansreloaded&pcampaignid=pcampaignidMKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1"><img alt="Jetzt bei Google Play" src="https://play.google.com/intl/en_us/badges/static/images/badges/de_badge_web_generic.png" width="150"/></a>'
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
            <h1>Spieler
              <div class="flex-icon">
                <i class="fas fa-users fa-2x"></i>
              </div>
            </h1>
            <h1 class="count"><?php echo $pstmt->rowCount(); ?></h1>
          </div>
          <div class="flex item-2">
            <h1>Aktive Bans
              <div class="flex-icon">
                <i class="fas fa-ban fa-2x"></i>
              </div>
            </h1>
            <h1 class="count"><?php echo $bstmt->rowCount(); ?></h1>
          </div>
          <div class="flex item-3">
            <h1>Aktive Mutes
              <div class="flex-icon">
                <i class="fas fa-volume-mute fa-2x"></i>
              </div>
            </h1>
            <h1 class="count"><?php echo $mstmt->rowCount(); ?></h1>
          </div>
        </div>
        <div class="flex-container animated fadeIn">
            <div class="flex item-1">
                <h1>Spieler online
                    <div class="flex-icon">
                        <i class="fas fa-user-alt fa-2x"></i>
                    </div>
                </h1>
                <h1 class="count"><?php echo $onstmt->rowCount(); ?></h1>
            </div>
            <div class="flex item-2">
                <h1>Bestrafte Spieler heute
                    <div class="flex-icon">
                        <i class="fas fa-user-lock fa-2x"></i>
                    </div>
                </h1>
                <h1 class="count"><?php echo $punishstmt->rowCount() ?></h1>
            </div>
            <div class="flex item-3">
                <h1>Onlinezeit Gesamt
                    <div class="flex-icon">
                        <i class="fas fa-hourglass-half fa-2x"></i>
                    </div>
                </h1>
                <h1 class="count"><?php echo $interval->format('<strong>%a</strong> Tage und <strong>%h</strong> Stunden'); ?></h1>
            </div>
        </div>
        <div class="flex-container animated fadeIn">
          <div class="flex item-1">
            <h1>Letzte Aktivitäten</h1>
            <table>
              <tr>
                <th>Spieler</th>
                <th>Von</th>
                <th>Ereignis</th>
                <th>Datum</th>
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
                    echo "Konsole";
                  } else {
                    echo UUIDResolve($row["UUID"]);
                  }
                  ?></td>
                  <td><?php
                  if($row["BYUUID"] == "KONSOLE"){
                    echo "Konsole";
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
                    echo "wurde gebannt wegen <strong>".htmlspecialchars(getReasonByReasonID($note))."</strong>";
                  } else if($action == "MUTE"){
                    echo "wurde gemutet wegen <strong>".htmlspecialchars(getReasonByReasonID($note))."</strong>";
                  } else if($action == "ADD_WORD_BLACKLIST"){
                    echo "hat das Wort <strong>".$note."</strong> verboten";
                  } else if($action == "DEL_WORD_BLACKLIST"){
                    echo "hat das Wort <strong>".$note."</strong> erlaubt";
                  } else if($action == "CREATE_CHATLOG"){
                    //Prepare Chatlog URL
                    $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
           					$finish_url = str_replace("index.php", "public/chatlog.php?id=", $url);
                    echo 'hat einen <a href="'.$finish_url.$note.'">Chatlog</a> erstellt';
                  } else if($action == "IPBAN_IP"){
                    echo "hat die IP <strong>".$note."</strong> gebannt";
                  } else if($action == "IPBAN_PLAYER"){
                    echo "wurde IP gebannt wegen <strong>".htmlspecialchars(getReasonByReasonID($note))."</strong>";
                  } else if($action == "KICK"){
                    echo "wurde gekickt wegen <strong>".$note."</strong>";
                  } else if($action == "REPORT"){
                    echo "wurde gemeldet wegen <strong>".$note."</strong>";
                  } else if($action == "REPORT_OFFLINE"){
                    echo "wurde gemeldet wegen <strong>".$note."</strong>";
                  } else if($action == "REPORT_ACCEPT"){
                    echo "hat einen Report angenommen <strong>#".$note."</strong>";
                  } else if($action == "UNBAN_IP"){
                    echo "hat die IP <strong>".$note."</strong> entbannt";
                  } else if($action == "UNBAN_BAN"){
                    echo "wurde entbannt";
                  } else if($action == "UNBAN_MUTE"){
                    echo "wurde entmutet";
                  } else if($action == "ADD_WEBACCOUNT"){
                    echo "hat einen Webaccount mit dem Rang <strong>".$note."</strong> erstellt";
                  } else if($action == "DEL_WEBACCOUNT"){
                    echo "hat den Webaccount gelöscht";
                  } else if($action == "AUTOMUTE_ADBLACKLIST"){
                    echo "wurde automatisch gemutet wegen Werbung (<strong>".$note."</strong>)";
                  } else if($action == "AUTOMUTE_BLACKLIST"){
                    echo "wurde automatisch gemutet wegen seinem Verhalten (<strong>".$note."</strong>)";
                  }
                  ?></td>
                  <td><?php echo date('d.m.Y H:i',$row["DATE"]/1000); ?></td>
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
                showModal("ERROR", "CSRF Fehler", "Deine Sitzung ist abgelaufen. Versuche die Seite erneut zu öffnen.");
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
                      showModal("SUCCESS", "Erfolgreich", "Dein Passwort wurde erfolgreich geändert.");
                    } else {
                      showModal("ERROR", "Fehler", "Die Passwörter stimmen nicht überein.");
                    }
                  } else {
                    showModal("ERROR", "Fehler", "Dein altes Passwort stimmt nicht.");
                  }
                }
              }
            } else {
              //Erstelle Token wenn Formular nicht abgesendet wurde
              $_SESSION["CSRF"] = generateRandomString(25);
            }
             ?>
            <h1>Passwort ändern</h1>
            <form action="index.php" method="post">
              <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
              <input type="password" name="currentpw" placeholder="Altes Passwort" autocomplete="current-password" required><br>
              <input type="password" name="newpw" placeholder="Neues Passwort" autocomplete="new-password" required><br>
              <input type="password" name="newpw2" placeholder="Neues Passwort wiederholen" autocomplete="new-password" required><br>
              <button type="submit" name="submit">Passwort ändern</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
