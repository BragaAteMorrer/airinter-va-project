<?php
/*******************************************************************************
 *   Update user rights
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
 * v6.00.19 (August 26, 2024) : Rights correction
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'rightsupdt.inc')) {
    header('location:../index.php');
    die();
}

$filedroits = CHEMIN.'admin/'.REDACREP.$rightsname.INCEXT;
$mettre = "<?php
if (stristr(\$_SERVER['SCRIPT_NAME'], '".$rightsname.".inc')) {
    header('location:../index.php');
    die();
}

\$drtuser[0]  = \"".$drtuser[0]."\";  // Accès à config générale
\$drtuser[1]  = \"".$drtuser[1]."\";  // Accès à config site
\$drtuser[2]  = \"".$drtuser[2]."\";  // Accès à config accueil
\$drtuser[3]  = \"".$drtuser[3]."\";  // Accès à config section
\$drtuser[4]  = \"".$drtuser[4]."\";  // Accès à Config services
\$drtuser[5]  = \"".$drtuser[5]."\";  // Accès à Config boites
\$drtuser[6]  = \"".$drtuser[6]."\";  // Accès à Maintenance
\$drtuser[7]  = \"".$drtuser[7]."\";  // Accès à Archivage Forum
\$drtuser[8]  = \"".$drtuser[8]."\";  // Accès à Contrôle d'intégrité DB
\$drtuser[9]  = \"".$drtuser[9]."\";  // Accès à Mot de passe Collaborateur
\$drtuser[10] = \"".$drtuser[10]."\"; // Accès à Config zone membres
\$drtuser[11] = \"".$drtuser[11]."\"; // Accès à Travaux
\$drtuser[12] = \"".$drtuser[12]."\"; // Accès à Droits
\$drtuser[13] = \"".$drtuser[13]."\"; // Accès à Contact avancé
\$drtuser[14] = \"".$drtuser[14]."\"; // Accès à Nouvelles
\$drtuser[15] = \"".$drtuser[15]."\"; // Accès à Articles
\$drtuser[16] = \"".$drtuser[16]."\"; // Accès à Réactions Articles
\$drtuser[17] = \"".$drtuser[17]."\"; // Accès à Boîtes menus
\$drtuser[18] = \"".$drtuser[18]."\"; // Accès à Photos
\$drtuser[19] = \"".$drtuser[19]."\"; // Accès à Téléchargements
\$drtuser[20] = \"".$drtuser[20]."\"; // Accès à Liens
\$drtuser[21] = \"".$drtuser[21]."\"; // Accès à FAQ
\$drtuser[22] = \"".$drtuser[22]."\"; // Accès à Livre d'or
\$drtuser[23] = \"".$drtuser[23]."\"; // Accès à Forum
\$drtuser[24] = \"".$drtuser[24]."\"; // Accès à Config Forum
\$drtuser[25] = \"".$drtuser[25]."\"; // Accès personnalisation des fichiers Lang
\$drtuser[26] = \"".$drtuser[26]."\"; // Accès à Boites libres
\$drtuser[27] = \"".$drtuser[27]."\"; // Accès à Bannières
\$drtuser[28] = \"".$drtuser[28]."\"; // Accès à Citations
\$drtuser[29] = \"".$drtuser[29]."\"; // Accès à Menu icônes
\$drtuser[30] = \"".$drtuser[30]."\"; // Statut à afficher
\$drtuser[31] = \"".$drtuser[31]."\"; // Accès à Compteur
\$drtuser[32] = \"".$drtuser[32]."\"; // Accès à Statistiques
\$drtuser[33] = \"".$drtuser[33]."\"; // Accès à Newsletter
\$drtuser[34] = \"".$drtuser[34]."\"; // Accès à Nouvelles RSS
\$drtuser[35] = \"".$drtuser[35]."\"; // Accès à Logs
\$drtuser[36] = \"".$drtuser[36]."\"; // Accès à Fichiers
\$drtuser[37] = \"".$drtuser[37]."\"; // Accès à Agenda
\$drtuser[38] = \"".$drtuser[38]."\"; // Password
\$drtuser[39] = \"".$drtuser[39]."\"; // Accès à Blogs
\$drtuser[40] = \"".$drtuser[40]."\"; // Accès à Réactions Blogs
\$drtuser[41] = \"".$drtuser[41]."\"; // Accès à Blog RSS
\$drtuser[42] = \"".$drtuser[42]."\"; // Fonction
\$drtuser[43] = \"".$drtuser[43]."\"; // Accès à Config Style
\$drtuser[44] = \"".$drtuser[44]."\"; // Accès à Import
\$drtuser[45] = \"".$drtuser[45]."\"; // Accès à Sécurité
\$drtuser[46] = \"".$drtuser[46]."\"; // Accès à Mise à jour / Extension
\$drtuser[47] = \"".$drtuser[47]."\"; // Accès à Sauvegarde du site
\$drtuser[48] = \"".$drtuser[48]."\"; // Accès à Version de GuppY
\$drtuser[49] = \"".$drtuser[49]."\"; // Accès à Config Recherche
\$drtuser[50] = \"".$drtuser[50]."\"; // Accès à Réseaux sociaux
\$drtuser[51] = \"".$drtuser[51]."\"; // Declaration RGPD														 
\$drtusersv[0] = \"".$drtusersv[0]."\"; // e-mail publication article
\$drtusersv[1] = \"".$drtusersv[1]."\"; // e-mail publication réaction article
\$drtusersv[2] = \"".$drtusersv[2]."\"; // e-mail publication nouvelle
\$drtusersv[3] = \"".$drtusersv[3]."\"; // e-mail publication blog
\$drtusersv[4] = \"".$drtusersv[4]."\"; // e-mail publication commentaire blog
\$drtusersv[5] = \"".$drtusersv[5]."\"; // e-mail publication livre d\'or
\$drtusersv[6] = \"".$drtusersv[6]."\"; // e-mail publication forum";
if (!empty($drtuserplg))  
    foreach($drtuserplg as $plg => $value) {
        $mettre .= "
\$drtuserplg['$plg'] = \"".$value."\"; // Accès plugin $plg";
    }
$mettre .='
';
WriteFullDB($filedroits, $mettre);
?>