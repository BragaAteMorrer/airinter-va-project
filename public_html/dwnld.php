<?php
/*******************************************************************************
 *   Download (popup)
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
define('NO_CRYPT', true);
include CHEMIN.'inc/includes.inc';
include CHEMIN.'inc/download.inc';
$gydwnl  = new GY_download();
$result  = $gydwnl->DWNL2_ok();
$tconfig = $gydwnl->tconfig;
$topmess = $gydwnl->topmess;
if (!empty($result[0])) {
    include CHEMIN.'inc/hpage.inc';
    if (function_exists('htable1'))
        htable1($topmess, $result[1].$tconfig, '100%');
    else
        htable($topmess, '100%');
    echo $result[2];
    btable();
    include 'inc/bpage.inc';
    exit();
}
$result = $gydwnl->DWNL2_content();
echo $result[0];
?>