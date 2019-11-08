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
            if(!isset($_GET["id"])){
              showModalRedirect("ERROR", "Fehler", "Es wurde keine Anfrage gestellt.", "reasons.php");
              exit;
            }
            if(isset($_POST["submit"]) && isset($_SESSION["CSRF"])){
              if($_POST["CSRFToken"] != $_SESSION["CSRF"]){
                showModal("ERROR", "CSRF Fehler", "Deine Sitzung ist abgelaufen. Versuche die Seite erneut zu öffnen.");
              } else {
                require("./mysql.php");
                $id = $_GET["id"];
                if(filter_var($_POST['zeit'], FILTER_VALIDATE_INT)){
                  $zeit = $_POST['zeit'];
                } else {
                  showModalRedirect("ERROR", "Fehler", "Du hast keine gültige Zahl angegeben.", "editreason.php?id=".$_GET["id"]);
                  exit;
                }

                $stmt = $mysql->prepare("SELECT * FROM reasons WHERE id = :id");
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                $stmt->execute();
                $data = 0;
                while($row = $stmt->fetch()){
                      $data++;
                }
                if($data == 1){
                  if($_POST["einheit"] == "m"){
                    $minuten = $zeit;
                  } else if($_POST["einheit"] == "s"){
                    $minuten = $zeit * 60;
                  } else if($_POST["einheit"] == "t"){
                    $minuten = $zeit * 60 * 24;
                  }
                  if($_POST["type"] == "ban"){
                    $type = 0;
                  } else if($_POST["type"] == "mute"){
                    $type = 1;
                  } else if($_POST["type"] == "permaban"){
                    $type = 0;
                    $minuten = -1;
                  } else if($_POST["type"] == "permamute"){
                    $type = 1;
                    $minuten = -1;
                  }

                    //Update Grund
                    $uhrzeit = time();
                    $stmt = $mysql->prepare("UPDATE reasons SET REASON = :grund, TIME = :min, TYPE = :type, PERMS = :perms WHERE ID = :id");
                    $stmt->bindParam(":grund", $_POST['grund'], PDO::PARAM_STR);
                    $stmt->bindParam(":min", $minuten, PDO::PARAM_INT);
                    $stmt->bindParam(":type", $type, PDO::PARAM_INT);
                    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                    if($_POST["perms"] != ""){
                      $perms = $_POST["perms"];
                    } else {
                      $perms = "null";
                    }
                    $stmt->bindParam(":perms", $perms, PDO::PARAM_STR);
                    $stmt->execute();
                    showModalRedirect("SUCCESS", "Erfolgreich", "Der Grund <strong>".htmlspecialchars($_POST["grund"])."</strong> wurde erfolgreich bearbeitet.", "editreason.php?id=".$_GET["id"]);
                } else {
                  showModal("ERROR", "Fehler", "Diese ID ist nicht registriert.");
                }
                exit;
              }
            } else {
              //Erstelle Token wenn Formular nicht abgesendet wurde
              $_SESSION["CSRF"] = generateRandomString(25);
            }
             ?>
            <h1>Bangrund bearbeiten #<strong><?php echo $_GET["id"]; ?></strong></h1>
            <form action="editreason.php?id=<?php echo $_GET["id"]; ?>" method="post">
              <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
              <p>Grund</p>
              <input type="text" name="grund" value="<?php echo getReasonByReasonID($_GET["id"]) ?>" maxlength="16" required/>
              <p>Zeit</p>
              <input type="number" name="zeit" value="<?php
              if(getTimeByReasonID($_GET["id"]) < 60){
                echo getTimeByReasonID($_GET["id"]);
              } else if(getTimeByReasonID($_GET["id"]) > 1439){
                $type = 2;
                echo getTimeByReasonID($_GET["id"]) / 60 / 24;
              } else {
                $type = 1;
                echo getTimeByReasonID($_GET["id"]) / 60;
              }
               ?>" required/>
               <p>Permission</p>
               <input type="text" name="perms" value="<?php echo getPermsByReasonID($_GET["id"]) ?>"/>
               <p>Einheit</p>
               <select name="einheit">
                 <?php
                 if($type == 0){
                   echo '<option value="m">Minuten</option>
                   <option value="s">Stunden</option>
                   <option value="t">Tage</option>';
                 } else if($type == 1){
                   echo '<option value="s">Stunden</option>
                   <option value="m">Minuten</option>
                   <option value="t">Tage</option>';
                 } else if($type == 2){
                   echo '<option value="t">Tage</option>
                   <option value="s">Stunden</option>
                   <option value="m">Minuten</option>';
                 }
                  ?>
               </select>
               <p>Typ</p>
               <select name="type">
                 <?php
                 if(isMuteByReasonID($_GET["id"]) && getTimeByReasonID($_GET["id"]) == -1){
                   echo '<option value="permamute">Permanenter Mute</option>
                   <option value="ban">Ban</option>
                   <option value="mute">Mute</option>
                   <option value="permaban">Permanenter Ban</option>';
                 } if(isMuteByReasonID($_GET["id"]) && getTimeByReasonID($_GET["id"]) != -1){
                   echo '<option value="mute">Mute</option>
                   <option value="ban">Ban</option>
                   <option value="permaban">Permanenter Ban</option>
                   <option value="permamute">Permanenter Mute</option>';
                 } else if(!isMuteByReasonID($_GET["id"]) && getTimeByReasonID($_GET["id"]) == -1){
                   echo '<option value="permaban">Permanenter Ban</option>
                   <option value="ban">Ban</option>
                   <option value="mute">Mute</option>
                   <option value="permamute">Permanenter Mute</option>';
                 } else if(!isMuteByReasonID($_GET["id"]) && getTimeByReasonID($_GET["id"]) != -1){
                   echo '<option value="ban">Ban</option>
                   <option value="mute">Mute</option>
                   <option value="permaban">Permanenter Ban</option>
                   <option value="permamute">Permanenter Mute</option>';
                 }
                  ?>
               </select>
              <button type="submit" name="submit">Speichern</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
