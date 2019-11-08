        <?php
        require("./inc/header.inc.php");
        if(!isMod($_SESSION['username'])){
          showModalRedirect("ERROR", "Fehler", "Der Zugriff auf diese Seite wurde verweigert.", "index.php");
          exit;
        }
        ?>
        <div class="flex-container animated fadeIn">
          <div class="flex item-1">
            <?php
            if(isset($_GET["del"])){
              require("./mysql.php");
              $stmt = $mysql->prepare("DELETE FROM chatlog WHERE LOGID = :id");
              $stmt->bindParam(":id", $_GET["del"], PDO::PARAM_STR);
              $stmt->execute();
              showModal("SUCCESS", "Erfolgreich", "Der Chatlog wurde erfolgreich gelöscht.");
            }
             ?>
             <div class="flex-button">
               <a href="reports.php" class="btn"><i class="fas fa-flag"></i> Reports</a>
             </div>
             <h1>Chatlogs</h1>
             <input type="text" name="username" id="username" placeholder="Nach ID oder Server suchen..." required>
             <div id="result"></div>
             <script>
           $(document).ready(function(){
            load_data();
            function load_data(query)
            {
              $.ajax({
                url:"fetch.php?type=CLOG",
                method:"post",
                data:{query:query},
                success:function(data)
                {
                  $('#result').html(data);
                }
              });
            }

            $('#username').keyup(function(){
              var search = $(this).val();
              if(search != '')
              {
                load_data(search);
              }
              else
              {
                load_data();
              }
            });
           });
           </script>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
