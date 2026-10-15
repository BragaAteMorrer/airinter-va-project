<?php
/*******************************************************************************
 *   DataBase Integrity Check
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
 * v6.00.19 (August 26, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'dbcheck.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[8] == '') {
        $nomzone = $admin[278];
        include 'access.inc';
        exit();
    }
}

$delta  = import('delta');
$range1 = import('range1');
$range2 = import('range2');
$integr = import('integr');

include 'inc/funcdbchk.inc';
$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[281]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[281], '100%');

echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=988&mnuid=937&tconfig=0#z2').$admin[280].$admin[305].'</p>
<hr />';

$max1 = ReadCounter(NEXTID);
$max2 = 10*ceil(($max1+10)/10);
if (empty($delta)) {
    $delta = DBCHK_DEFAULT;
}
else {
    $delta = min(DBCHK_MAX, $delta);
}

switch ($integr) {
case 1 :
    echo '
<br />
<fieldset>
'.$admin[295].$admin[296];
    if (empty($range1)) {
        $range1 = 1;
    }
    else {
        $range1 = Min($range1, $max1);
    }
    if (empty($range2)) {
        $range2 = $max2;
    }
    if ($range2 < $range1) {
        $range2 = $range1;
    }
    majIndexAROM();
    if (CheckDBmP($range1, $range2) == 0) {
        echo '
<div style="text-align:center;padding:10px;"><span class="forum2"><b>'.$admin[297].'</b></span></div>';
    }
    echo $admin[298].'
</fieldset>
<br />';
    break;
case 2 :
    echo '
<br />
<fieldset>'.$admin[685].'</fieldset>
<br />
'.BeginJavascript().'
PopupWindow("inc/dbbatch.php?lng='.$lng.'&range1='.$range1.'&range2='.$range2.'&delta='.$delta.'&checkit='.$range1.'","dbbatch",400,250,"no","no")
'.EndJavascript();
    majIndexAROM();
    break;
}
$docids = array_reverse(ReadDBFields(DOCID));
WriteCounter(NEXTID, $docids[0][1]);
$max1 = ReadCounter(NEXTID);
echo '
<div class="w-100 m-auto" style="max-width:360px;">
  <form name="integrity1" action="admin.php?lng='.$lng.'&amp;pg=dbcheck" method="post">
	<input type="hidden" name="integr" value="1" />
	<fieldset>
      <div class="table-responsive-md">
	  <table class="table">
		<tr>
		  <td style="text-align:right;width:50%;">'.$admin[333].'</td>
		  <td><input name="range1" class="form-control texte" type="text" value="1" size="10" /></td>
		  <td> (1)</td>
		</tr>
		<tr>
		  <td style="text-align:right;">'.$admin[334].'</td>
		  <td><input name="range2" class="form-control texte" type="text" value="'.$max1.'" size="10" /></td>
		  <td> ('.$max1.')</td>
		</tr>
	  </table>
      </div>
	</fieldset>
    <div class="quest" style="padding:2px 20px;">
      <div style="float:right">'.SubmitButton($admin[279], '', '', $imgdroite).'</div>
      <div style="clear:both"></div>
    </div>
  </form>
</div>
<br />
<div class="w-100 m-auto" style="max-width:360px;">
  <form name="integrity2" action="admin.php?lng='.$lng.'&amp;pg=dbcheck" method="post">
	<input type="hidden" name="integr" value="2" />
	<fieldset>
      <div class="table-responsive-md">
	  <table class="table">
		<tr>
		  <td style="text-align:right;width:50%;">'.$admin[333].'</td>
		  <td><input name="range1" class="form-control texte" value="1" type="text" size="10" /></td>
		  <td> (1)</td>
		</tr>
		<tr>
		  <td style="text-align:right;">'.$admin[334].'</td>
		  <td><input name="range2" class="form-control texte" value="'.$max2.'" type="text" size="10" /></td>
		  <td> ('.$max2.')</td>
		</tr>
		<tr>
		  <td style="text-align:right;">'.$admin[686].'</td>
		  <td><input name="delta" class="form-control texte" value="'.$delta.'" type="text" size="10" /></td>
		  <td> ('.DBCHK_MAX.')</td>
		</tr>
	  </table>
      </div>
    </fieldset>
    <div class="quest" style="padding:2px 20px;">
      <div style="float:right">'.SubmitButton($admin[575], '', '', $imgdroite).'</div>
      <div style="clear:both"></div>
    </div>
  </form>
</div>';
btable();
include CHEMIN.'admin/bpage.inc';
?>