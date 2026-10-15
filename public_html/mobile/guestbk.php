<?php
/*******************************************************************************
 *   Guestbook for Mobile
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

if ($serviz[12] != 'on') {
    exit($web[143]);
}

$section_index = 12;
$i = $lng == $lang[0] ? 0 : 10;
$nom_gb = getLabel($nom[$i+3]);
$section_name = $nom_gb;
include 'inc/members.inc';
if ($section_access) {

    $dbwork = SelectDBFields(TYP_GUESTBK,'a','');
    @rsort($dbwork);

    $topmess = $nom_gb;
    include 'inc/hpalm.inc';
    echo '
<div style="text-align:center;"><b>'.$nom_gb.'</b></div>
<hr />';
    if (empty($id)) {
    $id = 1;
    }
    if (!empty($dbwork)) {
        for ($i = $serviz[7]*($id-1); $i < Min($serviz[7]*$id, count($dbwork)); $i++) {
            ReadDoc($dbwork[$i][1]);
            echo '
<b>'.$web[39].$fielda1.'</b>&nbsp;'.$web[6].'
<b>'.$author.'</b>'.$web[7].' '.FormatDate($creadate).'
<br />&nbsp;&nbsp;&nbsp;&nbsp;
<i class="fas fa-globe-europe align-middle"></i>&nbsp;&nbsp;
<a href="'.$fieldb1.'" target="_blank" title="">'.$fieldb1.'</a>
<br /><br />
'.PathToImage($fieldc1).'<br />';
            if ($i < Min($serviz[7]*$id, count($dbwork))-1) {
                echo '
<hr />';
            }
        }
    }
    if (count($dbwork) > $serviz[7]) {
        echo '
<hr />
'.GetNavBar('guestbk.php?lng='.$lng.'&amp;id=', count($dbwork), $id, $serviz[7]);
    }
    include 'inc/bpalm.inc';
}
?>