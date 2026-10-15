<?php
/*******************************************************************************
 *   BATCH Forum Archiving
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2020 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.00 (December 15, 2020) : initial release
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '../../');
include_once CHEMIN.'admin/includes.inc';
include_once CHEMIN.'inc/funcarch.php';

/// nombre d'itération en phase d'archivage des fichiers ($followup = vide)
$delta1 = 200;
/// nombre d'itération en contrôle d'intégrité
$delta2 = 1000;

$archdate = import('archdate');
$archtyps = import('archtyps');
$numdb    = import('numdb');
$followup = import('followup');

function ArchiveOneFile($fic1, $fic2) {
    $archiveok = false;
    if (@copy($fic1, $fic2)) {
        SetChmod($fic1);
        $archiveok = @unlink($fic1);
    }
    return $archiveok;
}

function ArchiveFile($id, $annee, $typ) {
    $archivedok = false;
	$fileid     = TestFileId($id);
    if (FileDBExist(DBBASE.$fileid.INCEXT)) {
        $archivedok = ArchiveOneFile(DBBASE.$fileid.INCEXT, ARCHDBBASE.$fileid.INCEXT);
        if ($archivedok) {
            $db = array($typ, $id, 'a', $annee);
            AppendDBFields(DOCIDARCH, $db);
        }
        if (FileDBExist(DBCOUNT.$fileid.DBEXT)) {
            ArchiveOneFile(DBCOUNT.$fileid.DBEXT, ARCHDBCOUNT.$fileid.DBEXT);
        }
        if (FileDBExist(DBIPBASE.$fileid.DBEXT)) {
            ArchiveOneFile(DBIPBASE.$fileid.DBEXT, ARCHDBIPBASE.$fileid.DBEXT);
        }
    }
    return $archivedok;
}

@set_time_limit(0);

$archdate = substr($archdate.'0000', 0, 12);
$typs     = explode(' ', $archtyps);

if (empty($followup)) {
	$dbwork = array();
	if (in_array(TYP_BLOG, $typs) && $archdate > ReadCounter(DBBLOGARCHDATE)) {
		$dbw  = ReadDBFields(DBBLOG);
		$dbbl = array();
		foreach ($dbw as $arch) {
			$datearch = substr($arch[5], 0, 12);
			if ($archdate >= $datearch && !empty($datearch)) $dbbl[] = array($datearch, $arch[4], '1', $arch[4], $arch[7]);
		}
		$dbw = ReadDBFields(DBREBLOG);
		foreach ($dbw as $arch) {
			if (!empty($arch[0])) {
				foreach ($dbbl as $db)
					if ($db[1] == $arch[1])	$dbwork[] = array(substr($arch[5], 0, 12), $arch[0], '1', $arch[0], $arch[7]);
			}
		}
		$dbwork = array_merge($dbwork, $dbbl);
	}
	if (in_array(TYP_NEWS, $typs) && $archdate > ReadCounter(DBNEWSARCHDATE)) {
		$dbw = ReadDBFields(DBNEWS);
		foreach ($dbw as $arch) {
			$datearch = substr($arch[5], 0, 12);
			if ($archdate >= $datearch && !empty($datearch)) $dbwork[] = array($datearch, $arch[4], '1', $arch[4], $arch[7]);
		}
	}
	if (in_array(TYP_AGENDA, $typs) && $archdate > ReadCounter(DBAGENDAARCHDATE)) {
		$dbw = ReadDBFields(DBAGENDA);
		foreach ($dbw as $arch) {
			$datearch = substr($arch[0], 0, 12);
			if ($archdate >= $datearch && !empty($datearch)) $dbwork[] = array($datearch, $arch[4], '1', $arch[4], $arch[7]);
		}
	}
	if (in_array(TYP_FORUM, $typs) && $archdate > ReadCounter(DBFORUMARCHDATE)) {
		$dbw = ReadDBFields(DBTHREAD);
		foreach ($dbw as $arch) {
			$datearch = substr($arch[0], 0, 12);
			if ($archdate >= $datearch && !empty($datearch)) $dbwork[] = array($datearch, $arch[1], $arch[2], $arch[3], $arch[10]);
		}
	}
    @rsort($dbwork);
}

$args = '&archtyps='.$archtyps.'&archdate='.$archdate;

echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>'.$admin[577].'</title>
<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />
'.JavascriptFile(CHEMIN.'inc/hpage.js').'
</head>
<body style="background-color: #E7E1EF; overflow: hidden; margin:0 5px;">
<fieldset>';
if (empty($followup)) {
    $nbcheck = $delta1;
	$nbdb    = count($dbwork);
	$numdb   = empty($numdb) ? 0 : $numdb;
    echo '
  <p style="text-align:center;">'.$admin[577].' => '.$numdb.' / '.$nbdb.'</p>
  <div style="color: #000; height: 200px; overflow: auto;">
    <br />';
    while ($nbcheck > 0 && $numdb < $nbdb) {
		echo '&nbsp;&nbsp;- '.$admin[585].' : '.$dbwork[$numdb][3].'<br />';
		ArchiveFile($dbwork[$numdb][3], substr($dbwork[$numdb][0], 0, 4), $dbwork[$numdb][4]);
		$nbcheck--;
		$numdb++;
    }
    if ($numdb < $nbdb) {
        $nextstep = 'PopupWindow(\'archbatch.php?lng='.$lng.$args.'&numdb='.$numdb.'\',\'archbatch\',480,360,\'no\',\'no\')';
    }
    else {
        $nextstep = 'PopupWindow(\'archbatch.php?lng='.$lng.$args.'&followup=1\',\'archbatch\',480,360,\'no\',\'no\')';
    }
	echo '
  </div>';
}
elseif ($followup == 1) {
    echo '
  <p style="text-align:center;">'.$admin[587].'</p><br /><br />';
    $dbworkid  = ReadDBFields(DOCIDARCH);
	$dbworknew = array();
	foreach ($dbworkid as $db) {
		if (!isset($db[3])) {
			include ARCHDBBASE.TestFileID($db[1]).INCEXT;
			$dbworknew[] = array($db[0], $db[1], $db[2], substr($creadate, 0, 4));
		}
		else $dbworknew[] = $db;
	}
    @sort($dbworknew);
    WriteDBFields(DOCIDARCH, $dbworknew);
    $nextstep = 'PopupWindow("archbatch.php?lng='.$lng.$args.'&followup=2","archbatch",480,360,"no","no")';
}
elseif ($followup == 2) {
    echo '
  <p style="text-align:center;">'.$admin[592].'</p>
  <br /><br />';
	if (in_array(TYP_BLOG, $typs)) {
		UpdateDBdtbArch(TYP_BLOG, DBBLOGARCH);
		UpdateDBreblogArch();
	}
	if (in_array(TYP_NEWS, $typs)) {
		UpdateDBdtbArch(TYP_NEWS, DBNEWSARCH);
	}
	if (in_array(TYP_AGENDA, $typs)) {
		UpdateDBdtbArch(TYP_AGENDA, DBAGENDAARCH);
	}
	if (in_array(TYP_FORUM, $typs)) {
		UpdateDBforumArch();
	}
    $nextstep = 'PopupWindow("archbatch.php?lng='.$lng.$args.'&followup=3","archbatch",480,360,"no","no")';
}
else {
    echo '
  <p style="text-align:center;">'.$admin[589].'</p>
  <br /><br />';
    if (in_array(TYP_BLOG, $typs)) WriteCounter(DBBLOGARCHDATE, $archdate);
    if (in_array(TYP_NEWS, $typs)) WriteCounter(DBNEWSARCHDATE, $archdate);
    if (in_array(TYP_AGENDA, $typs)) WriteCounter(DBAGENDAARCHDATE, $archdate);
    if (in_array(TYP_FORUM, $typs)) WriteCounter(DBFORUMARCHDATE, $archdate);
    $typ = in_array(TYP_FORUM, $typs) ? TYP_FORUM : '';
    $nextstep = 'PopupWindow("dbbatch.php?lng='.$lng.'&typ='.$typ.'&checkquiet=1&delta='.$delta2.'","dbbatch",400,300,"no","no")';
}
echo '
  <p style="text-align:right;"><a href="#" onclick="window.close();"><b>'.$admin[458].'&nbsp;</b></a></p>
</fieldset>';
if ($nextstep != '') {
    echo BeginJavascript().$nextstep.EndJavascript();
}
echo '
</body>
</html>';
?>