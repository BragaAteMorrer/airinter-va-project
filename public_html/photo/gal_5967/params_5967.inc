<?php
if (stristr($_SERVER["SCRIPT_NAME"], "params_5967.inc")) {
    header("location:../index.php");
    die();
}

$param1 = stripslashes("10"); // Nombre maximum de miniatures par galerie
$param2 = stripslashes("100"); // Largeur maximum d'une miniature
$param3 = stripslashes("75"); // Hauteur maximum d'une miniature
$param4 = stripslashes("unitegallery"); // Choix effet pour le phototrama
$param5 = stripslashes("4"); // Intervalle entre diapos en secondes
$param6 = stripslashes("5"); // Nombre maximum de miniatures par ligne
$param7 = stripslashes("20"); // Espace en pixels entre les miniatures
$param8 = stripslashes("C"); // Position des miniatures L / C / R
$param9 = stripslashes(""); // Inclure dans le slishow on/off
$param10 = stripslashes("on"); // Galerie indépendante on/off
$param11 = stripslashes(""); // Cacher le compteur des photos on/off
$param12 = stripslashes(""); // Cacher le compteur des visites on/off
$param13 = stripslashes("ugsl"); // Thème UniteGallery

?>