<?php
require_once("mysql.php");

function redirect($url, $exit = true)
{

  if (!headers_sent()) {

    header('Location: ' . $url);
    header("Connection: close");
  }

  echo '<html>';
  echo '<head><title>Redirecting you...</title>';
  echo '<meta http-equiv="Refresh" content="0;url=' . $url . '" />';
  echo '</head>';
  echo '<body onload="location.replace(\'' . $url . '\')">';
  echo 'Redirecting to:<br />';
  echo "<a href=$url>$url</a><br /><br />";
  echo 'If you are not, please click on the link above.<br />';
  echo '</body>';
  echo '</html>';
  if ($exit) exit;
}

function isAdmin($username)
{
  $stmt = MySQLWrapper()->prepare("SELECT RANK FROM accounts WHERE USERNAME = :username");
  $stmt->bindParam(":username", $username, PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    if ($row["RANK"] == 3) {
      return true;
    } else {
      return false;
    }
  }
}

function isMod($username)
{
  $stmt = MySQLWrapper()->prepare("SELECT RANK FROM accounts WHERE USERNAME = :username");
  $stmt->bindParam(":username", $username, PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    if ($row["RANK"] > 1) {
      return true;
    } else {
      return false;
    }
  }
}

function isSup($username)
{
  $stmt = MySQLWrapper()->prepare("SELECT RANK FROM accounts WHERE USERNAME = :username");
  $stmt->bindParam(":username", $username, PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    if ($row["RANK"] > 0) {
      return true;
    } else {
      return false;
    }
  }
}

function UUIDResolve($uuid)
{
  $stmt = MySQLWrapper()->prepare("SELECT NAME, UUID FROM bans WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    if ($row["UUID"] == $uuid) {
      return $row["NAME"];
    }
  }
}

function NameResolve($mcname){
  $stmt = MySQLWrapper()->prepare("SELECT UUID FROM bans WHERE NAME = :name");
  $stmt->bindParam(":name", $mcname, PDO::PARAM_STR);
  $stmt->execute();
  $row = $stmt->fetch();
  return $row["UUID"];
}

function isInitialPassword($username)
{
  $stmt = MySQLWrapper()->prepare("SELECT AUTHCODE FROM accounts WHERE USERNAME = :username");
  $stmt->bindParam(":username", $username, PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    if ($row["AUTHCODE"] == "initialpassword") {
      return true;
    } else {
      return false;
    }
  }
}

function getAuthCodeByMCName($mcusername)
{
  //Name resolve
  $uuid = null;
  $stmt = MySQLWrapper()->prepare("SELECT NAME, UUID FROM bans WHERE NAME = :name");
  $stmt->bindParam(":name", $_GET["verify"], PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    if ($row["NAME"] == $_GET["verify"]) {
      $uuid = $row["UUID"];
    }
  }
  //Authcode SELECT SQL
  $getstmt = MySQLWrapper()->prepare("SELECT * FROM accounts WHERE UUID = :uuid");
  $getstmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $getstmt->execute();
  $row = $getstmt->fetch();
  return $row["AUTHCODE"];
}

function setAuthCodeByMCName($mcusername, $code)
{
  //Name Resolve
  $uuid = null;
  $stmt = MySQLWrapper()->prepare("SELECT NAME, UUID FROM bans WHERE NAME = :name");
  $stmt->bindParam(":name", $mcusername, PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    if ($row["NAME"] == $mcusername) {
      $uuid = $row["UUID"];
    }
  }
  //Function
  $stmt2 = MySQLWrapper()->prepare("UPDATE accounts SET AUTHCODE = :code WHERE UUID = :uuid");
  $stmt2->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt2->bindParam(":code", $code, PDO::PARAM_STR);
  $stmt2->execute();
}

function isAuth($mcusername)
{
  //Name Resolve
  $uuid = null;
  $stmt = MySQLWrapper()->prepare("SELECT NAME, UUID FROM bans WHERE NAME = :name");
  $stmt->bindParam(":name", $mcusername, PDO::PARAM_STR);
  $stmt->execute();
  $row = $stmt->fetch();
  $uuid = $row["UUID"];
  $stmt2 = MySQLWrapper()->prepare("SELECT AUTHSTATUS FROM accounts WHERE UUID = :uuid");
  $stmt2->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt2->execute();
  $row2 = $stmt2->fetch();
  if ($row2["AUTHSTATUS"] == 1) {
    return true;
  } else if ($row2["AUTHSTATUS"] == null) {
    return false;
  }
}

