<?php
require("./inc/header.inc.php");
if (!isMod($_SESSION['username'])) {
  showModalRedirect("ERROR", "Fehler", "Der Zugriff auf diese Seite wurde verweigert.", "index.php");
  exit;
}
?>
<div class="flex-container animated fadeIn">
  <div class="flex item-1">
    <?php
    if (!isset($_GET["archiv"])) {
      ////////////////////////////////////////
      // Show only reports flagged as unedited
      ////////////////////////////////////////
      if (isset($_GET["done"]) && isset($_GET["id"])) {

        $uuid = "null";
        $stmt2 = MySQLWrapper()->prepare("SELECT NAME, UUID FROM bans WHERE NAME = :name");
        $name = $_SESSION["username"];
        $stmt2->bindParam(":name", $name, PDO::PARAM_STR);
        $stmt2->execute();
        $row = $stmt2->fetch();
        $uuid = $row["UUID"];
        $stmt = MySQLWrapper()->prepare("UPDATE reports SET status = 1, TEAM = :webuser WHERE ID = :id");
        $stmt->bindParam(":id", $_GET["id"], PDO::PARAM_INT);
        $stmt->bindParam(":webuser", $uuid, PDO::PARAM_STR);
        $stmt->execute();
        showModalRedirect("SUCCESS", "Erfolgreich", "Der Report wurde erfolgreich als <strong>bearbeitet</strong> makiert.", "reports.php");
      }
      ?>
      <div class="flex-button">
        <a href="reports.php?archiv" class="btn"><i class="fas fa-book-open"></i> Archiv</a>
        <a href="chatlogs.php" class="btn"><i class="fas fa-comments"></i> Chatlogs</a>
      </div>
      <h1>Offene Reports</h1>
      <table>
        <tr>
          <th>Spieler</th>
          <th>Grund</th>
          <th>erstellt am</th>
          <th>erstellt von</th>
          <th>Aktionen</th>
        </tr>
        <tr>
          <?php

            $stmt = MySQLWrapper()->prepare("SELECT * FROM reports");
            $stmt->execute();
            while ($row = $stmt->fetch()) {
              if ($row["STATUS"] == 0) {
                echo "<tr>";
                echo '<td>' . UUIDResolve($row["UUID"]) . '</td>';
                echo '<td>' . htmlspecialchars($row["REASON"]) . '</td>';
                echo '<td>' . date('d.m.Y H:i', $row["CREATED_AT"] / 1000) . '</td>';
                if ($row["REPORTER"] != "KONSOLE") {
                  echo '<td>' . UUIDResolve($row["REPORTER"]) . '</td>';
                } else {
                  echo "<td>KONSOLE</td>";
                }
                echo '<td><a class="btn" href="reports.php?done&id=' . $row["ID"] . '"><i class="material-icons">done</i></a></td>';
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
        <a href="reports.php" class="btn"><i class="fas fa-eye-slash"></i> Offene Reports</a>
      </div>
      <h1>Alle Reports</h1>
      <table>
        <tr>
          <th>Spieler</th>
          <th>Grund</th>
          <th>erstellt am</th>
          <th>erstellt von</th>
          <th>bearbeitet von</th>
          <th>Status</th>
        </tr>
        <tr>
          <?php

            $stmt = MySQLWrapper()->prepare("SELECT * FROM reports");
            $stmt->execute();
            while ($row = $stmt->fetch()) {
              echo "<tr>";
              echo '<td>' . UUIDResolve($row["UUID"]) . '</td>';
              echo '<td>' . $row["REASON"] . '</td>';
              echo '<td>' . date('d.m.Y H:i', $row["CREATED_AT"] / 1000) . '</td>';
              if ($row["REPORTER"] != "KONSOLE") {
                echo '<td>' . UUIDResolve($row["REPORTER"]) . '</td>';
              } else {
                echo "<td>KONSOLE</td>";
              }
              echo '<td>' . UUIDResolve($row["TEAM"]) . '</td>';
              if ($row["STATUS"] == 0) {
                echo '<td><p style="color: red;">Offen</td>';
              } else {
                echo '<td><p style="color: green;">Erledigt</td>';
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