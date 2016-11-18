<?PHP
############################################################################################
$sys["root_path"] 	= "../../../";
$sys["script"] 		= true;
$sys["nav_id"]		= 73;
############################################################################################
include ( $sys["root_path"] . "_global/header.php" );
############################################################################################

// Parameter validieren
$inventar_id 			= htmlspecialchars ( mysql_escape_string ( $_GET["inventar_id"] ) );
$action	= htmlspecialchars ( mysql_escape_string ( $_GET["action"] ) );

// Beide Werte m�ssen vorhanden sein
if ( !empty ( $inventar_id ) AND is_numeric ( $inventar_id )  AND !empty($action))
{
	$inventar_data		= $db->fctSelectData ( "bew_inventar_res" , "`inventar_id` = " . $inventar_id );
	if ( !empty ( $inventar_data["inventar_id"] ) )
	{
		$allowed_fields = array ( "visum_lde" , "freigabe" , "visum_bb","");
		
		if ( in_array ( $action , $allowed_fields ) ) {
            if ($action == "visum_lde") {
                if ($inventar_data["inventar_visum_lde"] == 0) $target_value = 1;
                else $target_value = 0;
                $db->fctSendQuery("UPDATE `bew_inventar_res` SET `inventar_visum_lde` = " . $target_value . " WHERE `inventar_id` =" . $inventar_id);
            }

            if ($action == "freigabe") {
                if ($inventar_data["inventar_release"] == 0) {
                    $db->fctSendQuery("UPDATE `bew_inventar_res` SET `inventar_release` = 1 WHERE `inventar_id` =" . $inventar_id);
                } else {
                    $db->fctSendQuery("UPDATE `bew_inventar_res` SET `inventar_release` = 0, `inventar_visum_lde` = 0, `inventar_visum_bb` = 0 WHERE `inventar_id` =" . $inventar_id);
                }
            }

            if ($action == "visum_bb") {
                if ($inventar_data["inventar_visum_bb"] == 0) $target_value = 1;
                else $target_value = 0;
                $db->fctSendQuery("UPDATE `bew_inventar_res` SET `inventar_visum_bb` = " . $target_value . " WHERE `inventar_id` =" . $inventar_id);
            }
            header ( "Location: ./" );
        }else{
            // ung�ltige Aktion
            header ( "Location: ./" );
        }
	}
	else
	{
		// ung�ltiger Inventar
		header ( "Location: ./" );
	}
}
else
{
	// nicht alle n�tigen Werte
	header ( "Location: ./" );
}

############################################################################################
include ( $sys["root_path"] . "_global/footer.php" );
############################################################################################
?>