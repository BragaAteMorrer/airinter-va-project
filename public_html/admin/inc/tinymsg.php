<?php
/*******************************************************************************
 *   Admin general tinymsg
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2024 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.19 (August 26, 2024) : correction
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '../../');
include CHEMIN.'admin/includes.inc';
CreateDir(TEMPREP);

if (FileDBExist('../'.REDACREP.$userprefs[1].INCEXT)) {
    $wri = $userprefs[1];
    include('../'.REDACREP.$wri.INCEXT);
    $mdp = md5($drtuser[38]);
}
else {
    $wri = 'admin';
    include(CHEMIN.'admin/mdp.php');
}
$admcookie = isset($_COOKIE[ADMP_COOKIE]) ? $_COOKIE[ADMP_COOKIE] : '';
$strlen    = substr($admcookie, 0, 2);
if (empty($admcookie) || substr($admcookie], 2, intval($strlen)) != abs(crc32($mdp))) {
    die('Procédure non autorisée -- illegal process');
}

$lsn    = import('lsn');
$redige = import('redige');

if (!isset($lsn)) {
    die('Illegal action');
}

$dbusers = array();
$j = 0;
$file_users = opendir(MSGREP);
while ($nomfichier = readdir($file_users)) {
    if ('.dtb' == $nomfichier) {
        @unlink(MSGREP.$nomfichier);
        continue;
    }
    if (substr($nomfichier,-3) == 'dtb' && $nomfichier != $userprefs[1].DBEXT) {
        $dbusers[$j][0] = str_replace(substr($nomfichier,-4), '', $nomfichier);
        $j++;
    }
}
closedir($file_users);
@sort($dbusers);

echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />
<title>'.$admin[691].'</title>
'.JavascriptFile(CHEMIN.'inc/hpage.js').'
</head>
<body style="overflow: hidden;margin:5px;">
<fieldset>
<br />
<p style="text-align:center;">'.$admin[692].'</p>
<p style="text-align:center;">'.Min($lsn+1,count($dbusers)).' / '.count($dbusers).' '.$admin[516].'</p>';

if (FileDBExist(MSGREP.$userprefs[1].DBEXT)) {
    $dbmsg = ReadDBFields(MSGREP.$userprefs[1].DBEXT);
}
if ($redige == 1) {
    include(TEMPREP.'sendtinymsg.inc');
    if($tinymsg2 != '') {
        $tinymsgsend = $tinymsg1.'<br /><hr /><br />'.$tinymsg2;
    }
    else {
        $tinymsgsend = $tinymsg1;
    }
    $dbmsg = array();
    $dbmsg[0] = $admin[690];
    $dbmsg[1] = GetCurrentDateTime();
    $dbmsg[2] = RemoveConnector(stripslashes($tinymsgsend));
    $dbmsg[3] = 'lu';
    $dbmsg[4] = 'send';
    $dbmsg[5] = RemoveConnector(stripslashes($ancienmsg));
    $dbmsg[6] = $anciendate;
    $dbmsg[7] = $userprefs[8];
    AppendDBFields(MSGREP.$userprefs[1].DBEXT,$dbmsg);
}
if ($lsn < count($dbusers)) {
    include(TEMPREP.'sendtinymsg.inc');
    if($tinymsg2 != '') {
        $tinymsgsend = $tinymsg1.'<br /><hr /><br />'.$tinymsg2;
    }
    else {
        $tinymsgsend = $tinymsg1;
    }
    $dbmsg = array(
        $userprefs[1],
        GetCurrentDateTime(),
        RemoveConnector(stripslashes($tinymsgsend)),
        'new',
        '',
        RemoveConnector(stripslashes($ancienmsg)),
        $anciendate,
        $userprefs[8]);
    AppendDBFields(MSGREP.$dbusers[$lsn][0].DBEXT,$dbmsg);
    $lsn++;
    $nextstep = 'PopupWindow(\'tinymsg.php?lng='.$lng.'&lsn='.$lsn.'\',\'tinymsg\',400,250,\'no\',\'no\');';
} else {
    echo '
<hr />
<p style="text-align:center;"><b>'.$admin[693].'</b></p>
<br />';
    $nextstep = '';
    unlink(TEMPREP.'sendtinymsg.inc');
}
echo '
</fieldset>';
if ($nextstep != '') {
    echo BeginJavascript().$nextstep.EndJavascript();
}
echo '
</body>
</html>';
?>