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

if (stristr($_SERVER['SCRIPT_NAME'], 'hu-speed.inc')) {
    header('location:index.php');
    die();
}

$speed[1] = "Admin - Kapcsolati sebesség minta plugin";
$speed[2] = "A GuppY alapcsomag néhány nagyon egyszerû plugint tartalmaz példaként (a fejlesztõknek), és mint díszítõelemet a GuppY webhelyhez (a webmestereknek).";
$speed[3] = "Ez a teszt kiszámolja egy kép átlagos letöltési idejét a webhelyedrõl a számítógépedre.";
$speed[4] = "Elérhetõsége :";
$speed[5] = "Ez a plugin az ikonsávba van regisztrálva.";
$speed[6] = "Próbáld ki :";
$speed[7] = "Sebességmérés";
$speed[8] = "Sebességmérés plugin regisztrálása az ikonsávba : ";
$speed[9] = "A becslés számítása elkezdõdött...";
$speed[10] = "Nem lehetséges a kapcsolat";
$speed[11] = "28k Modem kapcsolat";
$speed[12] = "56k Modem kapcsolat";
$speed[13] = "Nagy sebességû kapcsolat";
$speed[14] = "Helyi kapcsolat";
$speed[15] = "Kb/sec";
$speed[16] = "A kapcsolat sebességének meghatározása a számítógéped és ezen webhely között";
$speed[17] = "<p>Kattintson a gombra, hogy felmérje a számítógép és a webhely közötti kapcsolat sebességét.</p><p>Ez a teszt csak akkor végez ki értékelést, ha kiszámítja a képnek a webhelyről történő betöltésének átlagos sebességét.</p><p>Az eredmény tehát méri a kép betöltési sebességét egy adott időben. Nyilvánvaló, hogy ha más feltöltések vannak folyamatban, akkor a mérés torzul.</p>";
$speed[18] = "Sebességmérés elindítása";
$speed[19] = "Eredmény :";
$speed[20] = 'Eltávolítás';
$speed[21] = '<strong>FIGYELEM ! Az eltávolítás végleges.</strong><br/>';
?>