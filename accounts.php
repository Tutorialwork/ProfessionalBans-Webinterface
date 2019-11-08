        <?php
        require("./inc/header.inc.php");
        if(!isAdmin($_SESSION['username'])){
          showModalRedirect("ERROR", "Fehler", "Der Zugriff auf diese Seite wurde verweigert.", "index.php");
          exit;
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
          <!--
          <div class="flex item-2 sidebox">
            <h1>Account erstellen</h1>
            <form action="bans.php" method="post">
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
        -->
        </div>
        <script type="text/javascript">
        function rankChangeModal(name){

        }
        </script>
      </div>
    </div>
  </body>
</html>
