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
		$db->fctSendQuery("UPDATE `bew_inventar_res` SET `inventar_damage` = '".$_GET["demage"]."', `inventar_visum_bb` = 0,, `inventar_visum_lde` = 0, `inventar_release` = 0  WHERE `inventar_id` = '".$_GET["inventar"]."'");
		header ( "Location: ./?alert=changeflag_ok&");
	}
	if ($sys["user"]["role_id"] == 1) {
		// ANZEIGE FÜR LERNENDE
		$sys["page_title"] = "Mein Inventar";

		$res_result = $db->fctSendQuery("SELECT bqr.res_id, bqr.res_visum_lde, cp.person_vorname, cp.person_name, cf.firma_name, bqp.periode_title, bqp.periode_start, bqp.periode_end FROM `bew_quali_res` AS bqr, `bew_quali_periode` AS bqp , `core_person` AS cp, `core_firma` AS cf WHERE bqr.person_id = " . $sys["user"]["person_id"] . " AND bqr.res_release = 1 AND bqr.res_discuss = 1 AND bqr.person_id = cp.person_id AND cp.firma_id = cf.firma_id AND bqr.periode_id = bqp.periode_id ORDER BY bqr.periode_id DESC");

		if (mysql_num_rows($res_result) == 0) {
			echo("<p>Zur Zeit ist noch kein Inventar im System hinterlegt.</p>\n");
		} else {
			while ($res_data = mysql_fetch_array($res_result)) {
				?>
				<h3><?PHP echo($res_data["periode_title"] . " (" . $res_data["periode_start"] . " - " . $res_data["periode_end"] . ")"); ?></h3>

				<table>
					<tr>
						<th>Zusammenfassung</th>
						<td>
							<?PHP
							$antwort_result = $db->fctSendQuery("SELECT * FROM `bew_quali_antwort` ORDER BY `antwort_id` ASC");
							while ($antwort_data = mysql_fetch_array($antwort_result)) {
								$zf_count = $db->fctCountData("bew_quali_resantwort", "`res_id` = " . $res_data["res_id"] . " AND `antwort_id` = " . $antwort_data["antwort_id"]);

								echo("<a title=\"" . $antwort_data["antwort_desc"] . "\">" . $antwort_data["antwort_text"] . "</a>: " . $zf_count . "<br />\n");
							}
							?>
						</td>
					</tr>
					<tr>
						<th>Gewichtung</th>
						<td>
							<?PHP
							$total_antwort = mysql_fetch_array($db->fctSendQuery("SELECT SUM(`antwort_id`) FROM `bew_quali_resantwort` WHERE `res_id` = " . $res_data["res_id"]));
							$frage_count = $db->fctCountData("bew_quali_resantwort", "`res_id` = " . $res_data["res_id"]);
							if ($frage_count == 0) {
								echo("-");
							} else echo(bcdiv($total_antwort[0], $frage_count, 1));
							?>
						</td>
					</tr>
				</table>

				<p>
					<a href="./?res_id=<?PHP echo($res_data["res_id"]); ?>&amp;"><img
							src="<?PHP echo($sys["icon_path"]); ?>bew_quali.gif" alt="Qualifikation &ouml;ffnen"
							border="0"/> Qualifikation &ouml;ffnen</a><br/>
					<?PHP
					if ($res_data["res_visum_lde"] == 0) {
						echo("<a href=\"./changeflag.php?res_id=" . $res_data["res_id"] . "&amp;value=res_visum_lde&amp;\"><img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Qualifikation visieren\" border=\"0\" /> Qualifikation visieren</a><br />\n");
					}
					?>
				</p>

				<?PHP
			}
		}
	} else {
		// Nur Administratoren dürfen Objekte erstellen
		if ($sys["user"]["role_id"] == 5) {
			echo("<p><a href=\"./add_o/\"><img src=\"" . $sys["icon_path"] . "global_add.gif\" alt=\"Objekt erstellen\" border=\"0\" /> Objekt erstellen</a></p>\n");
		}

		// Filterfelder vorbereiten
		if (isset ($_GET["person_s_semester"]) AND (is_numeric($_GET["person_s_semester"]) OR $_GET["person_s_semester"] == 0)) $person_s_semester = htmlspecialchars(mysql_escape_string($_GET["person_s_semester"]));
		if (isset ($_GET["inventar"]) AND (is_numeric($_GET["inventar"]) OR is_string($_GET["inventar"]))) $inventar = htmlspecialchars(mysql_escape_string($_GET["inventar"]));
        if (isset ($_GET["inventar_nr"]) AND (is_numeric($_GET["inventar_nr"]) OR $_GET["inventar_nr"] == "")) $inventar_nr = htmlspecialchars(mysql_escape_string($_GET["inventar_nr"]));

		
		$where_clause = "cp.person_deactivation = 0 AND cp.role_id = 1 AND cp.beruf_id = 1";
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
			$where_clause .= " AND bir.inventar_nr = ".$inventar;
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
			$inventar_result = $db->fctSendQuery("SELECT bir.* , bio.* , cp.* FROM  `bew_inventar_res` AS bir INNER JOIN  `bew_inventar_obj` AS bio ON bir.obj_id = bio.obj_id INNER JOIN  `core_person` AS cp ON bir.person_id = cp.person_id WHERE " . $where_clause . " ORDER BY cp.person_vorname, cp.person_name, bir.inventar_nr ASC");
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
					<th>Freigegeben</th>
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
						}
						echo("<td><a href=\"" . $sys["root_path"] . "/core/person/profile/?person_id=" . $inventar_data["person_id"] . "&\">" . $inventar_data["person_vorname"] . " " . $inventar_data["person_name"] . "</a></td>\n");
						echo("<td>". $inventar_data["obj_title"] . "</td>\n");
						echo("<td>". $inventar_data["inventar_nr"] . "</td>\n");
						echo("<td><textarea rows=\"2\" cols=\"10\" id=\"damage\">". $inventar_data["inventar_damage"] . "</textarea></td>\n");
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
						echo("<td><button value=\"".$inventar_data["inventar_id"]."\" onclick=\"fctSave(this.value,document.getElementById('damage').value);\"/>Speichern</button></td>");
						echo("</tr>\n");
					}
				}
				?>

			</table>

			<?PHP
		}
	}


	/*
    if ( isset ( $res_id ) AND !empty ( $res_data["res_id"] ) AND $access )
    {
        if ( isset ( $_GET["person_s_semester"] ) ) $person_s_semester 	= htmlspecialchars ( mysql_escape_string ( $_GET["person_s_semester"] ) );
        else										$person_s_semester 	= 0;
        if ( isset ( $_GET["periode_id"] ) ) 		$periode_id			= htmlspecialchars ( mysql_escape_string ( $_GET["periode_id"] ) );
        else										$periode_id			= 0;
        if ( $res_data["res_release"] == 0 && $sys["user"]["role_id"] < 5 )
        {
            echo ( "<p class=\"warning\">Die Qualifikation wurde noch nicht freigegeben.</p>\n" );
            echo ( "<p><a href=\"./?person_s_semester=" . $person_s_semester . "&amp;periode_id=" . $periode_id . "&amp;\"><img src=\"" . $sys["icon_path"] . "bew_quali.gif\" alt=\"zur�ck zur �bersicht\" border=\"0\" />  zur�ck zur �bersicht</a></p>\n" );
        }
        elseif ( $res_data["res_discuss"] == 0 && $sys["user"]["role_id"] < 5 )
        {
            echo ( "<p class=\"warning\">Die Qualifikation wurde noch nicht mit der / dem Lernenden besprochen.</p>\n" );
            echo ( "<p><a href=\"./?person_s_semester=" . $person_s_semester . "&amp;periode_id=" . $periode_id . "&amp;\"><img src=\"" . $sys["icon_path"] . "bew_quali.gif\" alt=\"zur�ck zur �bersicht\" border=\"0\" />  zur�ck zur �bersicht</a></p>\n" );
        }
        else
        {
            $sys["page_title"] 	= "Qualifikation anzeigen";

            if ( isset ( $_GET["alert"] ) )
            {
                if ( $_GET["alert"] == "edit_ok" )
                {
                    echo ( "<p class=\"notification\"><b>Änderungen an der Qualifikation erfolgreich gespeichert.</b></p>\n" );
                }
            }

            // Personendaten
            $person_data = $db->fctSelectData ( "core_person" , "`person_id` = " . $res_data["person_id"] );
            $firma_data  = $db->fctSelectData ( "core_firma" , "`firma_id` = " . $person_data["firma_id"] );

            // Zeitraum
            $periode_data = $db->fctSelectData ( "bew_quali_periode" , "`periode_id` = " . $res_data["periode_id"] );


        ?>

            <p>
            <a href="./?person_s_semester=<?PHP echo ( $person_s_semester ); ?>&amp;periode_id=<?PHP echo ( $periode_id ); ?>&amp;"><img src="<?PHP echo ( $sys["icon_path"] ); ?>bew_quali.gif" alt="zur�ck zur �bersicht" border="0" /> zur�ck zur �bersicht</a><br />
            <?PHP
            if ( !$res_data["res_release"] )
            {
            ?>
                <a href="./edit/?res_id=<?PHP echo ( $res_data["res_id"] ); ?>&amp;person_s_semester=<?PHP echo ( $person_s_semester ); ?>&amp;periode_id=<?PHP echo ( $periode_id ); ?>&amp;"><img src="<?PHP echo ( $sys["icon_path"] ); ?>global_edit.gif" alt="Qualifikation bearbeiten" border="0" /> Qualifikation bearbeiten</a><br />
                <a href="./del/?res_id=<?PHP echo ( $res_data["res_id"] ); ?>&amp;person_s_semester=<?PHP echo ( $person_s_semester ); ?>&amp;periode_id=<?PHP echo ( $periode_id ); ?>&amp;"><img src="<?PHP echo ( $sys["icon_path"] ); ?>global_del.gif" alt="Qualifikation l�schen" border="0" /> Qualifikation l�schen</a><br />
            <?PHP
            }
            ?>
            </p>

            <table>
            <tr>
            <th>Person</th>
            <td><p><?PHP echo ( $person_data["person_vorname"] . " " . $person_data["person_name"] . ", " . $firma_data["firma_name"] ); ?></p></td>
            </tr>
            <tr>
            <th>Zeitraum</th>
            <td><p><?PHP echo ( $periode_data["periode_title"] . " (" . $periode_data["periode_start"] . " - " . $periode_data["periode_end"] . ")" ); ?></p></td>
            </tr>
            <tr>
            <th>Zusammenfassung</th>
            <td>
            <?PHP
            echo ( "<ol style=\"list-style:upper-latin\">\n" );
            $antwort_result = $db->fctSendQuery ( "SELECT `antwort_id`, `antwort_desc` FROM `bew_quali_antwort` ORDER BY `antwort_id` ASC" );
            while ( $antwort_data = mysql_fetch_array ( $antwort_result ) )
            {
                $zf_count = $db->fctCountData ( "bew_quali_resantwort" , "`res_id` = " . $res_data["res_id"] . " AND `antwort_id` = " . $antwort_data["antwort_id"] );

                echo ( "<li>" . $zf_count . " (= " . $antwort_data["antwort_desc"] . ")</li>\n" );
            }
            echo ( "</ol>\n" );
            ?>
            </td>
            </tr>
            <tr>
            <th>Gewichtung</th>
            <td>
            <?PHP
            $total_antwort = mysql_fetch_array ( $db->fctSendQuery ( "SELECT SUM(`antwort_id`) FROM `bew_quali_resantwort` WHERE `res_id` = " . $res_data["res_id"] ) );
            $frage_count = $db->fctCountData ( "bew_quali_resantwort" , "`res_id` = " . $res_data["res_id"] );
            if ( $frage_count == 0 )
            {
                echo ( "<p>-</p>" );
            }
            else echo ( "<p>" . bcdiv ( $total_antwort[0] , $frage_count , 2 ) . "</p>" );
            ?>
            </td>
            </tr>
            </table>

            <?PHP
            $j = 1;

            $kat_result = $db->fctSendQuery ( "SELECT * FROM `bew_quali_kat` ORDER BY `kat_id` ASC" );
            while ( $kat_data = mysql_fetch_array ( $kat_result ) )
            {
                $frage_count = $db->fctCountData ( "bew_quali_frage" , "`kat_id` = " . $kat_data["kat_id"] . " AND `periode_id` = " . $periode_data["periode_id"] );
                if ( $frage_count > 0 )
                {
                    echo ( "<h3>" . $j . ". " . $kat_data["kat_title"] . "</h3>\n" );

                    echo ( "<table>\n" );
                    echo ( "<tr>\n" );
                    echo ( "<th>Leistung</th>\n" );

                    $antwort_result = $db->fctSendQuery ( "SELECT `antwort_text` FROM `bew_quali_antwort` ORDER BY `antwort_text` ASC" );
                    while ( $antwort_data = mysql_fetch_array ( $antwort_result ) )
                    {
                        echo ( "<th>" . $antwort_data["antwort_text"] . "</th>\n" );
                    }

                    echo ( "<th>Bemerkung</th>\n" );
                    echo ( "</tr>\n" );

                    $i = 1;

                    $frage_result = $db->fctSendQuery ( "SELECT * FROM `bew_quali_frage` WHERE `kat_id` = " . $kat_data["kat_id"] . " AND `periode_id` = " . $res_data["periode_id"] );
                    while ( $frage_data = mysql_fetch_array ( $frage_result ) )
                    {
                        echo ( "<tr>\n" );
                        echo ( "<td style=\"width: 260px;\">\n" );
                        echo ( "<b>" . $j . "." . $i . " " . $frage_data["frage_title"] . "</b><br /><br />\n" );
                        echo ( $frage_data["frage_desc"] . "<br /><br />\n" );
                        echo ( "</td>\n" );

                        $antwort_result = $db->fctSendQuery ( "SELECT * FROM `bew_quali_antwort` ORDER BY `antwort_id` ASC" );
                        while ( $antwort_data = mysql_fetch_array ( $antwort_result ) )
                        {
                            $resantwort_data = $db->fctSelectData ( "bew_quali_resantwort" , "`frage_id` = " . $frage_data["frage_id"] . " AND `antwort_id` = " . $antwort_data["antwort_id"] . " AND `res_id` = " . $res_data["res_id"] );

                            if ( !empty ( $resantwort_data["resantwort_id"] ) )
                            {
                                echo ( "<td>X</td>\n" );
                                $comment = $resantwort_data["resantwort_comment"];
                            }
                            else echo ( "<td>&nbsp;</td>\n" );
                        }

                        echo ( "<td>\n" );
                        if ( !empty ( $comment ) )
                        {
                            echo ( "<p style=\"border: 1px solid black;padding:5px;margin:0px;\">\n" );
                            echo ( nl2br ( $comment ) );
                            echo ( "</p>\n" );
                        }
                        echo ( "</td>\n" );
                        echo ( "</tr>\n" );

                        $i++;
                        $comment = "";
                    }
                    echo ( "<tr>\n" );
                    echo ( "<th>&nbsp;</th>\n" );

                    $antwort_result = $db->fctSendQuery ( "SELECT `antwort_text` FROM `bew_quali_antwort` ORDER BY `antwort_text` ASC" );
                    while ( $antwort_data = mysql_fetch_array ( $antwort_result ) )
                    {
                        echo ( "<th>" . $antwort_data["antwort_text"] . "</th>\n" );
                    }

                    echo ( "<th>&nbsp;</th>\n" );
                    echo ( "</tr>\n" );
                    echo ( "</table>\n" );

                    $j++;
                }
            }
            ?>

            <?PHP
            if ( $res_data["periode_id"] == 1 AND $res_data["res_probezeit"] > 0 )
            {
                ?>
                <h3><?PHP echo ( $j ); ?>. Empfehlungen</h3>

                <?PHP
                if ( $res_data["res_probezeit"] == 1 )
                {
                    echo ( "<p>Der/Die Lernende erf�llt die Voraussetzungen in der Praxis f�r eine definitive �bernahme in die Lehrzeit.</p>\n" );
                }
                elseif ( $res_data["res_probezeit"] == 2 )
                {
                    echo ( "<p>Der/Die Lernende erf�llt die Voraussetzungen in der Praxis f�r eine definitive �bernahme in die Lehrzeit nur teilweise. Wir schlagen vor, die Probezeit um 3 Monate zu verl�ngern.</p>\n" );
                }
                elseif ( $res_data["res_probezeit"] == 3 )
                {
                    echo ( "<p>Der/Die Lernende erf�llt die Voraussetzungen in der Praxis f�r eine definitive �bernahme in die Lehrzeit nicht. Wir beantragen, das Lehrverh�ltnis aufzul�sen.</p>\n" );
                }

                $j++;
            }
            ?>

            <h3><?PHP echo ( $j ); ?>. Ziele</h3>

            <p style="border: 1px solid black;padding:5px;">
                <?PHP
                if ( !empty ( $res_data["res_target"] ) ) 	echo ( nl2br ( $res_data["res_target"] ) );
                else 										echo ( "-\n" );
                ?>
            </p>

            <?PHP $j++; ?>

            <h3><?PHP echo ( $j ); ?>. Freigabe</h3>

            <?PHP
            if ( $quali_complete )
            {
                echo ( "<p class=\"quali_border\">\n" );

                if ( $res_data["res_release"] )				echo ( "<img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\" /> Qualifikation wurde freigegeben.\n" );
                else										echo ( "<img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\" /> Qualifikation wurde noch nicht freigegeben.\n" );

                echo ( "</p>\n" );
            }

            $j++;

            echo ( "<h3>" . $j . ". Gespr�ch</h3>\n" );

            if ( $quali_complete && $res_data["res_release"] )
            {
                echo ( "<p class=\"quali_border\">\n" );

                if ( $res_data["res_discuss"] )				echo ( "<img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\" /> Qualifikation wurde besprochen.\n" );
                else										echo ( "<img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\" /> Qualifikation wurde noch nicht besprochen.\n" );

                echo ( "</p>\n" );
             }
             ?>

            <?PHP $j++; ?>

            <h3><?PHP echo ( $j ); ?>. Unterschriften</h3>

            <?PHP
            if ( $quali_complete && $res_data["res_release"] && $res_data["res_discuss"] )
            {
                echo ( "<p class=\"quali_border\">\n" );

                if ( $res_data["res_visum_bb"] )			echo ( "<img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\" /> Visum Berufsbildner/in<br />" );
                else										echo ( "<img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\" /> Visum Berufsbildner/in<br />" );
                if ( $res_data["res_visum_lde"] )			echo ( "<img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\" /> Visum Lernende/r<br />" );
                else										echo ( "<img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\" /> Visum Lernende/r<br />" );
                if ( $res_data["res_visum_gv"] )			echo ( "<img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\" /> Visum gesetzliche Vertretung<br />" );
                else										echo ( "<img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\" /> Visum gesetzliche Vertretung<br />" );

                echo ( "</p>\n" );
            }

            $j++;
            ?>

            <h3><?PHP echo ( $j ); ?>. History</h3>

            <ul>
                <?PHP
                $history_result = $db->fctSendQuery ( "SELECT bqh.history_time, bqh.history_text, cp.person_vorname, cp.person_name, cf.firma_name FROM `bew_quali_history` AS bqh, `core_person` AS cp, `core_firma` AS cf WHERE bqh.person_id = cp.person_id AND cp.firma_id = cf.firma_id AND bqh.res_id = " . $res_data["res_id"] . " ORDER BY bqh.history_time DESC" );
                while ( $history_data = mysql_fetch_array ( $history_result ) )
                {
                    echo ( "<li>" . date ( "d.m.Y H:i" , $history_data["history_time"] ) . ": <b>" . $history_data["history_text"] . "</b> (" . $history_data["person_vorname"] . " " . $history_data["person_name"] . ", " . $history_data["firma_name"] . ")</li>\n" );
                }
                ?>
            </ul>

            <p>
            <a href="./?person_s_semester=<?PHP echo ( $person_s_semester ); ?>&amp;periode_id=<?PHP echo ( $periode_id ); ?>&amp;"><img src="<?PHP echo ( $sys["icon_path"] ); ?>bew_quali.gif" alt="zur�ck zur �bersicht" border="0" /> zur�ck zur �bersicht</a><br />
            <?PHP
            if ( !$res_data["res_release"] )
            {
            ?>
                <a href="./edit/?res_id=<?PHP echo ( $res_data["res_id"] ); ?>&amp;person_s_semester=<?PHP echo ( $person_s_semester ); ?>&amp;periode_id=<?PHP echo ( $periode_id ); ?>&amp;"><img src="<?PHP echo ( $sys["icon_path"] ); ?>global_edit.gif" alt="Qualifikation bearbeiten" border="0" /> Qualifikation bearbeiten</a><br />
                <a href="./del/?res_id=<?PHP echo ( $res_data["res_id"] ); ?>&amp;person_s_semester=<?PHP echo ( $person_s_semester ); ?>&amp;periode_id=<?PHP echo ( $periode_id ); ?>&amp;"><img src="<?PHP echo ( $sys["icon_path"] ); ?>global_del.gif" alt="Qualifikation l�schen" border="0" /> Qualifikation l�schen</a><br />
            <?PHP
            }
            ?>
            </p>

            <?PHP
        }
    }*/
function echoAll(){

}
############################################################################################
include ( $sys["root_path"] . "_global/footer.php" );
############################################################################################
?>