        <?php
        require("./inc/header.inc.php");
        if(!isAdmin($_SESSION['username'])){
          showModalRedirect("ERROR", "Fehler", "Der Zugriff auf diese Seite wurde verweigert.", "index.php");
          exit;
        }
        if(isset($_GET["delete"]) && isset($_GET["name"])){
          if(!isset($_GET["confirmed"])){
            if(!empty($_GET["name"])){
              $name = htmlspecialchars($_GET["name"], ENT_QUOTES, 'UTF-8');
              ?>
              <script>
                $.sweetModal.defaultSettings.confirm.yes.label = "Löschen";
                $.sweetModal.defaultSettings.confirm.cancel.label = "Abbrechen";
                $.sweetModal.confirm('Möchtest du wirklich <strong><?php echo $name ?></strong> löschen?', function() {
                  var xhttp = new XMLHttpRequest();
                  xhttp.open("GET", "accounts.php?delete&name=<?php echo $name ?>&confirmed");
                  xhttp.send();
                  $.sweetModal({
                    content: '<strong><?php echo $name ?></strong> wurde erfolgreich gelöscht.',
                    icon: $.sweetModal.ICON_SUCCESS,
                    onClose: function(){
                      window.location = "accounts.php";
                    }
                  });
                });
              </script>
              <?php
            } else {
              header("Location: accounts.php");
            }
          } else {
            require("./mysql.php");
            $stmt = $mysql->prepare("DELETE FROM accounts WHERE USERNAME = :user");
            $stmt->execute(array(":user" => $_GET["name"]));
          }
        }
        if(isset($_POST["submit"]) && isset($_SESSION["CSRF"])){
          if(!empty($_POST["username"]) && !empty($_POST["mcusername"])){
            if($_POST["CSRFToken"] != $_SESSION["CSRF"]){
              showModal("ERROR", "CSRF Fehler", "Deine Sitzung ist abgelaufen. Versuche die Seite erneut zu öffnen.");
            } else {
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :request");
              $stmt->execute(array(":request" => $_POST["mcusername"]));
              $row = $stmt->fetch();
              if(isPlayerExists($row["UUID"])){
                $stmt = $mysql->prepare("SELECT * FROM accounts WHERE UUID = :request");
                $stmt->execute(array(":request" => $row["UUID"]));
                if($stmt->rowCount() == 0){
                  $stmt = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = :request");
                  $stmt->execute(array(":request" => $_POST["username"]));
                  if($stmt->rowCount() == 0){
                    $stmt = $mysql->prepare("INSERT INTO accounts(UUID, USERNAME, PASSWORD, RANK, GOOGLE_AUTH, AUTHCODE) 
                    VALUES (:uuid, :name, :hash, :rank, 'null', 'initialpassword')");
                    $klartext = generateRandomString();
                    $hash = password_hash($klartext, PASSWORD_BCRYPT);
                    $stmt->execute(array(":uuid" => $row["UUID"], ":name" => $_POST["username"], ":hash" => $hash, ":rank" => $_POST["rang"]));
                    showModalRedirect("SUCCESS", "Erfolgreich", "Es wurde erfolgreich für <strong>".htmlspecialchars($_POST["mcusername"])."</strong> ein Webaccount angelegt mit dem temporären Passwort: <strong>".$klartext."</strong>", "accounts.php");
                  } else {
                    showModal("ERROR", "Fehler", "Dieser Username ist bereits vergeben");
                  }
                } else {
                  showModal("ERROR", "Fehler", "Dieser Spieler hat bereits einen Webaccount");
                }
              } else {
                showModal("ERROR", "Fehler", "Dieser Spieler hat das Netzwerk noch nie betreten");
              }
            }
          } else {
            showModal("ERROR", "Fehler", "Damit ein neuer Account erstellt werden kann werden alle Felder benötigt");
          }
        } else {
          //Erstelle Token wenn Formular nicht abgesendet wurde
          $_SESSION["CSRF"] = generateRandomString(25);
        }
        ?>
        <div class="flex-container animated fadeIn">
          <div class="flex item-1">
            <h1>Accounts</h1>
            <table>
              <tr>
                <th>Username</th>
                <th>Rang</th>
                <th>Google Authenticator</th>
                <th>Aktionen</th>
              </tr>
              <?php
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM accounts");
              $stmt->execute();
              while($row = $stmt->fetch()){
                echo "<tr>";
                echo '<td><strong>'.htmlspecialchars($row["USERNAME"]).'</strong></td>';
                if($row["RANK"] == 3){
                  echo '<td>Admin</td>';
                } else if($row["RANK"] == 2){
                  echo '<td>Moderator</td>';
                } else if($row["RANK"] == 1){
                  echo '<td>Supporter</td>';
                }
                if($row["GOOGLE_AUTH"] != "null"){
                  echo '<td>Ja</td>';
                } else {
                  echo '<td>Nein</td>';
                }
                  echo '<td><a href="editaccount.php?name='.$row["USERNAME"].'""><i class="material-icons">edit</i></a> ';
                if($row["USERNAME"] != $_SESSION["username"]){
                  echo '<a href="accounts.php?delete&name='.$row["USERNAME"].'"><i class="material-icons">block</i></a></td>';
                }
                echo "</tr>";
              }
               ?>
            </table>
          </div>
          <div class="flex item-2 sidebox">
            <h1>Account erstellen</h1>
            <form action="accounts.php" method="post">
              <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
              <input type="text" name="username" placeholder="Username" required><br>
              <input type="text" name="mcusername" placeholder="Minecraft Username" required><br>
              <select name="rang">
                <option value="3">Admin</option>
                <option value="2">Moderator</option>
                <option value="1">Supporter</option>
              </select><br>
              <button type="submit" name="submit">Account erstellen</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
