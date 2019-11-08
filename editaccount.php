        <?php
        require("./inc/header.inc.php");
        if(!isAdmin($_SESSION['username'])){
          showModalRedirect("ERROR", "Fehler", "Der Zugriff auf diese Seite wurde verweigert.", "index.php");
          exit;
        }
        ?>
        <div class="flex-container animated fadeIn">
          <div class="flex item-1 sidebox">
            <?php
            if(!isset($_GET["name"])){
              showModalRedirect("ERROR", "Fehler", "Es wurde keine Anfrage gestellt.", "accounts.php");
              exit;
            }
            if(isset($_POST["submit"]) && isset($_SESSION["CSRF"])){
              if($_POST["CSRFToken"] != $_SESSION["CSRF"]){
                showModal("ERROR", "CSRF Fehler", "Deine Sitzung ist abgelaufen. Versuche die Seite erneut zu öffnen.");
              } else {
                require("./mysql.php");
                //Fetch UUID from Userinput
                $stmt = $mysql->prepare("SELECT UUID FROM bans WHERE NAME = :username");
                $stmt->bindParam(":username", $_POST["mcusername"], PDO::PARAM_STR);
                $stmt->execute();
                while($row = $stmt->fetch()){
                  $uuid = $row["UUID"];
                }
                if(isPlayerExists($uuid)){
                  $rankint = (int) $_POST["rang"];
                  if(!empty($_POST["pw"])){
                    $hash = password_hash($_POST["pw"], PASSWORD_BCRYPT);
                    $stmt = $mysql->prepare("UPDATE accounts SET UUID = :uuid, RANK = :rank, PASSWORD = :pw WHERE USERNAME = :user");
                    $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
                    $stmt->bindParam(":rank", $rankint, PDO::PARAM_INT);
                    $stmt->bindParam(":user", $_GET["name"], PDO::PARAM_STR);
                    $stmt->bindParam(":pw", $hash, PDO::PARAM_STR);
                    $stmt->execute();
                  } else {
                    $stmt = $mysql->prepare("UPDATE accounts SET UUID = :uuid, RANK = :rank WHERE USERNAME = :user");
                    $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
                    $stmt->bindParam(":rank", $rankint, PDO::PARAM_INT);
                    $stmt->bindParam(":user", $_GET["name"], PDO::PARAM_STR);
                    $stmt->execute();
                  }
                  if(hasGoogleAuth($_GET["name"])){
                    $statusint = (int) $_POST["gauth"];
                    if($statusint == 0){
                      $stmt = $mysql->prepare("UPDATE accounts SET GOOGLE_AUTH = 'null' WHERE USERNAME = :user");
                      $stmt->bindParam(":user", $_GET["name"], PDO::PARAM_STR);
                      $stmt->execute();
                    }
                  }
                  showModalRedirect("SUCCESS", "Erfolgreich", "Der Benutzer wurde erfolgreich aktualisiert.", "editaccount.php?name=".$_GET["name"]);
                } else {
                  showModal("ERROR", "Fehler", "Der eingegebene Minecraft Account hat das Netzwerk noch nie betreten.");
                }
              }
            } else {
              //Erstelle Token wenn Formular nicht abgesendet wurde
              $_SESSION["CSRF"] = generateRandomString(25);
            }
             ?>
            <h1>Account bearbeiten von <?php echo $_GET["name"]; ?></h1>
            <form action="editaccount.php?name=<?php echo $_GET["name"]; ?>" method="post">
              <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
              <?php
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = :username");
              $stmt->bindParam(":username", $_GET['name'], PDO::PARAM_STR);
              $stmt->execute();
              while($row = $stmt->fetch()){
                ?>
                <p>Verknüpfter Minecraft Account</p>
                <input type="text" name="mcusername" placeholder="Verknüpfter Minecraft Account" value="<?php echo UUIDResolve($row["UUID"]); ?>" required><br>
                <p>Neues Passwort festlegen</p>
                <input type="password" name="pw" placeholder="Neues Passwort festlegen"><br>
                <p>Rang</p>
                <select name="rang">
                  <?php
                  if(isAdmin($_GET["name"])){
                    ?>
                    <option value="3">Admin</option>
                    <option value="2">Moderator</option>
                    <option value="1">Supporter</option>
                    <?php
                  } else if(isMod($_GET["name"])){
                    ?>
                    <option value="2">Moderator</option>
                    <option value="3">Admin</option>
                    <option value="1">Supporter</option>
                    <?php
                  } else if(isSup($_GET["name"])){
                    ?>
                    <option value="1">Supporter</option>
                    <option value="3">Admin</option>
                    <option value="2">Moderator</option>
                    <?php
                  }
                   ?>
                </select>
                <?php
                if(hasGoogleAuth($_GET["name"])){
                  ?>
                  <p>Google Authenticator</p>
                  <select name="gauth">
                    <option value="1">Aktiviert</option>
                    <option value="0">Deaktiviert</option>
                  </select>
                  <?php
                }
              }
               ?>
              <button type="submit" name="submit">Speichern</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
