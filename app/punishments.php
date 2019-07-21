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
    if(isset($request["unban"])){
      $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :user");
      $stmt->bindParam(":user", $request["unban"], PDO::PARAM_STR);
      $stmt->execute();
      $count = $stmt->rowCount();
      $row = $stmt->fetch();
      if($count != 0){
        if($row["BANNED"] == 1){
          $response["status"] = 1;
          $response["msg"] = "OK";
          $stmt = $mysql->prepare("UPDATE bans SET BANNED = 0 WHERE NAME = :user");
          $stmt->bindParam(":user", $request["unban"], PDO::PARAM_STR);
          $stmt->execute();
        } else {
          $response["status"] = 0;
          $response["msg"] = "User not banned";
        }
      } else {
        $response["status"] = 0;
        $response["msg"] = "User not found";
      }
    } else if(isset($request["unmute"])){
      $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :user");
      $stmt->bindParam(":user", $request["unmute"], PDO::PARAM_STR);
      $stmt->execute();
      $count = $stmt->rowCount();
      $row = $stmt->fetch();
      if($count != 0){
        if($row["MUTED"] == 1){
          $response["status"] = 1;
          $response["msg"] = "OK";
          $stmt = $mysql->prepare("UPDATE bans SET MUTED = 0 WHERE NAME = :user");
          $stmt->bindParam(":user", $request["unmute"], PDO::PARAM_STR);
          $stmt->execute();
        } else {
          $response["status"] = 0;
          $response["msg"] = "User not muted";
        }
      } else {
        $response["status"] = 0;
        $response["msg"] = "User not found";
      }
    } else if(isset($request["user"])){
      $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :user");
      $stmt->bindParam(":user", $request["user"], PDO::PARAM_STR);
      $stmt->execute();
      $count = $stmt->rowCount();
      if($count != 0){
        $response["status"] = 1;
        $response["msg"] = "OK";
        $player = array();
        while($row = $stmt->fetch()){
          array_push($player, array("ban" => $row["BANNED"], "mute" => $row["MUTED"], "reason" => $row["REASON"], "end" => $row["END"], "by" => $row["TEAMUUID"], "bans" => $row["BANS"], "mutes" => $row["MUTES"]));
        }
        $response["details"] = $player;
      } else {
        $response["status"] = 0;
        $response["msg"] = "User not found";
      }
    } else if(isset($request["ban"]) && isset($request["player"]) && isset($request["banid"])){
      $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :name");
      $stmt->bindParam(":name", $request["player"], PDO::PARAM_STR);
      $stmt->execute();
      $count = $stmt->rowCount();
      if($count != 0){
        $stmt = $mysql->prepare("SELECT * FROM reasons WHERE ID = :id");
        $stmt->bindParam(":id", $request["banid"], PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->rowCount();
        if($count != 0){
          $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :name");
          $stmt->bindParam(":name", $request["player"], PDO::PARAM_STR);
          $stmt->execute();
          $row = $stmt->fetch();
          if($row["BANNED"] == 0){
            
          } else {
            $response["status"] = 0;
            $response["msg"] = "User is already exists";
          }
        } else {
          $response["status"] = 0;
          $response["msg"] = "Reason is not exists";
        }
      } else {
        $response["status"] = 0;
        $response["msg"] = "User is not exists";
      }
    } else {
      $response["status"] = 1;
      $response["msg"] = "OK";
      $stmt = $mysql->prepare("SELECT * FROM bans WHERE BANNED = 1");
      $stmt->execute();
      $bans = array();
      while($row = $stmt->fetch()){
        array_push($bans, array("player" => $row["NAME"], "reason" => $row["REASON"], "end" => $row["END"], "by" => getNameByUUID($row["TEAMUUID"])));
      }
      $mutestmt = $mysql->prepare("SELECT * FROM bans WHERE MUTED = 1");
      $mutestmt->execute();
      $mutes = array();
      while($muterow = $mutestmt->fetch()){
        array_push($mutes, array("player" => $muterow["NAME"], "reason" => $muterow["REASON"], "end" => $muterow["END"], "by" => getNameByUUID($muterow["TEAMUUID"])));
      }
      $response["bans"] = $bans;
      $response["mutes"] = $mutes;
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
