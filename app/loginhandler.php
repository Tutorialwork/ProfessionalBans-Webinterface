<?php

class LoginHandler{

  public $token;
  private $uuid;

  public function __construct($uuid){
    if(!empty($uuid)){
      require("../mysql.php");
      $stmt = $mysql->prepare("SELECT * FROM apptokens WHERE UUID = :id");
      $stmt->bindParam(":id", $uuid, PDO::PARAM_STR);
      $stmt->execute();
      $count = $stmt->rowCount();
      if($count != 0){
        $row = $stmt->fetch();
        $this->token = $row["TOKEN"];
      } else {
        $this->token = null;
      }
      $this->uuid = $uuid;
    }
  }

  public function startSession(){
    require("../mysql.php");
    if($this->token != null){
      $stmt = $mysql->prepare("UPDATE apptokens SET TOKEN = :token WHERE UUID = :id");
      $this->token = $this->generateRandomString();
      $stmt->bindParam(":token", $this->token, PDO::PARAM_STR);
      $stmt->bindParam(":id", $this->uuid, PDO::PARAM_STR);
      $stmt->execute();
    } else {
      $stmt = $mysql->prepare("INSERT INTO apptokens (UUID, TOKEN) VALUES (:id, :token)");
      $this->token = $this->generateRandomString();
      $stmt->bindParam(":token", $this->token, PDO::PARAM_STR);
      $stmt->bindParam(":id", $this->uuid, PDO::PARAM_STR);
      $stmt->execute();
    }
  }

  private function generateRandomString($length = 55){
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }

}

 ?>
