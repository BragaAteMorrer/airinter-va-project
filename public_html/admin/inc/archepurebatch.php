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
 * v6.00.19 (August 26, 2024) : Archive modify and Tohelp modify
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '../../');
include_once CHEMIN.'admin/includes.inc';
include_once CHEMIN.'inc/funcarch.php';

/// nombre d'itération en phase d'archivage des fichiers ($followup = vide)
$delta1 = 200;
/// nombre d'itération en contrôle d'intégrité
$delta2 = 1000;
//log 
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');
$nomficlog = CHEMIN.'data/log/archiepurebatch'.'_'.$dateheureminiutesecl.'.log';

$archepurgedate = import('archepurgedate');
$archepurgetyps = import('archepurgetyps');
$numdb    = import('numdb');
$followup = import('followup');
function ArchiveOneFile($fic1) {
    $archepurgeiveok = false;
	SetChmod($fic1);
	$archepurgeiveok = @unlink($fic1);
    return $archepurgeiveok;
}

function ArchiveFile($id, $annee, $typ) {
	global $nomficlog;
    $archepurgeivedok = false;
	$fileid     = TestFileId($id);
    if (FileDBExist(ARCHDBBASE.$fileid.INCEXT)) {
        $archepurgeivedok = ArchiveOneFile(ARCHDBBASE.$fileid.INCEXT);
    }
	if (FileDBExist(ARCHDBCOUNT.$fileid.DBEXT))
	{
        $archepurgeivedok = ArchiveOneFile(ARCHDBCOUNT.$fileid.DBEXT);
    }
	if (FileDBExist(ARCHDBIPBASE.$fileid.DBEXT)) {
		$archepurgeivedok = ArchiveOneFile(ARCHDBIPBASE.$fileid.DBEXT);
	}	
    return $archepurgeivedok;
}

@set_time_limit(0);

$archepurgedate = substr($archepurgedate.'0000', 0, 12);
$typs     = explode(' ', $archepurgetyps);

if (empty($followup)) {
	$dbwork = array();
	$dbworkaepurer = array();
	if (in_array(TYP_BLOG, $typs) && $archepurgedate > ReadCounter(DBBLOGARCHDATE)) {
		$dbw  = ReadDBFields(DBBLOGARCH);
		$dbbl = array();
		foreach ($dbw as $archepurge) {
			$datedocumentarch = substr($archepurge[5], 0, 12);
			if ($datedocumentarch >= $archepurgedate && !empty($datedocumentarch)) 
			{
				$dbwork[] = array($datedocumentarch, $archepurge[4], '1', $archepurge[4], $archepurge[7]);
			}
			else
			{
				$dbworkaepurer[] = array($datedocumentarch, $archepurge[4], '1', $archepurge[4], $archepurge[7]);
			}
			if ($archepurgedate >= $datedocumentarch && !empty($datedocumentarch))
				$dbbl[] = array($datedocumentarch, $archepurge[4], '1', $archepurge[4], $archepurge[7]);
		}
		$dbw = ReadDBFields(DBREBLOGARCH);
		foreach ($dbw as $archepurge) {
			if (!empty($archepurge[0])) {
				foreach ($dbbl as $db)
					if ($db[1] == $archepurge[1])	$dbworkaepurer[] = array(substr($archepurge[5], 0, 12), $archepurge[0], '1', $archepurge[0], $archepurge[7]);
			}
		}
		$dbwork = array_merge($dbwork, $dbbl);
	}
	if (in_array(TYP_NEWS, $typs) && $archepurgedate > ReadCounter(DBNEWSARCHDATE)) {
		$dbw = ReadDBFields(DBNEWSARCH);
		foreach ($dbw as $archepurge) {
			$datedocumentarch = substr($archepurge[5], 0, 12);
			if ($datedocumentarch >= $archepurgedate && !empty($datedocumentarch)) 
			{
				$dbwork[] = array($datedocumentarch, $archepurge[4], '1', $archepurge[4], $archepurge[7]);
			}
			else
			{
				$dbworkaepurer[] = array($datedocumentarch, $archepurge[4], '1', $archepurge[4], $archepurge[7]);
			}
		}
	}
	if (in_array(TYP_AGENDA, $typs) && $archepurgedate > ReadCounter(DBAGENDAARCHDATE)) {
		$dbw = ReadDBFields(DBAGENDAARCH);
		foreach ($dbw as $archepurge) {
			$datedocumentarch = substr($archepurge[0], 0, 12);
			if ($datedocumentarch >= $archepurgedate && !empty($datedocumentarch)) 
			{
				$dbwork[] = array($datedocumentarch, $archepurge[4], '1', $archepurge[4], $archepurge[7]);
			}
			else
			{
				$dbworkaepurer[] = array($datedocumentarch, $archepurge[4], '1', $archepurge[4], $archepurge[7]);
			}
		}
	}
	if (in_array(TYP_FORUM, $typs) && $archepurgedate > ReadCounter(DBFORUMARCHDATE)) {
		$dbw = ReadDBFields(DBTHREADARCH);
		foreach ($dbw as $archepurge) {
			$datedocumentarch = substr($archepurge[0], 0, 12);
			if ($datedocumentarch >= $archepurgedate && !empty($datedocumentarch)) 
			{
				$dbwork[] = array($datedocumentarch, $archepurge[4], '1', $archepurge[4], $archepurge[7]);
			}
			else
			{
				$dbworkaepurer[] = array($datedocumentarch, $archepurge[4], '1', $archepurge[4], $archepurge[7]);
			}
//			if ($archepurgedate >= $datedocumentarch && !empty($datedocumentarch)) $dbwork[] = array($datedocumentarch, $archepurge[1], $archepurge[2], $archepurge[3], $archepurge[10]);
		}
	}
    @rsort($dbwork);
	WriteDBFields(DOCIDARCH, $dbwork);
	@rsort($dbworkaepurer);
}

