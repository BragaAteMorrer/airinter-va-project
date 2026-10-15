<?php
/*******************************************************************************
 *   Config Search
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

if (stristr($_SERVER['SCRIPT_NAME'], 'searchconfig.inc')) {
	header('location:../index.php');
	die();
}

$pertinencemin = 0;   // Affiche les résultats de pertinence supérieure à cette valeur
$resultbypage  = 10;   // Nombre de résultats par page
$barcolor      = stripslashes("vert");   // Couleur de la barre de pertinence
$bargrey       = stripslashes("3");   // Fond de la barre de pertinence
$styleocc[0]   = stripslashes("#ECF300");   // Couleur de la police
$styleocc[1]   = stripslashes("");   // Italique
$styleocc[2]   = stripslashes("bold");   // Caractères gras
$styleocc[3]   = stripslashes("");   // Souligné
$styleocc[4]   = stripslashes("on");   // "on"/"" Surligné en couleur
$bgcolor[0]    = stripslashes("#EC1B00");   // Couleur de l'occurence n° 1
$bgcolor[1]    = stripslashes("#B65100");   // Couleur de l'occurence n° 2
$bgcolor[2]    = stripslashes("#9B8700");   // Couleur de l'occurence n° 3
$bgcolor[3]    = stripslashes("#806CF3");   // Couleur de l'occurence n° 4
$bgcolor[4]    = stripslashes("#ECD887");   // Couleur de l'occurence n° 5
$bgcolor[5]    = stripslashes("#ECBDF3");   // Couleur de l'occurence n° 6
$bgcolor[6]    = stripslashes("#D1F36C");   // Couleur de l'occurence n° 7
$bgcolor[7]    = stripslashes("#D1D8F3");   // Couleur de l'occurence n° 8

$exclure0      = stripslashes("a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p, q, r, s, t, u, v, w, x, y, z, un, une, d', de, de la, du, des, l', le, la, les, Ã , au, et, et un, et une, et de, et du, et des, et le, et la, et les, et Ã , et au, sur, sur le, sur la, dans, dans le, dans la, en, ce, cette, mon, ma, mes, ton, ta, tes, son, sa, ses, je, tu, il, on");
$exclure1      = stripslashes("a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p, q, r, s, t, u, v, w, x, y, z, an, the, on, in, of");
$hideprint     = stripslashes("");
$confpage      = stripslashes("0");
?>