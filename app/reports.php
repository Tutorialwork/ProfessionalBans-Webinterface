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
      if(isset($request["done"])){
          $stmt = $mysql->prepare("SELECT * FROM reports WHERE ID = :id");
          $stmt->bindParam(":id", $request["done"], PDO::PARAM_INT);
          $stmt->execute();
          $count = $stmt->rowCount();
          if($count != 0){
              $stmt = $mysql->prepare("UPDATE reports SET STATUS = 1, TEAM = :webuser WHERE ID = :id");
              $stmt->bindParam(":id", $request["done"], PDO::PARAM_INT);
              $stmt->bindParam(":webuser", $access->uuid, PDO::PARAM_STR);
              $stmt->execute();
              $response["status"] = 1;
              $response["msg"] = "OK";
          } else {
            $response["status"] = 0;
            $response["msg"] = "Report not exists";
          }
      } else {
        $response["status"] = 1;
        $response["msg"] = "OK";
        $stmt = $mysql->prepare("SELECT * FROM reports WHERE STATUS = 0 ORDER BY CREATED_AT DESC");
        $stmt->execute();
        $reports = array();
        while ($row = $stmt->fetch()) {
          array_push($reports, array("id" => $row["ID"], "player" => getNameByUUID($row["UUID"]), "by" => getNameByUUID($row["REPORTER"]), "team" => getNameByUUID($row["TEAM"]), "reason" => $row["REASON"], "chatlog" => $row["LOG"], "status" => $row["STATUS"], "created_at" => $row["CREATED_AT"]));
        }
        $response["open_reports"] = $reports;
        $closestmt = $mysql->prepare("SELECT * FROM reports WHERE STATUS = 1 ORDER BY CREATED_AT DESC");
        $closestmt->execute();
        $closereports = array();
        while ($closerow = $closestmt->fetch()) {
          array_push($closereports, array("id" => $closerow["ID"], "player" => getNameByUUID($closerow["UUID"]), "by" => getNameByUUID($closerow["REPORTER"]), "team" => getNameByUUID($closerow["TEAM"]), "reason" => $closerow["REASON"], "chatlog" => $closerow["LOG"], "status" => $closerow["STATUS"], "created_at" => $closerow["CREATED_AT"]));
        }
        $response["closed_reports"] = $closereports;
        //Chatlogs
        $logstmt = $mysql->prepare("SELECT * FROM chatlog");
        $logstmt->execute();
        $chatlogs = array();
        while ($logrow = $logstmt->fetch()) {
          array_push($chatlogs, array("id" => $logrow["LOGID"], "player" => getNameByUUID($logrow["UUID"]), "by" => getNameByUUID($logrow["CREATOR_UUID"]), "server" => $logrow["SERVER"], "message" => $logrow["MESSAGE"], "messagedate" => $logrow["SENDDATE"], "logdate" => $logrow["CREATED_AT"]));
        }
        $response["chatlogs"] = $chatlogs;
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
