<?PHP
############################################################################################
$sys["root_path"] 	= "../../../../";
$sys["script"] 		= false;
$sys["nav_id"]		= 76;
############################################################################################
include ( $sys["root_path"] . "_global/header.php" );
############################################################################################
$sys["page_title"] 	= "Inventar l&oumlschen";
############################################################################################

if (isset ($_GET["alert"])) {
	if ($_GET["alert"] == "add_ok") {
		echo("<p class=\"notification\"><b>Objekt erfolgreich hinzugef&uumlgt.</b></p>\n");
	}
	if ($_GET["alert"] == "changeflag_ok") {
		echo("<p class=\"notification\"><b>&Aumlnderung erfolgreich vorgenommen.</b></p>\n");
	}
	if ($_GET["alert"] == "del_ok") {
		echo("<p class=\"notification\"><b>Qualifikation erfolgreich gel&oumlscht.</b></p>\n");
	}
}

if ($sys["user"]["role_id"] == 5) {
	if(isset($_GET["inventar"])){
		$db->fctSendQuery("DELETE FROM `bew_inventar_res` WHERE `inventar_id` = ".$_GET["inventar"]);
		header ( "Location: ./?alert=changeflag_ok&");
	}


	// Nur Administratoren dürfen Inventar erstellen

		echo("<p><a href=\"./add/\"><img src=\"" . $sys["icon_path"] . "global_add.gif\" alt=\"Inventar erstellen\" border=\"0\" />Inventar erstellen</a></p>\n");
		$where_clause = "cp.person_deactivation = 0 AND cp.role_id = 1 AND cp.beruf_id = 1 OR cp.person_id = 365";

		// Administratoren sehen den ganzen Inventar, unabhängig davon ob diese bereits freigegeben wurde oder nicht
		$inventar_result = $db->fctSendQuery("SELECT bir.* , bio.* , cp.* FROM  `bew_inventar_res` AS bir INNER JOIN  `bew_inventar_obj` AS bio ON bir.obj_id = bio.obj_id INNER JOIN `core_person` AS cp ON bir.person_id = cp.person_id WHERE " . $where_clause . " ORDER BY cp.person_vorname, cp.person_name, bir.inventar_nr ASC");


	if (mysql_num_rows($inventar_result) == 0) {
		echo("<p>Keine Inventar entspricht Ihren Filterkriterien.</p>\n");
	} else {
		?>

		<table>
			<tr>
				<th>Lernende/r</th>
				<th>Inventar</th>
				<th>Nr.</th>
				<th>Sch&aumlden</th>
				<th>L&oumlschen</th>
			</tr>

			<?PHP
			$row_highlight = true;

			while ($inventar_data= mysql_fetch_array($inventar_result)) {
				// Pr�fen ob der Benutzer �berhaupt zugreifen darf
				$access = fctCheckAccess($sys["user"], $inventar_data["person_id"], $db);
				if ($access) {
					if ($row_highlight) {
						echo("<tr class=\"row_highlight\">\n");
						$row_highlight = false;
					} else {
						echo("<tr>\n");
						$row_highlight = true;
					}if($inventar_data["person_id"] == 365){
						echo('<td><select name="person_id" size="1"><option value="">..</option>');
						$person_result = $db->fctSendQuery ( "SELECT cp.person_id, cp.person_vorname, cp.person_name FROM `core_person` AS cp WHERE cp.role_id = 1 AND ( cp.person_s_semester = 1 OR cp.person_s_semester = 2 ) AND `beruf_id` = 1 AND cp.person_deactivation = 0 ORDER BY cp.person_vorname ASC, cp.person_name ASC" );

						while ( $person_data = mysql_fetch_array ( $person_result ) )
						{
							echo ( "<option value=\"" . $person_data["person_id"] . "\"" . $s . ">" . $person_data["person_vorname"] . " " . $person_data["person_name"] . "</option>\n" );
						}
						echo('<select></td>');

					}else {
						echo("<td><a href=\"" . $sys["root_path"] . "/core/person/profile/?person_id=" . $inventar_data["person_id"] . "&\">" . $inventar_data["person_vorname"] . " " . $inventar_data["person_name"] . "</a></td>\n");
					}
					echo("<td>". $inventar_data["obj_title"] . "</td>\n");
					echo("<td>". $inventar_data["inventar_nr"] . "</td>\n");
					if(strlen($inventar_data["inventar_damage"])<40){
						echo("<td><textarea rows=\"3\" cols=\"10\" id=\"damage".$inventar_data["inventar_id"]."\" style=\"height: 20px;\">". $inventar_data["inventar_damage"] . "</textarea></td>\n");
					}else{
						echo("<td><textarea rows=\"3\" cols=\"10\" id=\"damage".$inventar_data["inventar_id"]."\" style=\"height: ".strlen($inventar_data["inventar_damage"])/1.9 ."px;\">". $inventar_data["inventar_damage"] . "</textarea></td>\n");
					}

					echo("<td><button value=\"".$inventar_data["inventar_id"]."\" onclick=\"fctDel(this.value,document.getElementById('damage".$inventar_data["inventar_id"]."').value);\"/>L&oumlschen</button></td>");
					echo("</tr>\n");
				}
			}
			?>

		</table>

		<?PHP
	}
}
############################################################################################
include ( $sys["root_path"] . "_global/footer.php" );
############################################################################################
?>