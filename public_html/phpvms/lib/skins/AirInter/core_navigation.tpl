<li><a href="<?php echo url('/'); ?>">Accueil</a></li>
<?php
if(!Auth::LoggedIn())
{
	// Show these if they haven't logged in yet
?>
	<li><a href="<?php echo url('/login'); ?>">Connection</a></li>
	<li><a href="<?php echo url('/registration'); ?>">Inscription</a></li>
<?php
}
else
{
	// Show these items only if they are logged in
?>
	<li><a href="<?php echo url('/profile'); ?>">Op&eacute;rations</a></li>
  <li><a href="<?php echo url('/pilots'); ?>">Pilotes</a></li>
  <li><a href="<?php echo url('/Schedules'); ?>">Horaires</a></li>	
<?php
}
?>
<li><a href="<?php echo url('/acars'); ?>">Carte des vols en cours</a></li>
<?php echo $MODULE_NAV_INC;?>
<?php
if(Auth::LoggedIn())
{
	if(PilotGroups::group_has_perm(Auth::$usergroups, ACCESS_ADMIN))
	{
		echo '<li><a href="'.fileurl('/admin').'" target="_blank">Administration</a></li>';
	}
?>


<li><a href="<?php echo url('/logout'); ?>">Quitter</a></li>
<?php
}
?>