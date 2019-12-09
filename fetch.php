<?php
if (isset($_GET["type"])) {
	if ($_GET["type"] == "BAN") {
		require("mysql.php");
		require("datamanager.php");
		$output = '';
		if (isset($_POST["query"])) {
			$stmt = $mysql->prepare('SELECT * FROM bans WHERE NAME LIKE :query AND BANNED = 1');
			$query = '%' . $_POST["query"] . '%';
			$stmt->bindParam(":query", $query, PDO::PARAM_STR);
		} else {
			$stmt = $mysql->prepare('SELECT * FROM bans WHERE BANNED = 1');
		}
		$stmt->execute();
		$counter = $stmt->rowCount();
		if ($counter > 0) {
			$output .= '<table class="highlight">
				<tr>
					<th>Spieler</th>
					<th>Grund</th>
					<th>gebannt bis</th>
					<th>gebannt von</th>
					<th>Aktionen</th>
				</tr>';
			while ($row = $stmt->fetch()) {
				$output .= '<tr>
				<td>' . $row["NAME"] . '</td>
				<td>' . $row["REASON"] . '</td>
				<td>';
				if ($row["END"] != "-1") {
					$output .= date('d.m.Y H:i', $row["END"] / 1000);
				} else {
					$output .= "PERMANENT";
				}
				$output .= '</td>
				<td>';
				if ($row["TEAMUUID"] != "KONSOLE") {
					$output .= UUIDResolve($row["TEAMUUID"]);
				} else {
					$output .= $row["TEAMUUID"];
				}
				$output .= '</td>
				<td><a class="waves-effect waves-light red btn" href="bans.php?delete&name=' . $row["NAME"] . '"><i class="material-icons">block</i></a></td>
				</tr>';
			}
			echo $output;
		} else {
			if (isset($_POST["query"])) {
				echo '<h3 style="color: red;">Es wurden keine Suchergebnisse gefunden die deiner Eingabe entsprechen!</h3>';
			} else {
				echo '<h3 style="color: red;">Es gibt derzeit keine aktiven Bans!</h3>';
			}
		}
	} else if ($_GET["type"] == "MUTE") {
		require("mysql.php");
		require("datamanager.php");
		$output = '';
		if (isset($_POST["query"])) {
			$stmt = $mysql->prepare('SELECT * FROM bans WHERE NAME LIKE :query AND MUTED = 1');
			$query = '%' . $_POST["query"] . '%';
			$stmt->bindParam(":query", $query, PDO::PARAM_STR);
		} else {
			$stmt = $mysql->prepare('SELECT * FROM bans WHERE MUTED = 1');
		}
		$stmt->execute();
		$counter = $stmt->rowCount();
		if ($counter > 0) {
			$output .= '<table class="highlight">
				<tr>
					<th>Spieler</th>
					<th>Grund</th>
					<th>gemutet bis</th>
	        <th>gemutet von</th>
					<th>Aktionen</th>
				</tr>';
			while ($row = $stmt->fetch()) {
				$output .= '<tr>
				<td>' . $row["NAME"] . '</td>
				<td>' . $row["REASON"] . '</td>
				<td>';
				if ($row["END"] != "-1") {
					$output .= date('d.m.Y H:i', $row["END"] / 1000);
				} else {
					$output .= "PERMANENT";
				}
				$output .= '</td>
				<td>';
				if ($row["TEAMUUID"] != "KONSOLE") {
					$output .= UUIDResolve($row["TEAMUUID"]);
				} else {
					$output .= $row["TEAMUUID"];
				}
				$output .= '</td>
				<td><a class="waves-effect waves-light red btn" href="mutes.php?delete&name=' . $row["NAME"] . '"><i class="material-icons">block</i></a></td>
				</tr>';
			}
			echo $output;
		} else {
			if (isset($_POST["query"])) {
				echo '<h3 style="color: red;">Es wurden keine Suchergebnisse gefunden die deiner Eingabe entsprechen!</h3>';
			} else {
				echo '<h3 style="color: red;">Es gibt derzeit keine aktiven Mutes!</h3>';
			}
		}
	} else if ($_GET["type"] == "CLOG") {
		require("mysql.php");
		require("datamanager.php");
		$output = '';
		if (isset($_POST["query"])) {
			$stmt = $mysql->prepare('SELECT * FROM chatlog WHERE LOGID LIKE :query
			OR SERVER LIKE :query');
			$query = '%' . $_POST["query"] . '%';
			$stmt->bindParam(":query", $query, PDO::PARAM_STR);
		} else {
			$stmt = $mysql->prepare('SELECT * FROM chatlog');
		}
		$stmt->execute();
		$counter = $stmt->rowCount();
		if ($counter > 0) {
			$logs = array();
			$output .= '<table class="highlight">
				<tr>
					<th>ID</th>
					<th>Spieler</th>
					<th>erstellt von</th>
	        <th>erstellt am</th>
					<th>Server</th>
					<th>Aktionen</th>
				</tr>';
			while ($row = $stmt->fetch()) {
				if (!in_array($row["LOGID"], $logs)) {
					array_push($logs, $row["LOGID"]);
					//Prepare url to link chatlog
					$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
					$finish_url = str_replace("fetch.php?type=CLOG", "public/chatlog.php?id=", $url);
					$output .= '<tr>
	 				<td><a href="' . $finish_url . $row["LOGID"] . '">' . $row["LOGID"] . '</a></td>
	 				<td>' . UUIDResolve($row["UUID"]) . '</td>
	 				<td>';
					if ($row["CREATOR_UUID"] != "KONSOLE") {
						$output .= UUIDResolve($row["CREATOR_UUID"]);
					} else {
						$output .= "KONSOLE";
					}
					$output .= '</td>
	 				<td>' . date('d.m.Y H:i', $row["CREATED_AT"] / 1000) . '</td>
	 				<td>' . $row["SERVER"] . '</td>
	 				<td><a href="public/chatlog.php?id=' . $row["LOGID"] . '"><i class="fas fa-eye"></i></a>
					<a href="chatlogs.php?del=' . $row["LOGID"] . '"><i class="fas fa-trash-alt"></i></a></td>
	 				</tr>';
				}
			}
			echo $output;
		} else {
			if (isset($_POST["query"])) {
				echo '<h3 style="color: red;">Es wurden keine Suchergebnisse gefunden die deiner Eingabe entsprechen!</h3>';
			} else {
				echo '<h3 style="color: red;">Es gibt derzeit keine Chatlogs!</h3>';
			}
		}
	} else if ($_GET["type"] == "SORTINDEX") {
		$i = 0;

		foreach ($_POST['item'] as $value) {
			// Execute statement:
			// UPDATE [Table] SET [Position] = $i WHERE [EntityId] = $value
			$i++;

			require("mysql.php");
			$stmt = $mysql->prepare("UPDATE reasons SET SORTINDEX = :index WHERE ID = :value");
			$stmt->bindParam(":index", $i, PDO::PARAM_INT);
			$stmt->bindParam(":value", $value, PDO::PARAM_STR);
			$stmt->execute();

			//$test = file_get_contents("https://tutorialwork.000webhostapp.com/email.php?to=tutorialworktv@gmail.com&subject=test&msg=index".$i."-value".$value);

		}
	} else if ($_GET["type"] == "SEARCH") {
		require("mysql.php");
		$output = '';
		if (isset($_POST["query"])) {
			$stmt = $mysql->prepare('SELECT * FROM bans WHERE NAME LIKE :query');
			$query = '%' . $_POST["query"] . '%';
			$stmt->bindParam(":query", $query, PDO::PARAM_STR);
		} else {
			$stmt = $mysql->prepare('SELECT * FROM bans ORDER BY LASTLOGIN DESC LIMIT 10');
		}
		$stmt->execute();
		$counter = $stmt->rowCount();
		if ($counter > 0) {
			$logs = array();
			$output .= '<table class="highlight">
				<tr>
					<th>Name</th>
					<th>Gebannt</th>
					<th>Gemutet</th>
	        		<th>Bans</th>
					<th>Mutes</th>
					<th>erster Login</th>
					<th>letzter Login</th>
					<th>Status</th>
				</tr>';
			while ($row = $stmt->fetch()) {
				$output .= '<tr>
	 				<td><a href="player.php?id=' . $row["UUID"] . '">' . $row["NAME"] . '</a></td>
	 				<td>';
				if ($row["BANNED"] == 0) {
					$output .= "Nein";
				} else {
					$output .= "Ja, wegen " . htmlspecialchars($row["REASON"]);
				}
				$output .= '</td>
	 				<td>';
				if ($row["MUTED"] == 0) {
					$output .= "Nein";
				} else {
					$output .= "Ja, wegen " . htmlspecialchars($row["REASON"]);
				}
				$output .= '</td>
	 				<td>' . $row["BANS"] . '</td>
	 				<td>' . $row["MUTES"] . '</td>
					<td>' . date('d.m.Y H:i', $row["FIRSTLOGIN"] / 1000) . '</td>
					<td>' . date('d.m.Y H:i', $row["LASTLOGIN"] / 1000) . '</td>
					<td>';
				if ($row["ONLINE_STATUS"] == 0) {
					$output .= '<p style="color: red;">Offline</p>';
				} else if ($row["ONLINE_STATUS"] == 1) {
					$output .= '<p style="color: green;">Online</p>';
				}
				$output .= '</td>
	 				</tr>';
			}
			echo $output;
		} else {
			if (isset($_POST["query"])) {
				echo '<h3 style="color: red;">Es wurden keine Suchergebnisse gefunden die deiner Eingabe entsprechen!</h3>';
			} else {
				echo '<h3 style="color: red;">Zurzeit gibt es keine Spieler!</h3>';
			}
		}
	}
}
