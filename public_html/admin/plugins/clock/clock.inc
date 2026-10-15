<?php
/*******************************************************************************
 *   Admin Clock Sample Plugin
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

if (stristr($_SERVER['SCRIPT_NAME'], 'clock.inc')) {
    header('location:../index.php');
    die();
}

$uninstall = import('uninstall');
$act       = import('act');

if ($uninstall == 'on') {
	include CHEMIN.'admin/plugins/clock/uninstall.inc';
	clk_uninstall('off');
}

if (is_file(CHEMIN.'plugins/clock/'.$lng.'-clock.inc')) {
    include CHEMIN.'plugins/clock/'.$lng.'-clock.inc';
} else {
    include CHEMIN.'plugins/clock/en-clock.inc';
}

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $clock[1]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($clock[1], '100%');

if (trim($serviz[31]) == '' || $userprefs[1] == $serviz[176] || $userprefs[1] == $serviz[31] || $drtuser[42] == 'admin') {
	if ($act == 'uninst') {
		echo'
<hr />
<div style="text-align:center;">
  <a href="admin.php?lng='.$lng.'&amp;pg=plugin&amp;plug=clock/clock">'.$clock[1].'</a>
</div>
<hr />';
		echo '
<div style="margin:20px;text-align:center;">
  <form name="uninst" action="admin.php?lng='.$lng.'&amp;pg=plugin&amp;plug=clock/clock" method="POST">
	<input type="hidden" name="uninstall" value="on" />
    <p class="text-center">'.$clock[10].'</p>
	'.SubmitButton($clock[9]).'
  </form>
</div>
<hr />';
		btable();
		include CHEMIN.'admin/bpage.inc';
		exit();
	}
}

echo '
<p>'.$clock[2].'</p>
<hr />
<p>'.$clock[3].'<br />
  <br />
  <u>'.$clock[4].'</u>
  <br />
  '.$clock[5].'
  <br />
  &lt;a href="['.$site[3].']plugins/clock/clock.php"&gt;'.$clock[7].'&lt;/a&gt;
  <br />
  <br />
  <u>'.$clock[6].'</u> <a href="'.CHEMIN.'plugins/clock/clock.php?lng='.$lng.'#z2" target="_blank"><b>'.$clock[7].'</b></a>
</p>
<br />
<p><i>'.$clock[8].'</i></p>';

if (trim($serviz[31]) == '' || $userprefs[1] == $serviz[176] || $userprefs[1] == $serviz[31] || $drtuser[42] == 'admin') {
	echo'
<hr />
<div style="text-align:center;padding:2px;">
  '.SubmitButton($clock[9], 'self.location.href=\'admin.php?lng='.$lng.'&pg=plugin&plug=clock/clock&act=uninst\'').'
</div>
<hr />';
}

btable();
include CHEMIN.'admin/bpage.inc';
?>