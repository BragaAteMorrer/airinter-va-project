<?php
/*******************************************************************************
 *   Download (popup) for Mobile
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

if ($serviz[35] != 'on') {
    exit($web[143]);
}

$section_index = 7;
$i = $lng == $lang[0] ? 0 : 10;
$nom_dn = getLabel($nom[$i+1]);
$section_name = $nom_dn;
include 'inc/members.inc';
if ($section_access) {

    if (!empty($pg)) {
        ReadDoc($pg);
        if (!isAccessGranted($fieldmod)) {
			header('HTTP/1.0 403 Forbidden');
        }
		if (isAuthorizedPublication($creadate)) {
			if ($lng == $lang[0]) {
				$nomdw = $fieldb1;
				$fdw   = explode(CONNECTOR, $fieldd1);
				$urldw = $fdw[0];
				$chk   = !empty($fdw[1])? $fdw[1]: '';
			}
			else {
				$nomdw = $fieldb2;
				$fdw   = explode(CONNECTOR, $fieldd2);
				$urldw = $fdw[0];
				$chk   = !empty($fdw[1])? $fdw[1]: '';
			}
			if (substr($urldw, 0, 5) == 'file/') {
				$urldw = CHEMIN.$urldw;
			}
			$nomfl = basename ($urldw);
			$sizefl = FileSizeInKb($urldw);
			if ($sizefl === FALSE) {
				$sizefl = $web[37];
			}
			else {
				$sizefl .= ' '.$web[28];
			}
			$modfl = @filemtime($urldw);
			if ($modfl === FALSE) {
				$modfl = $web[37];
			}
			else {
				$modfl = FormatDateStamp($modfl);
			}
			$dnldfl = @filemtime(DBBASE.$pg.DBEXT);
			if ($dnldfl === FALSE) {
				$dnldfl = $web[37];
			}
			else {
				$dnldfl = FormatDateStamp($dnldfl);
			}
			$dnldcounter = UpdateDocCounter($pg);
			$txtcount = $dnldcounter.' '.($dnldcounter <= 1 ? $web[188] : $web[189]);
			include 'inc/hpalm.inc';
			echo '
<div style="text-align:center">
  <b><a href="download.php?lng='.$lng.'&amp;id='.$id.'">'.$nom_dn.'</a> - '.$nomdw.'</b>
</div>
<hr />
'.$web[218].'&nbsp;<b>'.$nomdw.'</b><br />
'.$web[194].'&nbsp;<b><a href="'.$urldw.'">'.$nomfl.'</a></b><br />
'.$web[263].'&nbsp;'.$modfl.'<br />
'.$web[199].'&nbsp;'.$txtcount.'<br />
'.$web[197].'&nbsp;'.$dnldfl.'<br />
'.$web[195].'&nbsp;'.$sizefl.'<br />';
			include 'inc/bpalm.inc';
		}
    }
}
?>