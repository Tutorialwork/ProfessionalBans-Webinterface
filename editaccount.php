        <?php
        require("./inc/header.inc.php");
        if(!isAdmin($_SESSION['username'])){
          showModalRedirect("ERROR", $messages["error"], $messages["perms_err"], "index.php");
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
                showModal("ERROR", $messages["error"], $messages["csrf_err"]);
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
                  showModalRedirect("SUCCESS", $messages["success"], $messages["account_update"], "accounts.php");
                } else {
                  showModal("ERROR", $messages["error"], $messages["player_404"]);
                }
              }
            } else {
              //Erstelle Token wenn Formular nicht abgesendet wurde
              $_SESSION["CSRF"] = generateRandomString(25);
            }
             ?>
            <h1><?php echo $messages["account_edit"]." ".$_GET["name"]; ?></h1>
            <form action="editaccount.php?name=<?php echo $_GET["name"]; ?>" method="post">
              <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
              <?php
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = :username");
              $stmt->bindParam(":username", $_GET['name'], PDO::PARAM_STR);
              $stmt->execute();
              while($row = $stmt->fetch()){
                ?>
                <p><?php echo $messages["linked_account"] ?></p>
                <input type="text" name="mcusername" placeholder="<?php echo $messages["linked_account"] ?>" value="<?php echo UUIDResolve($row["UUID"]); ?>" required><br>
                <p><?php echo $messages["account_new_password"] ?></p>
                <input type="password" name="pw" placeholder="<?php echo $messages["account_new_password"] ?>"><br>
                <p><?php echo $messages["rank"] ?></p>
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
                    <option value="1"><?php echo $messages["enabled"] ?></option>
                    <option value="0"><?php echo $messages["disabled"] ?></option>
                  </select>
                  <?php
                }
              }
               ?>
              <button type="submit" name="submit"><?php echo $messages["save"] ?></button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
