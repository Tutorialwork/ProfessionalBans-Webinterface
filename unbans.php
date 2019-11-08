        <?php
        require("./inc/header.inc.php");
        if(!isMod($_SESSION['username'])){
          showModalRedirect("ERROR", "Fehler", "Der Zugriff auf diese Seite wurde verweigert.", "index.php");
          exit;
        }
        ?>
        <?php
        if(!isset($_GET["id"])){
          ?>
          <div class="flex-container animated fadeIn">
            <div class="flex item-1">
              <h1>Offene Entbannungsanträge</h1>
              <table>
                <tr>
                  <th>Spieler</th>
                  <th>Datum</th>
                  <th>Aktionen</th>
                </tr>
                <tr>
                  <?php
                  require("./mysql.php");
                  $stmt = $mysql->prepare("SELECT * FROM unbans WHERE STATUS = 0");
                  $stmt->execute();
                  while($row = $stmt->fetch()){
                    echo "<tr>";
                    echo '<td>'.UUIDResolve($row["UUID"]).'</td>';
                    echo '<td>'.date('d.m.Y H:i',$row["DATE"]).'</td>';
                    echo '<td><a href="unbans.php?id='.$row["ID"].'""><i class="fas fa-eye"></i></a> ';
                    echo "</tr>";
                  }
                   ?>
                </tr>
              </table>
            </div>
            <div class="flex item-1">
              <h1>Alle Entbannungsanträge</h1>
              <table>
                <tr>
                  <th>Spieler</th>
                  <th>Datum</th>
                  <th>Entscheidung</th>
                  <th>Aktionen</th>
                </tr>
                <tr>
                  <?php
                  require("./mysql.php");
                  $stmt = $mysql->prepare("SELECT * FROM unbans");
                  $stmt->execute();
                  while($row = $stmt->fetch()){
                    echo "<tr>";
                    echo '<td>'.UUIDResolve($row["UUID"]).'</td>';
                    echo '<td>'.date('d.m.Y H:i',$row["DATE"]).'</td>';
                    echo '<td>';
                    if($row["STATUS"] == 1){
                      echo "Ban aufgehoben";
                    } else if($row["STATUS"] == 2){
                      echo "Ban verkürzt";
                    } else if($row["STATUS"] == 3){
                      echo "Abgelehnt";
                    } else if($row["STATUS"] == 0){
                      echo "Ausstehend";
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
            function getUUID($id){
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
              $uuid = getUUID($_GET["id"]);
              $stmt = $mysql->prepare("UPDATE bans SET BANNED = 0 WHERE UUID = :uuid");
              $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
              $stmt->execute();
            } else if($status == 2){
              //Verkürzen auf 3 Tage
              $uuid = getUUID($_GET["id"]);
              $time = 259200 * 1000;
              $javatime = round(time() * 1000) + round($time);
              $stmt = $mysql->prepare("UPDATE bans SET END = :end WHERE UUID = :uuid");
              $stmt->bindParam(":end", $javatime, PDO::PARAM_STR);
              $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
              $stmt->execute();
            }
            header("Location: unbans.php");
          }
          ?>
          <div class="flex-container animated fadeIn">
            <div class="flex item-1 sidebox">
              <h1>Entbannungsantrag anschauen</h1>
              <?php
              require("./mysql.php");
              $stmt = $mysql->prepare("SELECT * FROM unbans WHERE ID = :id");
              $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_INT);
              $stmt->execute();
              while($row = $stmt->fetch()){
                ?>
                <h5>Spieler</h5>
                <p><?php echo UUIDResolve($row["UUID"]); ?></p>
                <h5>Glaubst du der Ban war gerechtfertigt?</h5>
                <p><?php
                if($row["FAIR"] == 1){
                  ?>
                  Ja, aber ich sehe meinen Fehler ein
                  <?php
                } if($row["FAIR"] == 0){
                  ?>
                  Nein, ich habe nichts getan
                  <?php
                }
                 ?></p>
                 <h5>Nachricht</h5>
                 <p><?php echo $row["MESSAGE"]; ?></p>
                 <h5>Entbannungsantrag erstellt am</h5>
                 <p><?php echo date('d.m.Y H:i',$row["DATE"]); ?></p>
                 <?php
                 if($row["STATUS"] == 0){
                   ?>
                   <form action="unbans.php?id=<?php echo $_GET["id"]; ?>" method="post">
                     <select name="choose">
                       <option value="1">Akzeptieren und Ban aufheben</option>
                       <option value="2">Akzeptieren und Ban verkürzen</option>
                       <option value="3">Ablehnen</option>
                     </select>
                     <button type="submit" name="submit">Speichern</button>
                   </form>
                   <?php
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
