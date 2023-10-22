<?php
require("includes/config.inc.php");
require("includes/common.inc.php");
require("includes/conn.inc.php");
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="UTF-8">
<title>Terminkalender</title>
	<link rel="stylesheet" href="css/common.css">
	<link rel="stylesheet" href="css/terminkalender.css">
</head>

<body>
	<nav>
		<ul>
			<li><a href="index.html">Startseite</a></li>
			<li><a href="termine_user.php">Termine je User</a></li>
			<li><a href="termine.php">Termine</a></li>
		</ul>
	</nav>
	<h1>Einladungen</h1>
	<ul class="termine">
		<?php		
		$sql = "
			SELECT
				tbl_termine.*,
				tbl_staaten.Bezeichnung AS Staat,
				tbl_kategorien.Bezeichnung AS Kategorie,
				tbl_kategorien.Farbcode,
				tbl_user.Nickname
			FROM tbl_termine
			LEFT JOIN tbl_staaten ON tbl_staaten.IDStaat=tbl_termine.FIDStaat
			INNER JOIN tbl_kategorien ON tbl_kategorien.IDKategorie=tbl_termine.FIDKategorie
			INNER JOIN tbl_user ON tbl_user.IDUser=tbl_termine.FIDUser
			WHERE(
				SELECT COUNT(*) AS cnt
				FROM tbl_termine_einladungen
				WHERE(
					FIDTermin=IDTermin
				)
			)>0
			ORDER BY tbl_termine.Beginn DESC
		";
		$termine = $conn->query($sql) or die("Fehler in der Query: " . $conn->error . "<br>" . $sql);
		while($termin = $termine->fetch_object()) {
			echo('
				<li class="kategorie" style="border-color:#' . $termin->Farbcode . '">
					<div class="nickname">' . $termin->Nickname . '</div>
					<div class="datum">
						von ' . date("j.n.Y, H:i",strtotime($termin->Beginn)) . ' Uhr bis ' . date("j.n.Y, H:i",strtotime($termin->Ende)) . ' Uhr
					</div>
					<div class="termin">' . $termin->Bezeichnung . '</div>
					<address>
						<div>' . $termin->Adresse . '</<div>
						<div>' . $termin->PLZ . ' ' . $termin->Ort . '</div>
						<div>' . $termin->Staat . '</div>
					</address>
					<div class="einladungen">
						eingeladen sind:
						<ul>
			');
			
			// ---- Einladungen je Termin: ----
			$sql = "
				SELECT
					tbl_user.Nickname,
					tbl_einladungsstati.Bezeichnung AS Status
				FROM tbl_termine_einladungen
				INNER JOIN tbl_user ON tbl_user.IDUser=tbl_termine_einladungen.FIDUser
				INNER JOIN tbl_einladungsstati ON tbl_einladungsstati.IDEinladungsstatus=tbl_termine_einladungen.FIDEinladungsstatus
				WHERE(
					tbl_termine_einladungen.FIDTermin=" . $termin->IDTermin . "
				)
			";
			$userliste = $conn->query($sql) or die("Fehler in der Query: " . $conn->error . "<br>" . $sql);
			while($user = $userliste->fetch_object()) {
				echo('
					<li>' . $user->Nickname . ': ' . $user->Status . '</li>
				');
			}
			// --------------------------------
			
			echo('		
						</ul>
					</div>
				</li>
			');
		}
		?>
	</ul>
</body>
</html>