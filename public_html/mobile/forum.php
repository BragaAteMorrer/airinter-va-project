<?php
/*******************************************************************************
 *   Forum for Mobile
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2023 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.11 (March 09, 2023) : correction $dbcat
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '../');
include CHEMIN.'inc/includes.inc';
if ($serviz[13] != 'on') {
    exit($web[143]);
}
$dbcat = ReadDBFields(DBFORUMCAT);
$okfr  = false;
if (!empty($dbcat)) 
{
	foreach ($dbcat as $cat) 
	{
		if (!isset($cat[5])) $cat[5] = '';
		if ($cat[5] == 'on') 
		{ 
			$okfr = true; break;
		}
	}
}
if (!$okfr) {
    exit($web[143]);
}

$section_index = 5;
$i = $lng == $lang[0] ? 0 : 1;
$nom_fr = getLabel($nom[$i+22]);
$section_name = $nom_fr;
include 'inc/members.inc';
if ($section_access) {

    $cat = import('cat');
	$catsauv = import('cat');

    if (!empty($pg)) {
        if (count(SelectDBForumByID(ReadDBFields(DBFORUM),$pg)) != 0) {
            $dbwork = SelectDBForumByID(ReadDBFields(DBFORUM),$pg);
        }
        else {
            $dbwork = array();
        }
    }
    elseif (!empty($cat)) {
        $dbwork = SelectDBForumByCat(ReadDBFields(DBFORUM),$cat);
    }
    else {
        $dbwork = ReadDBFields(DBFORUM);
    }

    if (!empty($cat)) {
        $dbworkcat = ReadDBFields(DBFORUMCAT);
        foreach ($dbworkcat as $dbw) {
            if ($dbw[0] == $cat) {
                if ($lng == $lang[0]) {
                    $topmess = getLabel($nom[22]);
                    if (!empty($dbw[1])) {
                        $topmess .= ' - '.$dbw[1];
                    }
                } else {
                    $topmess = getLabel($nom[23]);
                    if (!empty($dbw[2])) {
                        $topmess .= ' - '.$dbw[2];
                    }
                }
                break;
            }
        }
    }
    include 'inc/hpalm.inc';
    if (!empty($catxt)) {
        echo '
<div style="text-align:center"><b><a href="fortopic.php?lng='.$lng.'">'.$nom_fr.'</a> - '.$catxt.'</b></div>
<hr />';
    }
    else {
        echo '
<div style="text-align:center"><b><a href="fortopic.php?lng='.$lng.'">'.$nom_fr.'</a></b></div>
<hr />';
    }
    if (empty($id)) {
        $id = 1;
    }
    if (!empty($dbwork)) {
        for ($i = $serviz[17]*($id-1); $i < Min($serviz[17]*$id, count($dbwork)); $i++) {
            if ($serviz[34] == 'on') {
                $threadcounter = ReadDocCounter($dbwork[$i][2]);
            }
			if (!empty($cat))
			{
				ReadDoc($dbwork[$i][2]);
				$url = 'thread.php?lng='.$lng.'&amp;pg='.$dbwork[$i][2].'&amp;cat='.getForumcatId($catsauv).'&amp;fid='.$id;
			}
			else
				$url = 'thread.php?lng='.$lng.'&amp;pg='.$dbwork[$i][2].'&amp;fid='.$id;
            echo '
<a href="'.$url.'">'.$web[63].$dbwork[$i][1].'&nbsp;-&nbsp;<b>'.$dbwork[$i][5].'</b></a>
<br />
&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;'.$web[6].' <b>'.$dbwork[$i][3].'</b> '.$web[7].' '.FormatDate($creadate);
            if ($dbwork[$i][7] != 0) {
                $formail = '<b>'.$dbwork[$i][8].'</b>';
                if ($dbwork[$i][7] == 1) {
                    echo '
<br />
&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;'.$web[93].' '.$formail.' '.$web[94].' '.FormatDate($dbwork[$i][0]).')';
                }
                else {
                    echo '
<br />
&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;'.$dbwork[$i][7].' '.$web[66].' '.$formail.' '.$web[94].' '.FormatDate($dbwork[$i][0]).')';
                }
            }
            else {
                echo '
<br />
&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;'.$web[67];
            }
            if ($serviz[34] == "on") {
                if ($threadcounter <= 1) {
                    $txtcount = $web[188];
                }
                else {
                    $txtcount = $web[189];
                }
                echo '
<br />
&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;'.$web[217].' '.$threadcounter.' '.$txtcount;
            }
            echo '
<br />';
            if ($i < Min($serviz[17]*$id, count($dbwork))-1) {
                echo '
<hr />';
            }
        }
    }
    if (count($dbwork)>$serviz[17]) {
        echo '
<hr />';
        if (count($dbwork)>$serviz[17]) {
            echo '
<hr />'.GetNavBar(($site['URLR'] == 'on'? $lng.'-'.$urlrw[5].'-21-': 'forum.php?lng='.$lng.'&amp;id='), count($dbwork), $id, $serviz[17]);
            }
        echo '
<div style="text-align:center">
  <a href="fortopic.php?lng='.$lng.'">'.$web[134].'</a>
</div>';
    }
    include 'inc/bpalm.inc';
}
?>
