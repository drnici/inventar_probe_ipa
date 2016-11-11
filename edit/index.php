<?PHP
############################################################################################
$sys["root_path"] 	= "../../../../";
$sys["script"] 		= false;
$sys["nav_id"]		= 76;
############################################################################################
include ( $sys["root_path"] . "_global/header.php" );
############################################################################################
$sys["page_title"] 	= "Qualifikation bearbeiten";
############################################################################################

$res_id				= htmlspecialchars ( mysql_escape_string ( $_GET["res_id"] ) );
$person_s_semester	= htmlspecialchars ( mysql_escape_string ( $_GET["person_s_semester"] ) );
$periode_id			= htmlspecialchars ( mysql_escape_string ( $_GET["periode_id"] ) );
$res_data 			= $db->fctSelectData ( "bew_quali_res" , "`res_id` = '" . $res_id . "'" );

if ( isset ( $res_id ) AND !empty ( $res_data["res_id"] ) AND !$res_data["res_release"] )
{
	$res_target = $res_data["res_target"];

	// Personendaten
	$person_data 	= $db->fctSelectData ( "core_person" , "`person_id` = " . $res_data["person_id"] );
	$firma_data  	= $db->fctSelectData ( "core_firma" , "`firma_id` = " . $person_data["firma_id"] );
	
	// Zeitraum
	$periode_data 	= $db->fctSelectData ( "bew_quali_periode" , "`periode_id` = " . $res_data["periode_id"] );
	
	// Pr�fen, ob alle Punkte ausgew�hlt wurden -> d.h. ob die Quali vollst�ndig ist
	$antwort_done 	= $db->fctCountData ( "bew_quali_resantwort" , "`res_id` = " . $res_data["res_id"] );
	$antwort_undone = $db->fctCountData ( "bew_quali_frage" , "`periode_id` = " . $res_data["periode_id"] );
	
	// Ist die Quali vollst�ndig?
	$quali_complete = true;
	if ( $antwort_done < $antwort_undone )
	{
		$quali_complete = false;
		echo ( "<p class=\"warning\"><b>Achtung:</b> Die Qualifikation wurde noch nicht vollst�ndig ausgef�llt (" . $antwort_done . " von " . $antwort_undone . " Fragen bewertet).</p>\n" );
	}
	?>

	<form action="save.php" method="post" name="quali_edit">
    <input type="hidden" name="res_id" value="<?PHP echo ( $res_data["res_id"] ); ?>" />
    <input type="hidden" name="person_s_semester" value="<?PHP echo ( $person_s_semester ); ?>" />
    <input type="hidden" name="periode_id" value="<?PHP echo ( $periode_id ); ?>" />
	<table>
	<tr>
	<th>Person</th>
	<td><?PHP echo ( $person_data["person_vorname"] . " " . $person_data["person_name"] . ", " . $firma_data["firma_name"] ); ?></td>
	</tr>
	<tr>
	<th>Zeitraum</th>
	<td><?PHP echo ( $periode_data["periode_title"] . " (" . $periode_data["periode_start"] . " - " . $periode_data["periode_end"] . ")" ); ?></td>
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
			
			$antwort_result = $db->fctSendQuery ( "SELECT * FROM `bew_quali_antwort` ORDER BY `antwort_id` ASC" );
			while ( $antwort_data = mysql_fetch_array ( $antwort_result ) )
			{
				echo ( "<th><a title=\"" . $antwort_data["antwort_desc"] . "\">" . $antwort_data["antwort_text"] . "</a></th>\n" );
			}
			
			echo ( "<th>Bemerkung</th>\n" );
			echo ( "</tr>\n" );
			
			$i = 1;
			
			$frage_result = $db->fctSendQuery ( "SELECT * FROM `bew_quali_frage` WHERE `kat_id` = " . $kat_data["kat_id"] . " AND `periode_id` = " . $periode_data["periode_id"] );
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
					$resantwort_count = $db->fctCountData ( "bew_quali_resantwort" , "`res_id` = " . $res_data["res_id"] . " AND `frage_id` = " . $frage_data["frage_id"] . " AND `antwort_id` = " . $antwort_data["antwort_id"] );
					
					if ( $resantwort_count > 0 ) $c = " checked=\"checked\"";
					else						 $c = "";
					
					echo ( "<td><input type=\"radio\" name=\"antwort_" . $frage_data["frage_id"] . "\" value=\"" . $antwort_data["antwort_id"] . "\" class=\"width_auto\"" . $c . " onclick=\"document.getElementsByName('comment_" . $frage_data["frage_id"] . "')[0].disabled='';\" /></td>\n" );
				}
				
				$resantwort_data = $db->fctSelectData ( "bew_quali_resantwort" , "`res_id` = " . $res_data["res_id"] . " AND `frage_id` = " . $frage_data["frage_id"] );
				
				$d = "";
				if ( empty ( $resantwort_data["resantwort_id"] ) ) $d = " disabled=\"disabled\"";
				
				echo ( "<td><textarea name=\"comment_" . $frage_data["frage_id"] . "\" rows=\"2\" cols=\"2\" style=\"height:120px;\"" . $d . ">" . $resantwort_data["resantwort_comment"] . "</textarea></td>\n" );
				echo ( "</tr>\n" );
			
				$i++;
			}
			echo ( "</table>\n" );
			
			$j++;
		}
	}
	?>
    
	<?PHP
    if ( $res_data["periode_id"] == 1 )
    {
    	?>
        <h3><?PHP echo ( $j ); ?>. Empfehlungen</h3>
        
        <p><input type="radio" name="res_probezeit" value="1" class="width_auto" <?PHP if ( $res_data["res_probezeit"] == 1 ) echo ( " checked=\"checked\"" ); ?> /> Der/Die Lernende erf�llt die Voraussetzungen in der Praxis f�r eine definitive �bernahme in die Lehrzeit.</p>
        
        <p><input type="radio" name="res_probezeit" value="2" class="width_auto" <?PHP if ( $res_data["res_probezeit"] == 2 ) echo ( " checked=\"checked\"" ); ?> /> Der/Die Lernende erf�llt die Voraussetzungen in der Praxis f�r eine definitive �bernahme in die Lehrzeit nur teilweise. Wir schlagen vor, die Probezeit um 3 Monate zu verl�ngern.</p>
                
        <p><input type="radio" name="res_probezeit" value="3" class="width_auto" <?PHP if ( $res_data["res_probezeit"] == 3 ) echo ( " checked=\"checked\"" ); ?> /> Der/Die Lernende erf�llt die Voraussetzungen in der Praxis f�r eine definitive �bernahme in die Lehrzeit nicht. Wir beantragen, das Lehrverh�ltnis aufzul�sen.</p>
    	<?PHP
		$j++;
    }
    ?>	


	<h3><?PHP echo ( $j ); ?>. Ziele</h3>
	
	<p>
	<textarea name="res_target" rows="2" cols="2" style="width:700px;height: 160px;"><?PHP echo ( $res_target ); ?></textarea>
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
	?>
	
	<?PHP $j++; ?>
	
	<h3><?PHP echo ( $j ); ?>. Gespr�ch</h3>
	
	<?PHP
	if ( $quali_complete )
	{
		echo ( "<p class=\"quali_border\">\n" );
		
		if ( $res_data["res_discuss"] > 0 )			echo ( "<img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\" /> Qualifikation wurde mit der / dem Lernenden besprochen." );
        else										echo ( "<img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\" /> Qualifikation wurde noch nicht mit der / dem Lernenden besprochen." );
		
		echo ( "</p>\n" );
	}
	?>
	
	<?PHP $j++; ?>
	
	<h3><?PHP echo ( $j ); ?>. Unterschriften</h3>
	
	<?PHP
	if ( $quali_complete )
	{
		echo ( "<p class=\"quali_border\">\n" );
		
		if ( $res_data["res_visum_bb"] )			echo ( "<img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\" /> Visum Berufsbildner/in<br />" );
        else										echo ( "<img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\" /> Visum Berufsbildner/in<br />" );
		if ( $res_data["res_visum_lde"] )			echo ( "<img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\" /> Visum Lernende/r<br />" );
        else										echo ( "<img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\" /> Visum Lernende/r<br />" );
		if ( $res_data["res_visum_gv"] )			echo ( "<img src=\"" . $sys["icon_path"] . "global_ok.gif\" alt=\"Icon\" border=\"0\" /> Visum Eltern<br />" );
        else										echo ( "<img src=\"" . $sys["icon_path"] . "global_nok.gif\" alt=\"Icon\" border=\"0\" /> Visum Eltern<br />" );
		
		echo ( "</p>\n" );
	}
	?>

	<?PHP $j++; ?>
	
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
	<input type="submit" name="btn" value="�nderungen speichern" class="btn" />
	<input type="button" name="btn" value="Abbrechen" class="btn" onclick="self.location.href='../?person_s_semester=<?PHP echo ( $person_s_semester ); ?>&amp;periode_id=<?PHP echo ( $periode_id ); ?>&amp;';" />
	</p>

	</form>

<?PHP
}
else
{
	header ( "Location: ../" );
}

############################################################################################
include ( $sys["root_path"] . "_global/footer.php" );
############################################################################################
?>