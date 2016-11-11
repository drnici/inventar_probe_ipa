<?PHP
############################################################################################
$sys["root_path"] 	= "../../../../";
$sys["script"] 		= true;
$sys["nav_id"]		= 75;
############################################################################################
include ( $sys["root_path"] . "_global/header.php" );
############################################################################################

// Eingaben validieren
$obj_title    	= htmlspecialchars ( mysql_escape_string ( $_POST["obj_title"] ) );

// Einzigartigkeit des Objekts prüfen
$res_count 			= $db->fctCountData ( "bew_inventar_obj" , "`obj_title` = '" . $obj_title . "'");

// Mindestens ein Obejekttitel muss ausgefüllt sein(doppelte Aufführung darf nicht sein)
if ( !empty ( $obj_title ) )
{
	if ( $res_count == 0 )
	{
		// Objekttitel erstellen
		$db->fctSendQuery ( "INSERT INTO `bew_inventar_obj` (`obj_title`) VALUES ('" . $obj_title . "')" );
		$res_id = mysql_insert_id ( );

		// Speicherung OK, weiterleiten zur übersicht
		header ( "Location: ../?alert=add_ok&");
	}
	else
	{
		// der Obejekttitel existiert schon
		header ( "Location: ./?error=exi&obj_title=" . $obj_title . "&" );
	}
}
else
{
	// nicht alle Felder ausgef�llt
	header ( "Location: ./?error=leer&obj_title=" . $obj_title . "&" );
}

############################################################################################
include ( $sys["root_path"] . "_global/footer.php" );
############################################################################################
?>