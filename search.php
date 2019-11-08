        <?php
        require("./inc/header.inc.php");
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
             <h1>Suche</h1>
             <input type="text" name="username" id="username" placeholder="Nach einem Spieler suchen..." required>
             <div id="result"></div>
             <script>
           $(document).ready(function(){
            load_data();
            function load_data(query)
            {
              $.ajax({
                url:"fetch.php?type=SEARCH",
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
