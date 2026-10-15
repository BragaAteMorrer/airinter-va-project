<?php
/*******************************************************************************
 *   Text slideshow
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
define('LSDATAREP', CHEMIN.DATAREP.'other/liquidslider/');

$tconfig = import('tconfig', '', true, 0);
$idls    = import('idls', '', true, 0);

$lsdata  = SelectDBFieldsByField(ReadDBFields(LSDATAREP.'lsindex.dtb'), 0, $idls);
$topmess = $lsdata[0][$lng == $lang[0] ? 2 : 3];
$text    = ReadFullDB(LSDATAREP.'lsconstr_'.$idls.'.inc');

include 'inc/hpage.inc';
if (function_exists('htable1'))
    htable1($topmess, 'LS'.$tconfig, '100%');
else
    htable($topmess, '100%');
if (!empty($text)) echo '
<div class="w-100">'.$text.'</div>';

btable();
include 'inc/bpage.inc';
?>