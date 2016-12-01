<?PHP
############################################################################################
$sys["root_path"] 	= "../../../";
$sys["script"] 		= false;
$sys["nav_id"]		= 73;
############################################################################################
include ( $sys["root_path"] . "_global/header.php" );
############################################################################################
$sys["page_title"] 	= "Haftungsmodul Inventar";
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

	if(isset($_GET["inventar"]) AND isset($_GET["demage"])){
		$db->fctSendQuery("UPDATE `bew_inventar_res` SET `inventar_damage` = '".$_GET["demage"]."', `inventar_visum_bb` = 0, `inventar_visum_lde` = 0, `inventar_release` = 0  WHERE `inventar_id` = '".$_GET["inventar"]."'");
		header ( "Location: ./?alert=changeflag_ok&");
	}
	if ($sys["user"]["role_id"] == 1) {
		// ANZEIGE FÜR LERNENDE
		$sys["page_title"] = "Mein Inventar";

		$inventar_result = $db->fctSendQuery("SELECT bir.* , bio.* , cp.* FROM  `bew_inventar_res` AS bir INNER JOIN  `bew_inventar_obj` AS bio ON bir.obj_id = bio.obj_id INNER JOIN  `core_person` AS cp ON bir.person_id = cp.person_id WHERE bir.person_id = ".$sys["user"]["person_id"]." ORDER BY cp.person_vorname, cp.person_name, bir.inventar_nr ASC");

		if (mysql_num_rows($inventar_result) == 0) {
			echo("<p>Zur Zeit ist noch kein Inventar im System hinterlegt.</p>\n");
		} else{?>

			<table>
				<tr>
					<th>Inventar</th>
					<th>Nr.</th>
					<th>Sch&aumlden</th>
					<th>Freigeben</th>
					<th>BB</th>
					<th>Lde</th>
					<th>Speichern</th>
				</tr>

				<?PHP
				$row_highlight = true;

				while ($inventar_data= mysql_fetch_array($inventar_result)) {
					// Prüfen ob es freigegeben wurde
					if($inventar_data["inventar_release"]== 0){
						echo("<p>Zur Zeit ist noch kein Inventar freigegeben.</p>\n");
					}else {
						// Pr�fen ob der Benutzer �berhaupt zugreifen darf
						$access = fctCheckAccess($sys["user"], $inventar_data["person_id"], $db);
						if ($access) {
							if ($row_highlight) {
								echo("<tr class=\"row_highlight\">\n");
								$row_highlight = false;
							} else {
								echo("<tr>\n");
								$row_highlight = true;
							}
							echo("<td>" . $inventar_data["obj_title"] . "</td>\n");
							echo("<td>" . $inventar_data["inventar_nr"] . "</td>\n");
							if (strlen($inventar_data["inventar_damage"]) < 40) {
								echo("<td><textarea rows=\"3\" cols=\"10\" id=\"damage" . $inventar_data["inventar_id"] . "\" style=\"height: 20px;\">" . $inventar_data["inventar_damage"] . "</textarea></td>\n");
							} else {
								echo("<td><textarea rows=\"3\" cols=\"10\" id=\"damage" . $inventar_data["inventar_id"] . "\" style=\"height: " . strlen($inventar_data["inventar_damage"]) / 1.9 . "px\">" . $inventar_data["inventar_damage"] . "</textarea></td>\n");
							}
							echo("<td><img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\" /></td>\n");
							if ($inventar_data["inventar_visum_bb"] == 0) {
								echo("<td><img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\" /></td>\n");
							} else {
								echo("<td><img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\" /></td>\n");
							}
							if ($inventar_data["inventar_visum_lde"] == 0) {
								echo("<td><a href=\"changeflag.php?inventar_id=" . $inventar_data["inventar_id"] . "&amp;action=visum_lde&amp;\"><img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\"></a></td>\n");
							} else {
								echo("<td><a href=\"changeflag.php?inventar_id=" . $inventar_data["inventar_id"] . "&amp;action=visum_lde&amp;\"><img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\"></a></td>\n");
							}


							echo("<td><button value=\"" . $inventar_data["inventar_id"] . "\" onclick=\"fctSave(this.value,document.getElementById('damage" . $inventar_data["inventar_id"] . "').value);\"/>Speichern</button></td>");
							echo("</tr>\n");
						}
					}
				}
				?>

			</table>
			<?php
		}
	} else {
		// Nur Administratoren dürfen Inventar erstellen
		if ($sys["user"]["role_id"] == 5) {
			echo("<p><a href=\"./add/\"><img src=\"" . $sys["icon_path"] . "global_add.gif\" alt=\"Inventar erstellen\" border=\"0\" />Inventar erstellen</a></p>\n");
		}

		// Filterfelder vorbereiten
		if (isset ($_GET["person_s_semester"]) AND (is_numeric($_GET["person_s_semester"]) OR $_GET["person_s_semester"] == 0)) $person_s_semester = htmlspecialchars(mysql_escape_string($_GET["person_s_semester"]));
		if (isset ($_GET["inventar"]) AND (is_numeric($_GET["inventar"]) OR is_string($_GET["inventar"]))) $inventar = htmlspecialchars(mysql_escape_string($_GET["inventar"]));
        if (isset ($_GET["inventar_nr"]) AND (is_numeric($_GET["inventar_nr"]) OR $_GET["inventar_nr"] == "")) $inventar_nr = htmlspecialchars(mysql_escape_string($_GET["inventar_nr"]));

		
		$where_clause = "cp.person_deactivation = 0 AND cp.role_id = 1 AND cp.beruf_id = 1 OR cp.person_id = 365";
		$semester_result = $db->fctSendQuery("SELECT cp.person_s_semester FROM `core_person` AS cp WHERE " . $where_clause . " GROUP BY cp.person_s_semester");
        $inventar_name_result = $db->fctSendQuery("SELECT bio.* FROM `bew_inventar_obj` AS bio");
		$inventar_nr_result = $db->fctSendQuery("SELECT bir.inventar_nr FROM `bew_inventar_res` AS bir GROUP BY bir.inventar_nr ORDER BY bir.inventar_nr ASC");


		if (!isset ($person_s_semester)) {
			$person_s_semester = 0;
		}
        if (empty ($inventar)) {
            $inventar ="";
        }else{
			$where_clause .= " AND bio.obj_title = '".$inventar."'";
		}
        if (empty($inventar_nr)) {
            $inventar_nr ="";
        }else{
			$where_clause .= " AND bir.inventar_nr = ".$inventar_nr;
		}

		if ($person_s_semester > 0) {
			$where_clause .= " AND cp.person_s_semester = " . $person_s_semester;
		}


		// nach PERSON_S_SEMESTER filtern
		echo("<p></p>\n");
		echo("<select style=\"overflow: hidden\" name=\"person_s_semester\" size=\"0\" onchange=\"fctFilterInventar(this.value,'" . $inventar . "','" . $inventar_nr . "');\">\n");
		echo("<option value=\"\">Alle Semester</option>\n");
		while ($semester = mysql_fetch_array($semester_result)) {
			if ($person_s_semester == $semester["person_s_semester"]) $s = " selected";
			else                                                        $s = "";

			echo("<option value=\"" . $semester["person_s_semester"] . "\"" . $s . ">&bull; " . $semester["person_s_semester"] . ". Semester</option>\n");
		}
		echo("</select>\n");

        // nach Inventar filtern
		echo("<select style=\"overflow: hidden\" name=\"inventar\" size=\"0\" onchange=\"fctFilterInventar('".$person_s_semester."',this.value,'" . $inventar_nr . "')\">\n");
        echo("<option value=\"\">Ganzes Inventar</option>\n");
        while ($inventar_name = mysql_fetch_array($inventar_name_result)) {
            if ($inventar == $inventar_name["obj_title"]) $s = " selected";
            else                                                        $s = "";

            echo("<option value=\"" . $inventar_name["obj_title"] . "\"" . $s . ">" . $inventar_name["obj_title"] . "</option>\n");
        }
        echo("</select>\n");

		// nach Inventarnr filtern
		echo("<select style=\"overflow: hidden\"name=\"inventar_nr\" size=\"0\" onchange=\"fctFilterInventar('".$person_s_semester."','" . $inventar . "',this.value)\">\n");
		echo("<option value=\"\">Nr.</option>\n");
		while ($inventar_nr_r = mysql_fetch_array($inventar_nr_result)) {
			if ($inventar_nr == $inventar_nr_r["inventar_nr"]) $s = " selected";
			else                                                        $s = "";

			echo("<option value=\"" . $inventar_nr_r["inventar_nr"] . "\"" . $s . ">" . $inventar_nr_r["inventar_nr"] . "</option>\n");
		}
		echo("</select>\n");


		// Filterfelder ende
		if ($sys["user"]["role_id"] == 5) {
			// Administratoren sehen den ganzen Inventar, unabhängig davon ob diese bereits besprochen oder freigegeben wurden

			$inventar_result = $db->fctSendQuery("SELECT bir.* , bio.* , cp.* FROM  `bew_inventar_res` AS bir INNER JOIN  `bew_inventar_obj` AS bio ON bir.obj_id = bio.obj_id INNER JOIN `core_person` AS cp ON bir.person_id = cp.person_id WHERE " . $where_clause . " ORDER BY cp.person_vorname, cp.person_name, bir.inventar_nr ASC");
		} else {
			// andere Benutzer sehen nur freigegebene und besprochene Qualis
			$inventar_result = $db->fctSendQuery("SELECT bir.* , bio.* , cp.* FROM  `bew_inventar_res` AS bir INNER JOIN  `bew_inventar_obj` AS bio ON bir.obj_id = bio.obj_id INNER JOIN  `core_person` AS cp ON bir.person_id = cp.person_id WHERE " . $where_clause . " AND bir.person_id = ".$sys["user"]["person_id"]." ORDER BY cp.person_vorname, cp.person_name, bir.inventar_nr ASC");
		}


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
					<th>Freigeben</th>
					<th>BB</th>
					<th>Lde</th>
					<th>Speichern</th>
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
							echo('<td class="person_inventar"><select name="person_id" size="1"><option value="">..</option>');
							$person_result = $db->fctSendQuery ( "SELECT cp.person_id, cp.person_vorname, cp.person_name FROM `core_person` AS cp WHERE cp.role_id = 1 AND ( cp.person_s_semester = 1 OR cp.person_s_semester = 2 ) AND `beruf_id` = 1 AND cp.person_deactivation = 0 ORDER BY cp.person_vorname ASC, cp.person_name ASC" );

							while ( $person_data = mysql_fetch_array ( $person_result ) )
							{
								echo ( "<option value=\"" . $person_data["person_id"] . "\"" . $s . ">" . $person_data["person_vorname"] . " " . $person_data["person_name"] . "</option>\n" );
							}
							echo('<select></td>');

						}else {
							echo("<td class=\"person_inventar\"><a href=\"" . $sys["root_path"] . "/core/person/profile/?person_id=" . $inventar_data["person_id"] . "&\">" . $inventar_data["person_vorname"] . " " . $inventar_data["person_name"] . "</a></td>\n");
						}
						echo("<td>". $inventar_data["obj_title"] . "</td>\n");
						echo("<td>". $inventar_data["inventar_nr"] . "</td>\n");
						if(strlen($inventar_data["inventar_damage"])<40){
							echo("<td><textarea rows=\"3\" cols=\"10\" id=\"damage".$inventar_data["inventar_id"]."\" style=\"height: 20px;\">". $inventar_data["inventar_damage"] . "</textarea></td>\n");
						}else{
							echo("<td><textarea rows=\"3\" cols=\"10\" id=\"damage".$inventar_data["inventar_id"]."\" style=\"height: ".strlen($inventar_data["inventar_damage"])/1.9 ."px;\">". $inventar_data["inventar_damage"] . "</textarea></td>\n");
						}
						if($sys["user"]["role_id"] == 5){

							if($inventar_data["inventar_release"]== 0) {
								echo("<td><a href=\"changeflag.php?inventar_id=" . $inventar_data["inventar_id"] . "&amp;action=freigabe&amp;\"><img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\"></a></td>\n");
								if($inventar_data["inventar_visum_bb"] == 0){
									echo("<td><img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\"></td>\n");
								}else{
									echo("<td><img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\"></td>\n");
								}
								if($inventar_data["inventar_visum_lde"] == 0){
									echo("<td><img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\"></td>\n");
								}else{
									echo("<td><img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\"></td>\n");
								}
							}else{
								echo("<td><a href=\"changeflag.php?inventar_id=" . $inventar_data["inventar_id"] . "&amp;action=freigabe&amp;\"><img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\"></a></td>\n");
								if($inventar_data["inventar_visum_bb"] == 0){
									echo("<td><a href=\"changeflag.php?inventar_id=" . $inventar_data["inventar_id"] . "&amp;action=visum_bb&amp;\"><img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\"></a></td>\n");
								}else{
									echo("<td><a href=\"changeflag.php?inventar_id=" . $inventar_data["inventar_id"] . "&amp;action=visum_bb&amp;\"><img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\"></a></td>\n");
								}
								if($inventar_data["inventar_visum_lde"] == 0){
									echo("<td><img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\"></td>\n");
								}else{
									echo("<td><img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\"></td>\n");
								}
							}
						}else{
							if($inventar_data["inventar_release"]== 0) {
								echo("<td><img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\" /></td>\n");
							}else{
								echo("<td><a href=\"changeflag.php?inventar_id=" . $inventar_data["inventar_id"] . "&amp;action=freigabe&amp;\"><img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\"></a></td>\n");
								if($inventar_data["inventar_visum_bb"] == 0){
									echo("<td><img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\" /></td>\n");
								}else{
									echo("<td><img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\" /></td>\n");
								}
								if($inventar_data["inventar_visum_lde"] == 0){
									echo("<td><a href=\"changeflag.php?inventar_id=" . $inventar_data["inventar_id"] . "&amp;action=visum_lde&amp;\"><img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\"></a></td>\n");
								}else{
									echo("<td><a href=\"changeflag.php?inventar_id=" . $inventar_data["inventar_id"] . "&amp;action=visum_lde&amp;\"><img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\"></a></td>\n");
								}
							}

						}
						echo("<td><button value=\"".$inventar_data["inventar_id"]."\" onclick=\"fctSave(this.value,document.getElementById('damage".$inventar_data["inventar_id"]."').value);\"/>Speichern</button></td>");
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