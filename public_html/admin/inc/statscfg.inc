<?php
/*******************************************************************************
 *   Admin Statistiques
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2020 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.19 (December 15, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'statscfg.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[32] == '') {
        $nomzone = $admin[212];
        include 'access.inc';
        exit();
    }
}

$regit = import("regit");
if ($regit == 1) {
    $xstatscfg['Y'] = isset($_POST['statscfg']['Y']) ? 'on' : '';
    $xstatscfg['M'] = isset($_POST['statscfg']['M']) ? 'on' : '';
    $xstatscfg['D'] = isset($_POST['statscfg']['D']) ? 'on' : '';
    $xstatscfg['NBY'] = $_POST['statscfg']['NBY'];
    if (!is_numeric($xstatscfg['NBY']) && $xstatscfg['NBY'] < 0) $xstatscfg['NBY'] = 1;
	$statscfg['Y']= $xstatscfg['Y'];
	$statscfg['M']= $xstatscfg['M'];
	$statscfg['D']= $xstatscfg['D'];
	$statscfg['NBY']= $xstatscfg['NBY'];	
    include 'confupdt.inc';
}

include CHEMIN.'inc/statcalc.inc';
$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[980]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[980], '100%');
if (isset($xstatscfg['Y']))
	$statscfg['Y']= $xstatscfg['Y'];
if (isset($xstatscfg['M']))
	$statscfg['M']= $xstatscfg['M'];
if (isset($xstatscfg['D']))
	$statscfg['D']= $xstatscfg['D'];
if (isset($xstatscfg['NBY']))
	$statscfg['NBY']= $xstatscfg['NBY'];
echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1014&mnuid=937&tconfig=0#z2').$admin[981].'</p>
<hr />';

echo '
<div class="w-100 m-auto" style="max-width:560px;">
  <form name="statraz" action="admin.php?lng='.$lng.'&amp;pg=statscfg" method="post">
  <input type="hidden" name="regit" value="1";
	<fieldset style="">'.legend($admin[974]).'<br />
	  <div style="margin-bottom:12px;">
		<div class="d-inline-block" style="margin-left:10%;"><label for="statscfg[Y]">'.$admin[982].'</label></div>
		<div class="d-inline-block"><input class="ml-2" type="checkbox" id="statscfg[Y]" name="statscfg[Y]"'.Checked($statscfg['Y']).' value="'.$statscfg['Y'].'" /></div>
	  </div>
	  <div style="margin-bottom:12px;">
		<div class="d-inline-block" style="margin-left:10%"><label for="statscfg[M]">'.$admin[983].'</label></div>
		<div class="d-inline-block"><input class="ml-2" type="checkbox" id="statscfg[M]" name="statscfg[M]"'.Checked($statscfg['M']).' value="'.$statscfg['M'].'" /></div>
	  </div>
	  <div style="margin-bottom:12px;">
		<div class="d-inline-block" style="margin-left:10%;"><label for="statscfg[D]">'.$admin[984].'</label></div>
		<div class="d-inline-block"><input class="ml-2" type="checkbox" id="statscfg[D]" name="statscfg[D]"'.Checked($statscfg['D']).' value="'.$statscfg['D'].'"/></div>
	  </div>
	  <div style="margin-bottom:12px;">
		<div class="d-inline-block" style="margin-left:10%;">'.$admin[985].'</div>
		<div class="d-inline-block">
		  <select class="ml-2 form-control" name="statscfg[NBY]">
			<option value="1"'.Selected(1 == $statscfg['NBY']).'>1</option>
			<option value="2"'.Selected(2 == $statscfg['NBY']).'>2</option>
			<option value="3"'.Selected(3 == $statscfg['NBY']).'>3</option>
			<option value="4"'.Selected(4 == $statscfg['NBY']).'>4</option>
			<option value="5"'.Selected(5 == $statscfg['NBY']).'>5</option>
		  </select>
		</div>
	  </div>
	</fieldset>
	<div class="quest" style="padding:2px 20px;">
	  <div style="float:right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div>
	  <div style="clear:both"></div>
	</div>
  </form>
</div>';

btable();
include CHEMIN.'admin/bpage.inc';
?>