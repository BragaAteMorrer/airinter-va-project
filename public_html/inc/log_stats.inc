<?php
/*******************************************************************************
 *   Counter Log
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
 * v6.00.03 (August 10, 2021) : adaptation to php 8
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'log_stats.inc')) {
    header('location:../index.php');
    die();
}

function log_test() {
    if (is_file(DBLOGDATE)) {
        $last = ReadCounter(DBLOGDATE);
        return ($last != date('d/m/y'));
    } else {
        return TRUE;
    }
}

function log_stats() {
    WriteCounter(DBLOGDATE, date('d/m/y'));
    $s = ReadCounter(DBSTATS);
    $fhandle = fopen(DBLOGSTATS, 'a');
    fputs($fhandle, date('d/m/y;').$s."\n");
    fclose($fhandle);
}

if (log_test()) log_stats();
?>
