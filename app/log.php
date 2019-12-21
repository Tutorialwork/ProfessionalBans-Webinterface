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
    if(isset($request["player"])){
      $response["status"] = 1;
      $response["msg"] = "OK";
      $player = NameResolve($request["player"]);
      $stmt = $mysql->prepare("SELECT * FROM log WHERE UUID = :targetplayer ORDER BY DATE DESC");
      $stmt->execute(array(":targetplayer" => $player));
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
        $action = $row["ACTION"];
        $note = $row["NOTE"];
        if($action == "BAN" || $action == "MUTE" || $action == "IPBAN_PLAYER"){
          $note = getReasonByReasonID($note);
        }
        array_push($data, array("player" => $username, "byplayer" => $byusername, "action" => $row["ACTION"], "note" => $note, "date" => $row["DATE"]));
      }
      $response["log"] = $data;
    } else {
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
        $action = $row["ACTION"];
        $note = $row["NOTE"];
        if($action == "BAN" || $action == "MUTE" || $action == "IPBAN_PLAYER"){
          $note = getReasonByReasonID($note);
        }
        array_push($data, array("player" => $username, "byplayer" => $byusername, "action" => $row["ACTION"], "note" => $note, "date" => $row["DATE"]));
      }
      $response["log"] = $data;
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
