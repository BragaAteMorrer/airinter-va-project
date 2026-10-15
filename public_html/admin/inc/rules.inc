<?php
/*******************************************************************************
 *   Admin Forum rules
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
 * v6.00.19 (August 26, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'rules.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[23] == '') {
        $nomzone = $admin[9];
        include 'access.inc';
        exit();
    }
}
$qtype = TYP_RULES;
$bname = 0;
$bemail = 0;
$bcat = 0;
$btitre = 0;
$bcontenu = 1;
$bnouveau = 0;
$qformonly = 2;
$qtitle = $admin[798];
$qintro = '<br />'.$admin[866].ToHelp($lng, '&pg=1070&mnuid=937&tconfig=0#z2');
$qseeall = '';
$qwrite = '';
$qcat = '';
$qtitre = '';
$qcontenu = $admin[867];
$qnouveau = '';
$dbwork = SelectDBFieldsByStatus(SelectDBFieldsByType(ReadDBFields(DOCID),$qtype),'a');
if (count($dbwork) == 0) {
    include(DBRULES);
    $data[0]  = $qtype;
    $data[1]  = '';
    $data[2]  = 'a';
    $data[3]  = '';
    $data[4]  = '';
    $data[5]  = $user[0];
    $data[6]  = $user[1];
    $data[7]  = '';
    $data[8]  = '';
    $data[9]  = '';
    $data[10] = '';
    $data[11] = stripslashes($rule1);
    $data[12] = stripslashes($rule2);
    $data[13] = '';
    $data[14] = '';
    $id       = ActionOnFields('add', $data);
}
else {
    $id = $dbwork[0][1];
}
include 'inc/admin.inc';
?>