$args = '&archepurgetyps='.$archepurgetyps.'&archepurgedate='.$archepurgedate;

echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>'.$admin[1844].'</title>
<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />
'.JavascriptFile(CHEMIN.'inc/hpage.js').'
</head>
<body style="background-color: #E7E1EF; overflow: hidden; margin:0 5px;">
<fieldset>';
if (empty($followup)) {
    $nbcheck = $delta1;
	$nbdb    = count($dbworkaepurer);
	$numdb   = empty($numdb) ? 0 : $numdb;
    echo '
  <p style="text-align:center;">'.$admin[1844].' => '.$numdb.' / '.$nbdb.'</p>
  <div style="color: #000; height: 200px; overflow: auto;">
    <br />';
    while ($nbcheck > 0 && $numdb < $nbdb) {
		echo '&nbsp;&nbsp;- '.$admin[1855].' : '.$dbworkaepurer[$numdb][3].'<br />';
		ArchiveFile($dbworkaepurer[$numdb][3], substr($dbworkaepurer[$numdb][0], 0, 4), $dbworkaepurer[$numdb][4]);
		$nbcheck--;
		$numdb++;
    }
    if ($numdb < $nbdb) {
        $nextstep = 'PopupWindow(\'archepurebatch.php?lng='.$lng.$args.'&numdb='.$numdb.'\',\'archepurebatch\',480,360,\'no\',\'no\')';
    }
    else {
        $nextstep = 'PopupWindow(\'archepurebatch.php?lng='.$lng.$args.'&followup=1\',\'archepurebatch\',480,360,\'no\',\'no\')';
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
    $nextstep = 'PopupWindow("archepurebatch.php?lng='.$lng.$args.'&followup=2","archepurebatch",480,360,"no","no")';
}
elseif ($followup == 2) {
    echo '
  <p style="text-align:center;">'.$admin[1844].'</p>
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
    if (in_array(TYP_BLOG, $typs)) WriteCounter(DBBLOGARCHDATEEPUR, $archepurgedate);
    if (in_array(TYP_NEWS, $typs)) WriteCounter(DBNEWSARCHDATEEPUR, $archepurgedate);
    if (in_array(TYP_AGENDA, $typs)) WriteCounter(DBAGENDAARCHDATEEPUR, $archepurgedate);
    if (in_array(TYP_FORUM, $typs)) WriteCounter(DBFORUMARCHDATEEPUR, $archepurgedate);
    $nextstep = 'PopupWindow("archepurebatch.php?lng='.$lng.$args.'&followup=3","archepurebatch",480,360,"no","no")';
}
else
{
    echo '
  <p style="text-align:center;">'.$admin[1844].'</p>
  <br /><br />';
	echo '
  <p style="text-align:center;">'.$admin[1854].'</p>
  <br /><br />';
    $typ = in_array(TYP_FORUM, $typs) ? TYP_FORUM : '';
    $nextstep = 'PopupWindow("dbbatcharchives.php?lng='.$lng.'&typ='.$typ.'&checkquiet=1&delta='.$delta2.'","dbbatcharchives",400,300,"no","no")';
}
echo '
  <p style="text-align:right;"><a href="#" onclick="window.close();"><b>'.$admin[458].'&nbsp;</b></a></p>
</fieldset>';
if (!isset($nextstep)) $nextstep = '';
if ($nextstep != '') {
    echo BeginJavascript().$nextstep.EndJavascript();
}
echo '
</body>
</html>';
?>