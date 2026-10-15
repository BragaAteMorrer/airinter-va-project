<?php
/*******************************************************************************
 *   Configuration du captcha sélection texte
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
 * v6.00.12 (MAy 28, 2023) : initial release
 ******************************************************************************/
include CONFIG;

$cryptheight = 32;          // Hauteur du cryptogramme (en pixels)
$bgC         = '#FFFFFF';   // Couleur du fond au format héxadécimal
$bdC         = '#000000';   // Couleur du cadre au format héxadécimal
$bdS         = '#C9C9C9';   // Couleur du cadre au format héxadécimal
$etC         = 1;			// Epaisseur du trait du cadre
$corners     = 4;			// Rayon des coins arrondis

// ----------------------------
// Configuration des caractères
// ----------------------------

// Polices de caractères

$tfont = 'Arial';

// Caracteres autorisés
// Attention, certaines polices ne distinguent pas (ou difficilement) les majuscules 
// et les minuscules. Certains caractères sont faciles à confondre, il est donc
// conseillé de bien choisir les caractères utilisés.

$charel = 'ABCDEFGHKLMNPRTWXYZ234569abcdefghkmnprtwxyz';       // Caractères autorisés

$crypteasy = false;       // Création de cryptogrammes "faciles à lire" (true/false)
                          // composés alternativement de consonnes et de voyelles.

$charelc = 'BCDFGHKLMNPRTVWXZbcdefghkmnprtvwxz';   // Consonnes utilisées si $crypteasy = true
$charelv = 'AEOUYaeiouy';                          // Voyelles utilisées si $crypteasy = true

$difuplow = $serviz[115] == 'on' ? true : false;   // Différencie les Maj/Min lors de la saisie du code (true, false)

$charnbmin = 8;          // Nb minimum de caracteres dans le cryptogramme
$charnbmax = 10;         // Nb maximum de caracteres dans le cryptogramme

$charsize  = 16;        // Taille des caractères

// --------------------------------
// Configuration système & sécurité
// --------------------------------

$cryptsecure = "";    // Méthode de crytpage utilisée: "md5", "sha1" ou "" (aucune)
                         // "sha1" seulement si PHP>=4.2.0
                         // Si aucune méthode n'est indiquée, le code du cyptogramme est stocké 
                         // en clair dans la session.
                   
$cryptoneuse = false;  // Si vous souhaitez que la page de verification ne valide qu'une seule 
                       // fois la saisie en cas de rechargement de la page indiquer "true".
                       // Sinon, le rechargement de la page confirmera toujours la saisie. 
?>
