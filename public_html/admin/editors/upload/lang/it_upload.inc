<?php
/*******************************************************************************
 *   Admin Language Strings
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2020 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   courriel   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.00 (December 15, 2020) : initial release
 * v6.00.00 (December 27, 2020) : revised language by Max Bossi
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "it_upload.inc")) {
  header("location:../index.php");
  die();
}

$lang_upload[1] = "Creazione fallita perché esiste già una directory con lo stesso nome.<br /><br /> Si prega di ricominciare cambiando il nome della directory da creare";
$lang_upload[2] = "Fallimento perché esiste già un file con lo stesso nome.<br /><br /> Si prega di ricominciare cambiando il nome del file";
$lang_upload[3] = "Fallimento perché esiste già una directory con lo stesso nome.<br /><br /> Si prega di ricominciare cambiando il nome della directory";
$lang_upload[4] = "Non è stato possibile cancellare la directory perché il server non lo permette.<br /><br /> Si prega di cancellarla utilizzando il vostro software FTP";
$lang_upload[5] = "Upload fallito perché non è un file del modulo \"filename.ext\" ";
$lang_upload[6] = "Upload fallito perché esiste già un file con lo stesso nome.<br /><br /> Si prega di ricominciare cambiando il nome del file da caricare";
$lang_upload[7] = "Qui è dove si gestiscono i file e le directory sul tuo sito.<br /><i class='fas fa-search-location' style='font-size: 1.1rem;' title='Open'></i> : permette di aprire la directory<br /><i class='far fa-eye text-info align-middle' title='See'></i> : permette di visualizzare il file. <br /><i class='fas fa-edit align-middle' title='Rinomina '></i> : permette di rinominare il file o la directory<br /><i class='fas fa-times text-danger align-middle' title='Cancella '></i> : permette di cancellare il file o la directory.<br />";
$lang_upload[8] = "Creare una directory";
$lang_upload[9] = "Nome della directory";
$lang_upload[10] = "Crea";
$lang_upload[11] = 'Clicca qui per visualizzaez l\'help';
$lang_upload[12] = 'Clicca qui per nascondere l\'help';
$lang_upload[13] = "Carica un file";
$lang_upload[14] = "Directory";
$lang_upload[15] = "File";
$lang_upload[16] = "Dimensione (byte)";
$lang_upload[17] = "Azione";
$lang_upload[18] = "Caricare sul sito";
$lang_upload[19] = "Per recuperare l'indirizzo (URL) del file, cliccare sul suo nome";
$lang_upload[20] = "Devi compilare il campo";
$lang_upload[21] = "Non è permesso caricare questo tipo di file. Si prega di contattare l'amministratore del sito";
$lang_upload[22] = 'Anteprima';
$lang_upload[23] = 'Stai per cancellare il file';
$lang_upload[24] = 'Stai per cancellare la directory';
$lang_upload[25] = 'e tutti i suoi contenuti (file e sottodirectory)';
$lang_upload[26] = 'Attenzione, la cancellazione è irreversibile. Si prega di confermare la cancellazione perchè diventi effettiva';
$lang_upload[27] = 'Inserisci il nuovo nome del file';
$lang_upload[28] = "Si prega di digitare il nuovo nome del file senza l'estensione";
$lang_upload[29] = 'Inserisci il nuovo nome della directory';
$lang_upload[30] = 'Digita qui il nuovo nome della directory';
$lang_upload[31] = "Si prega di utilizzare solo caratteri non accentati e/o numeri e/o dei caratteri (- _) senza spazi nel nome.";
$lang_upload[32] = 'Aperto';
$lang_upload[33] = "Return";
$lang_upload[34] = "Directory parente";
$lang_upload[35] = "Visualizza";
$lang_upload[36] = "Rinomina";
$lang_upload[37] = "Cancella";
$lang_upload[38] = "Seleziona";

?>