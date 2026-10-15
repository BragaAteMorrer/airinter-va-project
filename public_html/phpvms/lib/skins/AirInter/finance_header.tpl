<span style="float: right;">
<form action="<?php echo url('/finances/viewreport');?>" method="get">
<strong>S&eacute;lectionner le rapport: </strong>
<?php
$years = StatsData::GetYearsSinceStart();
$months = StatsData::GetMonthsSinceStart();
$months = array_reverse($months, true);
?>
<select name="type">
	<option value="" <?php echo ($_GET['type']=='')?'selected="selected"':''?>>Consulter le r&eacute;sum&eacute;</option>
<?php
/*
 * Get the years since the VA started
 */
foreach($years as $yearname=>$timestamp)
{
	# Get the one that's currently selected
	if($_GET['type'] == 'y'.$timestamp)
		$selected = 'selected="selected"';
	else
		$selected = '';
	
?>
	<option value="<?php echo 'y'.$timestamp?>" <?php echo $selected?>>Annuel: <?php echo $yearname?></option>
	<?php
}

/*
 * Get all the months since the VA started
 */

foreach($months as $monthname=>$timestamp)
{
	# Get the one that's currently selected
	if($_GET['type'] == 'm'.$timestamp)
		$selected = 'selected="selected"';
	else
		$selected = '';
		
?>
	<option value="<?php echo 'm'.$timestamp?>" <?php echo $selected?>>Mensuel: <?php echo $monthname?></option>
<?php
}
?>
</select>
<input type="submit" name="submit" value="Consulter le rapport" />
</span>
</form>