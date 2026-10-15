<?php
/*******************************************************************************
 *   Clock Language Strings
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2013 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.00 (December 15, 2019) : initial release
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'en-clock.inc')) {
    header('location:index.php');
    die();
}

$clock[1] = 'Admin - Clock sample Plugin';
$clock[2] = 'GuppY comes with a few very simple plugins, as examples (for developers) and as embellishment of your GuppY website (for webmasters).';
$clock[3] = '<b>Clock</b> shows a live digital watch that was made in Javascript.';
$clock[4] = 'Accessed by : ';
$clock[5] = 'Basic, by the &lt;A&gt; HTML tag :';
$clock[6] = 'Try it :';
$clock[7] = 'Clock';
$clock[8] = 'Notes :<br />- all that is between [ and ] is facultative<br />- you can also choose the language for display by adding ?lng=xx (where xx=fr or en) at the end of the URL, like this: clock.php?lng=en to run Clock in English.';
$clock[9] = 'Uninstall';
$clock[10] = '<strong>WARNING ! The uninstall is final.</strong><br/>';
?>