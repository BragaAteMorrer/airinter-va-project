<?php
/*******************************************************************************
 *   Maintenance
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

function ReadFullDB($fic) {
    $DataDB = Array();
    if (is_file($fic)) {
        $DataDB = implode('', file($fic));
    }
    return $DataDB;
}

include 'inc/constantes.php';
include CONFIG;
$charset = 'UTF-8';
$z2      = '';
$lng     = isset($_GET['lng']) ? strip_tags($_GET['lng']) : $lang[0];
if (empty($serviz[44])) header('location: index.php?lng='.$lng);
$lng      = empty($lng) ? $lang[0] : (!in_array($lng, $lang) ? $lang[0] : $lng);
$aml0     = 'articles.php?lng='.$lng.'&pg='.$serviz[177].'&tconfig='.$serviz[178];
$aml1     = $lng.'-'.$urlrw[20].'-102-'.$serviz[177].'-'.$serviz[178];
$aml      = '<a href="'.(!empty($serviz[177]) ? CHEMIN.($site['URLR'] == 'on' ? $aml1 : $aml0).$z2 : '#').'" title="">';
$wbm0     = $site['PG'].'.php?lng='.$lng.'&typ='.TYP_MAIL;
$wbm1     = $lng.'-'.$urlrw[14].'-'.TYP_MAIL;
$wbm      = '<a href="'.CHEMIN.($site['URLR'] == 'on' ? $wbm1 : $wbm0).$z2.'" title="">';
$web[691] = 'Site en maintenance';
$web[692] = '<p>Site en maintenance pour quelques minutes.</p><p>...</p><p>Nous sommes désolés pour la gêne occasionnée, à bientôt.</p>';
$title0   = ' - '.$web[691];
$texte0   = ' - '.$web[692];
$title1   = $texte1 = '';
$web[691] = 'Site under maintenance';
$web[692] = '<p>This site is currently under maintenance.</p><p>...</p><p>We apologize for the inconvenience. Please, come back soon...</p>';
$title1   = ' - '.$web[691];
$texte1   = ' - '.$web[692];

echo '<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset='.$charset.'" />
<title>'.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).$title0.$title1.'</title>
<meta http-equiv="Refresh" Content="60; index.php" />
<link rel="stylesheet" href="'.CHEMIN.'inc/img/general/general.css" />
<style>
body {
    background-color: azure;
    text-align: center;
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 20px;
}
a, img, span, table {
    border-style: none;
    vertical-align: middle;
}
</style>
</head>
<body>
  <div style="width: 640px; border: 1px solid; text-align: center; margin: auto;">
    <div style="margin:auto; padding: 25px;">
      <strong>*** &nbsp;&nbsp;'.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'&nbsp;&nbsp; ***</strong>
    </div>
    <div style="display:inline-block; padding:5px; width: 200px; font-size: 0.85em;">
      '.$texte0.'
    </div>
    <div style="display:inline-block; padding: 72px 15px 5px 5px; text-align: center; vertical-align: top; width: 160px;">
      <i class="gen-maintenance"></i>
    </div>
    <div style="display:inline-block; padding: 5px; width: 200px; font-size: 0.85em;">
      '.$texte1.'
    </div>
    <p style="padding: 10px;">
	  <a href="http://www.freeguppy.org">
        <i class="gen-gyslogo"></i>
	  </a>
	  <br />
	  <a class="copyright" href="https://www.freeguppy.org/" title="" target="_blank">
        <i class="gen-gypower"></i>
      </a>
	  <span style="font-size: 0.7em; font-weight: normal;">  &nbsp; © '.MP_COPYRIGHT.' &nbsp;</span>
	  <a class="copyright" href="http://cecill.info/index.'.$lng.'.html" title="" target="_blank">
        <i class="gen-gycecill-'.($lng == 'fr' ? 'f' : 'e').'"></i>
      </a>
    </p>
  </div>
</body>
</html>';
?>
