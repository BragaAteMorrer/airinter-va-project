<?php
/*******************************************************************************
 *   Config Update
 *******************************************************************************
 *   GuppY PHP Script - version 6.0
 *   CeCILL Copyright (C) 2004-2023 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
  * v6.00.21 (December 17, 2024) : Information about a new serviz and config Closes posts
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'confupdt.inc')) {
    header('location:../index.php');
    die();
}

if ($site[3] == '') {
    $site[3] = 'http://'.str_replace('/admin','/',$_SERVER['HTTP_HOST'].dirname($PHP_SELF));
}

if (!isset($serviz[114])) $serviz[114] = '';
if (!isset($serviz[182])) $serviz[182] = '0';
if (!isset($nom[14])) $nom[14] = '';
if (!isset($nom[15])) $nom[14] = '';
if (!isset($nom[28])) $nom[28] = '';
if (!isset($nom[28])) $nom[29] = '';
if (!isset($droitaccegroupredac)) $droitaccegroupredac = '';
if (!isset($nom[58])) $nom[58] = '';
if (!isset($nom[59])) $nom[59] = '';
if (!isset($nom[60])) $nom[60] = '';
if (!isset($nom[61])) $nom[61] = '';
if (!isset($dynabox[19])) $dynabox[19] = '';
if (!isset($dynabox[20])) $dynabox[20] = '';
if (!isset($smtp[6])) 
{
	$smtp[6] = '';
	$smtp[7]  = '';
	
}				  
$serviz[34] = 'on'; // Compteur de lecture Threads Forum
$mettre = "<?php
if (stristr(\$_SERVER[\"SCRIPT_NAME\"], \"config.inc\")) {
    header(\"location:../index.php\");
    die();
}

\$site[0] = stripslashes(\"$site[0]\"); // Titre du site pour l Editorial langue principale
\$site[1] = stripslashes(\"$site[1]\"); // Meta Description du site
\$site[2] = stripslashes(\"$site[2]\"); // Meta Mots-clés du site
\$site[3] = stripslashes(\"$site[3]\"); // URL du site
// site[4] is in conskin.inc // Nouvelles sur la page d accueil
\$site[5] = stripslashes(\"$site[5]\"); // Activation des news ou pas
\$site[6] = stripslashes(\"$site[6]\"); // Hébergement derrière un proxy
\$site[7] = stripslashes(\"$site[7]\"); // Hébergement avec mise en cache
\$site[8] = stripslashes(\"$site[8]\"); // Temporisation en s de lecture du cache
\$site[9] = stripslashes(\"$site[9]\"); // Meta Auteur du site
\$site[10] = stripslashes(\"$site[10]\"); // Page pour la redirection des erreurs 404
\$site[11] = stripslashes(\"$site[11]\"); // Titre du site pour l Editorial langue secondaire
\$site[12] = stripslashes(\"$site[12]\"); // Page secondaire pour la redirection des erreurs 404
\$site[13] = stripslashes(\"$site[13]\"); // Hauteur en pixels des nouvelles
\$site[19] = stripslashes(\"$site[19]\"); // Format Dates
// site[20] is in confskin.inc // Messages Forum sur la page d accueil
// site[21] is in confskin.inc // ID du message Livre d or préféré
\$site[22] = stripslashes(\"$site[22]\"); // Format Heure
\$site[23] = stripslashes(\"$site[23]\"); // Séparateur Date & Heure
\$site[24] = stripslashes(\"$site[24]\"); // Logo Copyright
\$site[25] = stripslashes(\"$site[25]\"); // Position Logo Copyright
\$site[26] = stripslashes(\"$site[26]\"); // Compression des Pages
// site[27] = stripslashes(\"\"); // Inutilisé
// (supprimé) Barre de progression attente popup postage
// site[29] is in confskin.inc // Nombre de caractères des nouvelles en page d accueil
\$site[30] = stripslashes(\"$site[30]\"); // Activation du Blog ou pas
// site[31] is in confskin.inc // Blog sur la page d accueil
\$site[32] = stripslashes(\"$site[32]\"); // Icônes validator W3C
// site[33] is in confskin.inc // Nombre de caractères des billets en page d accueil
// site[34] is in confskin.inc // Nombre de caractères des posts forum en page d accueil
\$site[35] = stripslashes(\"$site[35]\"); // Style du Copyright
\$site[36] = stripslashes(\"$site[36]\"); // Hauteur en pixels des billets du blog
\$site[37] = stripslashes(\"$site[37]\"); // Thème éditeur public CKEditor
\$site['TZ'] = stripslashes(\"".$site['TZ']."\"); // Fuseau horaire
\$site['NTC'] = stripslashes(\"".$site['NTC']."\"); // Nombre de tentatives de connexion
\$site['URLR'] = stripslashes(\"".$site['URLR']."\"); // URL rewriting
\$site['PG'] = stripslashes(\"".$site['PG']."\"); // Alias du script postguest.php
\$site['BLS'] = stripslashes(\"".$site['BLS']."\"); // Alias du script blogs.php
\$site['BL'] = stripslashes(\"".$site['BL']."\"); // Alias du script blog.php
\$site['FR'] = stripslashes(\"".$site['FR']."\"); // Alias du script forum.php
\$site['FRT'] = stripslashes(\"".$site['FRT']."\"); // Alias du script fortopic.php
\$site['TH'] = stripslashes(\"".$site['TH']."\"); // Alias du script thread.php
\$site['GB'] = stripslashes(\"".$site['GB']."\"); // Alias du script guestbk.php
\$site['NE'] = stripslashes(\"".$site['NE']."\"); // Alias du script news.php
\$site['USR'] = stripslashes(\"".$site['USR']."\"); // Alias du script user.php
\$site['NL'] = stripslashes(\"".$site['NL']."\"); // Alias du script newsletter.php
\$site['DN'] = stripslashes(\"".$site['DN']."\"); // Alias du script download.php
\$site['CT'] = stripslashes(\"".$site['CT']."\"); // Alias du script contact.php
\$site['MD'] = stripslashes(\"".$site['MD']."\"); // Mode préconisé pour les dossiers
\$site['MF'] = stripslashes(\"".$site['MF']."\"); // Mode préconisé pour les fichiers

\$urlrw[0] = stripslashes(\"$urlrw[0]\"); // Accueil / Admin / Error
\$urlrw[1] = stripslashes(\"$urlrw[1]\"); // Blog
\$urlrw[2] = stripslashes(\"$urlrw[2]\"); // Contact
\$urlrw[3] = stripslashes(\"$urlrw[3]\"); // Connexion  / Compte
\$urlrw[4] = stripslashes(\"$urlrw[4]\"); // FAQ
\$urlrw[5] = stripslashes(\"$urlrw[5]\"); // Forum
\$urlrw[6] = stripslashes(\"$urlrw[6]\"); // Liens
\$urlrw[7] = stripslashes(\"$urlrw[7]\"); // Livre d or
\$urlrw[8] = stripslashes(\"$urlrw[8]\"); // Nouvelles
\$urlrw[9] = stripslashes(\"$urlrw[9]\"); // Photos
// urlrw[10] = stripslashes(\"\"); // Statistiques supprimé
\$urlrw[11] = stripslashes(\"$urlrw[11]\"); // Téléchargements
\$urlrw[12] = stripslashes(\"$urlrw[12]\"); // Recherches
\$urlrw[13] = stripslashes(\"$urlrw[13]\"); // Messages privés
\$urlrw[14] = stripslashes(\"$urlrw[14]\"); // Mesages publics
\$urlrw[15] = stripslashes(\"$urlrw[15]\"); // Utilisateurs
// urlrw[16] = stripslashes(\"\"); // Sondage supprimé
\$urlrw[17] = stripslashes(\"$urlrw[17]\"); // Calendrier / Agenda
\$urlrw[18] = stripslashes(\"$urlrw[18]\"); // Lettre d information
\$urlrw[19] = stripslashes(\"$urlrw[19]\"); // Documents RSS
\$urlrw[20] = stripslashes(\"$urlrw[20]\"); // Articles

\$page[12] = stripslashes(\"$page[12]\"); // Thème smileys
\$page[14] = stripslashes(\"$page[14]\"); // Thème skins
\$page[23] = stripslashes(\"$page[23]\"); // Thème avatars
// page[27] = stripslashes(\"\"); // Inutilisé

\$texte[3] = stripslashes(\"$texte[3]\"); // Puce articles

\$lang[0] = stripslashes(\"$lang[0]\"); // Langue principale du site
\$lang[1] = stripslashes(\"$lang[1]\"); // Deuxième langue du site

\$user[0] = stripslashes(\"$user[0]\"); // Webmaster
\$user[1] = stripslashes(\"$user[1]\"); // e-mail du responsable du site
\$user[3] = stripslashes(\"$user[3]\"); // e-mail PHP

\$nom[0] = stripslashes(\"$nom[0]\"); // Nom de l accueil langue principale
\$nom[1] = stripslashes(\"$nom[1]\"); // Page des téléchargements langue principale
\$nom[2] = stripslashes(\"$nom[2]\"); // Page des images langue principale
\$nom[3] = stripslashes(\"$nom[3]\"); // Page des liens langue principale
// (supprimé) Boite des articles à gauche langue principale
// (supprimé) Boite spéciale langue principale
// (supprimé) Sondage langue principale WEB
\$nom[7] = stripslashes(\"$nom[7]\"); // Nouvelles langue principale
\$nom[8] = stripslashes(\"$nom[8]\"); // Compteur langue principale WEB
\$nom[9] = stripslashes(\"$nom[9]\"); // Livre d or langue principale
\$nom[10] = stripslashes(\"$nom[10]\"); // Nom de l accueil deuxième langue
\$nom[11] = stripslashes(\"$nom[11]\"); // Page des téléchargements deuxième langue
\$nom[12] = stripslashes(\"$nom[12]\"); // Page des images deuxième langue
\$nom[13] = stripslashes(\"$nom[13]\"); // Page des liens deuxième langue
\$nom[14] = stripslashes(\"$nom[14]\"); // Bouton connexion langue principale
\$nom[15] = stripslashes(\"$nom[15]\"); // Bouton connexion deuxième langue
// (supprimé) Sondage deuxième langue WEB
\$nom[17] = stripslashes(\"$nom[17]\"); // Nouvelles deuxième langue
\$nom[18] = stripslashes(\"$nom[18]\"); // Compteur deuxième langue WEB
\$nom[19] = stripslashes(\"$nom[19]\"); // Livre d or deuxième langue
\$nom[20] = stripslashes(\"$nom[20]\"); // Recherche langue principale WEB
\$nom[21] = stripslashes(\"$nom[21]\"); // Recherche deuxième langue WEB
\$nom[22] = stripslashes(\"$nom[22]\"); // Forum langue principale
\$nom[23] = stripslashes(\"$nom[23]\"); // Forum deuxième langue
\$nom[24] = stripslashes(\"$nom[24]\"); // FAQ langue principale
\$nom[25] = stripslashes(\"$nom[25]\"); // FAQ deuxième langue
\$nom[26] = stripslashes(\"$nom[26]\"); // Statistiques langue principale
\$nom[27] = stripslashes(\"$nom[27]\"); // Statistiques deuxième langue
\$nom[28] = stripslashes(\"$nom[28]\"); // Bouton déconnexion langue principale
\$nom[29] = stripslashes(\"$nom[29]\"); // Bouton déconnexion deuxième langue
\$nom[30] = stripslashes(\"$nom[30]\"); // Boite Photorama langue principale WEB
\$nom[31] = stripslashes(\"$nom[31]\"); // Boite Photorama deuxième langue WEB
\$nom[32] = stripslashes(\"$nom[32]\"); // Boite Calendrier langue principale WEB
\$nom[33] = stripslashes(\"$nom[33]\"); // Boite Calendrier deuxième langue WEB
\$nom[34] = stripslashes(\"$nom[34]\"); // Boite préférences utilisateur langue principale WEB
\$nom[35] = stripslashes(\"$nom[35]\"); // Boite préférences utilisateur deuxième langue WEB
\$nom[36] = stripslashes(\"$nom[36]\"); // Boite RSS langue principale WEB
\$nom[37] = stripslashes(\"$nom[37]\"); // Boite RSS deuxième langue WEB
\$nom[38] = stripslashes(\"$nom[38]\"); // Boite Newsletter langue principale WEB
\$nom[39] = stripslashes(\"$nom[39]\"); // Boite Newsletter deuxième langue WEB
\$nom[40] = stripslashes(\"$nom[40]\"); // Boite Webmaster - Infos langue principale WEB
\$nom[41] = stripslashes(\"$nom[41]\"); // Boite Webmaster - Infos deuxième langue WEB
\$nom[42] = stripslashes(\"$nom[42]\"); // Blog langue principale
\$nom[43] = stripslashes(\"$nom[43]\"); // Blog deuxième langue
\$nom[44] = stripslashes(\"$nom[44]\"); // Boîte Menu langue principale WEB
\$nom[45] = stripslashes(\"$nom[45]\"); // Boîte Menu deuxième langue WEB
\$nom[46] = stripslashes(\"$nom[46]\"); // Contact langue principale
\$nom[47] = stripslashes(\"$nom[47]\"); // Contact deuxième langue
\$nom[48] = stripslashes(\"$nom[48]\"); // Réseaux sociaux langue principale WEB
\$nom[49] = stripslashes(\"$nom[49]\"); // Réseaux sociaux deuxième langue WEB
// (supprimé)
// (supprimé)
// (supprimé)
// (supprimé)
\$nom[54] = stripslashes(\"$nom[54]\"); // Boîte nouveautés langue principale WEB
\$nom[55] = stripslashes(\"$nom[55]\"); // Boîte nouveautés deuxième langue WEB
\$nom[56] = stripslashes(\"$nom[56]\"); // Boîte Contact langue principale WEB
\$nom[57] = stripslashes(\"$nom[57]\"); // Boîte Contact deuxième langue WEB
\$nom[58] = stripslashes(\"$nom[58]\"); // Page Affichage tous les documents langue principale WEB
\$nom[59] = stripslashes(\"$nom[59]\"); // Page Affichage tous les documents deuxième langue WEB
\$nom[60] = stripslashes(\"$nom[60]\"); // Page Affichage tous les posts à cloturer langue principale WEB
\$nom[61] = stripslashes(\"$nom[61]\"); // Page Affichage tous les posts à cloturer deuxième langue WEB

\$menuico[0] = stripslashes(\"$menuico[0]\"); // Icône Accueil
\$menuico[1] = stripslashes(\"$menuico[1]\"); // Icône Nouvelles
\$menuico[2] = stripslashes(\"$menuico[2]\"); // Icône Blog
\$menuico[3] = stripslashes(\"$menuico[3]\"); // Icône Photorama
\$menuico[4] = stripslashes(\"$menuico[4]\"); // Icône Téléchargements
\$menuico[5] = stripslashes(\"$menuico[5]\"); // Icône Liens
\$menuico[6] = stripslashes(\"$menuico[6]\"); // Icône FAQ
\$menuico[7] = stripslashes(\"$menuico[7]\"); // Icône Livre d or
\$menuico[8] = stripslashes(\"$menuico[8]\"); // Icône Forum
\$menuico[9] = stripslashes(\"$menuico[9]\"); // Icône Stats
\$menuico[10] = stripslashes(\"$menuico[10]\"); // Icône Contact avancé
\$menuico[11] = stripslashes(\"$menuico[11]\"); // Icône connexion
\$menuico[12] = stripslashes(\"$menuico[12]\"); // Page exécution Nouvelles 
\$menuico[13] = stripslashes(\"$menuico[13]\"); // Page exécution Blog
\$menuico[14] = stripslashes(\"$menuico[14]\"); // Page exécution Photorama
\$menuico[15] = stripslashes(\"$menuico[15]\"); // Page exécution Téléchargements
\$menuico[16] = stripslashes(\"$menuico[16]\"); // Page exécution Liens
\$menuico[17] = stripslashes(\"$menuico[17]\"); // Page exécution FAQ
\$menuico[18] = stripslashes(\"$menuico[18]\"); // Page exécution Livre d or
\$menuico[19] = stripslashes(\"$menuico[19]\"); // Page exécution Forum
// menuico[20] = stripslashes(\"\"); // Inutilisé
\$menuico[21] = stripslashes(\"$menuico[21]\"); // Page exécution Contact avancé
\$menuico[22] = stripslashes(\"$menuico[22]\"); // Page affichage Nouvelles
\$menuico[23] = stripslashes(\"$menuico[23]\"); // Page affichage Blog
\$menuico[24] = stripslashes(\"$menuico[24]\"); // Page affichage Photorama
\$menuico[25] = stripslashes(\"$menuico[25]\"); // Page affichage Téléchargements
\$menuico[26] = stripslashes(\"$menuico[26]\"); // Page affichage Liens
\$menuico[27] = stripslashes(\"$menuico[27]\"); // Page affichage FAQ
\$menuico[28] = stripslashes(\"$menuico[28]\"); // Page affichage Livre d or
\$menuico[29] = stripslashes(\"$menuico[29]\"); // Page affichage Forum
\$menuico[30] = stripslashes(\"$menuico[30]\"); // Page affichage Stats
\$menuico[31] = stripslashes(\"$menuico[31]\"); // Page affichage Contact avancé
\$menuico[32] = stripslashes(\"$menuico[32]\"); // Groupe Nouvelles
\$menuico[33] = stripslashes(\"$menuico[33]\"); // Groupe Blog
\$menuico[34] = stripslashes(\"$menuico[34]\"); // Groupe Photorama
\$menuico[35] = stripslashes(\"$menuico[35]\"); // Groupe Téléchargements
\$menuico[36] = stripslashes(\"$menuico[36]\"); // Groupe Liens
\$menuico[37] = stripslashes(\"$menuico[37]\"); // Groupe FAQ
\$menuico[38] = stripslashes(\"$menuico[38]\"); // Groupe Livre d or
\$menuico[39] = stripslashes(\"$menuico[39]\"); // Groupe Forum
\$menuico[40] = stripslashes(\"$menuico[40]\"); // Groupe Stats

// boites dynamiques partie WEB
\$dynabox[0] = stripslashes(\"$dynabox[0]\"); // Boîte Menu
// dynabox[1] = stripslashes(\"\"); // Boîte Sondage
\$dynabox[2] = stripslashes(\"$dynabox[2]\"); // Boîte Compteur
\$dynabox[3] = stripslashes(\"$dynabox[3]\"); // Boîte Recherche
\$dynabox[4] = stripslashes(\"$dynabox[4]\"); // Boîte Calendrier
\$dynabox[5] = stripslashes(\"$dynabox[5]\"); // Boîte préférences
\$dynabox[6] = stripslashes(\"$dynabox[6]\"); // Boîte Newsletter
\$dynabox[7] = stripslashes(\"$dynabox[7]\"); // Boîte RSS
\$dynabox[8] = stripslashes(\"$dynabox[8]\"); // Boîte Webmaster - Infos
\$dynabox[9] = stripslashes(\"$dynabox[9]\"); // Boîte réseaux sociaux
\$dynabox[10] = stripslashes(\"$dynabox[10]\"); // Boîte Rubriques blog
\$dynabox[11] = stripslashes(\"$dynabox[11]\"); // Boîte Derniers billets blog
\$dynabox[12] = stripslashes(\"$dynabox[12]\"); // Boîte Derniers commentaires blog
\$dynabox[13] = stripslashes(\"$dynabox[13]\"); // Boîte Calendrier du blog
\$dynabox[14] = stripslashes(\"$dynabox[14]\"); // Boîte Billets des amis blog
\$dynabox[15] = stripslashes(\"$dynabox[15]\"); // Boîte Nouveautés du site
\$dynabox[16] = stripslashes(\"$dynabox[16]\"); // Boîte Contacts avancés
\$dynabox[17] = stripslashes(\"$dynabox[17]\"); // Boîte Photorama
\$dynabox[18] = stripslashes(\"$dynabox[18]\"); // Boîte Archives blog
\$dynabox[19] = stripslashes(\"$dynabox[19]\"); // Page affichage tous les documents
\$dynabox[20] = stripslashes(\"$dynabox[20]\"); // Page affichage tous les posts à cloturer

\$serviz[0] = stripslashes(\"on\"); // Citations ON
\$serviz[1] = stripslashes(\"$serviz[1]\"); // Choix Page d accueil
\$serviz[2] = stripslashes(\"$serviz[2]\"); // Nb de news / page
\$serviz[3] = stripslashes(\"$serviz[3]\"); // Carnet de bord du site
\$serviz[4] = stripslashes(\"$serviz[4]\"); // Nb de téléchargements / page
\$serviz[5] = stripslashes(\"$serviz[5]\"); // Nb de liens / page
\$serviz[6] = stripslashes(\"$serviz[6]\"); // Nb de FAQ / page
\$serviz[7] = stripslashes(\"$serviz[7]\"); // Nb de messages Guestbook / page
\$serviz[8] = stripslashes(\"$serviz[8]\"); // Nouvelles ON / OFF
\$serviz[9] = stripslashes(\"$serviz[9]\"); // Photos ON / OFF
\$serviz[10] = stripslashes(\"$serviz[10]\"); // Téléchargements ON / OFF
\$serviz[11] = stripslashes(\"$serviz[11]\"); // Liens ON / OFF
\$serviz[12] = stripslashes(\"$serviz[12]\"); // Livre d or ON / OFF
\$serviz[13] = stripslashes(\"$serviz[13]\"); // Forum ON / OFF
\$serviz[14] = stripslashes(\"$serviz[14]\"); // FAQ ON / OFF
// serviz[15] = stripslashes(\"\"); // Inutilisé
\$serviz[16] = stripslashes(\"$serviz[16]\"); // Publication Nouvelles RSS ON / OFF
\$serviz[17] = stripslashes(\"$serviz[17]\"); // Nb de messages Forum / page
\$serviz[18] = stripslashes(\"$serviz[18]\"); // Désactiver le courrier interne ON / OFF
\$serviz[19] = stripslashes(\"on\"); // Bannières ON
\$serviz[20] = stripslashes(\"$serviz[20]\"); // Nb de messages Thread / page
\$serviz[21] = stripslashes(\"$serviz[21]\"); // Contact site ON / OFF
// serviz[22] = stripslashes(\"\"); // Inutilisé
// serviz[23] = stripslashes(\"\");  // Inutilisé
\$serviz[24] = stripslashes(\"$serviz[24]\"); // Boîte recherche ON / OFF
\$serviz[25] = stripslashes(\"$serviz[25]\"); // Rotation automatique des bannières
\$serviz[26] = stripslashes(\"$serviz[26]\"); // Durée d affichage en secondes d une bannière
\$serviz[27] = stripslashes(\"$serviz[27]\"); // Billets RSS des amis ON /OFF
// serviz[28] is in confskin.inc  // Préférences visiteurs
\$serviz[29] = stripslashes(\"$serviz[29]\"); // Réactions aux articles ON / OFF / Ok Admin
\$serviz[30] = stripslashes(\"$serviz[30]\"); // Nb Commentaires blog - Réactions aux articles / page
\$serviz[31] = stripslashes(\"$serviz[31]\"); // Pseudo administrateur principal
\$serviz[32] = stripslashes(\"$serviz[32]\"); // Accès à l administration rapide
\$serviz[33] = stripslashes(\"$serviz[33]\"); // Compteur de lecture Articles
\$serviz[34] = stripslashes(\"$serviz[34]\"); // Compteur de lecture Threads Forum
\$serviz[35] = stripslashes(\"$serviz[35]\"); // Compteur de Nb de téléchargements
\$serviz[36] = stripslashes(\"$serviz[36]\"); // Newsletter ON / OFF /TEST
\$serviz[37] = stripslashes(\"$serviz[37]\"); // Nb de news publiées
// \$serviz[38] is in confskin.inc  // RSS News ON / OFF
\$serviz[39] = stripslashes(\"$serviz[39]\"); // Durée Cache RSS
\$serviz[40] = stripslashes(\"$serviz[40]\"); // Publication dans livre d or
\$serviz[41] = stripslashes(\"$serviz[41]\"); // Publication dans forum
\$serviz[42] = stripslashes(\"$serviz[42]\"); // Option Rédacteur ON / OFF
\$serviz[43] = stripslashes(\"$serviz[43]\"); // Avatar préférences webmaster
\$serviz[44] = stripslashes(\"$serviz[44]\"); // Mise en maintenance du site
\$serviz[45] = stripslashes(\"on\"); // Masquer l icône Admin (forcé à on)
\$serviz[46] = stripslashes(\"$serviz[46]\"); // Nb de messages Agenda / page
\$serviz[47] = stripslashes(\"$serviz[47]\"); // Agenda ON / OFF
\$serviz[48] = stripslashes(\"$serviz[48]\"); // Nb de messages maximum dans Tinymessage
// serviz[49] = stripslashes(\"\"); // Inutilisé
\$serviz[50] = stripslashes(\"$serviz[50]\"); // Nb maxi de fichiers dans data/error
\$serviz[51] = stripslashes(\"$serviz[51]\"); // Nb maxi de lignes dans l anti-spam
\$serviz[52] = stripslashes(\"$serviz[52]\"); // Durée maxi de validité du code anti-spam
\$serviz[53] = stripslashes(\"$serviz[53]\"); // Blog ON / OFF
\$serviz[54] = stripslashes(\"$serviz[54]\"); // Publication Blog RSS ON / OFF
\$serviz[55] = stripslashes(\"$serviz[55]\"); // Nb de Blogs / page
\$serviz[56] = stripslashes(\"$serviz[56]\"); // Nb de blogs publiés
\$serviz[57] = stripslashes(\"$serviz[57]\"); // Commentaires sur Blog ON / OFF / Ok Admin
\$serviz[58] = stripslashes(\"$serviz[58]\"); // Nombre de lettres par lot
\$serviz[59] = stripslashes(\"$serviz[59]\"); // Menu dynamique Blog WEB
\$serviz[60] = stripslashes(\"$serviz[60]\"); // Publication Articles RSS ON / OFF
\$serviz[61] = stripslashes(\"$serviz[61]\"); // Nb de billets récents/commentaires / page
\$serviz[62] = stripslashes(\"$serviz[62]\"); // Recommander ON/OFF
\$serviz[63] = stripslashes(\"$serviz[63]\"); // Afficher le nombre d abonnés à la newsletter ON/OFF
\$serviz[64] = stripslashes(\"$serviz[64]\"); // Cacher la date de création dans Articles ON/OFF
\$serviz[65] = stripslashes(\"$serviz[65]\"); // Cacher la date de modification dans Articles ON/OFF
\$serviz[66] = stripslashes(\"$serviz[66]\"); // Cacher la catégorie dans Articles ON/OFF
\$serviz[67] = stripslashes(\"$serviz[67]\"); // Cacher le compteur dans Articles ON/OFF
\$serviz[68] = stripslashes(\"$serviz[68]\"); // Cacher les boutons  Prévisualiser et Imprimer dans Articles ON/OFF
\$serviz[69] = stripslashes(\"$serviz[69]\"); // Cacher la date de création dans Faq ON/OFF
\$serviz[70] = stripslashes(\"$serviz[70]\"); // Cacher la date de modification dans Faq  ON/OFF
\$serviz[71] = stripslashes(\"$serviz[71]\"); // Cacher le bouton Imprimer dans Faq ON/OFF
\$serviz[72] = stripslashes(\"$serviz[72]\"); // Cacher la date de création dans Nouvelles ON/OFF
\$serviz[73] = stripslashes(\"$serviz[73]\"); // Cacher l auteur dans Nouvelles ON/OFF
// serviz[74] is in confskin.inc // Cacher les boîtes en page d accueil
\$serviz[75] = stripslashes(\"$serviz[75]\"); // Nombre de documents dans les listes en Admin
\$serviz[76] = stripslashes(\"$serviz[76]\"); // Cacher les boutons  Prévisualiser et Imprimer dans Blog ON/OFF
\$serviz[77] = stripslashes(\"$serviz[77]\"); // Cacher les boutons  Prévisualiser et Imprimer dans Download ON/OFF
\$serviz[78] = stripslashes(\"$serviz[78]\"); // Afficher la liste dépliée dans TéléchargementON/OFF
\$serviz[79] = stripslashes(\"$serviz[79]\"); // Afficher la liste dépliée dans FAQ ON/OFF
\$serviz[80] = stripslashes(\"$serviz[80]\"); // Afficher la liste dépliée dans Liens ON/OFF
\$serviz[81] = stripslashes(\"$serviz[81]\"); // Afficher la liste dépliée dans Photorama ON/OFF
\$serviz[82] = stripslashes(\"$serviz[82]\"); // Nombre d articles dans le flux RSS articles
\$serviz[83] = stripslashes(\"$serviz[83]\"); // Boite latérale diaporama
\$serviz[84] = stripslashes(\"$serviz[84]\"); // Afficher IP des visiteurs
\$serviz[85] = stripslashes(\"$serviz[85]\"); // Site de contrôle des IP des visiteurs
\$serviz[86] = stripslashes(\"$serviz[86]\"); // Autoriser la liste des membres
\$serviz[87] = stripslashes(\"$serviz[87]\"); // Nombre maximum de galeries par page
\$serviz[88] = stripslashes(\"$serviz[88]\"); // Activer le captcha pour les préférences
\$serviz[89] = stripslashes(\"$serviz[89]\"); // Activer le captcha pour les nouvelles
\$serviz[90] = stripslashes(\"$serviz[90]\"); // Activer le captcha pour le livre d or
\$serviz[91] = stripslashes(\"$serviz[91]\"); // Activer le captcha pour le forum
\$serviz[92] = stripslashes(\"$serviz[92]\"); // Activer le captcha pour les réactions aux articles
\$serviz[93] = stripslashes(\"$serviz[93]\"); // Activer le captcha pour l inscription à la newsletter
\$serviz[94] = stripslashes(\"$serviz[94]\"); // Activer le captcha pour recommander
\$serviz[95] = stripslashes(\"$serviz[95]\"); // Activer le captcha pour le contact webmestre
\$serviz[96] = stripslashes(\"$serviz[96]\"); // Activer le captcha pour le blog
\$serviz[97] = stripslashes(\"$serviz[97]\"); // Activer le captcha pour les commentaires blog
\$serviz[98] = stripslashes(\"$serviz[98]\"); // Désactiver le captcha pour les membres
\$serviz[99] = stripslashes(\"$serviz[99]\"); // Contact site avancé
\$serviz[100] = stripslashes(\"$serviz[100]\"); // Type de captcha 0=pas de captcha 2=texte 3=sélection texte
\$serviz[101] = stripslashes(\"$serviz[101]\"); // Largeur de l éditeur
\$serviz[102] = stripslashes(\"$serviz[102]\"); // Cacher la barre de navigation dans articles ON/OFF
\$serviz[103] = stripslashes(\"$serviz[103]\"); // Ensemble des publications RSS
\$serviz[104] = stripslashes(\"$serviz[104]\"); // Inclure AddThis dans le blog
\$serviz[105] = stripslashes(\"$serviz[105]\"); // Inclure AddThis dans les articles
\$serviz[106] = stripslashes(\"$serviz[106]\"); // Inclure AddThis dans les nouvelles
\$serviz[107] = stripslashes(\"$serviz[107]\"); // Inclure AddThis dans le forum
\$serviz[108] = stripslashes(\"$serviz[108]\"); // Rotation automatique des citations
\$serviz[109] = stripslashes(\"$serviz[109]\"); // Durée d affichage en secondes d une citation
\$serviz[110] = stripslashes(\"$serviz[110]\"); // Délai suppression inscription newsletter 
\$serviz[111] = stripslashes(\"$serviz[111]\"); // Afficher les visites du jour
// serviz[112] = stripslashes(\"\"); // Inutilisé
\$serviz[113] = stripslashes(\"$serviz[113]\"); // Cacher les mots-clés dans Articles ON/OFF
\$serviz[114] = stripslashes(\"$serviz[114]\"); // Cacher l auteur des billets du blog ON/OFF
\$serviz[115] = stripslashes(\"$serviz[115]\"); // Captcha sensible à la casse ON/OFF
\$serviz[116] = stripslashes(\"$serviz[116]\"); // Nouveautés du site WEB
\$serviz[117] = stripslashes(\"$serviz[117]\"); // Nouveautés depuis la dernière connexion WEB
\$serviz[118] = stripslashes(\"$serviz[118]\"); // Nouveautés depuis un nombre de jours WEB
\$serviz[119] = stripslashes(\"$serviz[119]\"); // Nombre de jours à mémoriser WEB
\$serviz[120] = stripslashes(\"$serviz[120]\"); // Hauteur en pixels des nouveautés WEB
\$serviz[121] = stripslashes(\"$serviz[121]\"); // Inclure le calendrier dans les nouveautés
\$serviz[122] = stripslashes(\"$serviz[122]\"); // Inclure les articles dans les nouveautés
\$serviz[123] = stripslashes(\"$serviz[123]\"); // Inclure le blog dans les nouveautés
\$serviz[124] = stripslashes(\"$serviz[124]\"); // Inclure les téléchargements dans les nouveautés
\$serviz[125] = stripslashes(\"$serviz[125]\"); // Inclure la faq dans les nouveautés
\$serviz[126] = stripslashes(\"$serviz[126]\"); // Inclure le forum dans les nouveautés
\$serviz[127] = stripslashes(\"$serviz[127]\"); // Inclure les liens dans les nouveautés
\$serviz[128] = stripslashes(\"$serviz[128]\"); // Inclure le livre d or dans les nouveautés
\$serviz[129] = stripslashes(\"$serviz[129]\"); // Inclure les nouvelles dans les nouveautés
\$serviz[130] = stripslashes(\"$serviz[130]\"); // Inclure les photos dans les nouveautés
// serviz[131] is in confskin.inc  // Page associée au blog hors pleine page
\$serviz[132] = stripslashes(\"$serviz[132]\"); // Tous les documents
\$serviz[133] = stripslashes(\"$serviz[133]\"); // Hauteur maxi en pixels d une cellule de fil RSS
\$serviz[135] = stripslashes(\"$serviz[135]\"); // Nb jours affichage image nouveauté
\$serviz[136] = stripslashes(\"$serviz[136]\"); // Image nouveauté articles ON / OFF / Membres
\$serviz[137] = stripslashes(\"$serviz[137]\"); // Image nouveauté nouvelles ON / OFF / Membres
\$serviz[138] = stripslashes(\"$serviz[138]\"); // Image nouveauté blog ON / OFF / Membres
\$serviz[139] = stripslashes(\"$serviz[139]\"); // Image nouveauté forum ON / OFF / Membres
\$serviz[140] = stripslashes(\"$serviz[140]\"); // Image nouveauté faq ON / OFF / Membres
\$serviz[141] = stripslashes(\"$serviz[141]\"); // Image nouveauté livre d or ON / OFF / Membres
\$serviz[142] = stripslashes(\"$serviz[142]\"); // Image nouveauté photo ON / OFF / Membres
\$serviz[143] = stripslashes(\"$serviz[143]\"); // Image nouveauté téléchargements ON / OFF / Membres
\$serviz[144] = stripslashes(\"$serviz[144]\"); // Image nouveauté liens ON / OFF / Membres
\$serviz[145] = stripslashes(\"$serviz[145]\"); // Image nouveauté agenda ON / OFF / Membres
// serviz[146] = stripslashes(\"\"); // Inutilisé
// serviz[147] = stripslashes(\"\"); // Inutilisé
\$serviz[148] = stripslashes(\"$serviz[148]\"); // Nombre maxi de caractères du pseudo
\$serviz[149] = stripslashes(\"$serviz[149]\"); // Boîte compteur dans préférences
\$serviz[150] = stripslashes(\"$serviz[150]\"); // Téléchargements les plus récents
\$serviz[151] = stripslashes(\"$serviz[151]\"); // Téléchargements les plus téléchargés
\$serviz[152] = stripslashes(\"$serviz[152]\"); // Position texte et icône inversés pour les nouvelles
\$serviz[153] = stripslashes(\"$serviz[153]\"); // Position texte et icône inversés pour les billets
\$serviz[154] = stripslashes(\"$serviz[154]\"); // Image nouveauté option de menu ON / OFF / Membres
\$serviz[155] = stripslashes(\"$serviz[155]\"); // Type de courriel Contact
\$serviz[156] = stripslashes(\"$serviz[156]\"); // Photorama pleine page ON / OFF
\$serviz[157] = stripslashes(\"$serviz[157]\"); // Page secondaire du photorama
\$serviz[158] = stripslashes(\"$serviz[158]\"); // Cacher le nombre de membres inscrits
\$serviz[159] = stripslashes(\"$serviz[159]\"); // Cacher les membres connectés
\$serviz[166] = stripslashes(\"$serviz[166]\"); // Inclure les réactions articles dans les nouveautés
\$serviz[167] = stripslashes(\"$serviz[167]\"); // Inclure les commentaires blog dans les nouveautés
\$serviz[168] = stripslashes(\"$serviz[168]\"); // Pause entre les lots
\$serviz[169] = stripslashes(\"$serviz[169]\"); // Cacher la date des nouveautés du site WEB
\$serviz[171] = stripslashes(\"$serviz[171]\"); // Page secondaire du calendrier
\$serviz[172] = stripslashes(\"$serviz[172]\"); // Page secondaire utilisateur
\$serviz[173] = stripslashes(\"$serviz[173]\"); // Page secondaire connexion
\$serviz[174] = stripslashes(\"$serviz[174]\"); // Affichage tri des photos au sein de la galerie de photos par id ou no ordre
\$serviz[175] = stripslashes(\"$serviz[175]\"); // Autoriser les images dans ckeditor public
\$serviz[176] = stripslashes(\"$serviz[176]\"); // Pseudo administrateur secondaire
\$serviz[177] = stripslashes(\"$serviz[177]\"); // Numéro article pour les mentions légales
\$serviz[178] = stripslashes(\"$serviz[178]\"); // Numéro page pour afficher les mentions légales
\$serviz[179] = stripslashes(\"$serviz[179]\"); // Groupe pour accès à la liste des membres
\$serviz[180] = stripslashes(\"$serviz[180]\"); // Lister tous les membres
\$serviz[181] = stripslashes(\"$serviz[181]\"); // Cacher topblog ON / OFF
\$serviz[182] = stripslashes(\"$serviz[182]\"); // Affichage tri des galeries par catégorie ou Catégorie ASC et ID desc
\$serviz[183] = stripslashes(\"$serviz[183]\"); // Clôture automatique des posts du forum
\$serviz[184] = stripslashes(\"$serviz[184]\"); // Délai de cloture en nombre de jours

\$forum[3]  = stripslashes(\"$forum[3]\"); // Forum pleine page ON / OFF
\$forum[4]  = stripslashes(\"$forum[4]\"); // Position des boîtes
\$forum[5]  = stripslashes(\"$forum[5]\"); // Durée thread up (heures)
\$forum[6]  = stripslashes(\"$forum[6]\"); // Durée thread au top (jours)
\$forum[7]  = stripslashes(\"$forum[7]\"); // Echelle des messages
\$forum[8]  = stripslashes(\"$forum[8]\"); // Durée nouveau
\$forum[9]  = stripslashes(\"$forum[9]\"); // Affichage charte du forum ON / OFF
\$forum[10] = stripslashes(\"$forum[10]\"); // Affichage du lien uniquement ON / OFF
\$forum[15] = stripslashes(\"$forum[15]\"); // Cacher topforum ON / OFF

\$supervision[0] = stripslashes(\"$supervision[0]\"); // e-mail compteur
\$supervision[1] = stripslashes(\"$supervision[1]\"); // incrément de compteur pour e-mail
\$supervision[2] = stripslashes(\"$supervision[2]\"); // e-mail publication nouvelle
\$supervision[3] = stripslashes(\"$supervision[3]\"); // e-mail publication livre d or
\$supervision[4] = stripslashes(\"$supervision[4]\"); // e-mail publication forum
\$supervision[5] = stripslashes(\"$supervision[5]\"); // Type d e-mail PHP
\$supervision[6] = stripslashes(\"$supervision[6]\"); // e-mail réagir aux articles
\$supervision[7] = stripslashes(\"$supervision[7]\"); // e-mail inscription ou résiliation Newsletter
\$supervision[8] = stripslashes(\"$supervision[8]\"); // e-mail publication blog
\$supervision[9] = stripslashes(\"$supervision[9]\"); // e-mail commentaire blog
\$supervision[10] = stripslashes(\"$supervision[10]\"); // liste des e-mails pour la supervision
\$supervision[11] = stripslashes(\"$supervision[11]\"); // e-mail ouverture ou fermeture de compte
\$supervision[12] = stripslashes(\"$supervision[12]\"); // e-mail erreurs de connexion

\$smtp[0] = stripslashes(\"$smtp[0]\"); // serveur SMTP
\$smtp[1] = stripslashes(\"$smtp[1]\"); // avec authentification ON / OFF
\$smtp[2] = stripslashes(\"$smtp[2]\"); // préfixe serveur  / SSL / TLS
\$smtp[3] = stripslashes(\"$smtp[3]\"); // numéro de port SMTP
\$smtp[4] = stripslashes(\"$smtp[4]\"); // adresse mail utilisateur
\$smtp[5] = stripslashes(\"$smtp[5]\"); // mot de passe utilisateur
\$smtp[6] = stripslashes(\"$smtp[6]\"); // Log ON / OFF
\$smtp[7] = stripslashes(\"$smtp[7]\"); // Log si ON, nombre de jours de conservations

\$sitemap['SM'] = stripslashes(\"".$sitemap['SM']."\"); // Création d un sitemap
\$sitemap['BL'] = stripslashes(\"".$sitemap['BL']."\"); // Inclure le blog dans le sitemap
\$sitemap['AR'] = stripslashes(\"".$sitemap['AR']."\"); // Inclure les articles dans le sitemap
\$sitemap['NE'] = stripslashes(\"".$sitemap['NE']."\"); // Inclure les nouvelles dans le sitemap
\$sitemap['PH'] = stripslashes(\"".$sitemap['PH']."\"); // Inclure les photos dans le sitemap
\$sitemap['DN'] = stripslashes(\"".$sitemap['DN']."\"); // Inclure les téléchargements dans le sitemap
\$sitemap['FA'] = stripslashes(\"".$sitemap['FA']."\"); // Inclure la faq dans le sitemap
\$sitemap['LI'] = stripslashes(\"".$sitemap['LI']."\"); // Inclure les liens dans le sitemap
\$sitemap['GB'] = stripslashes(\"".$sitemap['GB']."\"); // Inclure le livre d or dans le sitemap
\$sitemap['FR'] = stripslashes(\"".$sitemap['FR']."\"); // Inclure le forum dans le sitemap
\$sitemap['AG'] = stripslashes(\"".$sitemap['AG']."\"); // Inclure l agenda dans le sitemap

\$statscfg['Y']   = stripslashes(\"".$statscfg['Y']."\");   // Mémoriser les fichiers année de statistiques
\$statscfg['M']   = stripslashes(\"".$statscfg['M']."\");   // Mémoriser les fichiers mois de statistiques
\$statscfg['D']   = stripslashes(\"".$statscfg['D']."\");   // Mémoriser les fichiers jour de statistiques
\$statscfg['NBY'] = stripslashes(\"".$statscfg['NBY']."\"); // Nombre d année à mémoriser

// members[0] = stripslashes(\"\"); // Inutilisé
\$members[1] = stripslashes(\"$members[1]\"); // Zone membre accès à la section Articles
\$members[2] = stripslashes(\"$members[2]\"); // Zone membre accès à la section Photo
\$members[3] = stripslashes(\"$members[3]\"); // Zone membre accès à la section Liens
\$members[4] = stripslashes(\"$members[4]\"); // Zone membre accès à la section FAQ
\$members[5] = stripslashes(\"$members[5]\"); // Zone membre accès à la section Forum
// members[6] = stripslashes(\"\"); // Inutilisé
\$members[7] = stripslashes(\"$members[7]\"); // Zone membre accès à la section Téléchargement
\$members[8] = stripslashes(\"$members[8]\"); // Zone membre accès publication Nouvelles
\$members[9] = stripslashes(\"$members[9]\"); // Zone membre accès publication Livre d Or
\$members[10] = stripslashes(\"$members[10]\"); // Zone membre accès publication Forum
\$members[11] = stripslashes(\"$members[11]\"); // Zone membre accès publication Reactions aux articles
\$members[12] = stripslashes(\"$members[12]\"); // Zone membre accès à la section Livre d Or
\$members[13] = stripslashes(\"$members[13]\"); // Zone membre accès à la section Nouvelles
\$members[14] = stripslashes(\"$members[14]\"); // Zone membre accès à la section Agenda
\$members[15] = stripslashes(\"$members[15]\"); // Zone membre accès à la section Blog
\$members[16] = stripslashes(\"$members[16]\"); // Zone membre accès publication Blog
\$members[17] = stripslashes(\"$members[17]\"); // Zone membre accès publication Commentaires blog
\$members[18] = stripslashes(\"$members[18]\"); // Zone membre accès inscription Newsletter
\$members[19] = stripslashes(\"$members[19]\"); // Inscriptions membres on /off / ok mail / ok webmaster
\$members[20] = stripslashes(\"$members[20]\"); // Zone membre accès contact avancé
\$members[21] = stripslashes(\"$members[21]\"); // Délai expiration cookie utilisateur
\$members[22] = stripslashes(\"$members[22]\"); // Zone membre accès nouveautés du site
\$members[23] = stripslashes(\"$members[23]\"); // Zone membre accès boîtes recherche

\$configlogNB = stripslashes(\"$configlogNB\"); // Nombre de lignes dans LogBook
\$droitaccegroupredac = stripslashes(\"$droitaccegroupredac\"); // Mise en place des dossiers spécifiques pour les rédacteurs
\$actionwaf = stripslashes(\"$actionwaf\"); // Activation pare-feu bannisement/blacklistage
";

include CHEMIN.INCREP.'lang/'.$lng.'-admin.inc';
$label = array(1049, 570, 572, 574, 573, 567, 571, 568, 569, 1050, 1051, 552, 551, 1048, 1052, 1053, 1054, 1055, 1056, 1061);
$n = 0;
foreach ($configlog as $key=>$value) {
    $mettre .= '$configlog['.$key.'] = stripslashes("'.$value.'"); // '.$admin[$label[$n]]."\n";
	$n++;
}
$mettre .= "\n";
$mettre .= '
?>';
WriteFullDB(CONFIG,$mettre);

?>