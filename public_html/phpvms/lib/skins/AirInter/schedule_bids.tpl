<h3>Mes R&eacute;servations</h3>
<?php
if(!$bids)
{
	echo '<p align="center">Vous n&#39;avec aucune r&eacute;servations</p>';
	return;
}
?>
<table id="tabledlist" class="tablesorter">
<thead>
<tr>
	<th>Num&eacute;ro de vol</th>
	<th>Route</th>
	<th>&Eacute;quipement</th>
	<th>Heure de d&eacute;part</th>
	<th>Heure d&#39;arriv&eacute;e</th>
	<th>Distance</th>
	<th>Options</th>
</tr>
</thead>
<tbody>
<?php
foreach($bids as $bid)
{
?>
<tr id="bid<?php echo $bid->bidid ?>">
	<td><?php echo $bid->code . $bid->flightnum; ?></td>
	<td align="center"><?php echo $bid->depicao; ?> to <?php echo $bid->arricao; ?></td>
	<td align="center"><?php echo $bid->aircraft; ?> (<?php echo $bid->registration?>)</td>
	<td><?php echo $bid->deptime;?></td>
	<td><?php echo $bid->arrtime;?></td>
	<td><?php echo $bid->distance;?></td>
	<td><a href="<?php echo url('/pireps/filepirep/'.$bid->bidid);?>">Enregistrement du PIREP</a><br />
		<a id="<?php echo $bid->bidid; ?>" class="deleteitem" href="<?php echo url('/schedules/removebid');?>">Enlever la r&eacute;servation **</a><br />
		<a href="<?php echo url('/schedules/brief/'.$bid->id);?>">Briefing Pilote</a><br />
		<a href="<?php echo url('/schedules/boardingpass/'.$bid->id);?>" />Carte d&#39;embarquement</a>
		
	</td>
</tr>
<?php
}
?>
</tbody>
</table>
<p align="right">** - double clic</p>
<hr>