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
            if(!isset($_GET["archiv"])){
              ////////////////////////////////////////
              // Show only reports flagged as unedited
              ////////////////////////////////////////
              if(isset($_GET["done"]) && isset($_GET["id"])){
                require("mysql.php");
                $uuid = "null";
                $stmt2 = $mysql->prepare("SELECT NAME, UUID FROM bans WHERE NAME = :name");
                $name = $_SESSION["username"];
                $stmt2->bindParam(":name", $name, PDO::PARAM_STR);
                $stmt2->execute();
                $row = $stmt2->fetch();
                $uuid = $row["UUID"];
                $stmt = $mysql->prepare("UPDATE reports SET status = 1, TEAM = :webuser WHERE ID = :id");
                $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_INT);
                $stmt->bindParam(":webuser", $uuid, PDO::PARAM_STR);
                $stmt->execute();
                showModalRedirect("SUCCESS", $messages["success"], $messages["report_done"], "reports.php");
              }
              ?>
              <div class="flex-button">
                <a href="reports.php?archiv" class="btn"><i class="fas fa-book-open"></i> <?php echo $messages["archive"] ?></a>
                <a href="chatlogs.php" class="btn"><i class="fas fa-comments"></i> Chatlogs</a>
              </div>
              <h1><?php echo $messages["open_reports"] ?></h1>
              <table>
                <tr>
                  <th><?php echo $messages["player"] ?></th>
                  <th><?php echo $messages["reason"] ?></th>
                  <th><?php echo $messages["created_at"] ?></th>
                  <th><?php echo $messages["created_from"] ?></th>
                  <th><?php echo $messages["event"] ?></th>
                </tr>
                <tr>
                  <?php
                  require("./mysql.php");
                  $stmt = $mysql->prepare("SELECT * FROM reports");
                  $stmt->execute();
                  while($row = $stmt->fetch()){
                    if($row["STATUS"] == 0){
                      echo "<tr>";
                      echo '<td>'.UUIDResolve($row["UUID"]).'</td>';
                      echo '<td>'.htmlspecialchars($row["REASON"]).'</td>';
                      echo '<td>'.date($messages["date_format"],$row["CREATED_AT"]/1000).'</td>';
                      if($row["REPORTER"] != "KONSOLE"){
                        echo '<td>'.UUIDResolve($row["REPORTER"]).'</td>';
                      } else {
                        echo "<td>Console</td>";
                      }
                      echo '<td><a class="btn" href="reports.php?done&id='.$row["ID"].'"><i class="material-icons">done</i></a></td>';
                      echo "</tr>";
                    }
                  }
                   ?>
                </tr>
              </table>
              <?php
            } else {
              ////////////////////////////////////////
              // Show all reports
              ////////////////////////////////////////
              ?>
              <div class="flex-button">
                <a href="reports.php" class="btn"><i class="fas fa-eye-slash"></i> <?php echo $messages["open_reports"] ?></a>
              </div>
              <h1><?php echo $messages["all_reports"] ?></h1>
              <table>
                <tr>
                  <th><?php echo $messages["player"] ?></th>
                  <th><?php echo $messages["reason"] ?></th>
                  <th><?php echo $messages["created_at"] ?></th>
                  <th><?php echo $messages["created_from"] ?></th>
                  <th><?php echo $messages["edited_by"] ?></th>
                  <th>Status</th>
                </tr>
                <tr>
                  <?php
                  require("./mysql.php");
                  $stmt = $mysql->prepare("SELECT * FROM reports");
                  $stmt->execute();
                  while($row = $stmt->fetch()){
                    echo "<tr>";
                    echo '<td>'.UUIDResolve($row["UUID"]).'</td>';
                    echo '<td>'.$row["REASON"].'</td>';
                    echo '<td>'.date($messages["date_format"],$row["CREATED_AT"]/1000).'</td>';
                    if($row["REPORTER"] != "KONSOLE"){
                      echo '<td>'.UUIDResolve($row["REPORTER"]).'</td>';
                    } else {
                      echo "<td>KONSOLE</td>";
                    }
                    echo '<td>'.UUIDResolve($row["TEAM"]).'</td>';
                    if($row["STATUS"] == 0){
                      echo '<td><p style="color: red;">'.$messages["report_status_0"].'</td>';
                    } else {
                      echo '<td><p style="color: green;">'.$messages["report_status_1"].'</td>';
                    }
                    echo "</tr>";
                  }
                   ?>
                </tr>
              </table>
              <?php
            }
             ?>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
