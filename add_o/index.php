<?PHP
############################################################################################
$sys["root_path"] 	= "../../../../";
$sys["script"] 		= false;
$sys["nav_id"]		= 74;
############################################################################################
include ( $sys["root_path"] . "_global/header.php" );
############################################################################################
$sys["page_title"] 	= "Objekt erstellen";
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

<form action="save.php" method="post" name="quali_add">

    <table>
        <tr>
            <th>Objekt</th>
            <th>*</th>
            <td>
                <input type="text" name="obj_title" placeholder="Objektname"/><span style="color:gray; padding-left:5px;">z.B. Computer, Maus, Bildschirm, etc...</span>
            </td>
        </tr>
    </table>
    
    <p>
        <input type="submit" name="btn" value="Objekt erstellen" class="btn" />
        <input type="button" name="btn" value="Abbrechen" class="btn" onclick="self.location.href='../';" />
    </p>

</form>

<?PHP
############################################################################################
include ( $sys["root_path"] . "_global/footer.php" );
############################################################################################
?>