function resetAuthStatus($mcusername)
{
  //Name Resolve
  $uuid = null;
  $stmt = MySQLWrapper()->prepare("SELECT NAME, UUID FROM bans WHERE NAME = :name");
  $stmt->bindParam(":name", $mcusername, PDO::PARAM_STR);
  $stmt->execute();
  $row = $stmt->fetch();
  $uuid = $row["UUID"];
  $stmt2 = MySQLWrapper()->prepare("UPDATE accounts SET AUTHCODE = 'null', AUTHSTATUS = null WHERE UUID = :uuid");
  $stmt2->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt2->execute();
}

function updatePasswordByMCName($mcusername, $pw)
{
  //Name Resolve
  $uuid = null;
  $resolvestmt = MySQLWrapper()->prepare("SELECT NAME, UUID FROM bans WHERE NAME = :name");
  $resolvestmt->bindParam(":name", $mcusername, PDO::PARAM_STR);
  $resolvestmt->execute();
  $row2 = $resolvestmt->fetch();
  $uuid = $row2["UUID"];
  //Function
  $hash = password_hash($pw, PASSWORD_BCRYPT);
  $stmt = MySQLWrapper()->prepare("UPDATE accounts SET PASSWORD = :pw WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->bindParam(":pw", $hash, PDO::PARAM_STR);
  $stmt->execute();
}

function isPlayerExists($uuid)
{
  $stmt = MySQLWrapper()->prepare("SELECT * FROM bans WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->execute();
  $data = 0;
  while ($row = $stmt->fetch()) {
    $data++;
  }
  if ($data == 0) {
    return false;
  } else {
    return true;
  }
}

function getMinutesByReasonID($id)
{
  $stmt = MySQLWrapper()->prepare("SELECT TIME FROM reasons WHERE ID = :id");
  $stmt->bindParam(":id", $id, PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    return $row["TIME"];
  }
}

function getReasonByReasonID($id)
{
  $stmt = MySQLWrapper()->prepare("SELECT REASON FROM reasons WHERE ID = :id");
  $stmt->bindParam(":id", $id, PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    return $row["REASON"];
  }
}

function getBanCounter($uuid)
{
  $stmt = MySQLWrapper()->prepare("SELECT BANS FROM bans WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    return $row["BANS"];
  }
}

function addBanCounter($uuid)
{
  $bans = getBanCounter($uuid);
  $bans++;
  $stmt = MySQLWrapper()->prepare("UPDATE bans SET BANS = :counter WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->bindParam(":counter", $bans, PDO::PARAM_INT);
  $stmt->execute();
}

function getMuteCounter($uuid)
{
  $stmt = MySQLWrapper()->prepare("SELECT MUTES FROM bans WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    return $row["MUTES"];
  }
}

function addMuteCounter($uuid)
{
  $mutes = getMuteCounter($uuid);
  $mutes++;
  $stmt = MySQLWrapper()->prepare("UPDATE bans SET MUTES = :counter WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->bindParam(":counter", $mutes, PDO::PARAM_INT);
  $stmt->execute();
}

function isBanned($uuid)
{
  $stmt = MySQLWrapper()->prepare("SELECT BANNED FROM bans WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    if ($row["BANNED"] == 1) {
      return true;
    } else {
      return false;
    }
  }
}

function isMuted($uuid)
{
  $stmt = MySQLWrapper()->prepare("SELECT MUTED FROM bans WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    if ($row["MUTED"] == 1) {
      return true;
    } else {
      return false;
    }
  }
}
function generateRandomString($length = 10)
{
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}

function getGrundByReasonID($id)
{
  $stmt = MySQLWrapper()->prepare("SELECT REASON FROM reasons WHERE ID = :id");
  $stmt->bindParam(":id", $id, PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    return $row["REASON"];
  }
}

function getPermsByReasonID($id)
{
  $stmt = MySQLWrapper()->prepare("SELECT PERMS FROM reasons WHERE ID = :id");
  $stmt->bindParam(":id", $id, PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    if ($row["PERMS"] != "null") {
      return $row["PERMS"];
    }
  }
}

function getTimeByReasonID($id)
{
  $stmt = MySQLWrapper()->prepare("SELECT TIME FROM reasons WHERE ID = :id");
  $stmt->bindParam(":id", $id, PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    return $row["TIME"];
  }
}

function isMuteByReasonID($id)
{
  $stmt = MySQLWrapper()->prepare("SELECT TYPE FROM reasons WHERE ID = :id");
  $stmt->bindParam(":id", $id, PDO::PARAM_STR);
  $stmt->execute();
  while ($row = $stmt->fetch()) {
    if ($row["TYPE"] == 1) {
      return true;
    } else {
      return false;
    }
  }
}

function hasGoogleAuth($username)
{
  $stmt = MySQLWrapper()->prepare("SELECT * FROM accounts WHERE USERNAME = :user");
  $stmt->bindParam(":user", $username, PDO::PARAM_STR);
  $stmt->execute();
  if ($row = $stmt->fetch()) {
    if ($row["GOOGLE_AUTH"] == "null" || $row["GOOGLE_AUTH"] == "0") {
      return false;
    } else {
      return true;
    }
  }
}

function getGToken($username)
{
  $stmt = MySQLWrapper()->prepare("SELECT * FROM accounts WHERE USERNAME = :user");
  $stmt->bindParam(":user", $username, PDO::PARAM_STR);
  $stmt->execute();
  if ($row = $stmt->fetch()) {
    return $row["GOOGLE_AUTH"];
  }
}
function showModal($type, $title, $message)
{
  ?>
  <script type="text/javascript">
    $.sweetModal({
      title: '<?php echo $title; ?>',
      content: '<?php echo $message; ?>',
      icon: $.sweetModal.ICON_<?php echo $type; ?>
    });
  </script>
<?php
}
function showModalRedirect($type, $title, $message, $location)
{
  ?>
  <script type="text/javascript">
    $.sweetModal({
      title: '<?php echo $title; ?>',
      content: '<?php echo $message; ?>',
      icon: $.sweetModal.ICON_<?php echo $type; ?>,
      onClose: function() {
        window.location = "<?php echo $location; ?>";
      }
    });
  </script>
<?php
}
function validateSession()
{
  $username = $_SESSION["username"];
  $stmt = MySQLWrapper()->prepare("SELECT * FROM accounts WHERE USERNAME = :user");
  $stmt->execute(array(":user" => $username));
  if ($stmt->rowCount() == 0) {
    session_destroy();
    header("Location: login.php");
  }
}

function getLastlogin($uuid)
{
  $stmt = MySQLWrapper()->prepare("SELECT * FROM bans WHERE UUID = :uuid");
  $stmt->execute(array(":uuid" => $uuid));
  $row = $stmt->fetch();
  return date('d.m.Y H:i', $row["LASTLOGIN"] / 1000);
}

function getUUID()
{
  $user = $_SESSION["username"];
  $stmt = MySQLWrapper()->prepare("SELECT UUID FROM bans WHERE NAME = :name");
  $stmt->execute(array(":name" => $user));
  $row = $stmt->fetch();
  return $row["UUID"];
}

function getAutoMuteMessage($uuid)
{
  $stmt = MySQLWrapper()->prepare("SELECT * FROM log WHERE UUID = :uuid AND ACTION = 'AUTOMUTE_BLACKLIST' OR UUID = :uuid AND ACTION = 'AUTOMUTE_ADBLACKLIST' ORDER BY DATE DESC");
  $stmt->execute(array(":uuid" => $uuid));
  if ($stmt->rowCount() != 0) {
    $row = $stmt->fetch();
    return $row["NOTE"];
  } else {
    return null;
  }
}

function isMuteAutoMute($uuid)
{
  $stmt = MySQLWrapper()->prepare("SELECT * FROM log WHERE UUID = :uuid AND ACTION = 'AUTOMUTE_BLACKLIST' OR UUID = :uuid AND ACTION = 'AUTOMUTE_ADBLACKLIST' ORDER BY DATE DESC");
  $stmt->execute(array(":uuid" => $uuid));
  $row = $stmt->fetch();

  $stmt2 = MySQLWrapper()->prepare("SELECT * FROM log WHERE UUID = :uuid AND ACTION = 'MUTE' ORDER BY DATE DESC");
  $stmt2->execute(array(":uuid" => $uuid));
  $row2 = $stmt2->fetch();

  if ($row["DATE"] > $row2["DATE"]) {
    return true;
  } else {
    return false;
  }
}
