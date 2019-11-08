        <?php
        require("./inc/header.inc.php");
        ?>
        <div class="flex-container animated fadeIn">
          <div class="flex item-1">
            <?php
            if(isset($_POST["gdisable"])){
              require("mysql.php");
              $stmt = $mysql->prepare("UPDATE accounts SET GOOGLE_AUTH = 'null' WHERE USERNAME = :user");
              $stmt->bindParam(":user", $_SESSION["username"], PDO::PARAM_STR);
              $stmt->execute();
            }
            if(hasGoogleAuth($_SESSION["username"])){
              ?>
              <h1>Google Authenticator</h1>
              <p>Du schütze derzeit deinen Account mit der 2-Faktor Authentifizierung.</p>
              <br>
              <form action="google-auth.php" method="post">
                <input type="hidden" name="CSRFToken" value="<?php echo $_SESSION["CSRF"]; ?>">
                <button type="submit" name="gdisable"><i class="fas fa-lock-open"></i> Deaktivieren</button>
              </form>
              <?php
            } else {
              ?>
              <h1>Google Authenticator</h1>
              <p>Schütze deinen Account mit der 2-Faktor Authentifizierung.</p>
              <?php
              //Erstelle Token
              require("./authmanager.php");
              $ga = new PHPGangsta_GoogleAuthenticator();
              $secret = $ga->createSecret();
              //POST Abfragen
              if(!isset($_POST["gactivate"])){
                ?>
                <a href='https://itunes.apple.com/de/app/google-authenticator/id388497605?mt=8'><img height="125" width="323" alt='Jetzt im App Store' src='https://delta.chat/assets/home/get-it-on-ios.png'/></a>
                <a href='https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=de&pcampaignid=MKT-Other-global-all-co-prtnr-py-PartBadge-Mar2515-1'><img height="125" width="323" alt='Jetzt bei Google Play' src='https://play.google.com/intl/en_us/badges/images/generic/de_badge_web_generic.png'/></a>
                <?php
              } else {
                require("mysql.php");
                $stmt = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = :user");
                $stmt->bindParam(":user", $_SESSION["username"], PDO::PARAM_STR);
                $stmt->execute();
                if($row = $stmt->fetch()){
                  if(!hasGoogleAuth($_SESSION["username"])){
                    $qrCodeUrl = $ga->getQRCodeGoogleUrl('ProfessionalBans', $secret);
                    echo '<img src="'.$qrCodeUrl.'" class="normal">';
                    $_SESSION["gtoken"] = $secret;
                  }
                }
              }
              ?>
              <form action="google-auth.php" method="post">
                <?php
                if(!isset($_POST["gactivate"])){
                  ?>
                  <button type="submit" name="gactivate"><i class="fas fa-mobile-alt"></i> Aktivieren</button>
                  <?php
                } else {
                  ?>
                  <button type="submit" name="gfinish"><i class="fas fa-lock"></i> Einrichtung abschließen</button>
                  <?php
                }
                ?>
              </form>
              <?php
              if(isset($_POST["gfinish"])){
                require("mysql.php");
                $stmt = $mysql->prepare("UPDATE accounts SET GOOGLE_AUTH = :auth WHERE USERNAME = :user");
                $stmt->bindParam(":user", $_SESSION["username"], PDO::PARAM_STR);
                $stmt->bindParam(":auth", $_SESSION["gtoken"], PDO::PARAM_STR);
                $stmt->execute();
                unset($_SESSION["gtoken"]);
                echo '<meta http-equiv="refresh" content="0; URL=google-auth.php">';
              }
            }
             ?>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
