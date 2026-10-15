<?php
# This will only show this message if it's not a popup window
# Ends on line 13-15
if(!isset($_GET['newwindow']))
{
?>
<h3>Carte d&#39;embarquement &eacute;l&eacute;ctronique</h3>

<p>Pour proc&eacute;der par la s&eacute;curit&eacute;, vous aurez besoin d&#39;une pi&egrave;ce d&#39;identit&eacute; avec photo et, d une carte d&#39;embarquement. Les clients de moins de 18 ans ne sont pas tenus de montrer une pi&egrave;ce d&#39;identit&eacute; avec photo.</p>

<p><a href="#" 
	onclick="window.open('<?php echo actionurl('/schedules/boardingpass/'.$schedule->id.'?newwindow');?>'); return false;">Ouvrir dans une nouvelle fen&ecirc;tre pour l&#39;impression</a></p>
<?php
}
?>

<style>
/* Some integrated styles here, for the popup */
.boardingpass {
	font-family: Tahoma, Verdana;
	font-size: 14px;
	background-color: #C5DCF5;
}
.boardingpass h3 {
	background: none;
	padding-left: 3px;
	padding-bottom: 2px;
}
.boardingpass .thickline
{
	background: #333;
	height: 2px;
}
</style>
<table width="90%" class="boardingpass">
	<tr>
		<td width="1%"><img src="<?php echo SITE_URL?>/lib/images/barcode.png" /></td>
		<td align="left"><h3><?php echo SITE_NAME;?></h3></td>
	</tr>
	<tr>
		<td colspan="2"><h3>Carte d&#39;embarquement</h3></td>
	</tr>
	<tr class="thickline">
		<td colspan="2"></td>
	</tr>
	<tr>
		<td valign="top">
			<table class="boardingpass">
				<tr>
				<td>
					<strong>Date:</strong> <br />
					<strong>Nom: </strong> <br />
					<strong>Num&eacute;ro carte de fid&eacute;lit&eacute;: </strong> <br />
					<strong>Num&eacute;ro de la carte d&#39;embarquement:</strong> 
				</td>
				<td valign="top">
					<?php echo date('Y-m-d'); ?><br />
					<?php echo Auth::$userinfo->firstname.' '.Auth::$userinfo->lastname?><br />
					<?php echo Auth::$userinfo->code.strtoupper(substr(md5(Auth::$userinfo->pilotid), 0, 6))?><br />
					<?php echo $schedule->bidid; ?><br />
				</td>
				</tr>
			</table>
		</td>
		<td valign="top">
			<strong>Porte:</strong> <?php # We are gonna get a random gate
				echo chr(rand(65, 90)); // echo's a random letter between A and Z
				echo rand(1, 30);		// gate # (between 1 and 30)
			?><br />
			<strong>Confirmation:</strong>
			<?php 
				# Generate a hash from the bid id, and get the first 6 characters
				# That'll be used for our confirmation number, and upper-case them
				echo strtoupper(substr(md5($schedule->bidid), 0, 6));
			?>
		</td>
	</tr>
	<tr class="thickline">
		<td colspan="2"></td>
	</tr>
	<tr>
		<td valign="top">
			<strong>Vol: </strong><?php echo $schedule->code.$schedule->flightnum?><br />
			<strong>D&eacute;part: </strong><?php echo $schedule->deptime; ?><br />
			<strong>Arriv&eacute;e: </strong><?php echo $schedule->arrtime;?><br />
		</td>
		<td valign="top">
			<strong>&Eacute;quipement: </strong><?php echo $schedule->aircraft?> <br />	
			<?php echo "$schedule->depname ($schedule->depicao)";?><br />
			<?php echo "$schedule->arrname ($schedule->arricao)"; ?><br />
		</td>
	</tr>
</table>