        <?php
        require("./inc/header.inc.php");
        if (!isMod($_SESSION['username'])) {
          showModalRedirect("ERROR", "Fehler", "Der Zugriff auf diese Seite wurde verweigert.", "index.php");
          exit;
        }
        ?>
        <div class="flex-container animated fadeIn">
          <div class="flex item-1">
            <?php
            if (!isset($_GET["ipbans"])) {
              ?>
              <div class="flex-button">
                <a href="bans.php?ipbans" class="btn"><i class="fas fa-book-open"></i> IP-Bans</a>
              </div>
              <h1>Aktive Bans</h1>
              <table>
                <tr>
                  <th>Spieler</th>
                  <th>Grund</th>
                  <th>gebannt bis</th>
                  <th>gebannt von</th>
                  <th>Aktionen</th>
                </tr>
                <tr>
                  <?php

                    $stmt = MySQLWrapper()->prepare("SELECT * FROM bans");
                    $stmt->execute();
                    while ($row = $stmt->fetch()) {
                      if ($row["BANNED"] == 1) {
                        echo "<tr>";
                        echo '<td>' . $row["NAME"] . '</td>';
                        echo '<td>' . htmlspecialchars($row["REASON"]) . '</td>';
                        echo '<td>' . (($row["END"] <= 0) ? "Permanent" : date('d.m.Y H:i', $row["END"] / 1000)) . '</td>';
                        echo '<td>';
                        if ($row["TEAMUUID"] == "KONSOLE") {
                          echo "Konsole";
                        } else {
                          echo UUIDResolve($row["TEAMUUID"]);
                        }
                        echo '</td>';
                        echo '<td><a href="bans.php?delete&name=' . $row["NAME"] . '"><i class="material-icons">block</i></a></td>';
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
              <h1>Aktive IP-Bans</h1>
              <table>
                <tr>
                  <th>IP</th>
                  <th>Spieler</th>
                  <th>Grund</th>
                  <th>gebannt bis</th>
                  <th>gebannt von</th>
                  <th>Aktionen</th>
                </tr>
                <tr>
                  <?php

                    $stmt = MySQLWrapper()->prepare("SELECT * FROM ips");
                    $stmt->execute();
                    while ($row = $stmt->fetch()) {
                      if ($row["BANNED"] == 1) {
                        echo "<tr>";
                        echo '<td>' . $row["IP"] . '</td>';
                        echo '<td>';
                        if ($row["USED_BY"] != "null") {
                          echo UUIDResolve($row["USED_BY"]);
                        }
                        echo '</td>';
                        echo '<td>' . htmlspecialchars($row["REASON"]) . '</td>';
                        echo '<td>' . ($row["END"] <= 0) ? "Permanent" : date('d.m.Y H:i', $row["END"] / 1000) . '</td>';
                        echo '<td>';
                        if ($row["TEAMUUID"] == "KONSOLE") {
                          echo "Konsole";
                        } else {
                          echo UUIDResolve($row["TEAMUUID"]);
                        }
                        echo '</td>';
                        echo '<td><a href="bans.php?delete&ip=' . $row["IP"] . '"><i class="material-icons">block</i></a></td>';
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
            if (isset($_POST["submit"]) && isset($_SESSION["CSRF"])) {
              if ($_POST["CSRFToken"] != $_SESSION["CSRF"]) {
                showModal("ERROR", "CSRF Fehler", "Deine Sitzung ist abgelaufen. Versuche die Seite erneut zu öffnen.");
              } else {
                if (empty($_POST["grund"])) {
                  showModal("ERROR", "Fehler", "Bitte gebe einen Grund an!");
                } else {
                  //Fetch UUID from Userinput
                  $stmt = MySQLWrapper()->prepare("SELECT UUID FROM bans WHERE NAME = :username");
                  $stmt->bindParam(":username", $_POST["spieler"], PDO::PARAM_STR);
                  $stmt->execute();
                  while ($row = $stmt->fetch()) {
                    $uuid = $row["UUID"];
                  }
                  if (isPlayerExists($uuid)) {
                    if (isBanned($uuid)) {
                      showModalRedirect("ERROR", "Fehler", "Dieser Spieler ist bereits gebannt.", "bans.php");
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
                    $stmt = MySQLWrapper()->prepare("UPDATE bans SET BANNED = 1, MUTED = 0, REASON = :reason, END = :end, TEAMUUID = :webUUID  WHERE UUID = :uuid");
                    $reason = getReasonByReasonID($_POST["grund"]);
                    $stmt->bindParam(":reason", $reason, PDO::PARAM_STR);
                    $stmt->bindParam(":end", $javaEND, PDO::PARAM_STR);

                    //UUID von User im Webinterface
                    $stmtUUID = MySQLWrapper()->prepare("SELECT * FROM accounts WHERE USERNAME = :name");
                    $stmtUUID->bindParam(":name", $_SESSION["username"], PDO::PARAM_STR);
                    $stmtUUID->execute();
                    $row = $stmtUUID->fetch();

                    if (!empty($row)) {
                      $stmt->bindParam(":webUUID", $row["UUID"], PDO::PARAM_STR);
                      $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
                      $stmt->execute();
                      addBanCounter($uuid);
                      showModalRedirect("SUCCESS", "Erfolgreich", "Der Spieler <strong>" . htmlspecialchars($_POST["spieler"]) . "</strong> wurde erfolgreich gebannt.", "bans.php");
                    } else {
                      showModal("ERROR", "Fehler", "Unbekannter fehler aufgetreten, bitte versuche es erneut.");
                    }
                  } else {
                    showModal("ERROR", "Fehler", "Dieser Spieler hat das Netzwerk noch nie betreten.");
                  }
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
              $row = $stmt->fetch();
              if (!empty($row)) {
                $stmt = MySQLWrapper()->prepare("UPDATE bans SET BANNED = 0 WHERE NAME = :username");
                $stmt->bindParam(":username", $_GET['name'], PDO::PARAM_STR);
                $stmt->execute();
                showModalRedirect("SUCCESS", "Erfolgreich", "<strong>" . $_GET["name"] . "</strong> wurde erfolgreich entbannt.", "bans.php");
              } else {
                showModal("ERROR", "Fehler", "Der angeforderte Benutzer wurde nicht gefunden.");
              }
            }
            if (isset($_GET["delete"]) && isset($_GET["ip"])) {

              $stmt = MySQLWrapper()->prepare("SELECT * FROM ips WHERE IP = :ip");
              $stmt->bindParam(":ip", $_GET['ip'], PDO::PARAM_STR);
              $stmt->execute();
              $row = $stmt->fetch();
              if (!empty($row)) {
                $stmt = MySQLWrapper()->prepare("UPDATE ips SET BANNED = 0 WHERE IP = :ip");
                $stmt->bindParam(":ip", $_GET['ip'], PDO::PARAM_STR);
                $stmt->execute();
                showModalRedirect("SUCCESS", "Erfolgreich", "Die IP-Adresse <strong>" . $_GET["ip"] . "</strong> wurde erfolgreich entbannt.", "bans.php?ipbans");
              } else {
                showModal("ERROR", "Fehler", "Die angeforderte IP-Adresse wurde nicht gefunden.");
              }
            }
            ?>
            <h1>Spieler bannen</h1>
            <form action="bans.php" method="post">
              <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
              <input type="text" name="spieler" placeholder="Spieler" required><br>
              <select name="grund">
                <?php

                $stmt = MySQLWrapper()->prepare("SELECT * FROM reasons");
                $stmt->execute();
                while ($row = $stmt->fetch()) {
                  if ($row["TYPE"] == 0) {
                    echo '<option value="' . $row["ID"] . '">' . htmlspecialchars($row["REASON"]) . '</option>';
                  }
                }
                ?>
              </select><br>
              <button type="submit" name="submit">Spieler bannen</button>
            </form>
          </div>
        </div>
        </div>
        </div>
        </body>

        </html>