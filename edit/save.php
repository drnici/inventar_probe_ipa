<?PHP
############################################################################################
$sys["root_path"] 	= "../../../../";
$sys["script"] 		= true;
$sys["nav_id"]		= 76;
############################################################################################
include ( $sys["root_path"] . "_global/header.php" );
############################################################################################

// Eingaben validieren
$res_id 			= htmlspecialchars ( mysql_escape_string ( $_POST["res_id"] ) );
$person_s_semester	= htmlspecialchars ( mysql_escape_string ( $_POST["person_s_semester"] ) );
$periode_id			= htmlspecialchars ( mysql_escape_string ( $_POST["periode_id"] ) );
$res_target			= htmlspecialchars ( mysql_escape_string ( $_POST["res_target"] ) );

if ( isset ( $_POST["res_probezeit"] ) )
{
	$res_probezeit  = htmlspecialchars ( mysql_escape_string ( $_POST["res_probezeit"] ) );
}
else $res_probezeit = 0;

$res_data 			= $db->fctSelectData ( "bew_quali_res" , "`res_id` = '" . $res_id . "'" );
	
// Quali muss existieren und darf noch nicht freigegeben sein
if ( isset ( $res_id ) AND !empty ( $res_data["res_id"] ) AND !$res_data["res_release"] )
{
	$db->fctSendQuery ( "UPDATE `bew_quali_res` SET `res_probezeit` = '" . $res_probezeit . "', `res_target` = '" . $res_target . "' WHERE `res_id` = " . $res_data["res_id"] );

	// alle bisher gespeicherten Antworten zu dieser Quali l�schen
	$db->fctSendQuery ( "DELETE FROM `bew_quali_resantwort` WHERE `res_id` = " . $res_data["res_id"] );
	
	// alle Fragen durchgehen und pr�fen, ob diese bereits ausgew�hlt wurde
	$frage_result = $db->fctSendQuery ( "SELECT `frage_id` FROM `bew_quali_frage`" );
	while ( $frage_data = mysql_fetch_array ( $frage_result ) )
	{
		$antwort_id = htmlspecialchars ( mysql_escape_string ( $_POST["antwort_" . $frage_data["frage_id"] ] ) );

		if ( !empty ( $antwort_id ) AND is_numeric ( $antwort_id ) )
		{
			$resantwort_comment = htmlspecialchars ( mysql_escape_string ( $_POST["comment_" . $frage_data["frage_id"] ] ) );
			$db->fctSendQuery ( "INSERT INTO `bew_quali_resantwort` (`res_id`,`frage_id`,`antwort_id`,`resantwort_comment`) VALUES (" . $res_data["res_id"] . "," . $frage_data["frage_id"] . "," . $antwort_id . ",'" . $resantwort_comment . "')" );
		}
	}
		
	// Eintrag in die History erstellen
	$db->fctSendQuery ( "INSERT INTO `bew_quali_history` (`res_id`,`person_id`,`history_time`,`history_text`) VALUES (" . $res_data["res_id"] . "," . $sys["user"]["person_id"] . "," . time ( ) . ",'Qualifikation bearbeitet')" );

	// Speicherung OK, weiterleiten zur �bersicht
	header ( "Location: ../?alert=edit_ok&res_id=" . $res_data["res_id"] . "&person_s_semester=" . $person_s_semester . "&periode_id=" . $periode_id . "&" );
}
else
{
	header ( "Location: ../?person_s_semester=" . $person_s_semester . "&periode_id=" . $periode_id . "&" );
	die ( );
}

############################################################################################
include ( $sys["root_path"] . "_global/footer.php" );
############################################################################################
?>