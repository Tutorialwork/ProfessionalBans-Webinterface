<?php

class TokenHandler{

  public $username;
  public $uuid;
  private $token;

  public function __construct($token){
    if(!empty($token)){
      require("../mysql.php");
      $stmt = $mysql->prepare("SELECT * FROM apptokens WHERE TOKEN = :id");
      $stmt->bindParam(":id", $token, PDO::PARAM_STR);
      $stmt->execute();
      $count = $stmt->rowCount();
      if($count != 0){
        $row = $stmt->fetch();
        $this->uuid = $row["UUID"];
        $stmt2 = $mysql->prepare("SELECT * FROM accounts WHERE UUID = :uuid");
        $stmt2->bindParam(":uuid", $this->uuid, PDO::PARAM_STR);
        $stmt2->execute();
        $row2 = $stmt2->fetch();
        $this->username = $row2["USERNAME"];
      } else {
        $this->token = null;
      }
      $this->token = $token;
    }
  }

}

 ?>
