        <?php
        require("./inc/header.inc.php");
        if(!isMod($_SESSION['username'])){
          showModalRedirect("ERROR", $messages["error"], $messages["perms_err"], "index.php");
          exit;
        }
        ?>
        <?php
        if(!isset($_GET["id"])){
          require("./mysql.php");
          ?>
          <div class="flex-container animated fadeIn">
            <div class="flex item-1">
              <?php
              $stmt = $mysql->prepare("SELECT * FROM unbans WHERE STATUS = 0");
              $stmt->execute();
              $count = $stmt->rowCount();
              if($count != 0){
                ?>
                <h1><?php echo $messages["open_unbans"] ?> (<?php echo $count ?>)</h1>
                <table>
                  <tr>
                    <th><?php echo $messages["player"] ?></th>
                    <th><?php echo $messages["date"] ?></th>
                    <th><?php echo $messages["event"] ?></th>
                  </tr>
                  <tr>
                    <?php
                    $stmt = $mysql->prepare("SELECT * FROM unbans WHERE STATUS = 0");
                    $stmt->execute();
                    while($row = $stmt->fetch()){
                      echo "<tr>";
                      echo '<td><a href="player.php?id='.$row["UUID"].'">'.UUIDResolve($row["UUID"]).'<a></td>';
                      echo '<td>'.date($messages["date_format"],$row["DATE"]).'</td>';
                      echo '<td><a href="unbans.php?id='.$row["ID"].'""><i class="fas fa-eye"></i></a> ';
                      echo "</tr>";
                    }
                    ?>
                  </tr>
                </table>
                <?php
              } else {
                echo '<p style="color: red;">'.$messages["no_unbans"].'</p>';
              }
              ?>
            </div>
            <div class="flex item-1">
              <h1><?php echo $messages["all_unbans"] ?></h1>
              <table>
                <tr>
                  <th><?php echo $messages["player"] ?></th>
                  <th><?php echo $messages["date"] ?></th>
                  <th><?php echo $messages["decision"] ?></th>
                  <th><?php echo $messages["event"] ?></th>
                </tr>
                <tr>
                  <?php
                  $stmt = $mysql->prepare("SELECT * FROM unbans ORDER BY DATE DESC");
                  $stmt->execute();
                  while($row = $stmt->fetch()){
                    echo "<tr>";
                    echo '<td><a href="player.php?id='.$row["UUID"].'">'.UUIDResolve($row["UUID"]).'<a></td>';
                    echo '<td>'.date($messages["date_format"],$row["DATE"]).'</td>';
                    echo '<td>';
                    if($row["STATUS"] == 1){
                      echo $messages["unban_status_1"];
                    } else if($row["STATUS"] == 2){
                      echo $messages["unban_status_2"];
                    } else if($row["STATUS"] == 3){
                      echo $messages["unban_status_3"];
                    } else if($row["STATUS"] == 0){
                      echo $messages["unban_status_0"];
                    }
                    echo '</td>';
                    echo '<td><a href="unbans.php?id='.$row["ID"].'""><i class="fas fa-eye"></i></a> ';
                    echo "</tr>";
                  }
                   ?>
                </tr>
              </table>
            </div>
          </div>
          <?php
        } else {
          if(isset($_POST["submit"])){
            function getUUIDFromRequest($id){
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM unbans WHERE ID = :id");
              $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_INT);
              $stmt->execute();
              $row = $stmt->fetch();
              return $row["UUID"];
            }
            require("./mysql.php");
            $status = (int) $_POST["choose"];
            $stmt = $mysql->prepare("UPDATE unbans SET STATUS = :status WHERE ID = :id");
            $stmt->bindParam(":status", $status, PDO::PARAM_INT);
            $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_INT);
            $stmt->execute();
            if($status == 1){
              $uuid = getUUIDFromRequest($_GET["id"]);
              $stmt = $mysql->prepare("UPDATE bans SET BANNED = 0 WHERE UUID = :uuid");
              $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
              $stmt->execute();
            } else if($status == 2){
              //Verkürzen auf 3 Tage
              $uuid = getUUIDFromRequest($_GET["id"]);
              $time = 259200 * 1000;
              $javatime = round(time() * 1000) + round($time);
              $stmt = $mysql->prepare("UPDATE bans SET END = :end WHERE UUID = :uuid");
              $stmt->bindParam(":end", $javatime, PDO::PARAM_STR);
              $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
              $stmt->execute();
            }
            ?>
            <meta http-equiv="refresh" content="0; URL=unbans.php">
            <?php
          }
          ?>
          <div class="flex-container animated fadeIn">
            <div class="flex item-1 sidebox">
              <h1><?php echo $messages["view_request"] ?></h1>
              <?php
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM unbans WHERE ID = :id");
              $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_INT);
              $stmt->execute();
              while($row = $stmt->fetch()){
                ?>
                <h5><?php echo $messages["player"] ?></h5>
                <p><?php echo UUIDResolve($row["UUID"]); ?></p>
                <h5><?php echo $messages["ban_fair_question"] ?></h5>
                <p><?php
                if($row["FAIR"] == 1){
                  echo $messages["ban_fair_answer_1"];
                } if($row["FAIR"] == 0){
                  echo $messages["ban_fair_answer_0"];
                }
                 ?></p>
                 <h5><?php echo $messages["message"] ?></h5>
                 <p><?php echo htmlspecialchars($row["MESSAGE"]); ?></p>
                 <h5><?php echo $messages["request_at"] ?></h5>
                 <p><?php echo date($messages["date_format"],$row["DATE"]); ?></p>
                 <?php
                 if($row["STATUS"] == 0){
                   ?>
                   <form action="unbans.php?id=<?php echo $_GET["id"]; ?>" method="post">
                     <select name="choose">
                       <option value="1"><?php echo $messages["unban_status_1"] ?></option>
                       <option value="2"><?php echo $messages["unban_status_2"] ?></option>
                       <option value="3"><?php echo $messages["unban_status_3"] ?></option>
                     </select>
                     <button type="submit" name="submit"><?php echo $messages["save"] ?></button>
                   </form>
                   <?php
                 } else {
                   ?>
                   <h5><?php echo $messages["decision"] ?></h5>
                   <?php
                   if($row["STATUS"] == 1){
                    echo $messages["unban_status_1"];
                   } else if($row["STATUS"] == 2){
                    echo $messages["unban_status_2"];
                   } else if($row["STATUS"] == 3){
                    echo $messages["unban_status_3"];
                   }
                 }
                  ?>
                <?php
              }
               ?>
            </div>
          </div>
          <?php
        }
         ?>
      </div>
    </div>
  </body>
</html>
