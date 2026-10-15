<?php
/*******************************************************************************
 *   Admin Acces
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

if (stristr($_SERVER["SCRIPT_NAME"], "access.inc")) {
	header("location:../index.php");
	die();
}

include CHEMIN.'admin/hpage.inc';
htable($admin[848], "100%");

echo '
<p style="text-align:center">'.$admin[847].'<b> '.$nomzone.'</b><br /></p>';

btable();
include CHEMIN.'admin/bpage.inc';
?>
