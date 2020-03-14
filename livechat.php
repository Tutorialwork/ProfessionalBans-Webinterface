<?php
require("./inc/livechat_header.inc.php");
?>
        <div class="flex-container animated fadeIn">
          <div class="flex item-1">
            <h1>Livechat</h1>
            <?php
            if(isset($_GET["server"])){
              ?>
              <p><?php echo $messages["messages_from"] ?>: <strong><?php echo $_GET["server"] ?></strong></p>
              <?php
            } else {
              ?>
              <p><?php echo $messages["messages_from"] ?>: <strong><?php echo $messages["all_servers"] ?></strong></p>
              <?php
            }
             ?>
            <div id="output"></div>
            <?php
            //Update Counter function
            if(isset($_GET["server"])){
              if(isset($_GET["page"])){
                $givenpage = $_GET["page"];
              } else {
                $givenpage = 1;
              }
              ?>
              <script type="text/javascript">
              $(document).ready(function(){


            var updateDiv = function ()
          {
            $('#output').load('livechat.php?update&server=<?php echo $_GET["server"] ?>&page=<?php echo $givenpage ?>', function () {
              deinTimer = window.setTimeout(updateDiv, 250);
            });
          }
          var deinTimer = window.setTimeout(updateDiv, 250);

            });
              </script>
              <?php
            } else {
              if(isset($_GET["page"])){
                $givenpage = $_GET["page"];
              } else {
                $givenpage = 1;
              }
              ?>
              <script type="text/javascript">
              $(document).ready(function(){


            var updateDiv = function ()
          {
            $('#output').load('livechat.php?update&page=<?php echo $givenpage; ?>', function () {
              deinTimer = window.setTimeout(updateDiv, 250);
            });
          }
          var deinTimer = window.setTimeout(updateDiv, 250);

            });
              </script>
              <?php
            }
             ?>
          </div>
          <div class="flex item-2 sidebox">
            <select name="server" onChange="window.document.location.href=this.options[this.selectedIndex].value;">
              <?php
              if(!isset($_GET["server"])){
                echo '<option value="livechat.php">'.$messages["all_servers"].'</option>';
                require("./mysql.php");
                $server = array();
                $stmt = $mysql->prepare("SELECT SERVER FROM chat");
                $stmt->execute();
                while($row = $stmt->fetch()){
                  if(!in_array($row["SERVER"], $server)){
                     array_push($server, $row["SERVER"]);
                  }
                }
                foreach ($server as $value) {
                  echo '<option value="livechat.php?server='.$value.'">'.$value.'</option>';
                }
              } else {
                require("./mysql.php");
                $server = array();
                $stmt = $mysql->prepare("SELECT SERVER FROM chat");
                $stmt->execute();
                while($row = $stmt->fetch()){
                  if(!in_array($row["SERVER"], $server)){
                     array_push($server, $row["SERVER"]);
                  }
                }
                echo '<option value="livechat.php?server='.$_GET["server"].'">'.$_GET["server"].'</option>';
                foreach ($server as $value) {
                  if($value != $_GET["server"]){
                    echo '<option value="livechat.php?server='.$value.'">'.$value.'</option>';
                  }
                }
                echo '<option value="livechat.php">'.$messages["all_servers"].'</option>';
              }
               ?>
            </select>
            <div class="flex-button">
              <p></p>
              <a href="livechat.php?download" class="btn"><i class="fas fa-file-download"></i> Download</a>
              <a href="livechat.php?clean" class="btn"><i class="far fa-trash-alt"></i> <?php echo $messages["delete"] ?></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
