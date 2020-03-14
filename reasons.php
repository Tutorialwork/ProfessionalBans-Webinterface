        <?php
        require("./inc/header.inc.php");
        if(!isAdmin($_SESSION['username'])){
          showModalRedirect("ERROR", $messages["error"], $messages["perms_err"], "index.php");
          exit;
        }
        ?>
        <div class="flex-container animated fadeIn">
          <div class="flex item-1">
            <?php
            if(isset($_GET["delete"]) && isset($_GET["id"])){
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM reasons WHERE ID = :id");
              $stmt->bindParam(":id", $_GET['id'], PDO::PARAM_INT);
              $stmt->execute();
              $data = 0;
              while($row = $stmt->fetch()){
                $data++;
              }
              if($data == 1){
                $stmt = $mysql->prepare("DELETE FROM reasons WHERE ID = :id");
                $stmt->bindParam(":id", $_GET['id'], PDO::PARAM_INT);
                $stmt->execute();
                $idstmt = $mysql->prepare("SELECT * FROM reasons");
                $idstmt->execute();
                $id = 0;
                while($row = $idstmt->fetch()){
                  $id++;
                  $stmt = $mysql->prepare("UPDATE reasons SET ID = :id WHERE ID = :dbid");
                  $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                  $stmt->bindParam(":dbid", $row["ID"], PDO::PARAM_INT);
                  $stmt->execute();
                }
                showModalRedirect("SUCCESS", $messages["success"], $messages["banreason_deleted"], "reasons.php");
              } else {
                showModal("ERROR", $messages["error"], $messages["banreason_404"]);
              }
            }

             ?>
             <script>
               $(function() {
                  $( "tbody" ).sortable({
                    axis: 'y',
                    update: function (event, ui) {
                      var data = $(this).sortable('serialize');

                      // POST to server using $.post or $.ajax
                      $.ajax({
                        data: data,
                        type: 'POST',
                        url: 'fetch.php?type=SORTINDEX'
                      });
                    }

                  }
                  );
                });
             </script>
            <h1><?php echo $messages["banreasons"] ?></h1>
            <table> 
              <tr>
                <th>ID</th>
                <th><?php echo $messages["reason"] ?></th>
                <th><?php echo $messages["duration"] ?></th>
                <th>Type</th>
                <th><?php echo $messages["added_at"] ?></th>
                <th>Bans</th>
                <th>Permission</th>
                <th><?php echo $messages["event"] ?></th>
              </tr>
              <tbody>
                <?php
                require("./mysql.php");
                $stmt = $mysql->prepare("SELECT * FROM reasons ORDER BY SORTINDEX ASC");
                $stmt->execute();
                while($row = $stmt->fetch()){
                  echo '<tr id="item-'.$row["ID"].'">';
                  echo '<td>'.$row["ID"].'</td>';
                  echo '<td>'.htmlspecialchars($row["REASON"]).'</td>';
                  if($row["TIME"] == -1){
                    echo "<td>Permanent</td>";
                  } else if($row["TIME"] < 60){
                    echo '<td>'.$row["TIME"].' '.$messages["minutes"].'</td>';
                  } else if($row["TIME"] < 1440){
                    $stunden = $row["TIME"] / 60;
                    echo '<td>'.$stunden.' '.$messages["hours"].'</td>';
                  } else {
                    $tage = $row["TIME"] / 1440;
                    echo '<td>'.$tage.' '.$messages["days"].'</td>';
                  }
                  if($row["TYPE"] == 0){
                    echo '<td>BAN</td>';
                  } else {
                    echo '<td>MUTE</td>';
                  }
                  echo '<td>'.date($messages["date_format"],$row["ADDED_AT"]).'</td>';
                  echo '<td>'.$row["BANS"].'</td>';
                  if($row["PERMS"] == "null"){
                    echo '<td>'.$messages["none"].'</td>';
                  } else {
                    echo '<td>'.$row["PERMS"].'</td>';
                  }
                  echo '<td><a href="editreason.php?id='.$row["ID"].'"><i class="material-icons">edit</i></a> ';
                  echo '<a href="reasons.php?delete&id='.$row["ID"].'"><i class="material-icons">block</i></a></td>';
                  echo '</tr>';
                }
                 ?>
              </tbody>
              
            </table>
          </div>
          <div class="flex item-2 sidebox">
            <?php
            if(isset($_POST["submit"]) && isset($_SESSION["CSRF"])){
              if($_POST["CSRFToken"] != $_SESSION["CSRF"]){
                showModal("ERROR", $messages["error"], $messages["csrf_err"]);
              } else {
                require("./mysql.php");
                $id = 0;
                if(isset($_POST["id"])){
                  $id = $_POST["id"];
                } else {
                  $id = countReasons() + 1;
                }

                if(filter_var($_POST['zeit'], FILTER_VALIDATE_INT)){
                  $zeit = $_POST['zeit'];
                } else {
                  showModalRedirect("ERROR", $messages["error"], $messages["no_valid_number"], "reasons.php");
                  exit;
                }

                if(getReasonByReasonID($id) == null){
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
                  $stmt = $mysql->prepare("SELECT * FROM reasons WHERE REASON = :grund");
                  $stmt->bindParam(":grund", $_POST['grund'], PDO::PARAM_STR);
                  $stmt->execute();
                  $count = $stmt->rowCount();
  
                  if($count == 0){
                    $uhrzeit = time();
                    $stmt = $mysql->prepare("INSERT INTO reasons (ID, REASON, TIME, TYPE, ADDED_AT, BANS, PERMS, SORTINDEX) VALUES (:id, :grund, :min, :type, :now, 0, :perms, :id)");
                    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                    $stmt->bindParam(":grund", $_POST['grund'], PDO::PARAM_STR);
                    $stmt->bindParam(":min", $minuten, PDO::PARAM_INT);
                    $stmt->bindParam(":type", $type, PDO::PARAM_INT);
                    $stmt->bindParam(":now", $uhrzeit, PDO::PARAM_INT);
                    if($_POST["perms"] != ""){
                      $perms = $_POST["perms"];
                    } else {
                      $perms = "null";
                    }
                    $stmt->bindParam(":perms", $perms, PDO::PARAM_STR);
                    $stmt->execute();
                    showModalRedirect("SUCCESS", $messages["success"], str_replace("%reason%", htmlspecialchars($_POST["grund"]), $messages["banreason_created"]), "reasons.php");
                } else {
                  showModal("ERROR", $messages["error"], $messages["banid_exists"]);
                }

                
              } else {
                showModal("ERROR", $messages["error"], $messages["reason_exists"]);
              }
               
              }
            } else {
              //Erstelle Token wenn Formular nicht abgesendet wurde
              $_SESSION["CSRF"] = generateRandomString(25);
            }
             ?>
            <h1><?php echo $messages["create_banreason"] ?></h1>
            <form action="reasons.php" method="post">
              <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
              <input type="number" name="id" placeholder="ID" value="<?php echo countReasons() + 1 ?>" require><br>
              <input type="text" name="grund" placeholder="<?php echo $messages["reason"] ?>" required><br>
              <input type="number" name="zeit" placeholder="<?php echo $messages["duration"] ?>" required><br>
              <input type="text" name="perms" placeholder="Permission (optional)"><br>
              <select name="einheit">
                <option value="m"><?php echo $messages["minutes"] ?></option>
                <option value="s"><?php echo $messages["hours"] ?></option>
                <option value="t"><?php echo $messages["days"] ?></option>
              </select><br>
              <select name="type">
                <option value="ban">Ban</option>
                <option value="mute">Mute</option>
                <option value="permaban">Permanenter Ban</option>
                <option value="permamute">Permanenter Mute</option>
              </select><br>
              <button type="submit" name="submit"><?php echo $messages["create_banreason"] ?></button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
