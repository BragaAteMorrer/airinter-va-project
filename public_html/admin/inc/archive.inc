<?php
/*******************************************************************************
 *   Datas Archiving
 *******************************************************************************
 *   GuppY PHP Script - version 6.00.03
 *   CeCILL Copyright (C) 2004-2020 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.19 (August 26, 2024) :Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'archive.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[7] == '') {
        $nomzone = $admin[577];
        include 'access.inc';
        exit();
    }
}

$archgo   = import('archgo', 'POST', 0);
$nextstep = import('nextstep');
$archdate = import('archdate');
$archbl   = import('archbl');
$archne   = import('archne');
$archag   = import('archag');
$archfr   = import('archfr');

$err_arch = $archbl != 'on' && $archne != 'on' && $archag != 'on' && $archfr != 'on' && $archgo == 1 ? true: false;
$err_date = 1 != preg_match('/[0-9]{8}/', $archdate) && $archgo == 1 ? true: false;

$erreur  = '';
$erreur .= $err_arch ? '<li>'.$admin[1297].'</li>': '';
$erreur .= $err_date ? '<li>'.$admin[591].'</li>': '';

//if (!isset($nextstep)) 
$nextstep = 0;
if ($archgo == 1 && empty($erreur)) {
    if ($archbl == 'on' && ($archdate.'0000' <= ReadCounter(DBBLOGARCHDATE) || $archdate.'0000' > GetCurrentDateTime())) $archbl = 'off';
	if ($archne == 'on' && ($archdate.'0000' <= ReadCounter(DBNEWSARCHDATE) || $archdate.'0000' > GetCurrentDateTime())) $archne = 'off';
	if ($archag == 'on' && ($archdate.'0000' <= ReadCounter(DBAGENDAARCHDATE) || $archdate.'0000' > GetCurrentDateTime())) $archag = 'off';
	if ($archfr == 'on' && ($archdate.'0000' <= ReadCounter(DBFORUMARCHDATE) || $archdate.'0000' > GetCurrentDateTime())) $archfr = 'off';
    if ($archbl == 'on' || $archne == 'on' || $archag == 'on' || $archfr == 'on') $nextstep = 1;
}

$headinc .= BeginJavascript().'
function VerifyForm() {
    var sto    = "";
    var erreur = false;
	if (document.archiveit.archbl.checked != true &&
		document.archiveit.archne.checked != true &&
		document.archiveit.archag.checked != true &&
		document.archiveit.archfr.checked != true) {
        sto += "  - '.addslashes($admin[1297]).'\n";
        erreur = true;
	}
	regexp = /[0-9]{8}/;
	if (!regexp.test(document.archiveit.archdate.value)) {
		sto += "  - '.addslashes($admin[591]).'\n";
		document.archiveit.archdate.className = "errorInputText";
		erreur = true;
	}
    if (erreur == true) {
        sto = "'.addslashes($admin[1298]).'\n\n" + sto;
        alert(sto);
        return false;
    }
}'.EndJavascript();

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[578]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[578], '100%');

if (!empty($erreur))
	echo displayErrorMsg($admin[1298], $erreur);
echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=992&mnuid=937&tconfig=0#z2').
  '<div class="w-100 m-auto text-center" style="max-width:940px;">'.$admin[579].'<br />'.$admin[580].'<br />'.$admin[581].'</div>
</p>
<hr />
<div style="max-width:520px; margin:auto;">
';
if ($nextstep == 0) {
	echo '
<p class="forum" style="text-align:left; padding:5px 20px;">'.$admin[1296].'</p>
<form name="archiveit" id="archiveit" action="admin.php?lng='.$lng.'&amp;pg=archive" method="post" onsubmit="return VerifyForm(); return false;">
  <input type="hidden" name="archgo" value="1" />
  <fieldset>
  <div class="table-responsive-md">
  <table class="table table-bordered w-100">
    <tr>
	  <td colspan="2" class="text-left w-70">&nbsp;</td>
      <td class="text-center w-30 pb-3">'.$admin[590].'</td>
    </tr>
    <tr>
      <td class="align-middle text-right w-55 py-2"><label for="archbl">'.$admin[770].' : </label></td>
      <td class="align-middle text-left w-15 py-2"><input id="archbl" name="archbl" type="checkbox"'.Checked($archbl == 'on').' /></td>
	  <td class="align-middle text-center w-30 py-2">'.(ReadCounter(DBBLOGARCHDATE) > 0 ? FormatDate(ReadCounter(DBBLOGARCHDATE)) : '-').'</td>
    </tr>
    <tr>
      <td class="align-middle text-right w-55 py-2"><label for="archne">'.$admin[13].' : </label></td>
      <td class="align-middle text-left w-15 py-2"><input id="archne" name="archne" type="checkbox"'.Checked($archne == 'on').' /></td>
	  <td class="align-middle text-center w-30 py-2">'.(ReadCounter(DBNEWSARCHDATE) > 0 ? FormatDate(ReadCounter(DBNEWSARCHDATE)) : '-').'</td>
    </tr>
    <tr>
      <td class="align-middle text-right w-55 py-2"><label for="archag">'.$admin[624].' : </label></td>
      <td class="align-middle text-left w-15 py-2"><input id="archag" name="archag" type="checkbox"'.Checked($archag == 'on').' /></td>
	  <td class="align-middle text-center w-30 py-2">'.(ReadCounter(DBAGENDAARCHDATE) > 0 ? FormatDate(ReadCounter(DBAGENDAARCHDATE)) : '-').'</td>
    </tr>
    <tr>
      <td class="align-middle text-right w-55 py-2"><label for="archfr">'.$admin[9].' : </label></td>
      <td class="align-middle text-left w-15 py-2"><input id="archfr" name="archfr" type="checkbox"'.Checked($archfr == 'on').' /></td>
	  <td class="align-middle text-center w-30 py-2">'.(ReadCounter(DBFORUMARCHDATE) > 0 ? FormatDate(ReadCounter(DBFORUMARCHDATE)) : '-').'</td>
    </tr>
    <tr>
      <td class="align-middle text-right w-55 py-2">'.$admin[582].'</td>
      <td class="align-middle text-left w-15 py-2">
        <input name="archdate" class="form-control '.($err_date ? 'errorInputText' : 'texte').'" 
          type="text" style="width:120px;" value="'.$archdate.'" title="'.$archdate.'" />
      </td>
	  <td class="text-center w-30 py-2">&nbsp;</td>
    </tr>
  </table>
  </div>
  </fieldset>
  <div class="row quest p-0 mt-2 w-98 m-auto">
    <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
    <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[583], '', '', $imgdroite).'</div></div>
  </div>
</form>
';
} elseif ($nextstep == 1) {
	$archtyps  = trim(($archbl == 'on' ? TYP_BLOG.' ' : '').($archne == 'on' ? TYP_NEWS.' ' : '').($archag == 'on' ? TYP_AGENDA.' ' : '').($archfr == 'on' ? TYP_FORUM : ''));
	$firststep = 'PopupWindow(\'inc/archbatch.php?lng='.$lng.'&archdate='.$archdate.'&archtyps='.$archtyps.'\',\'archbatch\',480,360,\'no\',\'no\')';
	echo BeginJavascript().$firststep.EndJavascript();
}
echo '
</div>';
btable();
include CHEMIN.'admin/bpage.inc';
?>