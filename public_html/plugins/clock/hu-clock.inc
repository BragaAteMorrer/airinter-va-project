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

if (stristr($_SERVER['SCRIPT_NAME'], 'hu-clock.inc')) {
    header('location:index.php');
    die();
}

$clock[1] = "Admin - Óra minta plugin";
$clock[2] = "A GuppY alapcsomag néhány nagyon egyszerû plugint tartalmaz  példaként (a fejlesztõknek), és mint díszítõelemet a GuppY webhelyhez (a webmestereknek).";
$clock[3] = "Az <b>Óra</b> plugin egy digitális lineáris órát mutat, ami Javascript nyelven lett megírva.";
$clock[4] = "Elérhetõsége : ";
$clock[5] = "Alapvetõen az &lt;A&gt; HTML taggal :";
$clock[6] = "Próbáld ki :";
$clock[7] = "Óra";
$clock[8] = "Megjegyzés :<br />- minden, ami [ és ] jelek között van, önállóan választható<br />- ki lehet választani a nyelvet is, az URL végén az ?lng=xx hozzáadásával (ahol xx=fr vagy en, vagy bármely rendelkezésre álló lefordított nyelv, ha az kézzel be van írva a clock.php fájlba, és az admin-ban clock.inc és a plugin.inc fájlba, mint if feltétel); például a clock.php?lng=en angol nyelven mutatja az óra plugin szövegeit.";
?>