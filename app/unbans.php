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
function getUUID($id){
  require("../mysql.php");
  $stmt = $mysql->prepare("SELECT * FROM unbans WHERE ID = :id");
  $stmt->bindParam(":id", $id, PDO::PARAM_INT);
  $stmt->execute();
  $row = $stmt->fetch();
  return $row["UUID"];
}
$response = array();
$JSONRequest = file_get_contents("php://input");
$request = json_decode($JSONRequest, TRUE);
if(isset($request["token"])){
  $access = new TokenHandler($request["token"]);
  if($access->username != null){
      if(isset($request["done"]) && isset($request["status"])){
          $stmt = $mysql->prepare("SELECT * FROM unbans WHERE ID = :id");
          $stmt->bindParam(":id", $request["done"], PDO::PARAM_INT);
          $stmt->execute();
          $count = $stmt->rowCount();
          if($count != 0){
            $status = (int) $request["status"];
            $stmt = $mysql->prepare("UPDATE unbans SET STATUS = :status WHERE ID = :id");
            $stmt->bindParam(":status", $status, PDO::PARAM_INT);
            $stmt->bindParam(":id", $request["done"], PDO::PARAM_INT);
            $stmt->execute();
            if($status == 1){
              $uuid = getUUID($request["done"]);
              $stmt = $mysql->prepare("UPDATE bans SET BANNED = 0 WHERE UUID = :uuid");
              $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
              $stmt->execute();
            } else if($status == 2){
              //Verkürzen auf 3 Tage
              $uuid = getUUID($request["done"]);
              $time = 259200 * 1000;
              $javatime = round(time() * 1000) + round($time);
              $stmt = $mysql->prepare("UPDATE bans SET END = :end WHERE UUID = :uuid");
              $stmt->bindParam(":end", $javatime, PDO::PARAM_STR);
              $stmt->bindParam(":uuid", $uuid, PDO::PARAM_STR);
              $stmt->execute();
            }
            $response["status"] = 1;
            $response["msg"] = "OK";
          } else {
            $response["status"] = 0;
            $response["msg"] = "Unbanrequst not exists";
          }
      } else {
        $response["status"] = 1;
        $response["msg"] = "OK";
        
        $stmt = $mysql->prepare("SELECT * FROM unbans WHERE STATUS = 0 ORDER BY DATE DESC");
        $stmt->execute();
        $unbans = array();
        while ($row = $stmt->fetch()) {
          $statement = $mysql->prepare("SELECT * FROM bans WHERE UUID = :uuid");
          $statement->bindParam(":uuid", $row["UUID"], PDO::PARAM_STR);
          $statement->execute();
          $db = $statement->fetch();
          array_push($unbans, array("id" => $row["ID"], "player" => getNameByUUID($row["UUID"]), "fair" => $row["FAIR"], "message" => $row["MESSAGE"], "created_at" => $row["DATE"], "status" => $row["STATUS"], "reason" => $db["REASON"], "end" => $db["END"]));
        }
        $response["open_unbans"] = $unbans;

        $stmt2 = $mysql->prepare("SELECT * FROM unbans ORDER BY DATE DESC");
        $stmt2->execute();
        $unbans_done = array();
        while ($row2 = $stmt2->fetch()) {
          array_push($unbans_done, array("id" => $row2["ID"], "player" => getNameByUUID($row2["UUID"]), "fair" => $row2["FAIR"], "message" => $row2["MESSAGE"], "created_at" => $row2["DATE"], "status" => $row2["STATUS"]));
        }
        $response["closed_unbans"] = $unbans_done;
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
