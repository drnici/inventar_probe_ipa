<?PHP
############################################################################################
$sys["root_path"] 	= "../../../../";
$sys["script"] 		= true;
$sys["nav_id"]		= 75;
############################################################################################
include ( $sys["root_path"] . "_global/header.php" );
############################################################################################

// Eingaben validieren
$obj_id   	= htmlspecialchars ( mysql_escape_string ( $_POST["obj_id"] ) );
$inventar_nr    = htmlspecialchars ( mysql_escape_string ( $_POST["inventar_nr"] ) );
$person_id = '';
if(!empty($_POST["person_id"])){
    $person_id = htmlspecialchars($_POST["person_id"]);
}
$author_id			= $sys["user"]["person_id"];

// Mindestens ein Obejekttitel muss ausgefüllt sein(doppelte Aufführung darf nicht sein)
if ( !empty ( $obj_id ) &&  !empty ( $inventar_nr ))
{
    // Inventar erstellen

    if(!empty($person_id)){
        $db->fctSendQuery ( "INSERT INTO `bew_inventar_res` (`person_id`,`obj_id`,`erfasser_id`,`inventar_nr`,`inventar_release`) VALUES (" . $person_id . "," . $obj_id . "," . $author_id . "," . $inventar_nr . ",1)" );
    }else{
        $db->fctSendQuery ( "INSERT INTO `bew_inventar_res` (`person_id`,`obj_id`,`erfasser_id`,`inventar_nr`) VALUES (" . $person_id . "," . $obj_id . "," . $author_id . "," . $inventar_nr . ")" );
    }
    // Speicherung OK, weiterleiten zur übersicht
    header ( "Location: ../?alert=add_ok&");
}
else
{
	// nicht alle Felder ausgef�llt
	header ( "Location: ./?error=leer&inventar_nr=" . $inventar_nr . "&obj_id=".$obj_id."&person_id=".$person_id."&" );
}

############################################################################################
include ( $sys["root_path"] . "_global/footer.php" );
############################################################################################
?>