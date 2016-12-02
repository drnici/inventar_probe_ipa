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
$person_id =  NULL;
$ersteller = $sys["user"]["person_id"];
if(isset($_POST["person_id"])) $person_id = htmlspecialchars ( mysql_escape_string ( $_POST["person_id"] ) );

// Einzigartigkeit des Inventars prüfen
$obj_count 			= $db->fctCountData ( "bew_inventar_res" , "`obj_id` = " . $obj_id . ";`inventar_nr` = " . $inventar_nr ."");
header ( "Location: ../?alert=add_ok&");

// Mindestens ein Obejekttitel muss ausgefüllt sein(doppelte Aufführung darf nicht sein)
if ( !empty ( $obj_id ) OR !empty ( $inventar_nr ) )
{
    if($obj_count == 0){
        // Inventar erstellen
        if(!empty($person_id) && $person_id != ""){
            $db->fctSendQuery ( "INSERT INTO `bew_inventar_res` (`person_id`,`obj_id`,`erfasser_id`,`inventar_nr`,`inventar_release`,`inventar_visum_lde`,`inventar_visum_bb`,`inventar_time`) VALUES (" . $person_id. "," . $obj_id . "," . $ersteller . ",'" . $inventar_nr . "',0,0,0,CURRENT_TIMESTAMP)" );
            $inhalt = "INSERT INTO `bew_inventar_res` (`person_id`,`obj_id`,`erfasser_id`,`inventar_nr`,`inventar_release`,`inventar_visum_lde`,`inventar_visum_bb`,`inventar_time`) VALUES (" . $person_id. "," . $obj_id . "," . $ersteller . ",'" . $inventar_nr . "',0,0,0,CURRENT_TIMESTAMP)";

        }else{
            $db->fctSendQuery ( "INSERT INTO `bew_inventar_res` (`obj_id`,`erfasser_id`,`inventar_nr`,`inventar_release`,`inventar_visum_lde`,`inventar_visum_bb`,`inventar_time`) VALUES (" . $obj_id . "," . $ersteller . ",'" . $inventar_nr . "',0,0,0,CURRENT_TIMESTAMP)" );
            $inhalt = "INSERT INTO `bew_inventar_res` (`obj_id`,`erfasser_id`,`inventar_nr`,`inventar_release`,`inventar_visum_lde`,`inventar_visum_bb`,`inventar_time`) VALUES (" . $obj_id . "," . $ersteller . ",'" . $inventar_nr . "',0,0,0,CURRENT_TIMESTAMP)";
        }
        header ( "Location: ../?alert=add_ok&inhalt=".$inhalt."&");
    }else{
        // das Inventar existiert schon
        header ( "Location: ./?error=exi&inventar_nr=" . $inventar_nr . "&obj_id=".$obj_id  ."&person_id=".$person_id."&" );
    }
}else{
    // nicht alle Felder ausgef�llt
    header ( "Location: ./?error=leer&inventar_nr=" . $inventar_nr . "&obj_id=".$obj_id  ."&person_id=".$person_id."&"  );
}
############################################################################################
include ( $sys["root_path"] . "_global/footer.php" );
############################################################################################
?>