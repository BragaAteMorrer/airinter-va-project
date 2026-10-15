<?php
/*******************************************************************************
 *   Mind Plugin
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

if (stristr($_SERVER['SCRIPT_NAME'], 'mind.inc')) {
    header('location:../index.php');
    die();
}

$uninstall = import('uninstall');
$act       = import('act');

if ($uninstall == 'on') {
	include CHEMIN.'admin/plugins/mind/uninstall.inc';
	mind_uninstall('off');
}

if (is_file(CHEMIN.'plugins/mind/'.$lng.'-mind.inc')) {
    include CHEMIN.'plugins/mind/'.$lng.'-mind.inc';
} else {
    include CHEMIN.'plugins/mind/en-mind.inc';
}

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $mind[1]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($mind[1], '100%');

if (trim($serviz[31]) == '' || $userprefs[1] == $serviz[176] || $userprefs[1] == $serviz[31] || $drtuser[42] == 'admin') {
	if ($act == 'uninst') {
		echo'
<hr />
<div style="text-align:center;">
  <a href="admin.php?lng='.$lng.'&amp;pg=plugin&amp;plug=mind/mind">'.$mind[1].'</a>
</div>
<hr />';
		echo '
<div style="margin:20px;text-align:center;">
  <form name="uninst" action="admin.php?lng='.$lng.'&amp;pg=plugin&amp;plug=mind/mind" method="POST">
	<input type="hidden" name="uninstall" value="on" />
    <p class="text-center">'.$mind[19].'</p>
	'.SubmitButton($mind[18]).'
  </form>
</div>
<hr />';
		btable();
		include CHEMIN.'admin/bpage.inc';
		exit();
	}
}

echo '
<p>'.$mind[2].'</p>
<hr />
<p>'.$mind[3].'
  <br /><br />
  <u>'.$mind[4].'</u> '.$mind[5].' <a href="admin.php?lng='.$lng.'&amp;pg=config6"><b>'.$admin[346].'</b></a>
  <br /><br />
  <u>'.$mind[6].'</u> <a href="'.CHEMIN.'plugins/mind/mind.php?lng='.$lng.'#z2" target="_blank"><b>'.$mind[7].'</b></a>
</p>';

if (trim($serviz[31]) == '' || $userprefs[1] == $serviz[176] || $userprefs[1] == $serviz[31] || $drtuser[42] == 'admin') {
	echo'
<hr />
<div style="text-align:center;padding:2px;">
  '.SubmitButton($mind[18], 'self.location.href=\'admin.php?lng='.$lng.'&pg=plugin&plug=mind/mind&act=uninst\'').'
</div>
<hr />';
}

btable();
include CHEMIN.'admin/bpage.inc';
?>