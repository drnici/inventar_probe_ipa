<?PHP
############################################################################################
$sys["root_path"] 	= "../../../../";
$sys["script"] 		= false;
$sys["nav_id"]		= 75;
############################################################################################
include ( $sys["root_path"] . "_global/header.php" );
############################################################################################
$sys["page_title"] 	= "Inventar erstellen";
############################################################################################

if ( isset ( $_GET["error"] ) )
{
    $obj_id 		= htmlspecialchars ( mysql_escape_string ( $_GET["obj_id"] ) );
    $inventar_nr    = htmlspecialchars ( mysql_escape_string ( $_GET["inventar_nr"] ) );
    $person_id      = htmlspecialchars ( mysql_escape_string ( $_GET["person_id"] ) );

	if ( $_GET["error"] == "exi" )
	{
		echo ( "<p class=\"warning\"><b>Fehler</b>: Ein Inventar mit dieser Nummmer un dem Objekt existiert bereits.</p>\n" );
	}
	if ( $_GET["error"] == "leer" )
	{
		echo ( "<p class=\"warning\"><b>Fehler</b>: Es sind nicht alle Felder ausgefüllt.</p>\n" );
	}
}
else
{
    $obj_id 		= '';
    $inventar_nr    = '';
    $person_id      = '';

}
?>

<form action="save.php" method="post" name="inventar_add">

    <table>
        <tr>
            <th>Objekt</th>
            <th>*</th>
            <td>
                <select name="obj_id" size="1">
                    <option value="">..</option>
                    <?PHP
                    $obj_result = $db->fctSendQuery ( "SELECT ob.obj_title,ob.obj_id FROM `bew_inventar_obj` AS ob" );

                    while ( $obj_data = mysql_fetch_array ( $obj_result ) )
                    {
                        echo ( "<option value=\"" . $obj_data["obj_id"] . "\">" . $obj_data["obj_title"] . "</option>\n" );
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>Inventar-Nr.</th>
            <th>*</th>
            <td><input type="text" name="inventar_nr" placeholder="Inventar-Nr."/></td>
        </tr>
        <tr>
            <th>Person</th>
            <td></td>
            <td>
                <select name="person_id" size="1">
                    <option value="">..</option>
                    <?PHP
                    $person_result = $db->fctSendQuery ( "SELECT cp.person_id, cp.person_vorname, cp.person_name FROM `core_person` AS cp WHERE cp.role_id = 1 AND ( cp.person_s_semester = 1 OR cp.person_s_semester = 2 ) AND `beruf_id` = 1 AND cp.person_deactivation = 0 ORDER BY cp.person_vorname ASC, cp.person_name ASC" );

                    while ( $person_data = mysql_fetch_array ( $person_result ) )
                    {
                        echo ( "<option value=\"" . $person_data["person_id"] . "\"" . $s . ">" . $person_data["person_vorname"] . " " . $person_data["person_name"] . "</option>\n" );
                    }
                    ?>
                </select>
            </td>
        </tr>
    </table>
    
    <p>
        <input type="submit" name="btn" value="Inventar erstellen" class="btn" />
        <input type="button" name="btn" value="Abbrechen" class="btn" onclick="self.location.href='../';" />
    </p>

</form>

<?PHP
############################################################################################
include ( $sys["root_path"] . "_global/footer.php" );
############################################################################################
?>