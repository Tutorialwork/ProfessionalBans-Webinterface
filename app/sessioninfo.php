<?php
require("tokenhandler.php");
require("../mysql.php");
$response = array();
$JSONRequest = file_get_contents("php://input");
$request = json_decode($JSONRequest, TRUE);
if(isset($request["token"])){
  $access = new TokenHandler($request["token"]);
  if($access->username != null){
      if(isset($request["usertoken"])){
        $info = new TokenHandler($request["usertoken"]);
        if($info->username != null){
          $response["status"] = 1;
          $response["msg"] = "OK";
          $response["username"] = $info->username;
          $response["uuid"] = $info->uuid;
        } else {
          $response["status"] = 0;
          $response["msg"] = "Token not found";
        }
      } else {
        $response["status"] = 0;
        $response["msg"] = "No token informations was requsted";
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
