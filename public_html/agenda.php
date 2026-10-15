<?php
/*******************************************************************************
 *   Agenda
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
 * v6.00.11 (March 09, 2023) : correction event display 
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '');
include CHEMIN.'inc/includes.inc';
include CHEMIN.'inc/agenda.inc';
$gyagv  = new GY_agenda();
$result = $gyagv->AGV_ok($userprefs[1], $lng);
if (is_array($result)) {
    include CHEMIN.'inc/hpage.inc';
    if (function_exists('htable1'))
	{
        htable1($topmess, $result[0].$tconfig, '100%');
	}
    else
        htable($topmess.'mmm', '100%');
    echo $result[1];
    btable();
    include 'inc/bpage.inc';
    exit();
}
if ($result == 1 ) 	$result = $gyagv->AGV_day($lng);
if ($result == 2 || $result == '') $result = $gyagv->AGV_month($lng);

$topmess = $gyagv->topmess;
$tconfig = $gyagv->tconfig;
include CHEMIN.'inc/hpage.inc';
if (function_exists('htable1'))
{
    htable1($topmess, $result[0].$tconfig, '100%');
}
else
    htable($topmess, '100%');
echo $result[1];
btable();
include CHEMIN.'inc/bpage.inc';
?>