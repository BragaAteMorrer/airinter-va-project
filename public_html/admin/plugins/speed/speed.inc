<?php
/*******************************************************************************
 *   Plugin Speed
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2013 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.00 (December 15, 2019) : initial release
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'speed.inc')) {
    header('location:../index.php');
    die();
}

$uninstall = import('uninstall');
$act       = import('act');

if ($uninstall == 'on') {
	include CHEMIN.'admin/plugins/speed/uninstall.inc';
	speed_uninstall('off');
}

if (is_file(CHEMIN.'plugins/speed/'.$lng.'-speed.inc')) {
    include CHEMIN.'plugins/speed/'.$lng.'-speed.inc';
} else {
    include CHEMIN.'plugins/speed/en-speed.inc';
}

include("plugins/funcplug.inc");

$speedy = import('speedy');
if ($regit == 1) {
    if ($speedy == 'on') {
        if (!PluginRegistered('speed')) {
            RegisterPlugin('speed', 'ALL', 'ALL');
        }
    }
    else {
        UnregisterPlugin('speed');
    }
}

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $speed[1]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($speed[1], '100%');

if (trim($serviz[31]) == '' || $userprefs[1] == $serviz[176] || $userprefs[1] == $serviz[31] || $drtuser[42] == 'admin') {
	if ($act == 'uninst') {
		echo'
<hr />
<div style="text-align:center;">
  <a href="admin.php?lng='.$lng.'&amp;pg=plugin&amp;plug=speed/speed">'.$speed[1].'</a>
</div>
<hr />';
		echo '
<div style="margin:20px;text-align:center;">
  <form name="uninst" action="admin.php?lng='.$lng.'&amp;pg=plugin&amp;plug=speed/speed" method="POST">
	<input type="hidden" name="uninstall" value="on" />
    <p class="text-center">'.$speed[21].'</p>
	'.SubmitButton($speed[20]).'
  </form>
</div>
<hr />';
		btable();
		include CHEMIN.'admin/bpage.inc';
		exit();
	}
}

echo '
<p>'.$speed[2].'</p>
<hr />
<p>'.$speed[3].'<br />
<br />
<u>'.$speed[4].'</u><br />
'.$speed[5].'<br />
<br />
<u>'.$speed[6].'</u> <a href="'.CHEMIN.'plugins/speed/speed.php?lng='.$lng.'" target="_blank"><b>'.$speed[7].'</b></a>
</p>
<form name="speed" action="admin.php?lng='.$lng.'&amp;pg=plugin&amp;plug=speed/speed" method="post">
  <input type="hidden" name="regit" value="1" />
  <fieldset style="width:45%; margin:0 auto;">
    <p style="text-align:center">
      <label for="speedy">'.$speed[8].'&nbsp;&nbsp;</label>
      <input type="checkbox" id="speedy" name="speedy"'.Checked(PluginRegistered("speed")).' />
    </p>
    <p style="text-align:center">'.SubmitButton($admin[66]).'</p>
  </fieldset>
</form>
<br />';

if (trim($serviz[31]) == '' || $userprefs[1] == $serviz[176] || $userprefs[1] == $serviz[31] || $drtuser[42] == 'admin') {
	echo'
<hr />
<div style="text-align:center;padding:2px;">
  '.SubmitButton($speed[20], 'self.location.href=\'admin.php?lng='.$lng.'&pg=plugin&plug=speed/speed&act=uninst\'').'
</div>
<hr />';
}

btable();
include CHEMIN.'admin/bpage.inc';
?>