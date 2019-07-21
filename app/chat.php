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
    $stmt = $mysql->prepare("SELECT * FROM chat ORDER BY SENDDATE DESC");
    $stmt->execute();
    $messages = array();
    while($row = $stmt->fetch()){
      array_push($messages, array("id" => $row["ID"], "player" => getNameByUUID($row["UUID"]), "server" => $row["SERVER"], "message" => $row["MESSAGE"], "date" => $row["SENDDATE"]));
    }
    $response["chat"] = $messages;
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
