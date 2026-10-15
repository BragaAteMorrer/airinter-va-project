<?php
/*******************************************************************************
 *   Blogs list for mobile
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

if ($serviz[53] != 'on') {
    exit($web[143]);
}

$section_index = 15;
$i = $lng == $lang[0] ? 0 : 1;
$nom_bl = getLabel($nom[$i+42]);
$section_name = $nom_bl;
include 'inc/members.inc';

if ($section_access) {

    include 'inc/hpalm.inc';

    $j = $lng == $lang[0] ? 0 : 1;
    $tartg = $nom_bl;
    $tartd = getLabel($nom[44 + $j]);
    $dbw = ReadDBFields(DBBLOG);
    if (!empty($dbw)) {
        $k = 0;
        $arttbl = array();
        foreach ($dbw as $db) {
			if (!isAuthorizedPublication($db[5])) continue;
            if (empty($db[5])) {
                $arttbl[$k][0] = 'left';
            }
            else {
                $arttbl[$k][0] = $db[5];
            }
            $arttbl[$k][1] = $db[0+$j];
            $arttbl[$k][2] = $db[2+$j];
            $arttbl[$k][3] = $db[4];
            $k++;
        }
        @sort($arttbl);
        $artboxpos = '';
        $rubr = '';
        foreach ($arttbl as $art) {
            if ($artboxpos <> $art[0]) {
                $artboxpos = $art[0];
                if ($artboxpos == 'left') {
                    echo '<div style="text-align:center;"><b>'.$tartg.'</b></div><hr />';
                }
                else {
                    echo '<hr /><div style="text-align:center;"><b>'.$tartd.'</b></div><hr />';
                }
            }
            if ($rubr <> $art[1]) {
                $rubr = $art[1];
                if (trim($rubr) != '') {
                    echo '<b>'.$rubr.'</b><br>';
                }
            }
            if (trim($rubr) != '' ) {
                echo '&nbsp;&nbsp;'.$texte[3].' <a href="blog.php?lng='.$lng.'&amp;pg='.$art[3].'">'.$art[2].'</a><br>';
            }
        }
    }

    include 'inc/bpalm.inc';
}
?>