<?php
/*******************************************************************************
 *   Admin Articles Counters
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

if (stristr($_SERVER['SCRIPT_NAME'], 'countart.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[31] == '') {
        $nomzone = $admin[17];
        include 'access.inc';
        exit();
    }
}

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.$admin[654];
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[654], '100%');

$dbwork = ReadDBFields(DBART);
echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1004&mnuid=937&tconfig=0#z2').'
<div style="text-align:center;">
  '.$admin[658].'<b> '.count($dbwork).'</b> '.$admin[659].'<br />
  <hr />
</div>
<br />
<div class="table-responsive-md">
<table class="table table-bordered w-98 m-auto">
  <tr class="forum">
    <td><b>'.$admin[5].'</b></td>
    <td><b> '.$admin[673].' </b></td>
    <td><b> '.$admin[144].' </b></td>
    <td><b> '.$admin[663].' </b></td>
  </tr>';
$j = $lng == $lang[0] ? 0 : 1;
$k = 0;
$arttbl = array();
for ($i = 0; $i < count($dbwork); $i++) {
    $arttbl[$k][0] = $dbwork[$i][0+$j];
    $arttbl[$k][1] = $dbwork[$i][2+$j];
    $arttbl[$k][2] = $dbwork[$i][4];
    $k++;
}
for ($i = 0; $i < count($arttbl); $i++) {
    $dbcounter = ReadDocCounter($arttbl[$i][2]);
    $txtcount = $dbcounter > 1 ? $admin[662]: $admin[661];
    $class = $i % 2 == 0 ? ' class="quest"' : ' class="rep"';
    echo '
  <tr'.$class.'>
    <td>'.$texte[3].' '.$arttbl[$i][1].'</td>
    <td class="text-center">'.$dbcounter." ".$txtcount.'</td>
    <td class="text-center">
      <a href="admin.php?lng='.$lng.'&amp;pg=gestcounter&amp;mod=1&amp;id='.$arttbl[$i][2].'&amp;cnum=2" title="">'.$admin[144].'</a>
    </td>
    <td class="text-center">
      <a href="admin.php?lng='.$lng.'&amp;pg=gestcounter&amp;del=1&amp;id='.$arttbl[$i][2].'&amp;cnum=2" title="">'.$admin[663].'</a>
    </td>
  </tr>';
}
echo '
</table>
</div>';

btable();
include CHEMIN.'admin/bpage.inc';
?>
