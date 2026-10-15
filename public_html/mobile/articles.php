<?php
/*******************************************************************************
 *   Articles for Mobile
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

$section_index = 1;
$section_name = $web[169];
include 'inc/members.inc';
if ($section_access) {
	$isdoc = count(SelectDBFields(TYP_ART,'a', $pg)) == 1;
    if ($isdoc) {
        ReadDoc($pg);
        if (!isAccessGranted($fieldmod)) {
			header('HTTP/1.0 403 Forbidden');
        }
		$isdoc = isAuthorizedPublication($creadate);
		if ($isdoc) {
			$countit = 1;
			if ($lng == $lang[0]) {
				$txtart1 = $fieldb1;
				$txtart2 = PathToImage($fieldc1);
				$txtart3 = $fielda1;
			}
			else {
				$txtart1 = $fieldb2;
				$txtart2 = PathToImage($fieldc2);
				$txtart3 = $fielda2;
			}
			$txtart4 = FormatDate($moddate);
			$txtart5 = FormatDate($creadate);
		}
    }
	if (!$isdoc) {
        $countit = 0;
        $txtart1 = $web[35];
        $txtart2 = $web[36];
        $txtart3 = $web[37];
        $txtart4 = $web[37];
        $txtart5 = $web[37];
        header('HTTP/1.0 404 Not Found');
    }

    $topmess = $txtart1;
    include 'inc/hpalm.inc';
    echo '
<div style="text-align:center"><b>'.$txtart1.'</b></div>
<hr />
'.$txtart2.'
<hr />
'.$web[95].' '.$txtart5.'
<br>
'.$web[20].' '.$txtart4.'
<br>
'.$web[21].' '.$txtart3.'
<br>';
    if ($serviz[33] == "on" && $countit == 1) {
        $artcounter = UpdateDocCounter($pg);
        $txtcount = $artcounter <= 1 ? $web[188] : $web[189];
        echo '
'.$web[190].' '.$artcounter.' '.$txtcount.'
<br>';
    }
    if ($serviz[29] == 'on' && $countit == 1) {
        $dbreaction = ReadDBFields(DBREACT);
        foreach ($dbreaction as $dbr) {
            if ($dbr[1] == $pg) {
                $dbwork[] = $dbr[0];
            }
        }
        @rsort($dbwork);
        echo '
<hr />
<div style="text-align:center"><b>'.$web[184].'</b></div>
<hr />';
        if (empty($id)) {
            $id = 1;
        }
        if (!empty($dbwork)) {
            for ($i = $serviz[30]*($id-1); $i < Min($serviz[30]*$id, count($dbwork)); $i++) {
                ReadDoc($dbwork[$i]);
                echo '
<b>'.$web[185].$fielda1.'</b>&nbsp;
'.$web[6].' <b>'.$author.'</b> '.$web[7].' '.FormatDate($creadate).'
<br />
<br />
'. PathToImage($fieldc1).'
<br />';
                if ($i < Min($serviz[30]*$id, count($dbwork))-1) {
                    echo '
<hr />';
                }
            }
        }
        else {
            echo '
'.$web[186].'
<br />';
        }
        if (count($dbwork) > $serviz[30]) {
            echo '
<hr />
'.GetNavBar('articles.php?lng='.$lng.'&amp;pg='.$pg.'&amp;id=', count($dbwork), $id, $serviz[30]);
            $idp = $id-1;
        }
    }
    include 'inc/bpalm.inc';
}
?>