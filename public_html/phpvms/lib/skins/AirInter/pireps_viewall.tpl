<h3>Liste des PIREPs</h3>
<p><?php if(isset($descrip)) { echo $descrip; }?></p>
<?php
if(!$pireps)
{
	echo '<p>Aucun PIREP n&#39;a &eacute;t&eacute; trouv&eacute;</p>';
	return;
}
?>
<table id="tabledlist" class="tablesorter">
<thead>
<tr>
	<th>Num&eacute;ro de vol</th>
	<th>D&eacute;part</th>
	<th>Arriv&eacute;e</th>
	<th>&Eacute;quipement</th>
	<th>Temps de vol (bloc/bloc)</th>
	<th>D&eacute;pos&eacute;</th>
	<th>Status</th>
	<?php
	// Only show this column if they're logged in, and the pilot viewing is the
	//	owner/submitter of the PIREPs
	if(Auth::LoggedIn() && Auth::$userinfo->pilotid == $userinfo->pilotid)
	{
		echo '<th>Options</th>';
	}
	?>
</tr>
</thead>
<tbody>
<?php
foreach($pireps as $report)
{
?>
<tr>
	<td align="center">
		<a href="<?php echo url('/pireps/view/'.$report->pirepid);?>"><?php echo $report->code . $report->flightnum; ?></a>
	</td>
	<td align="center"><?php echo $report->depicao; ?></td>
	<td align="center"><?php echo $report->arricao; ?></td>
	<td align="center"><?php echo $report->aircraft . " ($report->registration)"; ?></td>
	<td align="center"><?php echo $report->flighttime; ?></td>
	<td align="center"><?php echo date(DATE_FORMAT, $report->submitdate); ?></td>
	<td align="center">
		<?php
		
		if($report->accepted == PIREP_ACCEPTED)
			echo '<div id="success">Accept&eacute;</div>';
		elseif($report->accepted == PIREP_REJECTED)
			echo '<div id="error">Rejet&eacute;</div>';
		elseif($report->accepted == PIREP_PENDING)
			echo '<div id="error">En attente de validation</div>';
		elseif($report->accepted == PIREP_INPROGRESS)
			echo '<div id="error">Vol en cour</div>';
		
		?>
	</td>
	<?php
	// Only show this column if they're logged in, and the pilot viewing is the
	//	owner/submitter of the PIREPs
	if(Auth::LoggedIn() && Auth::$userinfo->pilotid == $report->pilotid)
	{
		?>
	<td align="right">
		<a href="<?php echo url('/pireps/addcomment?id='.$report->pirepid);?>">Ajouter un commentaire</a><br />
		<a href="<?php echo url('/pireps/editpirep?id='.$report->pirepid);?>">Editer PIREP</a>
	</td>
	<?php
	}
	?>
</tr>
<?php
}
?>
</tbody>
</table>