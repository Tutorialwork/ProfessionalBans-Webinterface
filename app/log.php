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
    $response["status"] = 1;
    $response["msg"] = "OK";
    $stmt = $mysql->prepare("SELECT * FROM log ORDER BY DATE DESC");
    $stmt->execute();
    $data = array();
    while($row = $stmt->fetch()){
      $username = null;
      $byusername = null;
      if($row["UUID"] != "null"){
        $username = getNameByUUID($row["UUID"]);
      }
      if($row["BYUUID"] != "null"){
        $byusername = getNameByUUID($row["BYUUID"]);
      }
      array_push($data, array("player" => $username, "byplayer" => $byusername, "action" => $row["ACTION"], "note" => $row["NOTE"], "date" => $row["DATE"]));
    }
    $response["log"] = $data;
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
