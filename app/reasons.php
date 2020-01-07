<?php
require("tokenhandler.php");
require("../mysql.php");
$response = array();
$JSONRequest = file_get_contents("php://input");
$request = json_decode($JSONRequest, TRUE);
if(isset($request["token"])){
  $access = new TokenHandler($request["token"]);
  if($access->username != null){
    require("../datamanager.php");
    if(isset($request["delete"])){
        if(isAdmin($access->username)){
          $stmt = $mysql->prepare("SELECT * FROM reasons WHERE ID = :id");
          $stmt->bindParam(":id", $request["delete"], PDO::PARAM_INT);
          $stmt->execute();
          $count = $stmt->rowCount();
          if($count != 0){
              $stmt = $mysql->prepare("DELETE FROM reasons WHERE ID = :id");
              $stmt->bindParam(":id", $request["delete"], PDO::PARAM_INT);
              $stmt->execute();
              $idstmt = $mysql->prepare("SELECT * FROM reasons");
                $idstmt->execute();
                $id = 0;
                while($row = $idstmt->fetch()){
                  $id++;
                  $stmt = $mysql->prepare("UPDATE reasons SET ID = :id WHERE ID = :dbid");
                  $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                  $stmt->bindParam(":dbid", $row["ID"], PDO::PARAM_INT);
                  $stmt->execute();
                }
              $response["status"] = 1;
              $response["msg"] = "OK";
          } else {
            $response["status"] = 0;
            $response["msg"] = "Reason not found";
          }
        } else {
          $response["status"] = 0;
          $response["msg"] = "Request not permitted";
        }
    } else if(isset($request["search"])) {
      $stmt = $mysql->prepare("SELECT * FROM reasons WHERE REASON = :query");
      $stmt->execute(array(":query" => $request["search"]));
      if($stmt->rowCount() != 0){
      $row = $stmt->fetch();
      $type = "";
      if($row["TYPE"] == 0){
        $type = "ban";
      } else if($row["TYPE"] == 1){
        $type = "mute";
      }
      $response["status"] = 1;
      $response["type"] = $type;
      $response["msg"] = "Reason is valid";
      } else {
      $response["status"] = 0;
      $response["msg"] = "Reason is not registered";
      }
    } else {
      $response["status"] = 1;
      $response["msg"] = "OK";
      $stmt = $mysql->prepare("SELECT * FROM reasons");
      $stmt->execute();
      $reasons = array();
      while($row = $stmt->fetch()){
          if($row["TIME"] == -1){
              $time = "Permanent";
            } else if($row["TIME"] < 60){
              $time = $row["TIME"]."min";
            } else if($row["TIME"] < 1440){
              $stunden = $row["TIME"] / 60;
              $time = $stunden."h";
            } else {
              $tage = $row["TIME"] / 1440;
              $time = $tage."days";
            }
        array_push($reasons, array("id" => $row["ID"], "reason" => $row["REASON"], "time" => $time, "type" => $row["TYPE"], "created_date" => $row["ADDED_AT"], "bans" => $row["BANS"], "perms" => $row["PERMS"]));
      }
      $response["reasons"] = $reasons;
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