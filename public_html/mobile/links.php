<?php
/*******************************************************************************
 *   Links for Mobile
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

if ($serviz[11] != 'on') {
    exit($web[143]);
}

$section_index = 3;
$i = $lng == $lang[0] ? 0 : 10;
$nom_ln = getLabel($nom[$i+3]);
$section_name = $nom_ln;
include 'inc/members.inc';
if ($section_access) {

    $dbwork = ReadDBFields(DBLINKS);
    @sort($dbwork);
    $topmess = $nom_ln;
    include 'inc/hpalm.inc';
    echo '
<div style="text-align:center"><b>'.$nom_ln.'</b></div>
<hr />
'.$web[38];
    if (empty($id)) {
      $id = 1;
    }
    if (!empty($dbwork)) {
        echo '
<br />
<ul>';
        $rubr = '';
        for ($i = $serviz[5]*($id-1); $i < Min($serviz[5]*$id, count($dbwork)); $i++) {
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
            echo '
    <i class="fas fa-external-link-alt align-middle"></i>&nbsp;&nbsp;&nbsp;
    <b><a href="'.$txt2.'" target="_blank">'.$txt1.'</a></b><br />
    '.$txt3.'<br /><br />';
        }
        echo '
</ul>';
    }
    if (count($dbwork)>$serviz[5]) {
        echo '
<hr />
'.GetNavBar('links.php?lng='.$lng.'&amp;id=', count($dbwork), $id, $serviz[5]);
    }
    include 'inc/bpalm.inc';
}
?>