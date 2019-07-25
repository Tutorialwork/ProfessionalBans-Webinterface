<?php
require("tokenhandler.php");
require("../mysql.php");
$response = array();
$JSONRequest = file_get_contents("php://input");
$request = json_decode($JSONRequest, TRUE);
if(isset($request["token"])){
  $access = new TokenHandler($request["token"]);
  if($access->username != null){
      if(isset($request["search"])){
          $stmt = $mysql->prepare("SELECT * FROM bans WHERE NAME LIKE :searchkey");
          $searchkey = "%".$request["search"]."%";
          $stmt->bindParam(":searchkey", $searchkey, PDO::PARAM_STR);
          $stmt->execute();
          $count = $stmt->rowCount();
          if($count != 0){
            $search = array();
            while ($row = $stmt->fetch()) {
              array_push($search, array("ban" => $row["BANNED"], "mute" => $row["MUTED"], "reason" => $row["REASON"], "end" => $row["END"], "by" => $row["TEAMUUID"], "bans" => $row["BANS"], "mutes" => $row["MUTES"]));
            }
            $response["status"] = 1;
            $response["msg"] = "OK";
            $response["search"] = $search;
          } else {
            $response["status"] = 0;
            $response["msg"] = "No results";
          }
      } else {
        $response["status"] = 0;
        $response["msg"] = "No search keyword was requested";
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
