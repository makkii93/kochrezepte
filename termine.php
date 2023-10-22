<?php
require("includes/config.inc.php");
require("includes/common.inc.php");
require("includes/conn.inc.php");

function kategorien_show() {
	global $conn;
	
	echo('
		<select name="kategorie">
			<option value="0">-</option>
	');
	$sql = "
		SELECT
			tbl_kategorien.*,
			tbl_user.Nickname
		FROM tbl_kategorien
		LEFT JOIN tbl_user ON tbl_user.IDUser=tbl_kategorien.FIDUser
		ORDER BY tbl_kategorien.Bezeichnung ASC
	";
	
	$kats = $conn->query($sql) or die("Fehler in der Query: " . $conn->error . "<br>" . $sql);
	while($kat = $kats->fetch_object()) {
		$user = !is_null($kat->Nickname) ? ' (' . $kat->Nickname . ')' : "";
		echo('
			<option value="' . $kat->IDKategorie . '">' . $kat->Bezeichnung . $user . '</option>
		');
	}
	
	echo('</select>');
}
function einladungen_show() {
	global $conn;
	
	echo('
		<select name="status">
			<option value="0">-</option>
	');
	$sql = "
		SELECT
			*
		FROM tbl_einladungsstati
		ORDER BY Bezeichnung ASC
	";
	
	$stati = $conn->query($sql) or die("Fehler in der Query: " . $conn->error . "<br>" . $sql);
	while($status = $stati->fetch_object()) {
		echo('
			<option value="' . $status->IDEinladungsstatus . '">' . $status->Bezeichnung . '</option>
		');
	}
	
	echo('</select>');
}
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
			<li><a href="termine_einladungen.php">Einladungen</a></li>
		</ul>
	</nav>
	<h1>Termine</h1>
	<form method="post">
		<label>
			Terminkategorie:
			<?php kategorien_show(); ?>
		</label>
		<!--
		<label>
			Einladung:
			<?php einladungen_show(); ?>
		</label>
		-->
		<label>
			Terminbezeichnung:
			<input type="text" name="termin">
		</label>
		<label>
			Nickname/Emailadresse:
			<input type="text" name="user">
		</label>
		<label>
			Datum:<br>
			von <input type="date" name="von">
			bis <input type="date" name="bis">
		</label>
		<input type="submit" value="filtern">
	</form>
	<ul class="termine">
		<?php
		$where = "";
		if(count($_POST)>0) {
			$arr = [];
			if(intval($_POST["kategorie"])>0) {
				$arr[] = "tbl_termine.FIDKategorie=" . intval($_POST["kategorie"]);
			}
			if(strlen($_POST["termin"])>0) {
				$arr[] = "tbl_termine.Bezeichnung LIKE '%" . $_POST["termin"] . "%'";
			}
			if(strlen($_POST["user"])>0) {
				$arr[] = "
					(
						tbl_user.Nickname LIKE '%" . $_POST["user"] . "%' OR
						tbl_user.Emailadresse LIKE '%" . $_POST["user"] . "%'
					)
				";
			}
			if(strlen($_POST["von"])>0) {
				$arr[] = "tbl_termine.Beginn>='" . $_POST["von"] . "'";
			}
			if(strlen($_POST["bis"])>0) {
				$arr[] = "tbl_termine.Ende<='" . $_POST["bis"] . "'";
			}
			
			if(count($arr)>0) {
				$where = "
					WHERE(
						" . implode(" AND ",$arr) . "
					)
				";
			}
		}
		
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
			" . $where . "
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
				</li>
			');
		}
		?>
	</ul>
</body>
</html>