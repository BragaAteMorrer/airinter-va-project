<?php
/*******************************************************************************
 *   Forum Thread for Mobile
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
 * v6.00.15 (November 29, 2023) : Correction illegal variable
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '../');
include CHEMIN.'inc/includes.inc';
if ($serviz[13] != 'on') {
    exit($web[143]);
}
$topmess = '';
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
$nom_frth = getLabel($nom[$i+22]);
$section_name = $nom_frth;
include 'inc/members.inc';
if ($section_access) {
    $thrd = import('thrd');
    $cat  = import('cat');
	$catsauv  = import('cat');
    $fid  = import('fid');

    $thread = 0;
    if (!empty($pg)) {
        $dbwork = SelectDBForumByID(ReadDBFields(DBFORUM),$pg);
        if (count($dbwork) != 0) {
            $thread = $dbwork[0][1];
        }
    }
    elseif (!empty($thrd)) {
        $dbwork = ReadDBFields(DBTHREAD);
        foreach ($dbwork as $dbw) {
            if ($dbw[1] == $thrd && $dbw[2] == '0') {
                $thread = $thrd;
                $pg = $dbw[3];
            }
        }
    }
    if ($serviz[34] == 'on') {
        $threadcounter = UpdateDocCounter($pg);
    }
    $dbthrd = SelectDBThreadByThread(ReadDBFields(DBTHREAD),$thread);
    @sort($dbthrd);

    if (!empty($cat)) {
        $catxt = getForumCatName($cat);
        $topmess = '<a href="fortopic.php?lng='.$lng.'">'.$nom_frth.'</a> - 
		<a href="forum.php?lng='.$lng.'&amp;id='.$fid.'&amp;cat='.getForumcatId($cat).'">'.$catxt.'</a> - ';
    }
    ReadDoc($pg);
    $topmess .= $web[63].$fielda1;
    include 'inc/hpalm.inc';
    if (!empty($catxt)) {
        echo '
<div style="text-align:center">
  <b><a href="fortopic.php?lng='.$lng.'">'.$nom_frth.'</a> - 
  <a href="forum.php?lng='.$lng.'&amp;cat='.getForumcatId($catsauv).'&amp;id='.$fid.'">'.$catxt.'</a> - '.$web[63].$fielda1.'</b>
</div>
<hr />';
    } else {
        echo '
<div style="text-align:center">
  <b><a href="fortopic.php?lng='.$lng.'">'.$nom_frth.'</a> - '.$web[63].$fielda1.'</b>
</div>
<hr />';
    }
    echo '
<b>'.$web[63].$fielda1.' </b> -&nbsp;
<b>'.$fieldb1.'</b>
<br />&nbsp; &nbsp; -&nbsp;'.$web[6].' <b>'.$author.'</b> '.$web[7].' '.FormatDate($creadate).'
<br />
<br />'.PathToImage($fieldc1).'
<br />
<hr />';
    if (empty($id)) {
        $id = 1;
    }
    if (!empty($dbthrd)) {
        for ($i = $serviz[20]*($id-1); $i < Min($serviz[20]*$id, count($dbthrd)); $i++) {
            ReadDoc($dbthrd[$i][3]);
            echo '
'.$web[68].$fielda2.'
<br />&nbsp; &nbsp; -&nbsp;'.$web[6].' <b>'.$author.'</b> '.$web[7].' '.FormatDate($creadate).'
<br />
<br />'.PathToImage($fieldc1).'
<br />
<hr />';
        }
    }
    if (count($dbthrd)>$serviz[20]) {
        echo '
'.GetNavBar(($site['URLR'] == 'on'? $lng.'-'.$urlrw[5].'-502'.getForumcatId($catsauv).$pg.'-': 'thread.php?lng='.$lng.'&amp;cat='.getForumcatId($catsauv).'&amp;pg='.$pg.'&amp;id='), count($dbthrd), $id, $serviz[20]);
    }
    echo '
<div style="text-align:center">';
	if (!empty($catsauv))
		echo '
  <a href="forum.php?lng='.$lng.'&amp;cat='.getForumcatId($catsauv).'&amp;id='.$fid.'">'.$web[70].'</a>';
	else
		echo '
  <a href="forum.php?lng='.$lng.'&amp;id='.$fid.'">'.$web[70].'</a>';
	echo '
</div>';
    include 'inc/bpalm.inc';
}
?>
