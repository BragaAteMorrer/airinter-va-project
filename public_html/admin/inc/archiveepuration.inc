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
 * v6.00.19 (August 26, 2024) : Initialize release and Tohelp add
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'archiveepuration.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[7] == '') {
        $nomzone = $admin[1844];
        include 'access.inc';
        exit();
    }
}

$archepurgego   = import('archepurgego', 'POST', 0);
$nextstep = import('nextstep');
$archepurgedate = import('archepurgedate');
$archepurgebl   = import('archepurgebl');
$archepurgene   = import('archepurgene');
$archepurgeag   = import('archepurgeag');
$archepurgefr   = import('archepurgefr');

$err_arch = $archepurgebl != 'on' && $archepurgene != 'on' && $archepurgeag != 'on' && $archepurgefr != 'on' && $archepurgego == 1 ? true: false;
$err_date = 1 != preg_match('/[0-9]{8}/', $archepurgedate) && $archepurgego == 1 ? true: false;

$erreur  = '';
$erreur .= $err_arch ? '<li>'.$admin[1297].'</li>': '';
$erreur .= $err_date ? '<li>'.$admin[591].'</li>': '';

//log 
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');
$nomficlog = CHEMIN.'data/log/archieepueration'.'_'.$dateheureminiutesecl.'.log';
//   file_put_contents(($nomficlog, ' debut archepurgego: '.$archepurgego.' '."\n", FILE_APPEND | LOCK_EX);
//   file_put_contents(($nomficlog, ' nextstep: '.$nextstep.' '."\n", FILE_APPEND | LOCK_EX);
//   file_put_contents(($nomficlog, ' archepurgedate: '.$archepurgedate.' '."\n", FILE_APPEND | LOCK_EX);
//   file_put_contents(($nomficlog, ' archepurgebl: '.$archepurgebl.' '."\n", FILE_APPEND | LOCK_EX);
//   file_put_contents(($nomficlog, ' archepurgene: '.$archepurgene.' '."\n", FILE_APPEND | LOCK_EX);
//   file_put_contents(($nomficlog, ' archepurgeag: '.$archepurgeag.' '."\n", FILE_APPEND | LOCK_EX);
//   file_put_contents(($nomficlog, ' archepurgefr: '.$archepurgefr.' '."\n", FILE_APPEND | LOCK_EX);

// fin de déclaration log

