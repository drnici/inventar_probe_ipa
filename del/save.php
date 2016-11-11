<?PHP
############################################################################################
$sys["root_path"] 	= "../../../../";
$sys["script"] 		= true;
$sys["nav_id"]		= 77;
############################################################################################
include ( $sys["root_path"] . "_global/header.php" );
############################################################################################

// Eingaben validieren
$res_id 			= htmlspecialchars ( mysql_escape_string ( $_POST["res_id"] ) );
$person_s_semester	= htmlspecialchars ( mysql_escape_string ( $_POST["person_s_semester"] ) );
$periode_id			= htmlspecialchars ( mysql_escape_string ( $_POST["periode_id"] ) );
$res_data 			= $db->fctSelectData ( "bew_quali_res" , "`res_id` = '" . $res_id . "'" );

if ( isset ( $res_id ) AND !empty ( $res_data["res_id"] ) AND !$res_data["res_release"] )
{
	// Antworten l�schen
	$db->fctSendQuery ( "DELETE FROM `bew_quali_resantwort` WHERE `res_id` = " . $res_id );

	// History l�schen
	$db->fctSendQuery ( "DELETE FROM `bew_quali_history` WHERE `res_id` = " . $res_id );
	
	// Quali l�schen
	$db->fctSendQuery ( "DELETE FROM `bew_quali_res` WHERE `res_id` = " . $res_id );
}

// gel�scht, alles OK
header ( "Location: ../?alert=del_ok&res_id=" . $res_data["res_id"] . "&person_s_semester=" . $person_s_semester . "&periode_id=" . $periode_id . "&" );

############################################################################################
include ( $sys["root_path"] . "_global/footer.php" );
############################################################################################
?>