<?php
/*******************************************************************************
 *   Control members access
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

if (stristr($_SERVER['SCRIPT_NAME'], 'members.inc')) {
    header('location:../index.php');
    die();
}

if (isset($section_index) && isset($section_name)) {
    if ($userprefs[1] == '' && $members[$section_index] == 'on') {
        include 'inc/hpalm.inc';
        echo '
<div style="text-align:center">
    <div style="font-weight:bold">'.$section_name.'</div>
    <hr />
    <div>'.$web[342].'</div>
    <br>
    <div>
      <a href="'.CHEMIN.'user.php?lng='.$lng.'">'.$web[160].'</a>
    </div>
    <br>
</div>';
        include 'inc/bpalm.inc';
        $section_access = FALSE;
    } else {
        $section_access = TRUE;
    }
} else {
    $section_access = TRUE;
}
?>