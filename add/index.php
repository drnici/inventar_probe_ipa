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
    $obj_title		= htmlspecialchars ( mysql_escape_string ( $_GET["obj_title"] ) );
	
	if ( $_GET["error"] == "exi" )
	{
		echo ( "<p class=\"warning\"><b>Fehler</b>: Das Objekt mit diesem Titel existiert bereits.</p>\n" );
	}
	if ( $_GET["error"] == "leer" )
	{
		echo ( "<p class=\"warning\"><b>Fehler</b>: Es sind nicht alle Felder ausgefüllt.</p>\n" );
	}
}
else
{
    $obj_title 			= '';
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
                    $obj_result = $db->fctSendQuery ( "SELECT ob.obj_title FROM `bew_inventar_obj` AS ob" );

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