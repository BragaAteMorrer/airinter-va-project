<div id="mainbox">
<?php

	// Show the News module, call the function ShowNewsFront
	//	This is in the modules/Frontpage folder
	
	MainController::Run('News', 'ShowNewsFront', 5);
?>
</div>
<div id="sidebar">
<?php
if(!Auth::LoggedIn())
{
	// Show these if they haven't logged in yet
?>
  <!-- Nothing for visitors
	<li><a href="<?php echo url('/login'); ?>">Connection</a></li>
	<li><a href="<?php echo url('/registration'); ?>">Inscription</a></li>
	-->
<?php
}
else
{
	// Show these items only if they are logged in
?>
  <h3>Rapports de vol</h3>
	
	<?php MainController::Run('PIREPS', 'RecentFrontPage', 5); ?>

	<h3>Nouveaux Pilotes</h3>
	
	<?php MainController::Run('Pilots', 'RecentFrontPage', 5); ?>
	
	<h3>Pilotes en ligne</h3>
	<p><i>Il y a eu <?php echo count($usersonline)?> pilote(s), et <?php echo count($guestsonline);?> visiteur(s) en ligne dans les <?php echo Config::Get('USERS_ONLINE_TIME')?> derni&egrave;res minutes.</i></p>
	
	<?php
	/* $usersonline also has the list of users -
		really simple example
		
		Or if you're not on the frontpage:
		$usersonline = StatsData::UsersOnline();
		
	
	foreach($usersonline as $pilot)	
	{
		echo "{$pilot->firstname} {$pilot->lastname}<br />";
	}
	*/
	?>
<?php
}
?>
	
</div>