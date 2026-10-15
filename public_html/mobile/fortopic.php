<?php
/*******************************************************************************
 *   Forum Topics for Mobile
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

$dbcat = array();
if ($serviz[13] == 'on')
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
if ($serviz[13] != 'on' || !$okfr) {
    exit($web[143]);
}


$section_index = 5;
$i = $lng == $lang[0] ? 0 : 1;
$nom_fr = getLabel($nom[$i+22]);
$section_name = $nom_fr;
include 'inc/members.inc';
if ($section_access) {

       $dbw           = ReadDBFields(DBFORUMCAT);
        $dbwork        = array();
        foreach ($dbw as $db) if (isForumAccessGranted($db[0]) && 'on' == $db[5]) $dbwork[] = $db;
        $dbworkcat = ReadDBFields(DBFORUM);
        for ($i = 0; $i < count($dbwork); $i++) {
            $prcat = explode(',', $dbwork[$i][0]);
            $dbwork[$i][0] = $prcat[0];
            $dbcat0 = $dbwork[$i][3];
            $dbcat1 = $dbwork[$i][4];
            $dbwork[$i][3] = 0;
            $dbwork[$i][4] = 0;
            $dbwork[$i] = array_pad($dbwork[$i] , 15, '');
            $dbwork[$i][14] = $dbcat0;
            $dbwork[$i][15] = $dbcat1;
            for ($j = 0; $j < count($dbworkcat); $j++) {
                $frcat = explode(',', $dbworkcat[$j][12]);
                $cat   = $frcat[0];
                $grp   = isset($frcat[1]) ? $frcat[1] : '';
                if ($cat == $dbwork[$i][0] && isForumAccessGranted($cat)) {
                    $dbwork[$i][3]++;
                    $dbwork[$i][4] = $dbwork[$i][4] + $dbworkcat[$j][7] + 1;
                    if (empty($dbwork[$i][6])) { 
                        if (!empty($dbworkcat[$j][8])) {
                            $dbwork[$i][6] = $dbworkcat[$j][8]; 
                            if ($dbworkcat[$j][11] != 'on') 
                                $dbwork[$i][7] = $dbworkcat[$j][9]; 
                            else 
                                $dbwork[$i][7] = ''; 
                            $dbwork[$i][8] = $dbworkcat[$j][0]; 
                        } 
                        else {
                            $dbwork[$i][6] = $dbworkcat[$j][3]; 
                            if ($dbworkcat[$j][10] != 'on') 
                                $dbwork[$i][7] = $dbworkcat[$j][4]; 
                            else 
                                $dbwork[$i][7] = ''; 
                            $dbwork[$i][8] = $dbworkcat[$j][0]; 
                        }
                        $dbwork[$i][9]  = $dbworkcat[$j][1]; 
                        $dbwork[$i][10] = $dbworkcat[$j][7]; 
                        $dbwork[$i][11] = ceil($dbworkcat[$j][7] / $serviz[20]); 
                        $dbwork[$i][12] = $dbworkcat[$j][2]; 
                        $dbwork[$i][13] = $dbworkcat[$j][12];
                    }
                }
            }
        }
        unset($dbworkcat);
        $dbwork    = array_values($dbwork);

    $topmess = $nom_fr;
    include 'inc/hpalm.inc';
    echo '
<div style="text-align:center"><b>'.$nom_fr.'</b></div>
<hr />';
    if (!empty($dbwork)) {
		foreach ($dbwork as $dbw) {
			if (!isForumAccessGranted($dbw[0])) continue;
			echo '
<a href="forum.php?lng='.$lng.'&amp;cat='.getForumcatId($dbw[0]).'"><b>'.$dbw[1].'</b></a>
<br />&nbsp;&nbsp;'.$web[131].' : '.$dbw[3].'
<br />&nbsp;&nbsp;'.$web[132].' : '.$dbw[4].'
<br />&nbsp;&nbsp;'.$web[133].' : ';
			if (!empty($dbw[5])) {
				echo '
'.$web[6].' <b>'.$dbw[6].'</b> ';
			}

			if (!empty($dbw[8])) {
				echo '
'.$web[7].' '.FormatDate($dbw[8]);
			}
			echo '
<br />';
        }
    }

    include 'inc/bpalm.inc';
}
?>
