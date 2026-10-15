<?php
/*******************************************************************************
 *   Admin Statistiques
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
 * v6.00.19 (December 15, 2024) : Tohelp modify and views statistics
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'statsvw.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[32] == '') {
        $nomzone = $admin[212];
        include 'access.inc';
        exit();
    }
}

include CHEMIN.'inc/statcalc.inc';
$topmess  = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[976]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[976], '100%');

echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=1010&mnuid=937&tconfig=0#z2').$admin[977].'</p>
<hr />';

echo '
<fieldset>'.legend($admin[219]);
if (strpos($endurl, '/admin/') !== false) include_once CHEMIN.INCREP.'lang/'.$lng.'-web'.INCEXT;
include CHEMIN.'inc/statshow.inc';
echo '
</fieldset>';

btable();
include CHEMIN.'admin/bpage.inc';
?>