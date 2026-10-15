<?php
/*******************************************************************************
 *   Admin Categories of forum
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2022 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.19 (August 26, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'frcateg.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[24] == '') {
    $nomzone = $admin[253];
    include 'access.inc';
    exit();
    }
}

$upd    = import('upd');
$xfrcat = import('xfrcat', 'POST');
$xfrgrp = import('xfrgrp', 'POST');

function Deplacer($i) {
    global $lng, $forum4, $page, $frcat, $admin, $sid;
	$ispred = $i == 0 || ($i > 0 && empty($frcat[$i][0])) ? false : true;
	$issucc = $i == $forum4 - 1 || ($i < $forum4 - 1 && empty($frcat[$i + 1][0])) ? false : true;
    $i++;
    $img1  = '<i class="fas fa-caret-up text-primary" style="font-size: 1.8rem;" title="'.$admin[1686].'"></i>';
    $img2  = '<i class="fas fa-caret-down text-danger" style="font-size: 1.8rem;" title="'.$admin[1687].'"></i>';
    $img1g = '<i class="fas fa-caret-up text-secondary" style="font-size: 1.8rem;" title=""></i>';
    $img2g = '<i class="fas fa-caret-down text-secondary" style="font-size: 1.8rem;" title=""></i>';
	$apred = $ispred ? '<a href="admin.php?lng='.$lng.'&amp;pg=frcateg&amp;upd=-'.$i.'">'.$img1.'</a>' : $img1g;
	$asucc = $issucc ? '<a href="admin.php?lng='.$lng.'&amp;pg=frcateg&amp;upd='.$i.'">'.$img2.'</a>' : $img2g;
	return $apred.'<br />'.$asucc;
}

function ReadForumCat() {
    global $frcat, $frgrp, $forum4;
    $frcat = ReadDbFields(DBFORUMCAT);
    $frgrp = array();
    for ($i = 0; $i < $forum4; $i++) {
        if (isset($frcat[$i][0])) {
            $tmp = explode(',', $frcat[$i][0]);
            $frcat[$i][0] = $tmp[0];
            $frgrp[$i]    = isset($tmp[1]) ? trim($tmp[1]) : '';
        } else {
            $frcat[$i] = array_pad(array(), 7, '');
            $frgrp[$i] = '';
        }
    }
}

function WriteForumCat($dbcat, $dbgrp) {
    global $frcat, $frgrp, $site, $lng;
    global $type,$fileid,$status,$creadate,$moddate,$author,$email,
         $fielda1,$fielda2,$fieldb1,$fieldb2,$fieldc1,$fieldc2,$fieldd1,$fieldd2,
         $fieldweb,$fieldmail,$fieldmod;
    $frcat2 = array();
    $update = FALSE;
    for ($i = 0; $i < count($dbcat); $i++) {
        if ($dbcat[$i][0] != '') {
            if ($frcat[$i][0] != $dbcat[$i][0] || $frgrp[$i] != $dbgrp[$i]) {
                UpdateDBForumCat($frcat[$i][0], $dbcat[$i][0], $frgrp[$i], $dbgrp[$i]);
                $update = TRUE;
            }
            if ($dbgrp[$i] != '') {
                $dbcat[$i][0] = str_replace(',', '', $dbcat[$i][0]).','.$dbgrp[$i];
            }
			$dbcat[$i][1] = stripslashes($dbcat[$i][1]);
			if (isset($dbcat[$i][2]))  $dbcat[$i][2] = stripslashes($dbcat[$i][2]); //langue 1
			else
				$dbcat[$i][2] = '';
			if (isset($dbcat[$i][3])) $dbcat[$i][3] = stripslashes($dbcat[$i][3]);
			else
			    $dbcat[$i][3] = '';
			if (isset($dbcat[$i][4])) $dbcat[$i][4] = stripslashes($dbcat[$i][4]); // Langue 1
			else
				$dbcat[$i][4] = '';
            if (isset($dbcat[$i][5])) $dbcat[$i][5] = $dbcat[$i][1] == '' ? '' : $dbcat[$i][5];
			else
				$dbcat[$i][5] = '';
            if (isset($dbcat[$i][6])) $dbcat[$i][6] = $dbcat[$i][1] == '' ? '' : $dbcat[$i][6];
			else
				$dbcat[$i][6] = '';
            if (!empty($dbcat[$i][1]))
                $frcat2[] = array($dbcat[$i][0].CONNECTOR.$dbcat[$i][1].CONNECTOR.$dbcat[$i][2].CONNECTOR.$dbcat[$i][3].CONNECTOR.$dbcat[$i][4].CONNECTOR.$dbcat[$i][5].CONNECTOR.$dbcat[$i][6]);
        }
    }
    if (!empty($frcat2)) WriteDBFields(DBFORUMCAT, $frcat2);
    if ($update) UpdateDBForum('', 0);
    if (($site[7] == 'on' || PHP700) && !empty($frcat2)) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
        echo '<script>window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=frcateg&upd=yes";</script>';
    }
}

function UpdateDBForumCat($icat1, $icat2, $grp1, $grp2) {
    global $type,$fileid,$status,$creadate,$moddate,$author,$email,
         $fielda1,$fielda2,$fieldb1,$fieldb2,$fieldc1,$fieldc2,$fieldd1,$fieldd2,
         $fieldweb,$fieldmail,$fieldmod;
    $cat1 = $icat1.(empty($grp1) ? '' : ','.$grp1);
    $cat2 = $icat2.(empty($grp2) ? '' : ','.$grp2);
    $dbforum = SelectDbFields(TYP_FORUM, "", "");
    foreach ($dbforum as $doc) {
        ReadDoc($doc[1]);
        if ($fieldb2 == $cat1) {
            $fieldb1 = addslashes($fieldb1);
            $fieldb2 = $cat2;
            $fieldc1 = addslashes($fieldc1);
            $fieldc2 = addslashes($fieldc2);
            WriteDoc();
        }
    }
}

$frcat  = ReadDbFields(DBFORUMCAT);
$forum4 = count($frcat) + 4;
if (is_numeric($upd)) {
    ReadForumCat();
    if (0 == $upd) {
        $xfrcat = $_POST['xfrcat'];
        foreach($xfrcat as &$db) {
            ksort($db);
        }
        $xfrgrp = $_POST['xfrgrp'];
        WriteForumCat($xfrcat, $xfrgrp);
    } else {
        $i = abs($upd) -1;          // rang de la ligne à déplacer
        $ud = $upd > 0 ? 1 : -1;    // sens +1 ou -1 du déplacement
        $temp = isset($frcat[$i]) ? $frcat[$i] : array_pad(array(), 7, '');
        $frcat[$i] = isset($frcat[$i+$ud]) ? $frcat[$i+$ud] : array_pad(array(), 7, '');
        $frcat[$i+$ud] = $temp;
        $temp = isset($frgrp[$i]) ? $frgrp[$i] : '';
        $frgrp[$i] = isset($frgrp[$i+$ud]) ? $frgrp[$i+$ud] : '';
        $frgrp[$i+$ud] = $temp;
        WriteForumCat($frcat, $frgrp);
        unset($temp);
    }
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
        echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=frcateg";'.EndJavascript();
    }
}
elseif ($upd == 'yes') UpdateDBForum('', 0);

ReadForumCat();
$widepage = 'on';
$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[953]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[953], "100%");
$bilingue = !empty($lang[1]);
$rowspan  = $bilingue ? ' rowspan="2"' : '';

echo '
<div style="width:100%; margin:auto;">
<p style="text-align:center;">'.ToHelp($lng, '&pg=1068&mnuid=937&tconfig=0#z2').$admin[948].'<br /><br />'.$admin[933].'</p>
<hr />
<form action="admin.php?lng='.$lng.'&amp;pg=frcateg" method="post">
  <div class="quest" style="padding:2px 20px;">
    <div style="float:right;">'.SubmitButton($admin[66], '', '', $imgdroite).'</div>
    <div style="clear:both;"></div>
  </div>
  <fieldset>'.legend($admin[864]).'
    <input type="hidden" name="upd" value="0" />
    <div class="table-responsive-md">
    <table class="table bord m-auto table-bordered w-100">
      <tr class="forum2">
        <td class="text-center p-3" style="max-width:30px;">&nbsp;</td>
        <td class="text-center p-3" style="max-width:30px;"><b>'.$admin[256].'</b></td>
        <td class="text-center p-3" style="max-width:180px;"><b>'.$admin[1059].'</b></td>
        <td class="text-center p-3" style="max-width:40px;"><b>'.$admin[765].'</b></td>
		<td class="text-center p-3" style="max-width:50px;"><b>'.$admin[1821].'</b></td>';
if ($bilingue) {
    echo '
        <td class="text-center p-3" style="max-width:40px;"><b>'.$admin[947].'</b></td>';
}
echo '
        <td class="text-center p-3" style="max-width:260px;"><b>'.$admin[255].'</b></td>
        <td class="text-center p-3" style="max-width:520px;"><b>'.$admin[949].'</b></td>
      </tr>';

$dbgroups = ReadDBFields(GROUPS);
$dbfr     = ReadDBFields(DBFORUM);
$maxnocat = 0;
for ($i = 0; $i < $forum4; $i++) {
    $acat     = explode(',', $frcat[$i][0]);
    $maxnocat = max($acat[0], $maxnocat);
}
foreach ($dbfr as $fr) {
    $acat     = explode(',', $fr[12]);
    $maxnocat = max($acat[0], $maxnocat);
}
for ($i = 0; $i < $forum4; $i++) {
    $color = $i %2 == 0 ? "rep" : "quest";
    $acat  = explode(',', $frcat[$i][0]);
    echo '
      <tr class="'.$color.' text-center">
        <td'.$rowspan.' class="align-middle">'.Deplacer($i).'</td>
        <td'.$rowspan.' class="align-middle text-center">
          <input type="text" name="xfrcat['.$i.'][0]" value="'.(empty($frcat[$i][0]) ? ++$maxnocat : $acat[0]).'" />
		  <div class="forum mx-auto text-center" style="width:40px;border:1px solid #000;border-radius:4px;">
            '.(empty($frcat[$i][0]) ? $i + 1 : $acat[0]).'
          </div>
        </td>';
    echo '
        <td'.$rowspan.' class="align-middle text-center">
          <select class="form-control mx-auto" style="max-width:160px;" name="xfrgrp['.$i.']">
            <option value=""> -- '.$admin[1604].' -- </option>
			<option value="all_members"'.Selected('all_members' == $frgrp[$i]).'>'.$admin[690].'</option>';
    foreach ($dbgroups as $dbgroup) {
        echo '
            <option value="'.$dbgroup[0].'"'.Selected($dbgroup[0] == $frgrp[$i]).'>'.$dbgroup[0].'</option>';
    }
    echo '
          </select>
        </td>
        <td'.$rowspan.' class="align-middle text-center">
          <input type="checkbox" name="xfrcat['.$i.'][5]"'.Checked($frcat[$i][5] == 'on').' />
        </td>';
	if (!isset($frcat[$i][6])) $frcat[$i][6] = '';
	echo '
        <td'.$rowspan.' class="align-middle text-center">
          <input type="checkbox" name="xfrcat['.$i.'][6]"'.Checked($frcat[$i][6] == 'on').' />
        </td>';
    if ($bilingue) {
        echo '
        <td class="align-middle text-center">'.$flag1.'</td>
        <td class="align-middle text-center">
          <input class="form-control texte align-middle" style="max-width:260px;" type="text" name="xfrcat['.$i.'][1]" value="'.$frcat[$i][1].'" />
        </td>
        <td class="align-middle text-center">
          <input class="form-control texte align-middle" style="max-width:520px;" type="text" name="xfrcat['.$i.'][3]" value="'.$frcat[$i][3].'" />
        </td>
      </tr>
      <tr class="'.$color.'" class="align-middle text-center">
        <td class="align-middle text-center">'.$flag2.'</td>
        <td class="align-middle text-center">
          <input class="form-control texte align-middle" style="max-width:260px;" type="text" name="xfrcat['.$i.'][2]" value="'.$frcat[$i][2].'" />
        </td>
        <td class="align-middle text-center">
          <input class="form-control texte align-middle" style="max-width:520px;" type="text" name="xfrcat['.$i.'][4]" value="'.$frcat[$i][4].'" />
        </td>';
    } else {
        echo '
        <td class="align-middle text-center">
          <input class="form-control texte align-middle" style="max-width:260px;" type="text" name="xfrcat['.$i.'][1]" value="'.$frcat[$i][1].'" />
        </td>
        <td class="align-middle text-center">
          <input class="form-control texte align-middle" style="max-width:520px;" type="text" name="xfrcat['.$i.'][3]" value="'.$frcat[$i][3].'" />
        </td>';
    }
    echo '
      </tr>';
}
echo '
  </table>
  </div>
  </fieldset>
  <div class="quest" style="padding:2px 20px;">
    <div style="float:right;">'.SubmitButton($admin[66], '', '', $imgdroite).'</div>
    <div style="clear:both;"></div>
  </div>
</form>
</div>';

btable();
include CHEMIN.'admin/bpage.inc';
?>