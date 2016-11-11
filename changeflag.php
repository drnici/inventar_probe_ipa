<?PHP
############################################################################################
$sys["root_path"] 	= "../../../";
$sys["script"] 		= true;
$sys["nav_id"]		= 26;
############################################################################################
include ( $sys["root_path"] . "_global/header.php" );
############################################################################################

// Parameter validieren
$res_id 			= htmlspecialchars ( mysql_escape_string ( $_GET["res_id"] ) );
$value				= strtolower ( htmlspecialchars ( mysql_escape_string ( $_GET["value"] ) ) );
$person_s_semester	= htmlspecialchars ( mysql_escape_string ( $_GET["person_s_semester"] ) );
$periode_id			= htmlspecialchars ( mysql_escape_string ( $_GET["periode_id"] ) );
	
// Beide Werte müssen vorhanden sein
if ( !empty ( $res_id ) AND is_numeric ( $res_id ) AND !empty ( $value ) )
{
	$res_data		= $db->fctSelectData ( "bew_quali_res" , "`res_id` = " . $res_id );
	if ( !empty ( $res_data["res_id"] ) )
	{
		$allowed_fields = array ( "res_release" , "res_discuss" , "res_visum_bb" , "res_visum_lde" , "res_visum_gv" );
		
		if ( in_array ( $value , $allowed_fields ) )
		{
			// bestehenden Status abrufen und so Zielwert bestimmen
			if ( $res_data[$value] == 0 ) 	$target_value = 1;
			else							$target_value = 0;
			
			// bei Freigabe alle anderen Flags auf 0 setzen
			if ( $value == "res_release" )
			{
				if ( $sys["user"]["role_id"] < 5 )
				{
					// nur für ADMIN
					header ( "Location: ./?person_s_semester=" . $person_s_semester . "&periode_id=" . $periode_id . "&" );
					die ( );
				}
				
				$db->fctSendQuery ( "UPDATE `bew_quali_res` SET `" . $value . "` = " . $target_value . ", `res_discuss` = 0, `res_visum_bb` = 0, `res_visum_lde` = 0, `res_visum_gv` = 0 WHERE `res_id` = '" . $res_id . "'" );
			}
			
			// bei Besprechung alle Visum-Flags auf 0 setzen
			if ( $value == "res_discuss" )
			{
				if ( $sys["user"]["role_id"] < 5 )
				{
					// nur für ADMIN
					header ( "Location: ./?person_s_semester=" . $person_s_semester . "&periode_id=" . $periode_id . "&" );
					die ( );
				}
				
				$db->fctSendQuery ( "UPDATE `bew_quali_res` SET `" . $value . "` = " . $target_value . ", `res_visum_bb` = 0, `res_visum_lde` = 0, `res_visum_gv` = 0 WHERE `res_id` = '" . $res_id . "'" );
			}

			// bei Visa keine anderen Flags beeinflussen
			if ( $value == "res_visum_bb" || $value == "res_visum_lde" || $value == "res_visum_gv" )
			{
				if ( $value == "res_visum_lde" AND $sys["user"]["role_id"] == 1 OR $value != "res_visum_lde" AND $sys["user"]["role_id"] == 5 )
				{
					$db->fctSendQuery ( "UPDATE `bew_quali_res` SET `" . $value . "` = " . $target_value . " WHERE `res_id` = '" . $res_id . "'" );
				}
				else
				{
					// ADMIN (für BB und GV) oder Lernende (bei Visum Lde)
					header ( "Location: ./?person_s_semester=" . $person_s_semester . "&periode_id=" . $periode_id . "&" );
					die ( );
				}

			}

			// Text für History-Eintrag unterscheiden
			if ( $target_value == 1 )
			{
				if ( $value == "res_release" ) 			$history_text = "Qualifikation freigegeben";
				if ( $value == "res_discuss" ) 			$history_text = "Qualifikation besprochen";
				if ( $value == "res_visum_bb" ) 		$history_text = "Visum Berufsbildner/in erteilt";
				if ( $value == "res_visum_lde" ) 		$history_text = "Visum Lernende/r erteilt";
				if ( $value == "res_visum_gv" ) 		$history_text = "Visum gesetzliche Vertretung erteilt";
			}
			else
			{
				if ( $value == "res_release" ) 			$history_text = "Qualifikation zurückgezogen";
				if ( $value == "res_discuss" ) 			$history_text = "Qualifikation nicht besprochen";
				if ( $value == "res_visum_bb" ) 		$history_text = "Visum Berufsbildner/in zurückgezogen";
				if ( $value == "res_visum_lde" ) 		$history_text = "Visum Lernende/r zurückgezogen";
				if ( $value == "res_visum_gv" ) 		$history_text = "Visum gesetzliche Vertretung zurückgezogen";
			}
			
			// Eintrag in die History erstellen
			$db->fctSendQuery ( "INSERT INTO `bew_quali_history` (`res_id`,`person_id`,`history_time`,`history_text`) VALUES (" . $res_id . "," . $sys["user"]["person_id"] . "," . time ( ) . ",'" . $history_text . "')" );
			
			header ( "Location: ./?alert=changeflag_ok&person_s_semester=" . $person_s_semester . "&periode_id=" . $periode_id . "&" );
		}
		else
		{
			// unerlaubtes Feld
			header ( "Location: ./?person_s_semester=" . $person_s_semester . "&periode_id=" . $periode_id . "&" );
		}
	}
	else
	{
		// ungültige Quali-ID
		header ( "Location: ./?person_s_semester=" . $person_s_semester . "&periode_id=" . $periode_id . "&" );
	}
}
else
{
	// nicht alle nötigen Werte
	header ( "Location: ./?person_s_semester=" . $person_s_semester . "&periode_id=" . $periode_id . "&" );
}

############################################################################################
include ( $sys["root_path"] . "_global/footer.php" );
############################################################################################
?>