$nextstep = 0;
//   file_put_contents(($nomficlog, ' nexstep initialiser à 0: '.$nextstep.' '."\n", FILE_APPEND | LOCK_EX);
if ($archepurgego == 1 && empty($erreur)) {
	//   file_put_contents(($nomficlog, ' test de archepurgego à 1 et aucune erreur: '.$archepurgego.' '."\n", FILE_APPEND | LOCK_EX);
	//   file_put_contents(($nomficlog, ' verif archepurgebl: '.$archepurgebl.' '."\n", FILE_APPEND | LOCK_EX);
	//   file_put_contents(($nomficlog, ' verif archepurgene: '.$archepurgene.' '."\n", FILE_APPEND | LOCK_EX);
	//   file_put_contents(($nomficlog, ' verif archepurgeag: '.$archepurgeag.' '."\n", FILE_APPEND | LOCK_EX);
	//   file_put_contents(($nomficlog, ' verif archepurgefr: '.$archepurgefr.' '."\n", FILE_APPEND | LOCK_EX);
	
    if ($archepurgebl == 'on' && ($archepurgedate.'0000' <= ReadCounter(DBBLOGARCHDATE) || $archepurgedate.'0000' > GetCurrentDateTime())) $archepurgebl = 'off';
	
	if ($archepurgene == 'on' && ($archepurgedate.'0000' <= ReadCounter(DBNEWSARCHDATE) || $archepurgedate.'0000' > GetCurrentDateTime()))
	{
		//   file_put_contents(($nomficlog, ' archepurgedate.0000: '.$archepurgedate.'0000'.' Counter: '.ReadCounter(DBNEWSARCHDATE)."\n", FILE_APPEND | LOCK_EX);
		//   file_put_contents(($nomficlog, ' archepurgedate.0000: '.$archepurgedate.'0000'.' datetime: '.GetCurrentDateTime()."\n", FILE_APPEND | LOCK_EX);
		$archepurgene = 'off';
	}
	if ($archepurgeag == 'on' && ($archepurgedate.'0000' <= ReadCounter(DBAGENDAARCHDATE) || $archepurgedate.'0000' > GetCurrentDateTime())) $archepurgeag = 'off';
	if ($archepurgefr == 'on' && ($archepurgedate.'0000' <= ReadCounter(DBFORUMARCHDATE) || $archepurgedate.'0000' > GetCurrentDateTime())) $archepurgefr = 'off';
	
	//   file_put_contents(($nomficlog, ' apres avec les dates de archepurgego à 1 et aucune erreur: '.$archepurgego.' '."\n", FILE_APPEND | LOCK_EX);
	//   file_put_contents(($nomficlog, ' apres verif archepurgebl: '.$archepurgebl.' '."\n", FILE_APPEND | LOCK_EX);
	//   file_put_contents(($nomficlog, ' apres verif archepurgene: '.$archepurgene.' '."\n", FILE_APPEND | LOCK_EX);
	//   file_put_contents(($nomficlog, ' apres verif archepurgeag: '.$archepurgeag.' '."\n", FILE_APPEND | LOCK_EX);
	//   file_put_contents(($nomficlog, ' apres verif archepurgefr: '.$archepurgefr.' '."\n", FILE_APPEND | LOCK_EX);

    if ($archepurgebl == 'on' || $archepurgene == 'on' || $archepurgeag == 'on' || $archepurgefr == 'on') 
	{
		$nextstep = 1;
		//   file_put_contents(($nomficlog, ' au moins est on nextstep: '.$nextstep.' '."\n", FILE_APPEND | LOCK_EX);
	}
	else
	   $erreur = $admin[1853];
}
//   file_put_contents(($nomficlog, ' apres les test de nextstep: '.$nextstep.' '."\n", FILE_APPEND | LOCK_EX);
$headinc .= BeginJavascript().'
function VerifyForm() {
    var sto    = "";
    var erreur = false;
	if (document.archiveepureit.archepurgebl.checked != true &&
		document.archiveepureit.archepurgene.checked != true &&
		document.archiveepureit.archepurgeag.checked != true &&
		document.archiveepureit.archepurgefr.checked != true) {
        sto += "  - '.addslashes($admin[1297]).'\n";
        erreur = true;
	}
	regexp = /[0-9]{8}/;
	if (!regexp.test(document.archiveepureit.archepurgedate.value)) {
		sto += "  - '.addslashes($admin[591]).'\n";
		document.archiveepureit.archepurgedate.className = "errorInputText";
		erreur = true;
	}
    if (erreur == true) {
        sto = "'.addslashes($admin[1298]).'\n\n" + sto;
        alert(sto);
        return false;
    }
}'.EndJavascript();

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[1844]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[1845], '100%');

if (!empty($erreur))
	echo displayErrorMsg($admin[1847], $erreur);
echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1148&mnuid=937&tconfig=0#z2').
  '<div class="w-100 m-auto text-center" style="max-width:940px;">'.$admin[1848].'<br />'.$admin[1849].'<br />'.$admin[1850].'</div>
</p>
<hr />
<div style="max-width:520px; margin:auto;">
';

