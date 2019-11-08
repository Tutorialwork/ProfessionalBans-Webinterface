<?php
function isAdmin($username){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT RANK FROM accounts WHERE USERNAME = :username");
  $stmt->bindParam(":username", $username, PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    if($row["RANK"] == 3){
      return true;
    } else {
      return false;
    }
  }
}
function isMod($username){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT RANK FROM accounts WHERE USERNAME = :username");
  $stmt->bindParam(":username", $username, PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    if($row["RANK"] > 1){
      return true;
    } else {
      return false;
    }
  }
}
function isSup($username){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT RANK FROM accounts WHERE USERNAME = :username");
  $stmt->bindParam(":username", $username, PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    if($row["RANK"] > 0){
      return true;
    } else {
      return false;
    }
  }
}
function UUIDResolve($uuid){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT NAME, UUID FROM bans WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    if($row["UUID"] == $uuid){
      return $row["NAME"];
    }
  }
}
function isInitialPassword($username){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT AUTHCODE FROM accounts WHERE USERNAME = :username");
  $stmt->bindParam(":username", $username, PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    if($row["AUTHCODE"] == "initialpassword"){
      return true;
    } else {
      return false;
    }
  }
}
function getAuthCodeByMCName($mcusername){
  require("./mysql.php");
  //Name resolve
  $uuid = null;
  $stmt = $mysql->prepare("SELECT NAME, UUID FROM bans WHERE NAME = :name");
  $stmt->bindParam(":name", $_GET["verify"], PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    if($row["NAME"] == $_GET["verify"]){
        $uuid = $row["UUID"];
    }
  }
  //Authcode SELECT SQL
  $getstmt = $mysql->prepare("SELECT * FROM accounts WHERE UUID = :uuid");
  $getstmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $getstmt->execute();
  $row = $getstmt->fetch();
  return $row["AUTHCODE"];
}
function setAuthCodeByMCName($mcusername, $code){
  require("./mysql.php");
  //Name Resolve
  $uuid = null;
  $stmt = $mysql->prepare("SELECT NAME, UUID FROM bans WHERE NAME = :name");
  $stmt->bindParam(":name", $mcusername, PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    if($row["NAME"] == $mcusername){
        $uuid = $row["UUID"];
    }
  }
  //Function
  $stmt2 = $mysql->prepare("UPDATE accounts SET AUTHCODE = :code WHERE UUID = :uuid");
  $stmt2->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt2->bindParam(":code", $code, PDO::PARAM_STR);
  $stmt2->execute();
}
function isAuth($mcusername){
  require("./mysql.php");
  //Name Resolve
  $uuid = null;
  $stmt = $mysql->prepare("SELECT NAME, UUID FROM bans WHERE NAME = :name");
  $stmt->bindParam(":name", $mcusername, PDO::PARAM_STR);
  $stmt->execute();
  $row = $stmt->fetch();
  $uuid = $row["UUID"];
  $stmt2 = $mysql->prepare("SELECT AUTHSTATUS FROM accounts WHERE UUID = :uuid");
  $stmt2->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt2->execute();
  $row2 = $stmt2->fetch();
  if($row2["AUTHSTATUS"] == 1){
    return true;
  } else if($row2["AUTHSTATUS"] == null){
    return false;
  }
}
function resetAuthStatus($mcusername){
  require("./mysql.php");
  //Name Resolve
  $uuid = null;
  $stmt = $mysql->prepare("SELECT NAME, UUID FROM bans WHERE NAME = :name");
  $stmt->bindParam(":name", $mcusername, PDO::PARAM_STR);
  $stmt->execute();
  $row = $stmt->fetch();
  $uuid = $row["UUID"];
  $stmt2 = $mysql->prepare("UPDATE accounts SET AUTHCODE = 'null', AUTHSTATUS = null WHERE UUID = :uuid");
  $stmt2->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt2->execute();
}
function updatePasswordByMCName($mcusername, $pw){
  require("./mysql.php");
  //Name Resolve
  $uuid = null;
  $resolvestmt = $mysql->prepare("SELECT NAME, UUID FROM bans WHERE NAME = :name");
  $resolvestmt->bindParam(":name", $mcusername, PDO::PARAM_STR);
  $resolvestmt->execute();
  $row2 = $resolvestmt->fetch();
  $uuid = $row2["UUID"];
  //Function
  $hash = password_hash($pw, PASSWORD_BCRYPT);
  $stmt = $mysql->prepare("UPDATE accounts SET PASSWORD = :pw WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->bindParam(":pw", $hash, PDO::PARAM_STR);
  $stmt->execute();
}
function isPlayerExists($uuid){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT * FROM bans WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->execute();
  $data = 0;
  while($row = $stmt->fetch()){
    $data++;
  }
  if($data == 0){
    return false;
  } else {
    return true;
  }
}
function getMinutesByReasonID($id){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT TIME FROM reasons WHERE ID = :id");
  $stmt->bindParam(":id", $id, PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    return $row["TIME"];
  }
}
function getReasonByReasonID($id){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT REASON FROM reasons WHERE ID = :id");
  $stmt->bindParam(":id", $id, PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    return $row["REASON"];
  }
}
function getBanCounter($uuid){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT BANS FROM bans WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    return $row["BANS"];
  }
}
function addBanCounter($uuid){
  require("./mysql.php");
  $bans = getBanCounter($uuid);
  $bans++;
  $stmt = $mysql->prepare("UPDATE bans SET BANS = :counter WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->bindParam(":counter", $bans, PDO::PARAM_INT);
  $stmt->execute();
}
function getMuteCounter($uuid){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT MUTES FROM bans WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    return $row["MUTES"];
  }
}
function addMuteCounter($uuid){
  require("./mysql.php");
  $mutes = getMuteCounter($uuid);
  $mutes++;
  $stmt = $mysql->prepare("UPDATE bans SET MUTES = :counter WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->bindParam(":counter", $mutes, PDO::PARAM_INT);
  $stmt->execute();
}
function isBanned($uuid){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT BANNED FROM bans WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    if($row["BANNED"] == 1){
      return true;
    } else {
      return false;
    }
  }
}
function isMuted($uuid){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT MUTED FROM bans WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    if($row["MUTED"] == 1){
      return true;
    } else {
      return false;
    }
  }
}
function generateRandomString($length = 10) {
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
}
function getGrundByReasonID($id){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT REASON FROM reasons WHERE ID = :id");
  $stmt->bindParam(":id", $id, PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    return $row["REASON"];
  }
}
function getPermsByReasonID($id){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT PERMS FROM reasons WHERE ID = :id");
  $stmt->bindParam(":id", $id, PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    if($row["PERMS"] != "null"){
      return $row["PERMS"];
    }
  }
}
function getTimeByReasonID($id){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT TIME FROM reasons WHERE ID = :id");
  $stmt->bindParam(":id", $id, PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    return $row["TIME"];
  }
}
function isMuteByReasonID($id){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT TYPE FROM reasons WHERE ID = :id");
  $stmt->bindParam(":id", $id, PDO::PARAM_STR);
  $stmt->execute();
  while($row = $stmt->fetch()){
    if($row["TYPE"] == 1){
      return true;
    } else {
      return false;
    }
  }
}
function hasGoogleAuth($username){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = :user");
  $stmt->bindParam(":user", $username, PDO::PARAM_STR);
  $stmt->execute();
  if($row = $stmt->fetch()){
    if($row["GOOGLE_AUTH"] == "null" || $row["GOOGLE_AUTH"] == "0"){
      return false;
    } else {
      return true;
    }
  }
}
function getGToken($username){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = :user");
  $stmt->bindParam(":user", $username, PDO::PARAM_STR);
  $stmt->execute();
  if($row = $stmt->fetch()){
    return $row["GOOGLE_AUTH"];
  }
}
function showModal($type, $title, $message){
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
function showModalRedirect($type, $title, $message, $location){
  ?>
  <script type="text/javascript">
  $.sweetModal({
    title: '<?php echo $title; ?>',
	  content: '<?php echo $message; ?>',
	  icon: $.sweetModal.ICON_<?php echo $type; ?>,
    onClose: function(){
            window.location = "<?php echo $location; ?>";
          }
  });
  </script>
  <?php
}
function validateSession(){
  $username = $_SESSION["username"];
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = :user");
  $stmt->execute(array(":user" => $username));
  if($stmt->rowCount() == 0){
    session_destroy();
    header("Location: login.php");
  }
}
function getLastlogin($uuid){
  require("./mysql.php");
  $stmt = $mysql->prepare("SELECT * FROM bans WHERE UUID = :uuid");
  $stmt->execute(array(":uuid" => $uuid));
  $row = $stmt->fetch();
  return date('d.m.Y H:i',$row["LASTLOGIN"]/1000);
}
function getUUID(){
  require("./mysql.php");
  $user = $_SESSION["username"];
  $stmt = $mysql->prepare("SELECT UUID FROM bans WHERE NAME = :name");
  $stmt->execute(array(":name" => $user));
  $row = $stmt->fetch();
  return $row["UUID"];
}
 ?>
