<?php
/*******************************************************************************
 *   FAQ for mobile
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

if ($serviz[14] != 'on') {
    exit($web[143]);
}

$section_index = 4;
$i = $lng == $lang[0] ? 0 : 1;
$nom_fa = getLabel($nom[$i+24]);
$section_name = $nom_fa;
include 'inc/members.inc';
if ($section_access) {

    $dbwork = ReadDBFields(DBFAQ);
    @sort($dbwork);

    $topmess = $nom_fa;
    include 'inc/hpalm.inc';
    echo '
<div style="text-align:center;"><b>'.$nom_fa.'</b></div><hr />';
    if (empty($id)) {
        $id = 1;
    }
    if (!empty($dbwork)) {
        echo '
<br>';
		$rubr = '';
        for ($i = $serviz[6]*($id-1); $i < Min($serviz[6]*$id, count($dbwork)); $i++) {
            ReadDoc($dbwork[$i][4]);
			if (!isAccessGranted($fieldmod) || !isAuthorizedPublication($creadate)) continue;
            if ($lng == $lang[0]) {
                if ($rubr <> $fielda1) {
                    $rubr = $fielda1;
                    if (trim($rubr) != '') {
						echo '
<ul><li><b>'.$rubr.'</b><br><br></li></ul>';
                    }
                }
                $txt1 = $fieldb1;
                $txt2 = PathToImage($fieldc1);
            }
            else {
                if ($rubr <> $fielda2) {
                    $rubr = $fielda2;
                    if (trim($rubr) != '') {
                        echo '
<ul><li><b>'.$rubr.'</b><br><br></li></ul>';
                    }
                }
                $txt1 = $fieldb2;
                $txt2 = PathToImage($fieldc2);
            }
            echo '
    <i class="far fa-question-circle align-middle"></i>&nbsp;&nbsp;'.$txt1.'
    <br><br>'.$txt2.'<br><br>';
            if ($i < Min($serviz[6]*$id, count($dbwork))-1) {
                echo '<hr />';
            }
        }
    }
    if (count($dbwork)>$serviz[6]) {
        echo '<hr />'.GetNavBar('faq.php?lng='.$lng.'&amp;id=', count($dbwork), $id, $serviz[6]);
    }
    include 'inc/bpalm.inc';
}
?>