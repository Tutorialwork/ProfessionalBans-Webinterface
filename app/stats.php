<?php
require("tokenhandler.php");
require("../mysql.php");
$response = array();
$JSONRequest = file_get_contents("php://input");
$request = json_decode($JSONRequest, TRUE);
if(isset($request["token"])){
  $access = new TokenHandler($request["token"]);
  if($access->username != null){
    $response["status"] = 1;
    $response["msg"] = "OK";
    $pstmt = $mysql->prepare("SELECT * FROM bans");
    $pstmt->execute();
    $data = 0;
    while($row = $pstmt->fetch()){
          $data++;
    }
    $mstmt = $mysql->prepare("SELECT * FROM bans WHERE MUTED = 1");
    $mstmt->execute();
    $mutes = 0;
    while($row = $mstmt->fetch()){
          $mutes++;
    }
    $bstmt = $mysql->prepare("SELECT * FROM bans WHERE BANNED = 1");
    $bstmt->execute();
    $bans = 0;
    while($row = $bstmt->fetch()){
          $bans++;
    }
    $response["player"] = $data;
    $response["mutes"] = $mutes;
    $response["bans"] = $bans;
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
