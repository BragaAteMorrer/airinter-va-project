<?php
/*******************************************************************************
 *   Articles
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
define('CHEMIN', '');
include CHEMIN.'inc/includes.inc';
include CHEMIN.'inc/articles.inc';
$gyart      = new GY_articles();
$result     = $gyart->ART_content();
$userprefs3 = $gyart->userprefs3;
$widepage   = $gyart->widepage;
$tconfig    = $gyart->tconfig;
$topmess    = $gyart->topmess;
if ($gyart->countit === false) {
    if ($site[10] != '') header('location:'.$site[3].'error.php?lng='.$lng.'&err=404');
    else header('HTTP/1.0 404 Not Found');
}
include CHEMIN.'inc/hpage.inc';
if (function_exists('htable1'))
    htable1($gyart->img.'<a href="'.$site[3].$gyart->urltitre.'" title="'.$web[511].'">'.$topmess.'</a>', 'ART'.$tconfig, '100%');
else
    htable($gyart->img.'<a href="'.$site[3].$gyart->urltitre.'" title="'.$web[511].'">'.$topmess.'</a>', '100%');
echo $gyart->txtart2;
btable();
$result = $gyart->ART_react();
if (!is_null($result)) {
    htable($result[0], '100%');
    echo $result[1];
    btable();
}
include 'inc/bpage.inc';
?>