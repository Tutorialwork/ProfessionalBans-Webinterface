<?php
require("tokenhandler.php");
require("../mysql.php");
function getNameByUUID($uuid){
  require("../mysql.php");
  $stmt = $mysql->prepare("SELECT * FROM bans WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->execute();
  $row = $stmt->fetch();
  return $row["NAME"];
}
$response = array();
$JSONRequest = file_get_contents("php://input");
$request = json_decode($JSONRequest, TRUE);
if(isset($request["token"])){
  $access = new TokenHandler($request["token"]);
  if($access->username != null){
    require("../datamanager.php");
    if(isAdmin($access->username)){
      $response["status"] = 1;
      $response["msg"] = "OK";
      $stmt = $mysql->prepare("SELECT * FROM accounts");
      $stmt->execute();
      $users = array();
      while($row = $stmt->fetch()){
        $bansstmt = $mysql->prepare("SELECT * FROM bans WHERE UUID = :uuid");
        $bansstmt->bindParam(":uuid", $row["UUID"], PDO::PARAM_STR);
        $bansstmt->execute();
        $row2 = $bansstmt->fetch();
        array_push($users, array("username" => $row["USERNAME"], "mc" => getNameByUUID($row["UUID"]), "rank" => $row["RANK"], "googleauth" => $row["GOOGLE_AUTH"], "bans" => $row2["BANS"], "mutes" => $row2["MUTES"], "firstlogin" => $row2["FIRSTLOGIN"], "lastlogin" => $row2["LASTLOGIN"]));
      }
      $response["users"] = $users;
    } else {
      $response["status"] = 0;
      $response["msg"] = "Request not permitted";
    }
  } else {
    $response["status"] = 0;
    $response["msg"] = "Access denied";
  }
} else {
  $response["status"] = 0;
  $response["msg"] = "Invaild request";
}
echo json_encode($response);
 ?>
