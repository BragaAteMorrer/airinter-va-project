<?php
/*******************************************************************************
 *   Mind Plugin
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

if (stristr($_SERVER['SCRIPT_NAME'], 'en-mind.inc')) {
    header('location:index.php');
    die();
}

$mind[1] = 'Admin - Riddle sample Plugin';
$mind[2] = 'GuppY comes with a few very simple plugins, as examples (for developers) and as embellishment of your GuppY website (for webmasters).';
$mind[3] = 'I can read in your mind. Don\'t you believe it? Try! You will be amazed...';
$mind[4] = 'Accessed by : ';
$mind[5] = 'A lateral Box, to be configured in';
$mind[6] = 'Try it :';
$mind[7] = 'Riddle';
$mind[8] = 'Riddle Box';
$mind[9] = 'Give it a try!';
$mind[10] = 'The machine that reads in your mind';
$mind[11] = '1. Think about a number with two digits (example: 54)';
$mind[12] = '2. Substract each of these two digits (5 and 4) to the chosen number (example: 54 - 5 - 4 = 45)';
$mind[13] = '3. Look for this result in the following table and memorize the corresponding icon';
$mind[14] = '4. <strong>Concentrate very much on this icon.</strong> Then click on the button and the computer will guess it!';
$mind[15] = 'Guess my thoughts';
$mind[16] = 'Here is the icon that I could read in your mind:';
$mind[17] = 'Guess again my thoughts';
$mind[18] = 'Uninstall';
$mind[19] = '<strong>WARNING ! The uninstall is final.</strong><br/>';
?>