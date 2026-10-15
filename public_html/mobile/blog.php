<?php
/*******************************************************************************
 *   Blog for Mobile
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
 * v6.00.15 (November 29, 2023) : Correction illegal variable
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
	$isdoc = count(SelectDBFields(TYP_BLOG,'a', $pg)) == 1;
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
        $txtart6 = $author;
		}
    }
	if (!$isdoc) {
        $countit = 0;
        $txtart1 = $web[35];
        $txtart2 = $web[36];
        $txtart3 = $web[37];
        $txtart4 = $web[37];
        $txtart5 = $web[37];
        $txtart6 = $web[37];
        header('HTTP/1.0 404 Not Found');
    }
    $topmess = $txtart1;
    include 'inc/hpalm.inc';
    $doc = array();
	$docn = '';
	$docp = '';
    $dbblog = ReadDBFields(DBBLOG);
    if (count($dbblog) > 1) {
        for ($i = 0; $i < count($dbblog); $i++) {
            $doc[$i] = $dbblog[$i][4];
            if ($doc[$i] == $pg){
                $docp = $i > 0 ? $dbblog[$i-1][4] : '';
                $docn = $i < count($dbblog)-1 ? $dbblog[$i+1][4] : '';
            }
        }
		$f = '';
        echo '
<p style="align:center;">';
        if ($pg == $dbblog[count($dbblog)-1][4]) {
            $docn = $dbblog[count($dbblog)-1][4];
            $f = 'N';
        }
        echo '
  <a href="blog.php?lng='.$lng.'&amp;pg='.$doc[count($dbblog)-1].'" title="'.$web[339].'"><i class="fas fa-angle-double-left cur'.$f.' align-middle" title="'.$web[339].'"></i></a>
  <a href="blog.php?lng='.$lng.'&amp;pg='.$docn.'" title="'.$web[32].'"><i class="fas fa-angle-left cur'.$f.' align-middle" title="'.$web[32].'"></i></a>
  <b>'.$txtart1.'</b>';
		$d = '';
        if ($pg == $dbblog[0][4]) {
            $docp = $dbblog[0][4];
            $d = '_n';
        }
        echo '
  <a href="blog.php?lng='.$lng.'&amp;pg='.$docp.'" title="'.$web[34].'"><i class="fas fa-angle-right cur'.$f.' align-middle" title="'.$web[34].'"></i></a>
  <a href="blog.php?lng='.$lng.'&amp;pg='.$doc[0].'" title="'.$web[338].'"><i class="fas fa-angle-double-right cur'.$f.' align-middle" title="'.$web[338].'"></i></a>
</p>';
    }
    echo '
'.$web[393].' <b>'.$txtart3.'</b> - '.$web[6].' <b>'.$txtart6.'</b>
<br />
<hr />
'.$txtart2.'
<hr />
'.$web[95].' '.$txtart5.'
<br />';
    if ($serviz[33] == 'on' && $countit == 1) {
        $artcounter = UpdateDocCounter($pg);
        $txtcount = $artcounter <= 1 ? $web[188] : $web[189];
        echo '
'.$web[190].' '.$artcounter.' '.$txtcount.'
<br />';
    }
    if ($serviz[57] == 'on' && $countit == 1) {
        $dbreaction = ReadDBFields(DBREBLOG);
        foreach ($dbreaction as $dbr) {
            if ($dbr[1] == $pg) {
                $dbwork[] = $dbr[0];
            }
        }
        @rsort($dbwork);
        echo '
<hr />
<div style="text-align:center;"><b>'.$web[407].'</b></div>
<hr />';
        if (empty($id)) {
            $id = 1;
        }
        if (!empty($dbwork)) {
            for ($i = $serviz[30]*($id-1); $i < Min($serviz[30]*$id, count($dbwork)); $i++) {
                ReadDoc($dbwork[$i]);
                echo '
<b>'.$web[185].$fielda1.'</b>&nbsp;'.$web[6].'<b>'.$author.'</b> '.$web[7].' '.FormatDate($creadate).'
<br />
<br />
'.PathToImage($fieldc1).'
<br />';
                if ($i < Min($serviz[30]*$id, count($dbwork))-1) {
                    echo '
<hr />';
                }
            }
        }
        else {
            echo '
'.$web[186].'<br>';
        }
        if (count($dbwork) > $serviz[30]) {
            echo '
<hr />
'.GetNavBar('blog.php?lng='.$lng.'&amp;pg='.$pg.'&amp;id=', count($dbwork), $id, $serviz[30]);
        }
    }
    include 'inc/bpalm.inc';
}
?>