//   file_put_contents(($nomficlog, ' apres htable test de nextstep: '.$nextstep.' '."\n", FILE_APPEND | LOCK_EX);
if ($nextstep == 0) {
//   file_put_contents(($nomficlog, ' Dans le test de nextstep à 0: '.$nextstep.' '."\n", FILE_APPEND | LOCK_EX);
	echo '
<p class="forum" style="text-align:left; padding:5px 20px;">'.$admin[1845].'</p>
<form name="archiveepureit" id="archiveepureit" action="admin.php?lng='.$lng.'&amp;pg=archiveepuration" method="post" onsubmit="return VerifyForm(); return false;">
  <input type="hidden" name="archepurgego" value="1" />
  <fieldset>
  <div class="table-responsive-md">
  <table class="table table-bordered w-100">
    <tr>
	  <td colspan="2" class="text-left w-70">&nbsp;</td>
      <td class="text-center w-30 pb-3">'.$admin[590].'</td>
      <td class="text-center w-30 pb-3">'.$admin[1852].'</td>
    </tr>';
// date des précédents archives
echo '
    <tr>
      <td class="align-middle text-right w-55 py-2"><label for="archepurgebl">'.$admin[770].' : </label></td>
      <td class="align-middle text-left w-15 py-2"><input id="archepurgebl" name="archepurgebl" type="checkbox"'.Checked($archepurgebl == 'on').' /></td>
	  <td class="align-middle text-center w-20 py-2">'.(ReadCounter(DBBLOGARCHDATE) > 0 ? FormatDate(ReadCounter(DBBLOGARCHDATE)) : '-').'</td>
	  <td class="align-middle text-center w-20 py-2">'.(ReadCounter(DBBLOGARCHDATEEPUR) > 0 ? FormatDate(ReadCounter(DBBLOGARCHDATEEPUR)) : '-').'</td>
    </tr>
    <tr>
      <td class="align-middle text-right w-55 py-2"><label for="archepurgene">'.$admin[13].' : </label></td>
      <td class="align-middle text-left w-15 py-2"><input id="archepurgene" name="archepurgene" type="checkbox"'.Checked($archepurgene == 'on').' /></td>
	  <td class="align-middle text-center w-20 py-2">'.(ReadCounter(DBNEWSARCHDATE) > 0 ? FormatDate(ReadCounter(DBNEWSARCHDATE)) : '-').'</td>
	  <td class="align-middle text-center w-20 py-2">'.(ReadCounter(DBNEWSARCHDATEEPUR) > 0 ? FormatDate(ReadCounter(DBNEWSARCHDATEEPUR)) : '-').'</td>
    </tr>
    <tr>
      <td class="align-middle text-right w-55 py-2"><label for="archepurgeag">'.$admin[624].' : </label></td>
      <td class="align-middle text-left w-15 py-2"><input id="archepurgeag" name="archepurgeag" type="checkbox"'.Checked($archepurgeag == 'on').' /></td>
	  <td class="align-middle text-center w-20 py-2">'.(ReadCounter(DBAGENDAARCHDATE) > 0 ? FormatDate(ReadCounter(DBAGENDAARCHDATE)) : '-').'</td>
	  <td class="align-middle text-center w-20 py-2">'.(ReadCounter(DBAGENDAARCHDATEEPUR) > 0 ? FormatDate(ReadCounter(DBAGENDAARCHDATEEPUR)) : '-').'</td>
    </tr>
    <tr>
      <td class="align-middle text-right w-55 py-2"><label for="archfr">'.$admin[9].' : </label></td>
      <td class="align-middle text-left w-15 py-2"><input id="archepurgefr" name="archepurgefr" type="checkbox"'.Checked($archepurgefr == 'on').' /></td>
	  <td class="align-middle text-center w-20 py-2">'.(ReadCounter(DBFORUMARCHDATE) > 0 ? FormatDate(ReadCounter(DBFORUMARCHDATE)) : '-').'</td>
	  <td class="align-middle text-center w-20 py-2">'.(ReadCounter(DBFORUMARCHDATEEPUR) > 0 ? FormatDate(ReadCounter(DBFORUMARCHDATEEPUR)) : '-').'</td>
    </tr>
    <tr>
      <td class="align-middle text-right w-55 py-2">'.$admin[1851].'</td>
      <td class="align-middle text-left w-15 py-2">
        <input name="archepurgedate" class="form-control '.($err_date ? 'errorInputText' : 'texte').'" 
          type="text" style="width:120px;" value="'.$archepurgedate.'" title="'.$archepurgedate.'" />
      </td>
	  <td class="text-center w-30 py-2">&nbsp;</td>
    </tr>
  </table>
  </div>
  </fieldset>
  <div class="row quest p-0 mt-2 w-98 m-auto">
    <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
    <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[1846], '', '', $imgdroite).'</div></div>
  </div>
</form>
';
} elseif ($nextstep == 1) {
    //   file_put_contents(($nomficlog, ' apres test à 1 nextstep: '.$nextstep.' '."\n", FILE_APPEND | LOCK_EX);
	$archepurgetyps  = trim(($archepurgebl == 'on' ? TYP_BLOG.' ' : '').($archepurgene == 'on' ? TYP_NEWS.' ' : '').($archepurgeag == 'on' ? TYP_AGENDA.' ' : '').($archepurgefr == 'on' ? TYP_FORUM : ''));
	$firststep = 'PopupWindow(\'inc/archepurebatch.php?lng='.$lng.'&archepurgedate='.$archepurgedate.'&archepurgetyps='.$archepurgetyps.'\',\'archepurebatch\',480,360,\'no\',\'no\')';
	echo BeginJavascript().$firststep.EndJavascript();
}
echo '
</div>';
btable();
include CHEMIN.'admin/bpage.inc';
?>