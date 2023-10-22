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
			<li><a href="termine.php">alle Termine</a></li>
			<li><a href="termine_einladungen.php">Einladungen</a></li>
		</ul>
	</nav>
	<h1>Termine je User</h1>
	<ul class="user">
		<?php
		$sql = "
			SELECT
				*
			FROM tbl_user
			ORDER BY Nickname ASC
		";
		$userliste = $conn->query($sql) or die("Fehler in der Query: " . $conn->error . "<br>" . $sql);
		while($user = $userliste->fetch_object()) {
			echo('
				<li>
					' . $user->Nickname . ' (<a href="mailto:' . $user->Emailadresse . '" target="_blank">' . $user->Emailadresse . '</a>)
					<div>' . $user->Vorname . ' ' . $user->Nachname . '</div>
					<div class="notiz">' . $user->Notiz . '</div>
					<ul class="termine">
			');
			
			// ---- Termine je User: ----
			$sql = "
				SELECT
					tbl_termine.*,
					tbl_staaten.Bezeichnung AS Staat,
					tbl_kategorien.Bezeichnung AS Kategorie,
					tbl_kategorien.Farbcode
				FROM tbl_termine
				LEFT JOIN tbl_staaten ON tbl_staaten.IDStaat=tbl_termine.FIDStaat
				INNER JOIN tbl_kategorien ON tbl_kategorien.IDKategorie=tbl_termine.FIDKategorie
				WHERE(
					tbl_termine.FIDUser=" . $user->IDUser . "
				)
				ORDER BY tbl_termine.Beginn ASC
			";
			$termine = $conn->query($sql) or die("Fehler in der Query: " . $conn->error . "<br>" . $sql);
			while($termin = $termine->fetch_object()) {
				echo('
					<li class="kategorie" style="border-color:#' . $termin->Farbcode . '">
						<div class="datum">
							von ' . date("j.n.Y, H:i",strtotime($termin->Beginn)) . ' Uhr bis ' . date("j.n.Y, H:i",strtotime($termin->Ende)) . ' Uhr
						</div>
						<div class="termin">' . $termin->Bezeichnung . '</div>
						<address>
							<div>' . $termin->Adresse . '</<div>
							<div>' . $termin->PLZ . ' ' . $termin->Ort . '</div>
							<div>' . $termin->Staat . '</div>
						</address>
					</li>
				');
			}
			// --------------------------
			
			echo('
					</ul>
				</li>
			');
		}
		?>
	</ul>
</body>
</html>