<?php
/*******************************************************************************
 *   News for mobile
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

if ($serviz[8] != 'on') {
  exit($web[143]);
}

$section_index = 13;
$i = $lng == $lang[0] ? 0 : 10;
$nom_ne = getLabel($nom[$i+7]);
$section_name = $nom_ne;
include 'inc/members.inc';
if ($section_access) {

    include 'inc/hpalm.inc';

    $dbw = SelectDBFields(TYP_NEWS, 'a', '');
    @rsort($dbw);
    if (empty($id)) {
      $id = 1;
    }
    $minnews = $serviz[2];
    $maxnews = Min($serviz[2]*$id,count($dbw));
    echo '
<div style="text-align:center"><b>'.$nom_ne.'</b></div>
<hr />';
    if (!empty($dbw)) {
        for ($i = $minnews*($id-1); $i < $maxnews; $i++) {
            ReadDoc($dbw[$i][1]);
			if (!isAccessGranted($fieldmod) || !isAuthorizedPublication($creadate)) continue;
            $hr = '
<hr />';
            if ($i == $maxnews-1) {
                $hr = '';
            }
            if ($lng == $lang[0]) {
                $txt1 = $fieldb1;
                $txt2 = PathToImage($fieldc1);
            }
            else {
                $txt1 = $fieldb2;
                $txt2 = PathToImage($fieldc2);
            }
            echo '
<b>'.$txt1.'</b> - '.$web[6].' <b>'.$author.'</b> '.$web[7].' '.FormatDate($creadate).'
<br />
<br />
    '.$txt2.$hr;
        }
    }
    if (count($dbw) > $minnews) {
        echo '
<hr />
'.GetNavBar('news.php?lng='.$lng.'&amp;id=', count($dbw), $id, $minnews);
    }
    include 'inc/bpalm.inc';
}
?>