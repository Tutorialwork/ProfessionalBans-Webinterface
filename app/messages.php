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
function getOnlinePlayers(){
  require("../mysql.php");
  $stmt = $mysql->prepare("SELECT * FROM bans WHERE ONLINE_STATUS = 1");
  $stmt->execute();
  $count = $stmt->rowCount();
  return $count;
}
function getOnlineStatus($player){
  require("../mysql.php");
  $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :player");
  $stmt->bindParam(":player", $player, PDO::PARAM_STR);
  $stmt->execute();
  $row = $stmt->fetch();
  if($row["ONLINE_STATUS"] == 0){
    return false;
  } else if($row["ONLINE_STATUS"] == 1){
    return true;
  }
}
$response = array();
$JSONRequest = file_get_contents("php://input");
$request = json_decode($JSONRequest, TRUE);
if(isset($request["token"])){
  $access = new TokenHandler($request["token"]);
  if($access->username != null){
    if(isset($request["send"]) && isset($request["to"]) && isset($request["onlinecheck"])){
        $stmt = $mysql->prepare("INSERT INTO privatemessages (SENDER, RECEIVER, MESSAGE, STATUS, DATE) VALUES (:sender, :to, :msg, :status, :now)");
        $stmt->bindParam(":sender", $access->uuid, PDO::PARAM_STR);

        $stmt2 = $mysql->prepare("SELECT * FROM bans WHERE NAME = :user");
        $stmt2->bindParam(":user", $request["to"], PDO::PARAM_STR);
        $stmt2->execute();
        $row2 = $stmt2->fetch();
        
        $stmt->bindParam(":to", $row2["UUID"], PDO::PARAM_STR);
        $stmt->bindParam(":msg", $request["send"], PDO::PARAM_STR);
        $status = 0;
        $stmt->bindParam(":status", $status, PDO::PARAM_INT);
        $now = time() * 1000;
        $stmt->bindParam(":now", $now, PDO::PARAM_STR);
        $stmt->execute();
        $response["status"] = 1;
        $response["msg"] = "OK";
    } else if(isset($request["broadcast"]) && isset($request["message"])){
      if(getOnlinePlayers() > 0){
        $stmt = $mysql->prepare("INSERT INTO privatemessages (SENDER, RECEIVER, MESSAGE, STATUS, DATE) VALUES (:sender, :to, :msg, :status, :now)");
        $stmt->bindParam(":sender", $access->uuid, PDO::PARAM_STR);
        $type = "BROADCAST";
        $stmt->bindParam(":to", $type, PDO::PARAM_STR);
        $stmt->bindParam(":msg", $request["message"], PDO::PARAM_STR);
        $status = 0;
        $stmt->bindParam(":status", $status, PDO::PARAM_INT);
        $now = time() * 1000;
        $stmt->bindParam(":now", $now, PDO::PARAM_STR);
        $stmt->execute();
        $response["status"] = 1;
        $response["msg"] = "OK";
      } else {
        $response["status"] = 0;
        $response["msg"] = "No players online";
      }
    } else if(isset($request["teamchat"])){
        $response["status"] = 1;
        $response["msg"] = "OK";
        $stmt = $mysql->prepare("SELECT * FROM privatemessages WHERE RECEIVER = 'TEAM' ORDER BY DATE DESC");
        $stmt->execute();
        $messages = array();
        while($row = $stmt->fetch()){
          array_push($messages, array("id" => $row["ID"], "from" => getNameByUUID($row["SENDER"]), "message" => $row["MESSAGE"], "status" => $row["STATUS"], "date" => $row["DATE"]));
        }
        $response["teamchat"] = $messages;
    } else {
        $response["status"] = 1;
        $response["msg"] = "OK";
        $stmt = $mysql->prepare("SELECT * FROM privatemessages WHERE RECEIVER = :uuid ORDER BY DATE DESC");
        $stmt->bindParam(":uuid", $access->uuid, PDO::PARAM_STR);
        $stmt->execute();
        $messages = array();
        while($row = $stmt->fetch()){
          array_push($messages, array("id" => $row["ID"], "from" => getNameByUUID($row["SENDER"]), "message" => $row["MESSAGE"], "status" => $row["STATUS"], "date" => $row["DATE"]));
        }
        $response["privatemessages"] = $messages;
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
