<?php
if (stristr($_SERVER['SCRIPT_NAME'], 'IT001Alex.inc')) {
    header('location:../index.php');
    die();
}

$drtuser[0]  = "on";  // Accès à config générale
$drtuser[1]  = "on";  // Accès à config site
$drtuser[2]  = "on";  // Accès à config accueil
$drtuser[3]  = "on";  // Accès à config section
$drtuser[4]  = "on";  // Accès à Config services
$drtuser[5]  = "on";  // Accès à Config boites
$drtuser[6]  = "on";  // Accès à Maintenance
$drtuser[7]  = "on";  // Accès à Archivage Forum
$drtuser[8]  = "on";  // Accès à Contrôle d'intégrité DB
$drtuser[9]  = "on";  // Accès à Mot de passe Collaborateur
$drtuser[10] = "on"; // Accès à Config zone membres
$drtuser[11] = "on"; // Accès à Travaux
$drtuser[12] = "on"; // Accès à Droits
$drtuser[13] = "on"; // Accès à Contact avancé
$drtuser[14] = "on"; // Accès à Nouvelles
$drtuser[15] = "on"; // Accès à Articles
$drtuser[16] = "on"; // Accès à Réactions Articles
$drtuser[17] = "on"; // Accès à Boîtes menus
$drtuser[18] = "on"; // Accès à Photos
$drtuser[19] = "on"; // Accès à Téléchargements
$drtuser[20] = "on"; // Accès à Liens
$drtuser[21] = "on"; // Accès à FAQ
$drtuser[22] = "on"; // Accès à Livre d'or
$drtuser[23] = "on"; // Accès à Forum
$drtuser[24] = "on"; // Accès à Config Forum
$drtuser[25] = "on"; // Accès personnalisation des fichiers Lang
$drtuser[26] = "on"; // Accès à Boites libres
$drtuser[27] = "on"; // Accès à Bannières
$drtuser[28] = "on"; // Accès à Citations
$drtuser[29] = "on"; // Accès à Menu icônes
$drtuser[30] = "Admin"; // Statut à afficher
$drtuser[31] = "on"; // Accès à Compteur
$drtuser[32] = "on"; // Accès à Statistiques
$drtuser[33] = "on"; // Accès à Newsletter
$drtuser[34] = "on"; // Accès à Nouvelles RSS
$drtuser[35] = "on"; // Accès à Logs
$drtuser[36] = "on"; // Accès à Fichiers
$drtuser[37] = "on"; // Accès à Agenda
$drtuser[38] = "f978b9fc8d27fc8849023668a4909d57"; // Password
$drtuser[39] = "on"; // Accès à Blogs
$drtuser[40] = "on"; // Accès à Réactions Blogs
$drtuser[41] = "on"; // Accès à Blog RSS
$drtuser[42] = "admin"; // Fonction
$drtuser[43] = "on"; // Accès à Config Style
$drtuser[44] = "on"; // Accès à Import
$drtuser[45] = "on"; // Accès à Sécurité
$drtuser[46] = "on"; // Accès à Mise à jour / Extension
$drtuser[47] = "on"; // Accès à Sauvegarde du site
$drtuser[48] = "on"; // Accès à Version de GuppY
$drtuser[49] = "on"; // Accès à Config Recherche
$drtuser[50] = "on"; // Accès à Réseaux sociaux
$drtuser[51] = "on"; // Declaration RGPD														 
$drtusersv[0] = "on"; // e-mail publication article
$drtusersv[1] = "on"; // e-mail publication réaction article
$drtusersv[2] = "on"; // e-mail publication nouvelle
$drtusersv[3] = "on"; // e-mail publication blog
$drtusersv[4] = "on"; // e-mail publication commentaire blog
$drtusersv[5] = "on"; // e-mail publication livre d\'or
$drtusersv[6] = "on"; // e-mail publication forum
$drtuserplg['clock'] = "on"; // Accès plugin clock
$drtuserplg['mind'] = "on"; // Accès plugin mind
$drtuserplg['speed'] = "on"; // Accès plugin speed
