<?php
require("tokenhandler.php");
require("../mysql.php");
function getNameByUUID($uuid)
{
  require("../mysql.php");
  $stmt = $mysql->prepare("SELECT * FROM bans WHERE UUID = :uuid");
  $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
  $stmt->execute();
  $row = $stmt->fetch();
  return $row["NAME"];
}
function getUUIDByName($name)
{
  require("../mysql.php");
  $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :user");
  $stmt->bindParam(":user", $name, PDO::PARAM_STR);
  $stmt->execute();
  $row = $stmt->fetch();
  return $row["UUID"];
}
$response = array();
$JSONRequest = file_get_contents("php://input");
$request = json_decode($JSONRequest, TRUE);
if (isset($request["token"])) {
  $access = new TokenHandler($request["token"]);
  if ($access->username != null) {
    if (isset($request["unban"])) {
      require("../datamanager.php");
      if(isMod($access->username)){
        $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :user");
        $stmt->bindParam(":user", $request["unban"], PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->rowCount();
        $row = $stmt->fetch();
        if ($count != 0) {
          if ($row["BANNED"] == 1) {
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
      } else {
        $response["status"] = 0;
        $response["msg"] = "Request not permitted";
      }
    } else if (isset($request["unmute"])) {
      $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :user");
      $stmt->bindParam(":user", $request["unmute"], PDO::PARAM_STR);
      $stmt->execute();
      $count = $stmt->rowCount();
      $row = $stmt->fetch();
      if ($count != 0) {
        if ($row["MUTED"] == 1) {
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
    } else if (isset($request["user"])) {
      $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :user");
      $stmt->bindParam(":user", $request["user"], PDO::PARAM_STR);
      $stmt->execute();
      $count = $stmt->rowCount();
      if ($count != 0) {
        $response["status"] = 1;
        $response["msg"] = "OK";
        $player = array();
        while ($row = $stmt->fetch()) {
          array_push($player, array("ban" => $row["BANNED"], "mute" => $row["MUTED"], "reason" => $row["REASON"], "end" => $row["END"], "by" => $row["TEAMUUID"], "bans" => $row["BANS"], "mutes" => $row["MUTES"]));
        }
        $response["details"] = $player;
      } else {
        $response["status"] = 0;
        $response["msg"] = "User not found";
      }
    } else if (isset($request["ban"]) && isset($request["player"]) && isset($request["banid"])) {
      require("../datamanager.php");
      if(isMod($access->username)){
        $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :name");
        $stmt->bindParam(":name", $request["player"], PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->rowCount();
        if ($count != 0) {
          $stmt = $mysql->prepare("SELECT * FROM reasons WHERE ID = :id");
          $stmt->bindParam(":id", $request["banid"], PDO::PARAM_INT);
          $stmt->execute();
          $count = $stmt->rowCount();
          if ($count != 0) {
            $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :name");
            $stmt->bindParam(":name", $request["player"], PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row["BANNED"] == 0) {
                $stmt = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = ?");
                $stmt->execute(array($request["player"]));
                if($stmt->rowCount() == 0){
                    $now = time();
                    if (getMinutesByReasonID($request["banid"]) != "-1") { //Kein Perma Ban
                        $phpEND = $now + getMinutesByReasonID($request["banid"]) * 60;
                        $javaEND = $phpEND * 1000;
                    } else {
                        //PERMA BAN
                        $javaEND = -1;
                    }
                    $stmt = $mysql->prepare("UPDATE bans SET BANNED = 1, MUTED = 0, REASON = :reason, END = :end, TEAMUUID = :webUUID  WHERE NAME = :user");
                    $reason = getReasonByReasonID($request["banid"]);
                    $stmt->bindParam(":reason", $reason, PDO::PARAM_STR);
                    $stmt->bindParam(":end", $javaEND, PDO::PARAM_STR);

                    //UUID von User im Webinterface
                    $useruuid = $access->uuid;

                    $stmt->bindParam(":webUUID", $useruuid, PDO::PARAM_STR);
                    $stmt->bindParam(":user", $request["player"], PDO::PARAM_STR);
                    $stmt->execute();
                    addBanCounter(getUUIDByName($request["player"]));

                    $response["status"] = 1;
                    $response["msg"] = "OK";
                } else {
                    $response["status"] = 0;
                    $response["msg"] = "You are not permitted to ban this user";
                }
            } else {
              $response["status"] = 0;
              $response["msg"] = "User is already banned";
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
        $response["status"] = 0;
        $response["msg"] = "Request not permitted";
      }
    } else if (isset($request["mute"]) && isset($request["player"]) && isset($request["banid"])) {
      $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :name");
      $stmt->bindParam(":name", $request["player"], PDO::PARAM_STR);
      $stmt->execute();
      $count = $stmt->rowCount();
      if ($count != 0) {
        $stmt = $mysql->prepare("SELECT * FROM reasons WHERE ID = :id");
        $stmt->bindParam(":id", $request["banid"], PDO::PARAM_INT);
        $stmt->execute();
        $count = $stmt->rowCount();
        if ($count != 0) {
          $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME = :name");
          $stmt->bindParam(":name", $request["player"], PDO::PARAM_STR);
          $stmt->execute();
          $row = $stmt->fetch();
          if ($row["BANNED"] == 0) {
            $now = time();
            if (getMinutesByReasonID($request["banid"]) != "-1") { //Kein Perma Ban
              $phpEND = $now + getMinutesByReasonID($request["banid"]) * 60;
              $javaEND = $phpEND * 1000;
            } else {
              //PERMA BAN
              $javaEND = -1;
            }
            $stmt = $mysql->prepare("UPDATE bans SET BANNED = 0, MUTED = 1, REASON = :reason, END = :end, TEAMUUID = :webUUID  WHERE NAME = :user");
            $reason = getReasonByReasonID($request["banid"]);
            $stmt->bindParam(":reason", $reason, PDO::PARAM_STR);
            $stmt->bindParam(":end", $javaEND, PDO::PARAM_STR);

            //UUID von User im Webinterface
            $useruuid = $access->uuid;

            $stmt->bindParam(":webUUID", $useruuid, PDO::PARAM_STR);
            $stmt->bindParam(":user", $request["player"], PDO::PARAM_STR);
            $stmt->execute();
            addMuteCounter(getUUIDByName($request["player"]));

            $response["status"] = 1;
            $response["msg"] = "OK";
          } else {
            $response["status"] = 0;
            $response["msg"] = "User is already muted";
          }
        } else {
          $response["status"] = 0;
          $response["msg"] = "Reason is not exists";
        }
      } else {
        $response["status"] = 0;
        $response["msg"] = "User is not exists";
      }
    } else if (isset($request["reasons"])){
      $response["status"] = 1;
      $response["msg"] = "OK";
      $stmt = $mysql->prepare("SELECT * FROM reasons WHERE TYPE = 0");
      $stmt->execute();
      $ban_reasons = array();
      while($row = $stmt->fetch()){
        array_push($ban_reasons, array("id" => $row["ID"], "reason" => $row["REASON"], "time" => $row["TIME"]));
      }
      $stmt2 = $mysql->prepare("SELECT * FROM reasons WHERE TYPE = 1");
      $stmt2->execute();
      $mute_reasons = array();
      while($row = $stmt2->fetch()){
        array_push($mute_reasons, array("id" => $row["ID"], "reason" => $row["REASON"], "time" => $row["TIME"]));
      }
      $response["ban_reasons"] = $ban_reasons;
      $response["mute_reasons"] = $mute_reasons;
    } else {
      $response["status"] = 1;
      $response["msg"] = "OK";
      $stmt = $mysql->prepare("SELECT * FROM bans WHERE BANNED = 1");
      $stmt->execute();
      $bans = array();
      while ($row = $stmt->fetch()) {
        array_push($bans, array("player" => $row["NAME"], "reason" => $row["REASON"], "end" => $row["END"], "by" => getNameByUUID($row["TEAMUUID"])));
      }
      $mutestmt = $mysql->prepare("SELECT * FROM bans WHERE MUTED = 1");
      $mutestmt->execute();
      $mutes = array();
      while ($muterow = $mutestmt->fetch()) {
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