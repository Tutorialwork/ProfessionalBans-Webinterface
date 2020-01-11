<?php
require("tokenhandler.php");
require("../mysql.php");
require("../datamanager.php");
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
    if(isset($request["send"]) && isset($request["to"])){
        $uuid = NameResolve($request["to"]);
        $onlinestmt = $mysql->prepare("SELECT * FROM apptokens WHERE UUID = :uuid");
        $onlinestmt->execute(array(":uuid" => $uuid));

        if($onlinestmt->rowCount() == 0){
          if(getOnlineStatus($request["to"])){
            /* 
            Insert Message (Player is online)
            */
            $stmt = $mysql->prepare("INSERT INTO privatemessages (SENDER, RECEIVER, MESSAGE, STATUS, DATE) VALUES (:sender, :to, :msg, :status, :now)");
            $stmt->bindParam(":sender", $access->uuid, PDO::PARAM_STR);
            $stmt->bindParam(":to", $uuid, PDO::PARAM_STR);
            $stmt->bindParam(":msg", $request["send"], PDO::PARAM_STR);
            $status = 0;
            $stmt->bindParam(":status", $status, PDO::PARAM_INT);
            $now = time() * 1000;
            $stmt->bindParam(":now", $now, PDO::PARAM_STR);
            $stmt->execute();
            $response["status"] = 1;
            $response["msg"] = "OK";
          } else {
            $response["status"] = 0;
            $response["msg"] = "Player currently offline";
          }
        } else {
          /* 
          Insert Message (Player has app)
          */
          $stmt = $mysql->prepare("INSERT INTO privatemessages (SENDER, RECEIVER, MESSAGE, STATUS, DATE) VALUES (:sender, :to, :msg, :status, :now)");
          $stmt->bindParam(":sender", $access->uuid, PDO::PARAM_STR);
          $stmt->bindParam(":to", $uuid, PDO::PARAM_STR);
          $stmt->bindParam(":msg", $request["send"], PDO::PARAM_STR);
          $status = 0;
          $stmt->bindParam(":status", $status, PDO::PARAM_INT);
          $now = time() * 1000;
          $stmt->bindParam(":now", $now, PDO::PARAM_STR);
          $stmt->execute();
          $response["status"] = 1;
          $response["msg"] = "OK";
        }
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
          array_push($messages, array("id" => $row["ID"], "from" => UUIDResolve($row["SENDER"]), "message" => $row["MESSAGE"], "status" => $row["STATUS"], "date" => $row["DATE"]));
        }
        $response["teamchat"] = $messages;
    } else {
        $response["status"] = 1;
        $response["msg"] = "OK";
        $stmt = $mysql->prepare("SELECT * FROM privatemessages WHERE RECEIVER = :uuid OR SENDER = :uuid ORDER BY DATE DESC");
        $stmt->bindParam(":uuid", $access->uuid, PDO::PARAM_STR);
        $stmt->execute();
        $messages = array();
        while($row = $stmt->fetch()){
          array_push($messages, array("id" => $row["ID"], "to" => UUIDResolve($row["RECEIVER"]), "from" => UUIDResolve($row["SENDER"]), "message" => $row["MESSAGE"], "status" => $row["STATUS"], "date" => $row["DATE"]));
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
