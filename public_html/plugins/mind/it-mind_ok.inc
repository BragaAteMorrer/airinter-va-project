<?php
/*******************************************************************************
 *   Mind Plugin
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
 * v6.00.00 (December 27, 2020) : revised language by Max Bossi
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'it-mind.inc')) {
    header('location:index.php');
    die();
}

$mind[1] = "Admin - Plugin Esempio Indovinello";
$mind[2] = "GuppY viene fornito con alcuni plugin molto semplici, a titolo di esempio (per sviluppatori) e per abbellire il tuo sito GuppY (per webmaster).";
$mind[3] = "Posso leggerti nel pensiero. Non ci credi? Provalo! Rimarrai sbalordito ...";
$mind[4] = "Accesso da:";
$mind[5] = "Una scatola laterale, da configurare";
$mind[6] = "Guardalo funzionare:";
$mind[7] = "Indovinello";
$mind[8] = "Box Indovinello";
$mind[9] = "Ci sto provando!";
$mind[10] = "La macchina per leggere la mente";
$mind[11] = "1. Pensa a un numero di due cifre (esempio: 54)";
$mind[12] = "2. Sottrai ciascuna di queste due cifre (5 e 4) dal numero scelto (esempio: 54 - 5 - 4 = 45)";
$mind[13] = "3. Trova questo risultato nell'elenco e memorizza il simbolo corrispondente";
$mind[14] = "<strong> Concentrati molto su questo simbolo. </strong> Quindi fai clic sul pulsante e la macchina lo indovinerà!";
$mind[15] = "Indovina i miei pensieri";
$mind[16] = "Ecco il simbolo che ho letto nella tua mente:";
$mind[17] = "Riscopri i miei pensieri";
$mind[18] = 'Disinstalla';
$mind[19] = '<strong>ATTENZIONE! La disinstallazione è definitiva.</strong><br/>';

?>