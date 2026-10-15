<?php
/*******************************************************************************
 *   Admin Technicals Information
 *******************************************************************************
 *   GuppY PHP Script - version 6.01
 *   CeCILL Copyright (C) 2004-2023 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.0.14 (July 18, 2023) : intégration tarteaucitron et rgpd
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'lancrgpd.inc')) {
    header('location:../index.php');
    die();
}

echo '<script src="'.CHEMIN.'inc/tarteaucitron/tarteaucitron.js"></script>';
include CONFIGREP.'rgpd.js';
