<?php
require("tokenhandler.php");
require("../mysql.php");
$response = array();
$JSONRequest = file_get_contents("php://input");
$request = json_decode($JSONRequest, TRUE);
if(isset($request["token"]) && isset($request["firebase_token"])){
  $access = new TokenHandler($request["token"]);
  if($access->username != null){
      $stmt = $mysql->prepare("UPDATE apptokens SET FIREBASE_TOKEN = :ftoken WHERE TOKEN = :token");
      $stmt->bindParam(":ftoken", $request["firebase_token"], PDO::PARAM_STR);
      $stmt->bindParam(":token", $request["token"], PDO::PARAM_STR);
      $stmt->execute();
      $response["status"] = 1;
      $response["msg"] = "Updated firebase token for ".$access->username;
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
