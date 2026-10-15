<?php
/*******************************************************************************
 *   Plugin Speed
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

if (stristr($_SERVER['SCRIPT_NAME'], 'en-speed.inc')) {
  header('location:index.php');
  die();
}

$speed[1] = 'Admin - Connection Speed sample Plugin';
$speed[2] = 'GuppY comes with a few very simple plugins, as examples (for developers) and as embellishment of your GuppY website (for webmasters).';
$speed[3] = 'This test calculates the average time needed to download one image from your website to your PC.';
$speed[4] = 'Accessed by :';
$speed[5] = 'This plugin registers to the upper modules bar.';
$speed[6] = 'Try it :';
$speed[7] = 'Connection Speed';
$speed[8] = 'Register Connection Speed Plugin in the modules bar : ';
$speed[9] = 'Estimation being processed...';
$speed[10] = 'Connection not possible';
$speed[11] = '28k Modem Connection';
$speed[12] = '56k Modem Connection';
$speed[13] = 'High Speed Connection';
$speed[14] = 'Local Connection';
$speed[15] = 'Kb/sec';
$speed[16] = 'Estimate the connection speed between your PC and this site';
$speed[17] = '<p>Click on the button to evaluate the speed of your connection between your PC and this website.</p><p>This test only evaluates by calculating the average rate to load an image from the site to your PC.</p><p>The result therefore measures the speed of loading an image at a given time. Obviously, if other loading are in progress, the measurement is distorted.</p>';
$speed[18] = 'Launch the evaluation of the connection speed';
$speed[19] = 'Result :';
$speed[20] = 'Uninstall';
$speed[21] = '<strong>WARNING ! The uninstall is final.</strong><br/>';
?>