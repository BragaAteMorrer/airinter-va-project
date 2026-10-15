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

if (stristr($_SERVER['SCRIPT_NAME'], 'statsprg.inc')) {
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

$purge = import('purge');
$mper  = import('mper', 'POST', FALSE);

if ($purge == 1) {
    if (is_string($mper)) {
        $mper = array($mper);
    }
    foreach ($mper as $item) {
        $tmp = explode(',', $item);
        $xper = $tmp[0];
        $xfile = isset($tmp[1]) ? $tmp[1] : '';
        if ('current' == $xper) {
            $dbh = InitDBlog(1);
            WriteDBFields(DBLOGH,$dbh);
            $dbd = InitDBlog(24);
            WriteDBFields(DBLOGD,$dbd);
            $dbm = InitDBlog(31);
            WriteDBFields(DBLOGM,$dbm);
            $dby = InitDBlog(12);
            WriteDBFields(DBLOGY,$dby);
        } elseif (!empty($xfile)) {
            DestroyDBFile(OLDSSTATSREP.$xfile);
        }
    }
}
include CHEMIN.'inc/statcalc.inc';
$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[978]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[978], '100%');

echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1012&mnuid=937&tconfig=0#z2').$admin[979].'</p>
<hr />';


echo '
<div class="w-100 m-auto" style="max-width:280px;">
  <form name="statraz" action="admin.php?lng='.$lng.'&amp;pg=statsprg" method="post">
    <input type="hidden" name="purge" value="1" />
    <fieldset>'.legend($admin[216]).'  
    <p style="text-align:center;"><b>'.$admin[1723].'</b></p>
    <p style="text-align:center;">
      <select class="form-control" name="mper[]" multiple="multiple" size="10">
        <option value="current">'.$admin[1726].'</option>';
$foptions = ListStatsFiles();
if (!empty($foptions)) {
    echo '
        <optgroup label="'.$admin[1727].'">';
    foreach ($foptions as $name => $value) {
    echo '
        <option value="'.$value.'">'.$name.'</option>';
    }
    echo '
        </optgroup>';
}
echo '
      </select>
    </p>
    <p style="text-align:center;"><b>'.$admin[26].'</b> '.$admin[48].'</p>
    </fieldset>
    <div class="quest" style="padding:2px 20px;">
      <div style="float:right">'.SubmitButton($admin[218], '', '', $imgdroite).'</div>
      <div style="clear:both"></div>
    </div>
  </form>
</div>';

btable();
include CHEMIN.'admin/bpage.inc';
?>