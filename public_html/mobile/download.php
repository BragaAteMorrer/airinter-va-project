<?php
/*******************************************************************************
 *   Download for Mobile
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
 * v6.00.07 (February 24, 2022) : correction script
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '../');
include CHEMIN.'inc/includes.inc';

if ($serviz[10] != 'on') {
    exit($web[143]);
}

$section_index = 7;
$i = $lng == $lang[0] ? 0 : 10;
$nom_dn = getLabel($nom[$i+1]);
$section_name = $nom_dn;
include 'inc/members.inc';
if ($section_access) {

    $dbwork = ReadDBFields(DBDNLOAD);
    @sort($dbwork);

    $topmess = $nom_dn;
    include 'inc/hpalm.inc';
    echo '
<div style="text-align:center"><b>'.$nom_dn.'</b></div>
<hr />'.$web[38];
    if (empty($id)) {
        $id = 1;
    }
    if (!empty($dbwork)) {
        echo '
<br />
<ul>';
        $rubr = '';
        for ($i = $serviz[4]*($id-1); $i < Min($serviz[4]*$id, count($dbwork)); $i++) {
            ReadDoc($dbwork[$i][4]);
			if (!isAccessGranted($fieldmod) || !isAuthorizedPublication($creadate)) continue;
            if ($lng == $lang[0]) {
                if ($rubr <> $fielda1) {
                    $rubr = $fielda1;
                    if (trim($rubr) != '') {
                        echo '
  <li><b>'.$rubr.'</b><br /><br />';
                    }
                }
                $txt1 = $fieldb1;
                $txt2 = $fieldd1;
                $txt3 = PathToImage($fieldc1);
            }
            else {
                if ($rubr <> $fielda2) {
                    $rubr = $fielda2;
                    if (trim($rubr) != '') {
                        echo '
  <li><b>'.$rubr.'</b><br /><br />';
                    }
                }
                $txt1 = $fieldb2;
                $txt2 = $fieldd2;
                $txt3 = PathToImage($fieldc2);
            }
            if (substr($txt2, 0, 5) == 'file/') {
                $txt2 = CHEMIN.$txt2;
            }
            $downcounter = -1;
            if ($serviz[35] == 'on') {
                if (!strstr($txt2, '.php') && !strstr($txt2, '.htm')) {
                    $downcounter  = ReadDocCounter($dbwork[$i][4]);
                    $txtcount = $downcounter <= 1 ? $web[188] : $web[189];
                }
            }
            if ($downcounter == -1) {
                echo '
    <i class="fas fa-download align-middle"></i>&nbsp;&nbsp;&nbsp;
    <b><a href="'.$txt2.'">'.$txt1.'</a></b><br />';
            }
            else {
                echo '
    <i class="fas fa-download align-middle"></i>&nbsp;&nbsp;&nbsp;
    <b><a href="dwnld.php?lng='.$lng.'&amp;pg='.$dbwork[$i][4].'&amp;id='.$id.'">'.$txt1.'</a></b>
    <br>'.$web[191].' '.$downcounter.' '.$txtcount.'<br />';
            }
            echo '
    '.$txt3.'<br /><br /></li>';
        }
        echo '
</ul>';
    }
    if (count($dbwork)>$serviz[4]) {
        echo '
<hr />'.GetNavBar('download.php?lng='.$lng.'&amp;id=', count($dbwork), $id, $serviz[4]);
    }
    include 'inc/bpalm.inc';
}
?>