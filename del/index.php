<?PHP
############################################################################################
$sys["root_path"] 	= "../../../../";
$sys["script"] 		= false;
$sys["nav_id"]		= 77;
############################################################################################
include ( $sys["root_path"] . "_global/header.php" );
############################################################################################
$sys["page_title"] 	= "Qualifikation l�schen";
############################################################################################

$res_id				= htmlspecialchars ( mysql_escape_string ( $_GET["res_id"] ) );
$person_s_semester	= htmlspecialchars ( mysql_escape_string ( $_GET["person_s_semester"] ) );
$periode_id			= htmlspecialchars ( mysql_escape_string ( $_GET["periode_id"] ) );
$res_data 			= $db->fctSelectData ( "bew_quali_res" , "`res_id` = '" . $res_id . "'" );

if ( isset ( $res_id ) AND !empty ( $res_data["res_id"] ) AND !$res_data["res_release"] )
{
	// Personendaten
	$person_data = $db->fctSelectData ( "core_person" , "`person_id` = " . $res_data["person_id"] );
	$firma_data  = $db->fctSelectData ( "core_firma" , "`firma_id` = " . $person_data["firma_id"] );
	
	// Zeitraum
	$periode_data = $db->fctSelectData ( "bew_quali_periode" , "`periode_id` = " . $res_data["periode_id"] );
?>

	<p>
    <a href="./"><img src="<?PHP echo ( $sys["icon_path"] ); ?>bew_quali.gif" alt="zur�ck zur �bersicht" border="0" /> zur�ck zur �bersicht</a><br />
    </p>

	<table>
	<tr>
	<th>Person</th>
	<td><?PHP echo ( $person_data["person_vorname"] . " " . $person_data["person_name"] . ", " . $firma_data["firma_name"] ); ?></td>
	</tr>
	<tr>
	<th>Zeitraum</th>
	<td><?PHP echo ( $periode_data["periode_title"] . " (" . $periode_data["periode_start"] . " - " . $periode_data["periode_end"] . ")" ); ?></td>
	</tr>
    <tr>
	<th>Zusammenfassung</th>
    <td>
	<?PHP
	$antwort_result = $db->fctSendQuery ( "SELECT * FROM `bew_quali_antwort` ORDER BY `antwort_id` ASC" );
	while ( $antwort_data = mysql_fetch_array ( $antwort_result ) )
	{
		$zf_count = $db->fctCountData ( "bew_quali_resantwort" , "`res_id` = " . $res_data["res_id"] . " AND `antwort_id` = " . $antwort_data["antwort_id"] );
		
		echo ( "<a title=\"" . $antwort_data["antwort_desc"] . "\">" . $antwort_data["antwort_text"] . "</a>: " . $zf_count . "<br />\n" );
	}
	?>
    </td>
	</tr>
    <tr>
	<th>Gewichtung</th>
    <td>
    <?PHP
	$total_antwort = mysql_fetch_array ( $db->fctSendQuery ( "SELECT SUM(`antwort_id`) FROM `bew_quali_resantwort` WHERE `res_id` = " . $res_data["res_id"] ) );
	$frage_count = $db->fctCountData ( "bew_quali_resantwort" , "`res_id` = " . $res_data["res_id"] );
	if ( $frage_count == 0 )
	{
		echo ( "-" );
	}
	else echo ( bcdiv ( $total_antwort[0] , $frage_count , 2 ) );
	?>
    </td>
    </tr>
	</table>
    
    <p class="warning"><b>Achtung</b>: Wollen Sie diese Qualifikation wirklich unwiderruflich l�schen?</p>
    
    <form action="save.php" method="post" name="quali_del">
    	<input type="hidden" name="res_id" value="<?PHP echo ( $res_data["res_id"] ); ?>" />
    	<input type="hidden" name="person_s_semester" value="<?PHP echo ( $person_s_semester ); ?>" />
    	<input type="hidden" name="periode_id" value="<?PHP echo ( $periode_id ); ?>" />
    	<p>
            <input type="submit" name="btn" value="Qualifikation l�schen" class="btn" />
            <input type="button" name="btn" value="Abbrechen" class="btn" onclick="self.location.href='../?person_s_semester=<?PHP echo ( $person_s_semester ); ?>&amp;periode_id=<?PHP echo ( $periode_id ); ?>&amp;';" />
        </p>
    </form>
    
<?PHP
}
else
{
	header ( "Location: ../" );
}

############################################################################################
include ( $sys["root_path"] . "_global/footer.php" );
############################################################################################
?>