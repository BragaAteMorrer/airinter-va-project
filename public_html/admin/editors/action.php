<?php
/*******************************************************************************
 *   Upload security by Djchouix
 *******************************************************************************
 *   GuppY PHP Script - version 6
 *   CeCILL Copyright (C) 2004-2022 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = http://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V5 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.06 (January 13, 2022) : update ckeditor
 ******************************************************************************/
 
if (FileDBExist(CHEMIN.'admin/'.REDACREP.$userprefs[1].INCEXT)) {
    $wri = $userprefs[1];
}
else {
    $wri = "admin";
}
?>