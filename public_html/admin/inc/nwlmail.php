<?php
/*******************************************************************************
 *   Send Newsletter
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
 * v6.00.11 (March 09, 2023) : correction $bodycorps
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '../../');
include CHEMIN.'admin/includes.inc';
CreateDir(TEMPREP);

$lng      = import('lng');
$lsn      = import('lsn', '', true, 0);
$id       = import('id');
$selgroup = import('selgroup');
$npp      = import('npp', '', true, $lsn);
$dep      = import('dep', '', true, 1);
$from     = import('from');

if (is_file(CHEMIN.'inc/lang/'.$lng.'-admin'.INCEXT))
    include CHEMIN.'inc/lang/'.$lng.'-admin'.INCEXT;
else
    die('BAD Language name !');

$lst = ReadDBFields(TEMPREP.'nwltemp.dtb');

echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />
<title>'.$admin[546].'</title>
'.JavascriptFile(CHEMIN.'inc/hpage.js').'
</head>
<body bgcolor="#FFFFFF" style="margin:5px; overflow: hidden;">
<fieldset style="width:100%;margin:auto;max-width:300px;">';
if ($lsn != -1) {
    echo '
<p style="width:100%;margin:auto;text-align:center;">'.$admin[524].'</p>
<p style="width:100%;margin:auto;text-align:center;">'.Min($lsn+1,count($lst)).' / '.count($lst).' '.$admin[516].'</p>';
}
if ($lsn < count($lst)) {
	$nom38 = getLabel($nom[38]);
	$nom38 = mb_substr($nom38, 0, 1, $charset) == '§' ? mb_substr($nom38, 1, mb_strlen($nom38 ,$charset) ,$charset) : $nom38;
	$nom39 = getLabel($nom[39]);
	$nom39 = mb_substr($nom39, 0, 1, $charset) == '§' ? mb_substr($nom39, 1, mb_strlen($nom39 ,$charset) ,$charset) : $nom39;
    ReadDoc($id);
	$to = $lst[$lsn][1];
	if ($lst[$lsn][2] == $lang[0]) {
		include CHEMIN.'inc/lang/'.$lang[0].'-admin'.INCEXT;
		$sujet = strip_tags($fieldb1);
		$bodycorps  = '<p style="text-align:center;">'.$admin[1709].'<br />';
		$bodycorps .= '<a href="'.$site[3].$site['NL'].'.php?lng='.$lang[0].'&pg='.$id.'&nlpseudo='.$lst[$lsn][0].'&nlgrp='.$selgroup.'">';
		$bodycorps .= $site[3].$site['NL'].'.php?lng='.$lang[0].'&pg='.$id.'&nlpseudo='.$lst[$lsn][0].'&nlgrp='.$selgroup.'</a></p>';
		$bodycorps .= '<fieldset style="padding:15px;"><legend> '.$nom38.' </legend>'.ForceToAbsolute($fieldc1).'</fieldset>';
		$bodycorps .= '<p style="text-align:center;font-size:0.75em;">'.$admin[549].'<br />';
		$bodycorps .= '<a href="'.$site[3].$site['NL'].".php?lng=".$lst[$lsn][2]."&action=unsub&nlsecure=on&nlpseudo=".$lst[$lsn][0]."&nlmail=".$lst[$lsn][1].'">';
		$bodycorps .= $site[3].$site['NL'].".php?lng=".$lst[$lsn][2]."&action=unsub&nlsecure=on&nlpseudo=".$lst[$lsn][0]."&nlmail=".$lst[$lsn][1]."</a></p>";
	}
	else {
		include CHEMIN.'inc/lang/'.$lang[1].'-admin'.INCEXT;
		$sujet = strip_tags($fieldb2);
		$bodycorps  = '<p style="text-align:center;">'.$admin[1709].'<br />';
		$bodycorps .= '<a href="'.$site[3].$site['NL'].'.php?lng='.$lang[1].'&pg='.$id.'&nlpseudo='.$lst[$lsn][0].'&nlgrp='.$selgroup.'">';
		$bodycorps .= $site[3].$site['NL'].'.php?lng='.$lang[1].'&pg='.$id.'&nlpseudo='.$lst[$lsn][0].'&nlgrp='.$selgroup.'</a></p>';
		$bodycorps .= '<fieldset style="padding:15px;"><legend> '.$nom39.' </legend>'.ForceToAbsolute($fieldc2).'</fieldset>';
		$bodycorps .= '<p style="text-align:center;font-size:0.75em;">'.$admin[549].'<br />';
		$bodycorps .= '<a href="'.$site[3].$site['NL'].".php?lng=".$lst[$lsn][2]."&action=unsub&nlsecure=on&nlpseudo=".$lst[$lsn][0]."&nlmail=".$lst[$lsn][1].'">';
		$bodycorps .= $site[3].$site['NL'].".php?lng=".$lst[$lsn][2]."&action=unsub&nlsecure=on&nlpseudo=".$lst[$lsn][0]."&nlmail=".$lst[$lsn][1]."</a></p>";
	}
	eMailHtmlTo($sujet, $bodycorps, $to, $from);
	$lsn++;
    if ($lsn < count($lst) && !empty($npp) && !empty($dep) && $lsn%$npp == 0 ) sleep($dep);
	$nextstep = "PopupWindow(\"nwlmail.php?lng=".$lng."&id=".$id."&selgroup=".$selgroup."&from=".$from."&lsn=".$lsn."&npp=".$npp."&dep=".$dep."\",\"nwlmail\",400,225,\"no\",\"no\")";
}
else {
    echo '
<hr />
<p style="width:100%;margin:auto;text-align:center;"><b>'.$admin[522].'</b></p>
<br />';
    $nextstep = "";
	@unlink(TEMPREP.'nwltemp.dtb');
}
echo '
</fieldset>';
if ($nextstep != "") {
    echo BeginJavascript().$nextstep.EndJavascript();
}
echo '
</body>
</html>';
?>
