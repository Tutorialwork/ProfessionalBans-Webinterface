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
                $.sweetModal.defaultSettings.confirm.yes.label = "<?php echo $messages["delete"] ?>";
                $.sweetModal.defaultSettings.confirm.cancel.label = "<?php echo $messages["cancel"] ?>";
                $.sweetModal.confirm('<?php echo str_replace("%username%", $name, $messages["delete_account_question"]) ?>', function() {
                  var xhttp = new XMLHttpRequest();
                  xhttp.open("GET", "accounts.php?delete&name=<?php echo $name ?>&confirmed");
                  xhttp.send();
                  $.sweetModal({
                    content: '<strong><?php echo $name ?></strong> <?php echo $messages["delete_account_success"] ?>',
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
              showModal("ERROR", $messages["error"], $messages["csrf_err"]);
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
                    showModalRedirect("SUCCESS", $messages["success"], str_replace(array("%username%", "%password%"), array(htmlspecialchars($_POST["mcusername"]), $klartext), $messages["create_account_success"]), "accounts.php");
                  } else {
                    showModal("ERROR", $messages["error"], $messages["username_taken"]);
                  }
                } else {
                  showModal("ERROR", $messages["error"], $messages["account_exist"]);
                }
              } else {
                showModal("ERROR", $messages["error"], $messages["player_404"]);
              }
            }
          } else {
            showModal("ERROR", $messages["error"], $messages["fill_err"]);
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
                <th><?php echo $messages["rank"] ?></th>
                <th>Google Authenticator</th>
                <th><?php echo $messages["event"] ?></th>
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
                  echo '<td>'.$messages["yes"].'</td>';
                } else {
                  echo '<td>'.$messages["no"].'</td>';
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
            <h1><?php echo $messages["create_account"] ?></h1>
            <form action="accounts.php" method="post">
              <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
              <input type="text" name="username" placeholder="Username" required><br>
              <input type="text" name="mcusername" placeholder="Minecraft Username" required><br>
              <select name="rang">
                <option value="3">Admin</option>
                <option value="2">Moderator</option>
                <option value="1">Supporter</option>
              </select><br>
              <button type="submit" name="submit"><?php echo $messages["create_account"] ?></button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
