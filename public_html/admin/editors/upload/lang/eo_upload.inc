<?php
/*******************************************************************************
 *   MinieditorTextarea réalisé par Djchouix - Licence CeCILL
 *   Web site = http://lebrikabrak.free.fr/
 *   e-mail   = lebrikabrak@free.fr
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2020 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = http://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V5 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.00 (December 15, 2020) : initial release
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "eo_upload.inc")) {
  header("location:../index.php");
  die();
}

$lang_upload[1] = "Malsukceso de la kreo ĉar dosierujo kun la sama nomo jam ekzistas. <br /> <br /> Bonvolu rekomenci ŝanĝante la nomon de la kreota dosierujo";
$lang_upload[2] = "Malsukceso ĉar dosiero kun la sama nomo jam ekzistas. <br /> <br /> Bonvolu rekomenci ŝanĝante la dosiernomon";
$lang_upload[3] = "Malsukceso ĉar dosierujo kun la sama nomo jam ekzistas. <br /> <br /> Bonvolu rekomenci ŝanĝante la nomon de la dosierujo";
$lang_upload[4] = "Malsukceso de dosierujforigo ĉar la servilo ne permesas tion. <br /> <br /> Bonvolu forigi ĝin per via FTP programaro.";
$lang_upload[5] = "Malsukceso de la alŝuto ĉar ĝi ne estas dosiero de la formo \"dosiernomo.ext\" ";
$lang_upload[6] = "Malsukceso de la alŝuto ĉar dosiero kun la sama nomo jam ekzistas. <br /> <br /> Bonvolu rekomenci ŝanĝante la nomon de la alŝutota dosiero";
$lang_upload[7] = "Tie vi administras dosierojn kaj dosierujojn en via retejo. <br /> <i class = 'fas fa-search-location' style = 'font-size: 1.1rem;' title = 'Malfermi'> </i>: permesas malfermi la dosierujon.<br /> <i class = 'far fa-eye text-info align-middle' title = 'Vidi'> </i> permesas vidi la dosieron. <br /> <i class = 'fas fa-edit align-middle' title = 'Renomi'> </i>: permesas renomi la dosieron aŭ dosierujon <br /> <i class = 'fas fa-times text-danger align-middle 'title =' Forigi '> </i>: permesas forigi la dosieron aŭ dosierujon. <br /> ";
$lang_upload[8] = "Krei dosierujon";
$lang_upload[9] = "Dosieruja nomo";
$lang_upload[10] = "Krei";
$lang_upload[11] = 'Alklaku tie por montri la helpon';
$lang_upload[12] = 'Alklaku tie por kaŝi la helpon';
$lang_upload[13] = "Alŝuti dosieron";
$lang_upload[14] = "Dosierujo";
$lang_upload[15] = "Dosiero";
$lang_upload[16] = "Grando (bajto)";
$lang_upload[17] = "Ago";
$lang_upload[18] = "Alŝuti al la retejo";
$lang_upload[19] = "Por retrovi la adreson (URL) de la dosiero, bonvolu alklaki ĝian nomon.";
$lang_upload[20] = 'Vi devas plenskribi la kampon';
$lang_upload[21] = "Vi ne rajtas alŝuti tiun dosiertipon. Bonvolu kontakti la retejestron.";
$lang_upload[22] = 'Antaŭrigardo';
$lang_upload[23] = 'Vi estas forigonta la dosieron';
$lang_upload[24] = 'Vi estas forigonta la dosierujon';
$lang_upload[25] = 'same kiel ĝia tuta enhavo (dosieroj kaj subdosierujoj)';
$lang_upload[26] = 'Atentu, la forigo estas neinversigebla. \n Bonvolu konfirmi tiun forigon por ke ĝi efektiviĝu.';
$lang_upload[27] = 'Bonvolu enigi la novan nomon de la dosiero';
$lang_upload[28] = 'entajpu tie la novan nomon de la dosiero sen la finaĵo';
$lang_upload[29] = 'Bonvolu enigi la novan nomon de la dosierujo';
$lang_upload[30] = 'entajpu tie la novan nomon de la dosierujo';
$lang_upload[31] = 'Atentu, vi devas uzi nur signojn sen supersignoj kaj/aŭ nombrojn kaj/aŭ signojn (- _) sen interspaco en la nomo.';
$lang_upload[32] = "Malfermi";
$lang_upload[33] = "Reveno";
$lang_upload[34] = "Gepatra dosierujo";
$lang_upload[35] = "Vidi";
$lang_upload[36] = "Renomi";
$lang_upload[37] = "Forigi";
$lang_upload[38] = "Selekti";

?>
