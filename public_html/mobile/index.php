<?php
/*******************************************************************************
 *   Site Index for mobile
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
 * v6.00.11 (March 09, 2023) : correction forum $dbcat
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '../');
include CHEMIN.'inc/includes.inc';
$dbcat = array();
$okfr  = false;
if ($serviz[13] == 'on')
{
	$dbcat = ReadDBFields(DBFORUMCAT);

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
}
if ($serviz[1] != 0) {
    switch ($serviz[1]) {
    case 1 : if ($serviz[53] == '') $serviz[1] = 0; break; // Blog
    case 2 : if ($serviz[8]  == '') $serviz[1] = 0; break; // News
    case 3 : if ($serviz[13] == '' || !$okfr) $serviz[1] = 0; break; // Forum
    case 4 : if ($serviz[47] == '') $serviz[1] = 0; break; // Agenda
    }
}

if ($serviz[1] != 0) {
    switch ($serviz[1]) {
    case 1 : header('location:blogs.php?lng='.$lng); die();
    case 2 : header('location:news.php?lng='.$lng); die();
    case 3 : header('location:forum.php?lng='.$lng); die();
    case 4 : exit($web[143]);
    }
}

include 'inc/hpalm.inc';
echo '
<div style="text-align:center; margin:auto;"><b>'.sprintf($web[1], stripslashes($site[($lang[0] == $lng ? 0 : 11)])).'</b></div>
<hr />';
$dbfb = ReadDBFields(DBFREEBOX);
foreach ($dbfb as $fb) {
	if ($fb[0] == 'E') {
		ReadDoc($fb[4]);
		if ($status == 'a' && isAccessGranted($fieldmod) && isAuthorizedPublication($creadate)) {
			$title = $lng == $lang[0] ? $fieldb1 : $fieldb2;
			$title = preg_replace('![§]!', '', $title);
			echo '
<div><b>'.$title.'</b>'.PathToImage($lng == $lang[0] ? $fieldc1 : $fieldc2).'</div>';
		}
		break;
	}
}

echo '
<hr />';
$dbmb = array();
$dbw  = array();
foreach ($templates as $key => $value)
	$dbw  = array_merge($dbw, $xposbox[$key]['L'], $xposbox[$key]['R'], $xposbox[$key]['A'], $xposbox[$key]['U'], $xposbox[$key]['T']['L'], 
                        $xposbox[$key]['T']['C'], $xposbox[$key]['T']['R'], $xposbox[$key]['B']['L'], $xposbox[$key]['B']['C'], $xposbox[$key]['B']['R']);
 $dbw  = array_unique($dbw);
foreach ($dbw as $xbox) {
	if (!empty($xbox)) {
		$xb = explode(CONNECTOR, $xbox);
		if ($xb[1] == 'fbox_menubox') $dbmb[] = $xb[2];
	}
}

foreach ($dbmb as $mb) {
	ReadDoc($mb);
	$ilng    = $lng == $lang[0] ? 0 : 1 ;
	$titre   = $lng == $lang[0] ? $fieldb1 : $fieldb2;
	$titre   = mb_substr(trim($titre), 0, 1, $charset) == '§' ? mb_substr(trim($titre), 1, 255, $charset) : $titre;
	$contenu = PathToImage($lng == $lang[0] ? $fieldc1 : $fieldc2);
	if ($status != 'a' || !isAccessGranted($fieldmod) || !isAuthorizedPublication($creadate)) continue;
	echo '
<div><b>'.$titre.'</b>'.$contenu.'</div>';
	$dbw     = SelectDbFieldsByField(ReadDBFields(DBOPTMENU), 5, $mb);
	$optmenu = array();
	foreach ($dbw as $item) {
		if (!isAccessGranted($item[6])) continue;
		ReadDoc($item[4]);
		if ($status != 'a') continue;
		$url = $lng == $lang[0] ? $fieldd1 : $fieldd2;
		if (strpos($url, 'articles') !== false) {		
			$urls = explode('||', $url);
			$urls[0] = str_replace('&', '&amp;', $urls[0]);
			$optmenu[] = array(trim($item[0 + $ilng]), trim($item[2 + $ilng]), $item[4], $urls[0]);
			unset($urls);
		}
	}
	unset($dbw);
	sort($optmenu);
	
	if (!empty($optmenu)) {        
		$content = '
<div style="margin-left:12px;">';
		$rubr = '---';
		foreach ($optmenu as $opt) {
			if ($rubr <> $opt[0]) {
				if ('---' != $rubr) {
					$content .= '
  </ul>';
				}
				$rubr = $opt[0];
				$content .= '
  <p><b>'.$rubr.'</b></p>
  <ul style="margin-left:12px;">';
			}
			$content .= '
	<li class="item"><a href="'.$opt[3].'"'.' title="'.strip_tags($opt[1]).'">'.$opt[1].'</a></li>';
		}
		$content .= '
  </ul>
</div>';
		echo $content;
	}
	echo '<hr />';
}

$dbfb = ReadDBFields(DBFREEBOX);
foreach ($dbfb as $fb) {
	if ($fb[0] == 'B') {
		ReadDoc($fb[4]);
		if ($status == 'a' && isAccessGranted($fieldmod) && isAuthorizedPublication($creadate)) {
			$titre = $lng == $lang[0] ? $fieldb1 : $fieldb2;
			$titre = mb_substr(trim($titre), 0, 1, $charset) == '§' ? mb_substr(trim($titre), 1, 255, $charset) : $titre;
			echo '
<div><b>'.$titre.'</b>'.PathToImage($lng == $lang[0] ? $fieldc1 : $fieldc2).'</div>';
		}
		break;
	}
}
include 'inc/bpalm.inc';
?>
