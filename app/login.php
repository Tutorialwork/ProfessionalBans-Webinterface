<?php
require("loginhandler.php");
require("../mysql.php");
$response = array();
$JSONRequest = file_get_contents("php://input");
$request = json_decode($JSONRequest, TRUE);
if(isset($request["username"]) && isset($request["password"])){
  $username = $request["username"];
  $password = $request["password"];
  $stmt = $mysql->prepare("SELECT * FROM accounts WHERE USERNAME = :user");
  $stmt->bindParam(":user", $username, PDO::PARAM_STR);
  $stmt->execute();
  $count = $stmt->rowCount();
  if($count != 0){
    $row = $stmt->fetch();
    if(password_verify($password, $row["PASSWORD"])){
      $session = new LoginHandler($row["UUID"]);
      $session->startSession();
      $response["status"] = 1;
      $response["msg"] = "Success! Welcome ".$row["USERNAME"];
      $response["token"] = $session->token;
    } else {
      $response["status"] = 0;
      $response["msg"] = "Username or password is not correct";
    }
  } else {
    $response["status"] = 0;
    $response["msg"] = "Username or password is not correct";
  }
} else {
  $response["status"] = 0;
  $response["msg"] = "Invaild request";
}
echo json_encode($response);
 ?>
