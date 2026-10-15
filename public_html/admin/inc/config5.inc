<?php
/*******************************************************************************
 *   Admin Configuration
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
  * v6.00.21 (December 17, 2024) : Check Button and Delay in a day for closing posts forum
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'config5.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[4] == '') {
        $nomzone = $admin[169];
        include 'access.inc';
        exit();
    }
}
$regit = import('regit');

if ($regit == 1) {
    include CONFIG;
	$urls   = file_exists(MAPSURLS) ? file_get_contents(MAPSURLS) : '';
	$ctrlsb = $sitemap['SM'].$sitemap['BL'].$sitemap['AR'].$sitemap['NE'].$sitemap['PH'].$sitemap['DN'].$sitemap['FA'].$sitemap['LI'].$sitemap['GB'].$sitemap['FR'].$sitemap['AG'].$urls;

	// on renomme les scripts contenant des formulaires lutte anti-spam
	$site['PG']  = empty($site['PG'])  ? 'postguest'  : $site['PG'];
	$site['BLS'] = empty($site['BLS']) ? 'blogs'      : $site['BLS'];
	$site['BL']  = empty($site['BL'])  ? 'blog'       : $site['BL'];
	$site['FR']  = empty($site['FR'])  ? 'forum'      : $site['FR'];
	$site['FRT'] = empty($site['FRT']) ? 'fortopic'   : $site['FRT'];
	$site['TH']  = empty($site['TH'])  ? 'thread'     : $site['TH'];
	$site['GB']  = empty($site['GB'])  ? 'guestbk'    : $site['GB'];
	$site['NE']  = empty($site['NE'])  ? 'news'       : $site['NE'];
	$site['USR'] = empty($site['USR']) ? 'user'       : $site['USR'];
	$site['NL']  = empty($site['NL'])  ? 'newsletter' : $site['NL'];
	$site['DN']  = empty($site['DN'])  ? 'download'   : $site['DN'];
	$site['CT']  = empty($site['CT'])  ? 'contact'    : $site['CT'];
	
	$ctrlb = $site['PG'].$site['BLS'].$site['BL'].$site['FR'].$site['FRT'].$site['TH'].$site['GB'].$site['NE'].$site['USR'].$site['NL'].$site['DN'].$site['CT'];

	$sitepg  = import('sitepg');
	$sitepg  = empty($sitepg) ? 'postguest' : $sitepg;
	$sitepg  = preg_match('!^[a-z0-9_]{2,20}$!', $sitepg)  ? $sitepg  : $site['PG'];
	$sitebls = import('sitebls');
	$sitebls = empty($sitebls) ? 'blogs' : $sitebls;
	$sitebls = preg_match('!^[a-z0-9_]{2,20}$!', $sitebls) ? $sitebls : $site['BLS'];
	$sitebl  = import('sitebl');
	$sitebl  = empty($sitebl) ? 'blog' : $sitebl;
	$sitebl  = preg_match('!^[a-z0-9_]{2,20}$!', $sitebl)  ? $sitebl  : $site['BL'];
	$sitefr  = import('sitefr');
	$sitefr  = empty($sitefr) ? 'forum' : $sitefr;
	$sitefr  = preg_match('!^[a-z0-9_]{2,20}$!', $sitefr)  ? $sitefr  : $site['FR'];
	$sitefrt = import('sitefrt');
	$sitefrt = empty($sitefrt) ? 'fortopic' : $sitefrt;
	$sitefrt = preg_match('!^[a-z0-9_]{2,20}$!', $sitefrt) ? $sitefrt : $site['FRT'];
	$siteth  = import('siteth');
	$siteth  = empty($siteth) ? 'thread' : $siteth;
	$siteth  = preg_match('!^[a-z0-9_]{2,20}$!', $siteth)  ? $siteth  : $site['TH'];
	$sitegb  = import('sitegb');
	$sitegb  = empty($sitegb) ? 'guestbk' : $sitegb;
	$sitegb  = preg_match('!^[a-z0-9_]{2,20}$!', $sitegb)  ? $sitegb  : $site['GB'];
	$sitene  = import('sitene');
	$sitene  = empty($sitene) ? 'news' : $sitene;
	$sitene  = preg_match('!^[a-z0-9_]{2,20}$!', $sitene)  ? $sitene  : $site['NE'];
	$siteusr = import('siteusr');
	$siteusr = empty($siteusr) ? 'user' : $siteusr;
	$siteusr = preg_match('!^[a-z0-9_]{2,20}$!', $siteusr) ? $siteusr : $site['USR'];
	$sitenl  = import('sitenl');
	$sitenl  = empty($sitenl) ? 'newsletter' : $sitenl;
	$sitenl  = preg_match('!^[a-z0-9_]{2,20}$!', $sitenl)  ? $sitenl  : $site['NL'];
	$sitedn  = import('sitedn');
	$sitedn  = empty($sitedn) ? 'download' : $sitedn;
	$sitedn  = preg_match('!^[a-z0-9_]{2,20}$!', $sitedn)  ? $sitedn  : $site['DN'];
	$sitect  = import('sitect');
	$sitect  = empty($sitect) ? 'contact' : $sitect;
	$sitect  = preg_match('!^[a-z0-9_]{2,20}$!', $sitect)  ? $sitect  : $site['CT'];
	
	$droitaccegroupredac = import('droitaccegroupredac');

	$ctrls   = array_count_values(array($sitepg, $sitebls, $sitebl, $sitefr, $sitefrt, $siteth, $sitegb, $sitene, $siteusr, $sitenl, $sitedn, $sitect));

	$sitepg  = file_exists(CHEMIN.$sitepg.'php')  || $ctrls[$sitepg]  > 1 ? $site['PG']  : $sitepg;
	$sitebls = file_exists(CHEMIN.$sitebls.'php') || $ctrls[$sitebls] > 1 ? $site['BLS'] : $sitebls;
	$sitebl  = file_exists(CHEMIN.$sitebl.'php')  || $ctrls[$sitebl]  > 1 ? $site['BL']  : $sitebl;
	$sitefr  = file_exists(CHEMIN.$sitefr.'php')  || $ctrls[$sitefr]  > 1 ? $site['FR']  : $sitefr;
	$sitefrt = file_exists(CHEMIN.$sitefrt.'php') || $ctrls[$sitefrt] > 1 ? $site['FRT'] : $sitefrt;
	$siteth  = file_exists(CHEMIN.$siteth.'php')  || $ctrls[$siteth]  > 1 ? $site['TH']  : $siteth;
	$sitegb  = file_exists(CHEMIN.$sitegb.'php')  || $ctrls[$sitegb]  > 1 ? $site['GB']  : $sitegb;
	$sitene  = file_exists(CHEMIN.$sitene.'php')  || $ctrls[$sitene]  > 1 ? $site['NE']  : $sitene;
	$siteusr = file_exists(CHEMIN.$siteusr.'php') || $ctrls[$siteusr] > 1 ? $site['USR'] : $siteusr;
	$sitenl  = file_exists(CHEMIN.$sitenl.'php')  || $ctrls[$sitenl]  > 1 ? $site['NL']  : $sitenl;
	$sitedn  = file_exists(CHEMIN.$sitedn.'php')  || $ctrls[$sitedn]  > 1 ? $site['DN']  : $sitedn;
	$sitect  = file_exists(CHEMIN.$sitect.'php')  || $ctrls[$sitect]  > 1 ? $site['CT']  : $sitect;

	if ($sitepg != $site['PG']) 
		if (!rename(CHEMIN.$site['PG'].'.php', CHEMIN.$sitepg.'.php')) $sitepg = $site['PG'];
		elseif (file_exists(CHEMIN.$site['PG'].'.php')) @unlink(CHEMIN.$site['PG'].'.php');

	if ($sitebls != $site['BLS'])
		if (!rename(CHEMIN.$site['BLS'].'.php', CHEMIN.$sitebls.'.php')) $sitebls = $site['BLS'];
		elseif (file_exists(CHEMIN.$site['BLS'].'.php')) @unlink(CHEMIN.$site['BLS'].'.php');

	if ($sitebl != $site['BL'])
		if (!rename(CHEMIN.$site['BL'].'.php', CHEMIN.$sitebl.'.php')) $sitebl = $site['BL'];
		elseif (file_exists(CHEMIN.$site['BL'].'.php')) @unlink(CHEMIN.$site['BL'].'.php');

	if ($sitefr != $site['FR'])
		if (!rename(CHEMIN.$site['FR'].'.php', CHEMIN.$sitefr.'.php')) $sitefr = $site['FR'];
		elseif (file_exists(CHEMIN.$site['FR'].'.php')) @unlink(CHEMIN.$site['FR'].'.php');

	if ($sitefrt != $site['FRT'])
		if (!rename(CHEMIN.$site['FRT'].'.php', CHEMIN.$sitefrt.'.php')) $sitefrt = $site['FRT'];
		elseif (file_exists(CHEMIN.$site['FRT'].'.php')) @unlink(CHEMIN.$site['FRT'].'.php');

	if ($siteth != $site['TH'])
		if (!rename(CHEMIN.$site['TH'].'.php', CHEMIN.$siteth.'.php')) $siteth = $site['TH'];
		elseif (file_exists(CHEMIN.$site['TH'].'.php')) @unlink(CHEMIN.$site['TH'].'.php');

	if ($sitegb != $site['GB'])
		if (!rename(CHEMIN.$site['GB'].'.php', CHEMIN.$sitegb.'.php')) $sitegb = $site['GB'];
		elseif (file_exists(CHEMIN.$site['GB'].'.php')) @unlink(CHEMIN.$site['GB'].'.php');

	if ($sitene != $site['NE'])
		if (!rename(CHEMIN.$site['NE'].'.php', CHEMIN.$sitene.'.php')) $sitene = $site['NE'];
		elseif (file_exists(CHEMIN.$site['NE'].'.php')) @unlink(CHEMIN.$site['NE'].'.php');

	if ($siteusr != $site['USR'])
		if (!rename(CHEMIN.$site['USR'].'.php', CHEMIN.$siteusr.'.php')) $siteusr = $site['USR'];
		elseif (file_exists(CHEMIN.$site['USR'].'.php')) @unlink(CHEMIN.$site['USR'].'.php');

	if ($sitenl != $site['NL'])
		if (!rename(CHEMIN.$site['NL'].'.php', CHEMIN.$sitenl.'.php')) $sitenl = $site['NL'];
		elseif (file_exists(CHEMIN.$site['NL'].'.php')) @unlink(CHEMIN.$site['NL'].'.php');

	if ($sitedn != $site['DN'])
		if (!rename(CHEMIN.$site['DN'].'.php', CHEMIN.$sitedn.'.php')) $sitedn = $site['DN'];
		elseif (file_exists(CHEMIN.$site['DN'].'.php')) @unlink(CHEMIN.$site['DN'].'.php');

	if ($sitect != $site['CT'])
		if (!rename(CHEMIN.$site['CT'].'.php', CHEMIN.$sitect.'.php')) $sitect = $site['CT'];
		elseif (file_exists(CHEMIN.$site['CT'].'.php')) @unlink(CHEMIN.$site['CT'].'.php');
	
	$ctrla = $sitepg.$sitebls.$sitebl.$sitefr.$sitefrt.$siteth.$sitegb.$sitene.$siteusr.$sitenl.$sitedn.$sitect;
	
    $site[5]     = import('site5', 'POST', true, '0'); // Activation des news ou pas
    $site[10]    = import('site10'); // Page pour la redirection des erreurs 404
    $site[12]    = import('site12', 'POST', true, '0'); // Page secondaire pour la redirection des erreurs 404
    $site[13]    = import('site13', 'POST', true, '0'); // Hauteur en pixels des nouvelles
    $site[30]    = import('site30', 'POST', true, '0'); // Activation du blog ou pas
    $site[36]    = import('site36', 'POST', true, '0'); // Hauteur en pixels des billets du blog
	$site['NTC'] = max(1, import('siteNTC')); // Nombre de tentatives de connexion
	$site['PG']  = $sitepg; // Alias du script postguest.php
	$site['BLS'] = $sitebls; // Alias du script blogs.php
	$site['BL']  = $sitebl; // Alias du script blog.php
	$site['FR']  = $sitefr; // Alias du script forum.php
	$site['FRT'] = $sitefrt; // Alias du script fortopic.php
	$site['TH']  = $siteth; // Alias du script thread.php
	$site['GB']  = $sitegb; // Alias du script guestbk.php
	$site['NE']  = $sitene; // Alias du script news.php
	$site['USR'] = $siteusr; // Alias du script user.php
	$site['NL']  = $sitenl; // Alias du script newsletter.php
	$site['DN']  = $sitedn; // Alias du script download.php
	$site['CT']  = $sitect; // Alias du script contact.php

    $texte[3]    = import('texte3'); // Puce articles

    $menuico[12] = import('menuico12', 'POST', true, '0'); // Page exécution Nouvelles
    $menuico[13] = import('menuico13', 'POST', true, '0'); // Page exécution Blog
    $menuico[14] = import('menuico14', 'POST', true, '0'); // Page exécution Photorama
    $menuico[15] = import('menuico15', 'POST', true, '0'); // Page exécution Téléchargements
    $menuico[16] = import('menuico16', 'POST', true, '0'); // Page exécution Liens
    $menuico[17] = import('menuico17', 'POST', true, '0'); // Page exécution FAQ
    $menuico[18] = import('menuico18', 'POST', true, '0'); // Page exécution Livre d'or
    $menuico[19] = import('menuico19', 'POST', true, '0'); // Page exécution Forum
    $menuico[21] = import('menuico21', 'POST', true, '0'); // Page exécution Contact avancé

    $members[1]  = import('members1'); // Zone membre accès à la section Articles
    $members[2]  = import('members2'); // Zone membre accès à la section Photo
    $members[3]  = import('members3'); // Zone membre accès à la section Liens
    $members[4]  = import('members4'); // Zone membre accès à la section FAQ
    $members[5]  = import('members5'); // Zone membre accès à la section Forum
    $members[7]  = import('members7'); // Zone membre accès à la section Téléchargement
    $members[8]  = import('members8'); // Zone membre accès publication Nouvelles
    $members[9]  = import('members9'); // Zone membre accès publication Livre d'Or
    $members[10] = import('members10'); // Zone membre accès publication Forum
    $members[11] = import('members11'); // Zone membre accès publication Réactions aux articles
    $members[12] = import('members12'); // Zone membre accès à la section Livre d'Or
    $members[13] = import('members13'); // Zone membre accès à la section Nouvelles
    $members[14] = import('members14'); // Zone membre accès à la section Agenda
    $members[15] = import('members15'); // Zone membre accès à la section Blog
    $members[16] = import('members16'); // Zone membre accès publication Blog
    $members[17] = import('members17'); // Zone membre accès à publication Commentaires blogs
    $members[18] = import('members18'); // Zone membre accès à inscription Newsletter
    $members[20] = import('members20'); // Zone membre accès contact avancé
    $members[22] = import('members22'); // Zone membre accès nouveautés du site

    $serviz[0]  = 'on'; //  Citations, service désactivable par boite
    $serviz[1]  = import('serviz1', 'POST', true, '0'); // Choix page Accueil
    $serviz[2]  = max(1, import('serviz2')); // Nb de news / page
    $serviz[4]  = max(1, import('serviz4')); // Nb de téléchargements / page
    $serviz[5]  = max(1, import('serviz5')); // Nb de liens / page
    $serviz[6]  = max(1, import('serviz6')); // Nb de FAQ / page
    $serviz[7]  = max(1, import('serviz7')); // Nb de messages Guestbook / page
    $serviz[8]  = import('serviz8'); // Nouvelles ON / OFF
    $serviz[9]  = import('serviz9'); // Photos ON / OFF
    $serviz[10] = import('serviz10'); // Téléchargements ON / OFF
    $serviz[11] = import('serviz11'); // Liens ON / OFF
    $serviz[12] = import('serviz12'); // Livre d'or ON / OFF
    $serviz[13] = import('serviz13'); // Forum ON / OFF
    $serviz[14] = import('serviz14'); // FAQ ON / OFF
    $serviz[16] = import('serviz16'); // Publication Nouvelles RSS ON / OFF
    $serviz[17] = max(1, import('serviz17')); // Nb de messages Forum / page
    $serviz[18] = import('serviz18'); // Désactiver le courrier interne ON / OFF
    $serviz[19] = 'on'; //  Bannières, service désactivable par boite
    $serviz[20] = max(1, import('serviz20')); // Nb de messages Thread / page
    $serviz[21] = import('serviz21'); // Contact site ON /OFF
    $serviz[25] = import('serviz25'); // Rotation automatique des bannières
    $serviz[26] = max(1, import('serviz26')); // Durée d'affichage en secondes d'une bannière
    $serviz[27] = import('serviz27'); // Billets RSS des amis ON / OFF
    $serviz[29] = import('serviz29', 'POST', true, '0'); // Réactions aux articles ON / OFF / Ok Admin
    $serviz[30] = max(1, import('serviz30')); // Nb Réactions aux articles / page
    $serviz[33] = import('serviz33'); // Compteur de lecture Articles
    // Compteur de lecture Threads Forum transféré dans config7
    $serviz[35] = import('serviz35'); // Compteur de Nb de téléchargements
    $serviz[36] = import('serviz36'); // Newsletter ON / OFF
    $serviz[37] = max(1, import('serviz37')); // Nb de news / articles publiés
    $serviz[39] = import('serviz39'); // Durée Cache RSS
    $serviz[40] = import('serviz40', 'POST', true, '0'); // Publication dans le livre d'or
    $serviz[41] = import('serviz41', 'POST', true, '0'); // Publication dans le forum
    $serviz[43] = import('serviz43'); // Avatar préférences webmaster
    $serviz[45] = 'on'; // forcé à on : Masquer l'icône Admin
    $serviz[46] = max(1, import('serviz46')); // Nb de messages Agenda / page
    $serviz[47] = import('serviz47'); // Agenda ON / OFF
    $serviz[48] = max(1, import('serviz48')); // Nb de messages maximum dans Tinymessage
    $serviz[50] = max(1, import('serviz50')); // Nb maxi de fichiers dans data/error
    $serviz[51] = max(50, import('serviz51')); // Nb maxi de lignes dans l'anti-spam
    $serviz[52] = max(10, import('serviz52')); // Durée maxi de validité du code anti-spam
    $serviz[53] = import('serviz53'); // Blog ON / OFF
    $serviz[54] = import('serviz54'); // Publication Blog RSS ON / OFF
    $serviz[55] = max(1, import('serviz55')); // Nb de Blogs / page
    $serviz[56] = max(1, import('serviz56')); // Nb de blogs publiés
    $serviz[57] = import('serviz57', 'POST', true, '0'); // Commentaires sur Blog ON /OFF / OK Admin
    $serviz[58] = import('serviz58'); // Nombre de lettres par lot
    $serviz[59] = import('serviz59', 'POST', true, '0'); // Menu dynamique Blog WEB
    $serviz[60] = import('serviz60'); // Publication Articles RSS ON / OFF
    $serviz[61] = max(1, import('serviz61')); // Nb billets récents-commentaires / page
    $serviz[62] = import('serviz62'); // Recommander ON/OFF
    $serviz[63] = import('serviz63'); // Afficher le nombre d'abonnés à la newsletter ON/OFF
    $serviz[64] = import('serviz64'); // Cacher la date de création dans Articles ON/OFF
    $serviz[65] = import('serviz65'); // Cacher la date de modification dans Articles ON/OFF
    $serviz[66] = import('serviz66'); // Cacher la catégorie dans Articles ON/OFF
    $serviz[67] = import('serviz67'); // Cacher le compteur dans Articles ON/OFF
    $serviz[68] = import('serviz68'); // Cacher le bouton Imprimer dans Articles ON/OFF
    $serviz[69] = import('serviz69'); // Cacher la date de création dans Faq ON/OFF
    $serviz[70] = import('serviz70'); // Cacher la date de modification dans Faq  ON/OFF
    $serviz[71] = import('serviz71'); // Cacher le bouton Imprimer dans Faq ON/OFF
    $serviz[72] = import('serviz72'); // Cacher la date de création dans Nouvelles ON/OFF
    $serviz[73] = import('serviz73'); // Cacher l'auteur dans Nouvelles ON/OFF
	// Cacher les boîtes en page d'accueil ON/OFF transféré dans config3
    $serviz[75] = max(1, import('serviz75')); // Nombre de documents dans les listes en Admin
    $serviz[76] = import('serviz76'); // Cacher la date de modification dans Blog ON/OFF
    $serviz[77] = import('serviz77'); // Cacher la date de modification dans Download ON/OFF
    $serviz[78] = import('serviz78'); // Déplier la liste des téléchargements
    $serviz[79] = import('serviz79'); // Déplier la liste de la FAQ
    $serviz[80] = import('serviz80'); // Déplier la liste des liens
    $serviz[81] = import('serviz81'); // Déplier la liste des liens
    $serviz[82] = max(1, import('serviz82')); // Nombre d'articles dans le flux RSS articles
    $serviz[83] = import('serviz83', 'POST', true, '0'); // Boite latérale diaporama
    $serviz[84] = import('serviz84'); // Afficher IP des visiteurs
    $serviz[85] = import('serviz85'); // Site de contrôle des IP des visiteurs
    $serviz[86] = import('serviz86'); // Autoriser la liste des membres
    $serviz[87] = import('serviz87'); // Nombre maximum de galeries par page
    $serviz[88] = import('serviz88'); // Activer le captcha pour les préférences
    $serviz[89] = import('serviz89'); // Activer le captcha pour les nouvelles
    $serviz[90] = import('serviz90'); // Activer le captcha pour le livre d'or
    $serviz[91] = import('serviz91'); // Activer le captcha pour le forum
    $serviz[92] = import('serviz92'); // Activer le captcha pour les réactions aux articles
    $serviz[93] = import('serviz93'); // Activer le captcha pour l'inscription à la newsletter
    $serviz[94] = import('serviz94'); // Activer le captcha pour recommander
    $serviz[95] = import('serviz95'); // Activer le captcha pour le contact webmestre
    $serviz[96] = import('serviz96'); // Activer le captcha pour le blog
    $serviz[97] = import('serviz97'); // Activer le captcha pour les commentaires blog
    $serviz[98] = import('serviz98'); // Désactiver pour les membres
    $serviz[99] = import('serviz99'); // Contact site avancé
    $serviz[100] = import('serviz100', 'POST', true, '0'); // Type de captcha 0=pas de captcha 1=image 2=texte 3=sélection texte
    $serviz[102] = import('serviz102'); // Cacher la barre de navigation dans articles ON/OFF
	$serviz[103] = import('serviz103'); // Ensemble des publications RSS
	$serviz[104] = import('serviz104'); // Inclure AddThis dans le blog
	$serviz[105] = import('serviz105'); // Inclure AddThis dans les articles
	$serviz[106] = import('serviz106'); // Inclure AddThis dans les nouvelles
	$serviz[107] = import('serviz107'); // Inclure AddThis dans le forum
    $serviz[108] = import('serviz108'); // Rotation automatique des citations
    $serviz[109] = max(1, import('serviz109')); // Durée d'affichage en secondes d'une citation
	$serviz[110] = max(1, import('serviz110')); // Délai suppression inscription newsletter
	$serviz[111] = import('serviz111'); // Afficher les visites du jour
    $serviz[113] = import('serviz113'); // Cacher les mots-clés dans Articles ON/OFF
    $serviz[114] = import('serviz114'); // Cacher titre et auteur des billets ON/OFF
    $serviz[115] = import('serviz115'); // Captcha sensible à la casse ON/OFF
	$serviz[116] = import('serviz116'); // Nouveautés du site WEB
	$serviz[117] = import('serviz117'); // Nouveautés depuis la dernière connexion WEB
	$serviz[118] = import('serviz118'); // Nouveautés depuis un nombre de jours WEB
	$serviz[119] = max(1, import('serviz119')); // Nombre de jours à mémoriser WEB
	$serviz[120] = import('serviz120'); // Hauteur en pixels des nouveautés WEB
	$serviz[121] = import('serviz121'); // Inclure le calendrier dans les nouveautés
	$serviz[122] = import('serviz122'); // Inclure les articles dans les nouveautés
	$serviz[123] = import('serviz123'); // Inclure le blog dans les nouveautés
	$serviz[124] = import('serviz124'); // Inclure les téléchargements dans les nouveautés
	$serviz[125] = import('serviz125'); // Inclure la faq dans les nouveautés
	$serviz[126] = import('serviz126'); // Inclure leforum dans les nouveautés
	$serviz[127] = import('serviz127'); // Inclure les liens dans les nouveautés
	$serviz[128] = import('serviz128'); // Inclure le livre d'or dans les nouveautés
	$serviz[129] = import('serviz129'); // Inclure les nouvelles dans les nouveautés
	$serviz[130] = import('serviz130'); // Inclure les photos dans les nouveautés
	$serviz[132] = import('serviz132'); // Tous les documents du site WEB
	$serviz[133] = import('serviz133'); // Hauteur maxi en pixels d'une cellule de fil RSS
	$serviz[134] = import('serviz134'); // Nb maxi de caractères dans une cellule de fil RSS
	$serviz[135] = import('serviz135'); // Nb jours affichage image nouveauté
	$serviz[136] = import('serviz136'); // Image nouveauté articles ON / OFF / Membres
	$serviz[137] = import('serviz137'); // Image nouveauté nouvelles ON / OFF / Membres
	$serviz[138] = import('serviz138'); // Image nouveauté blog ON / OFF / Membres
	$serviz[139] = import('serviz139'); // Image nouveauté forum ON / OFF / Membres
	$serviz[140] = import('serviz140'); // Image nouveauté faq ON / OFF / Membres
	$serviz[141] = import('serviz141'); // Image nouveauté livre d'or ON / OFF / Membres
	$serviz[142] = import('serviz142'); // Image nouveauté photo ON / OFF / Membres
	$serviz[143] = import('serviz143'); // Image nouveauté téléchargements ON / OFF / Membres
	$serviz[144] = import('serviz144'); // Image nouveauté liens ON / OFF / Membres
	$serviz[145] = import('serviz145'); // Image nouveauté agenda ON / OFF / Membres
	$serviz[148] = max(import('serviz148'), 20); // Nombre maxi de caractères du pseudo
	$serviz[149] = import('serviz149'); // Boîte compteur dans préférences
	$serviz[150] = import('serviz150'); // Téléchargements les plus récents
	$serviz[151] = import('serviz151'); // Téléchargements les plus téléchargés
	$serviz[152] = import('serviz152'); // Position texte et icône inversés pour les nouvelles
	$serviz[153] = import('serviz153'); // Position texte et icône inversés pour les billets
	$serviz[154] = import('serviz154'); // Image nouveauté option de menu ON / OFF / Membres
    $serviz[156] = import('serviz156'); // Photorama pleine page ON / OFF
    $serviz[157] = import('serviz157', 'POST', true, '0'); // Page secondaire du photorama
    $serviz[158] = import('serviz158'); // Cacher le nombre de membres inscrits
    $serviz[159] = import('serviz159'); // Cacher les membres connectés
	$serviz[166] = import('serviz166'); // Inclure les réactions articles dans les nouveautés
	$serviz[167] = import('serviz167'); // Inclure les commentaires blog dans les nouveautés
	$serviz[168] = import('serviz168'); // Pause entre les lots
    $serviz[169] = import('serviz169'); // Cacher la date des nouveautés du site WEB
    $serviz[171] = import('serviz171', 'POST', true, '0'); // Page secondaire du calendrier
    $serviz[172] = import('serviz172', 'POST', true, '0'); // Page secondaire utilisateur
    $serviz[173] = import('serviz173', 'POST', true, '0'); // Page secondaire connexion
	$serviz[174] = import('serviz174'); // Affichage tri des photos au sein de la galerie de photos par id ou no ordre
    $serviz[177] = import('serviz177'); // Numéro article pour les mentions légales
    $serviz[178] = import('serviz178'); // Numéro page pour afficher les mentions légales
    $serviz[179] = import('serviz179'); // Groupe pour accès à la liste des membres
    $serviz[180] = import('serviz180'); // Lister tous les membres
    $serviz[181] = import('serviz181'); // Affichage topblog ON / OFF
	$serviz[182] = import('serviz182'); // Affichage tri des galeries par catégorie ou Catgéorie ASC et ID desc
	$serviz[183] = import('serviz183'); // Clôture automatique des posts du forum
	$serviz[184] = import('serviz184'); // Délai de cloture en nombre de jours
    
    $actionwaf = import('actionwaf'); // Activation pare-feu bannisement/blacklistage
	if ($actionwaf == '') $actionwaf = '1';
    if ($fctwri == 'admin' && ($serviz[31] == $userprefs[1] || $serviz[176] == $userprefs[1] || is_file(CHEMIN.'install/install.php'))) {
        $site[37]   = import('site37'); // Thème éditeur public CKEditor
        $serviz[3]  = import('serviz3'); // Carnet de bord du site
        $serviz[31] = trim(import('serviz31')); // Pseudo administrateur principal
        // Contrôle admin principal
        $okPadmin = !empty($serviz[31]) && is_file(MSGREP.$serviz[31].DBEXT) ? true : false;
        if (!$okPadmin) $serviz[31] = ' ';
        $serviz[32]  = import('serviz32'); // Accès à l'administration rapide
        $serviz[101] = import('serviz101'); // Largeur de l'éditeur
        $serviz[175] = import('serviz175'); // Autoriser les images dans ckeditor public
        $serviz[176] = trim(import('serviz176')); // Pseudo administrateur secondaire
        $serviz[42]  = import('serviz42', 'POST', true, !empty($serviz[176]) ? 'on' : ''); // Option collaborateur
        // Contrôle admin secondaire
        $okSadmin = false;
        if (!empty($serviz[176]) && is_file(MSGREP.$serviz[176].DBEXT) && $serviz[42] == 'on') {
            $dbadmins = ReadDBFields(ADMINS);
            foreach ($dbadmins as $row)
                if ($row[0] == $serviz[176] && $row[1] == 'admin') {
                    $okSadmin = true;
                    break;
                }
        }
        if (!$okSadmin) $serviz[176] = ' ';
    }
/*	
	if ($serviz[1] != '0') $menuico[0] = ''; // Icône Accueil
	if ($serviz[8] == '')  $menuico[1] = ''; // Icône Nouvelles
	if ($serviz[8] == 'on')  $menuico[1] = 'on'; // Icône Nouvelles
	if ($serviz[53] == '') $menuico[2] = ''; // Icône Blog
	if ($serviz[53] == 'on') $menuico[2] = 'on'; // Icône Blog	
	if ($serviz[9] == '')  $menuico[3] = ''; // Icône Photorama
	if ($serviz[9] == 'on')  $menuico[3] = 'on'; // Icône Photorama
	if ($serviz[10] == '') $menuico[4] = ''; // Icône Téléchargements
	if ($serviz[10] == 'on') $menuico[4] = 'on'; // Icône Téléchargements
	if ($serviz[11] == '') $menuico[5] = ''; // Icône Liens
	if ($serviz[11] == 'on') $menuico[5] = 'on'; // Icône Liens	
	if ($serviz[14] == '') $menuico[6] = ''; // Icône FAQ
	if ($serviz[14] == 'on') $menuico[6] = 'on'; // Icône FAQ	
	if ($serviz[12] == '') $menuico[7] = ''; // Icône Livre d'or
	if ($serviz[12] == 'on') $menuico[7] = 'on'; // Icône Livre d'or	
	if ($serviz[13] == '') $menuico[8] = ''; // Icône Forum
	if ($serviz[13] == 'on') $menuico[8] = 'on'; // Icône Forum
	if ($serviz[99] == '') $menuico[10] = ''; // Icône Contact avancé
	if ($serviz[99] == 'on') $menuico[10] = 'on'; // Icône Contact avancé
*/

    $supervision[0]  = import('supervision0'); // e-mail compteur
    $supervision[1]  = max(1, import('supervision1')); // incrément de compteur pour e-mail
    $supervision[2]  = import('supervision2'); // e-mail publication nouvelle
    $supervision[3]  = import('supervision3'); // e-mail publication livre d'or
    $supervision[4]  = import('supervision4'); // e-mail publication forum
 // $supervision[5]  = import('supervision5'); // Type d'e-mail PHP
	$supervision[5]  = 'phpmailer'; // Type d'e-mail PHP
    $supervision[6]  = import('supervision6'); // e-mail réagir aux articles
    $supervision[7]  = import('supervision7'); // e-mail inscription ou résiliation Newsletter
    $supervision[8]  = import('supervision8'); // e-mail publication blog
    $supervision[9]  = import('supervision9'); // e-mail commentaire blog
    $supervision[10] = import('supervision10'); // liste e-mails supervision
    $supervision[11] = import('supervision11'); // e-mail ouverture ou fermeture de compte
    $supervision[12] = import('supervision12'); // e-mail erreurs de connexion
    
 //   $members[21] = intval(import('members21')); // Délai expiration cookie utilisateur
	$members[21] = ''; // Délai expiration cookie utilisateur

    $smtp[0] = strtolower(import('smtp0')); // serveur SMTP
 // $smtp[1] = import('smtp1'); // avec authentification ON / OFF
    $smtp[1] = 'on'; // avec authentification ON / OFF
    $smtp[2] = strtolower(import('smtp2')); // préfixe serveur "" / SSL / TLS
    $smtp[3] = import('smtp3'); // numéro de port SMTP
    $smtp[4] = CheckEmail(import('smtp4')); // adresse mail utilisateur
    $smtp5   = import('smtp5'); 
    $smtp5_2 = import('smtp5_2');
    $smtp[5] = !empty($smtp5) && $smtp5_2 == $smtp5 ? gy_crypt(CRYPT_KEY, $smtp5) : $smtp[5]; // mot de passe utilisateur
	$smtp[6] = import('smtp6'); // Activation log Smtp on / off
	$smtp[7] = import('smtp7'); // Délai de conservation en jour(s)															
	
	$sitemap['SM'] = import('sitemapSM'); // Création d'un sitemap
	$sitemap['BL'] = import('sitemapBL'); // Inclure le blog dans le sitemap
	$sitemap['AR'] = import('sitemapAR'); // Inclure les articles dans le sitemap
	$sitemap['NE'] = import('sitemapNE'); // Inclure les nouvelles dans le sitemap
	$sitemap['PH'] = import('sitemapPH'); // Inclure les photos dans le sitemap
	$sitemap['DN'] = import('sitemapDN'); // Inclure les téléchargements dans le sitemap
	$sitemap['FA'] = import('sitemapFA'); // Inclure la faq dans le sitemap
	$sitemap['LI'] = import('sitemapLI'); // Inclure les liens dans le sitemap
	$sitemap['GB'] = import('sitemapGB'); // Inclure le livre d'or dans le sitemap
	$sitemap['FR'] = import('sitemapFR'); // Inclure le forum dans le sitemap
	$sitemap['AG'] = import('sitemapAG'); // Inclure l'agenda dans le sitemap

    include 'inc/confupdt.inc';
 /*

  Si la variable $site[3] contenant l'url du site
   Modification ds fichiers dans data/config rgpd/js et rgpd.inc
   
*/
	include CONFIGREP.'rgpd.inc';

	if ($serviz[177] == '') $pgmentionslegales = '17';
	else
	 $pgmentionslegales = $serviz[177]; 	
     $rgpd["privacyUrl"] = !empty($rgpd["privacyUrl"]) ? str_replace(SITE3, '', trim($rgpd["privacyUrl"])) : 'articles.php?lng='.$lng.'&pg='.$pgmentionslegales;
/**
 * Code à enregistrer dans le fichier rgpd.js
 */
	$mettre = '
<script type="text/javascript">
    tarteaucitron.init({
      "privacyUrl": "'.$rgpd["privacyUrl"].'", /* Privacy policy url */

      "hashtag": "'.$rgpd["hashtag"].'", /* Open the panel with this hashtag */
      "cookieName": "'.$rgpd["cookieName"].'", /* Cookie name */

      "orientation": "'.$rgpd["orientation"].'", /* Banner position (top - bottom) */
   
      "groupServices": '.$rgpd["groupServices"].', /* Group services by category */
                       
      "showAlertSmall": false, /* Show the small banner on bottom right */
      "cookieslist": false, /* Show the cookie list */
                       
      "closePopup": false, /* Show a close X on the banner */

      "showIcon": '.$rgpd["showIcon"].', /* Show cookie icon to manage cookies */
      "iconSrc": "'.(!empty($rgpd["iconSrc"]) && !preg_match('`^http(s)?\:`', $rgpd["iconSrc"]) ? SITE3.$rgpd["iconSrc"] : $rgpd["iconSrc"]).'", /* Optionnal: URL image */
      "iconPosition": "'.$rgpd["iconPosition"].'", /* BottomRight, BottomLeft, TopRight and TopLeft */

      "adblocker": false, /* Show a Warning if an adblocker is detected */
                       
      "DenyAllCta" : '.$rgpd["DenyAllCta"].', /* Show the deny all button */
      "AcceptAllCta" : '.$rgpd["AcceptAllCta"].', /* Show the accept all button when highPrivacy on */
      "highPrivacy": true, /* HIGHLY RECOMMANDED Disable auto consent */
                       
      "handleBrowserDNTRequest": false, /* If Do Not Track == 1, disallow all */

      "removeCredit": false, /* Remove credit link */
      "moreInfoLink": true, /* Show more info link */

      "useExternalCss": false, /* If false, the tarteaucitron.css file will be loaded */
      "useExternalJs": false, /* If false, the tarteaucitron.js file will be loaded */

      //"cookieDomain": ".my-multisite-domaine.fr", /* Shared cookie for multisite */
                      
      "readmoreLink": "", /* Change the default readmore link */

      "mandatory": true, /* Show a message about mandatory cookies */
    });
	
	'.$rgpd["servicesCode"].'
	
</script>';
	//
	
	WriteFullDB(CONFIGREP.'rgpd.js', $mettre);
	
	/**
	 * Code à enregistrer dans le fichier rgpd.inc
	 */	
	$rgpd_values = '<?php
$rgpd = array(	
      "privacyUrl" => stripslashes("'.addslashes($rgpd["privacyUrl"]).'"), /* Privacy policy url */

      "hashtag" => stripslashes("'.addslashes($rgpd["hashtag"]).'"), /* Open the panel with this hashtag */
      "cookieName" => stripslashes("'.addslashes($rgpd["cookieName"]).'"), /* Cookie name */

      "orientation" => "'.$rgpd["orientation"].'", /* Banner position (top - bottom) */
   
      "groupServices" => '.$rgpd["groupServices"].', /* Group services by category */
                       
      "showAlertSmall" => false, /* Show the small banner on bottom right */
      "cookieslist" => false, /* Show the cookie list */
                       
      "closePopup" => false, /* Show a close X on the banner */

      "showIcon" => '.$rgpd["showIcon"].', /* Show cookie icon to manage cookies */
      "iconSrc" => stripslashes("'.addslashes($rgpd["iconSrc"]).'"), /* Optionnal: URL image */
      "iconPosition" => "'.$rgpd["iconPosition"].'", /* BottomRight, BottomLeft, TopRight and TopLeft */

      "adblocker" => false, /* Show a Warning if an adblocker is detected */
                       
      "DenyAllCta"  => '.$rgpd["DenyAllCta"].', /* Show the deny all button */
      "AcceptAllCta"  => '.$rgpd["AcceptAllCta"].', /* Show the accept all button when highPrivacy on */
      "highPrivacy" => true, /* HIGHLY RECOMMANDED Disable auto consent */
                       
      "handleBrowserDNTRequest" => false, /* If Do Not Track == 1, disallow all */

      "removeCredit" => false, /* Remove credit link */
      "moreInfoLink" => true, /* Show more info link */

      "useExternalCss" => false, /* If false, the tarteaucitron.css file will be loaded */
      "useExternalJs" => false, /* If false, the tarteaucitron.js file will be loaded */

      //"cookieDomain" => ".my-multisite-domaine.fr", /* Shared cookie for multisite */
                      
      "readmoreLink" => "", /* Change the default readmore link */

      "mandatory" => true, /* Show a message about mandatory cookies */
	  
	  "servicesCode" => stripslashes("'.addslashes($rgpd["servicesCode"]).'"),   /* Declaration of services for rgpd */
);
	
?>';
	//
	WriteFullDB(CONFIGREP.'rgpd.inc', $rgpd_values);
// fin de mise à jour rgpd.inc et rgps.js	
	$mapurls = import('urlsmap'); // URLs non indexées
	WriteFullDB(MAPSURLS, $mapurls);
	
	$ctrlsa  = $sitemap['SM'].$sitemap['BL'].$sitemap['AR'].$sitemap['NE'].$sitemap['PH'].$sitemap['DN'].$sitemap['FA'].$sitemap['LI'].$sitemap['GB'].$sitemap['FR'].$sitemap['AG'].$mapurls;
	$smtyps  = array(TYP_BLOG, TYP_ART, TYP_NEWS, TYP_PHOTO, TYP_DNLOAD, TYP_FAQ, TYP_LINKS, TYP_GUESTBK, TYP_FORUM, TYP_AGENDA);
	$smslcts = array(TYP_BLOG=>$sitemap['BL'], TYP_ART=>$sitemap['AR'], TYP_NEWS=>$sitemap['NE'], TYP_PHOTO=>$sitemap['PH'], TYP_DNLOAD=>$sitemap['DN'], 
	                 TYP_FAQ=>$sitemap['FA'], TYP_LINKS=>$sitemap['LI'], TYP_GUESTBK=>$sitemap['GB'], TYP_FORUM=>$sitemap['FR'], TYP_AGENDA=>$sitemap['AG']);
	if ($sitemap['SM'] == 'on') {
        if ($ctrlsa != $ctrlsb) {
            foreach($smtyps as $smtyp)
                if ($smslcts[$smtyp] == 'on') UpdateSitemaps($smtyp);
            UpdateSitemaps('URLS');
        }
    } else {
        UpdateSitemaps('NONE');
    }
    
	if ($site['URLR'] == 'on' && $ctrlb != $ctrla) {
		setURLrewriting('');
		setURLrewriting('on');
	}

    include_once CHEMIN.INCREP.'funcrss.php';
    UpdateRSSnews();
    UpdateRSSblog();
    UpdateRSSart();
	UpdateRSSall();
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $site[8]);
        echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=config5";'.EndJavascript();
    }
}
include CONFIG;
$mapurls = file_exists(MAPSURLS) ? file_get_contents(MAPSURLS) : '';
if (is_file ($meskin.'confskin.inc')) {
    include $meskin.'confskin.inc';
} else {
    die('Skin not found : '.$meskin);
}

$symbls = array('', '&bull;', '&deg;', '-', '-', '-', '-');

function LineTXT($ls, $label, $name, $value, $size=40, $typ='text', $left='60%', $readonly='') {
	global $charset, $symbls;
	$indent = 12 + ($ls - 1) * 24;
	if ($readonly != '') $readonly='readonly="readonly"';
    return '
        <div class="row">
          <div class="col-md-7 d-inline-block pt-3 align-middle">
		    <label for="'.$name.'" style="padding-left:'.$indent.'px;"><b>'.$symbls[$ls].'</b>&nbsp;&nbsp;'.$label.'</label>
          </div>
          <div class="col-md-5 d-inline-block">
		    <input class="form-control texte" type="'.$typ.'" id="'.$name.'" name="'.$name.'" 
              value="'.htmlentities($value, ENT_QUOTES, $charset).'" '.$readonly.' style="max-width: '.$size.'px;" />
          </div>
		</div>';
}
function LineTXT2($label, $name, $value, $size=80) {
	global $charset;
    return '
      <div class="pl-3 align-middle"><label for="'.$name.'">'.$label.'</label></div>
      <div style="margin-top:-8px;">
        <input class="form-control texte" type="text" id="'.$name.'" name="'.$name.'" value="'.htmlentities($value, ENT_QUOTES, $charset).'" size="'.$size.'" />
	  </div>';
}
function LineCB($ls, $label, $name, $value, $script='', $readonly='') {
	global $symbls;
	$indent = 12 + ($ls - 1) * 24;
	if ($readonly != '') $readonly='disabled';
    return '
        <div class="row pt-4 pb-2 align-middle">
          <div class="col-md-7">
            <label for="'.$name.'" style="padding-left:'.$indent.'px;"><b>'.$symbls[$ls].'</b>&nbsp;&nbsp;'.$label.'</label>
          </div>
          <div class="col-md-5">
            <input class="pt-2" type="checkbox" id="'.$name.'" '.$readonly.' name="'.$name.'"'.Checked($value).$script.' />
          </div>
		</div>';
}
function LineSELECT($ls, $label, $name, $options, $value, $wkey=false, $mwidth=180, $disable='') {
	global $charset, $symbls;
	$indent = 12 + ($ls - 1) * 24;
    $out = '
        <div class="row">
          <div class="col-md-7 d-inline-block pt-3 align-middle">
            <label for="'.$name.'" style="padding-left:'.$indent.'px;"><b>'.$symbls[$ls].'</b>&nbsp;&nbsp;'.$label.'</label>
          </div>
          <div class="col-md-5 d-inline-block">
            <select class="form-control mt-2" style="max-width:'.$mwidth.'px;" id="'.$name.'" name="'.$name.'" '.$disable.'>';
    foreach ($options as $key=>$val) {
        $out .= '
              <option value="'.$key.'"'.Selected($value == $key || $value == html_entity_decode($key, ENT_QUOTES, $charset)).'>
                '.($wkey ? $key.' - ' : '').$val.'
              </option>';
    }
    return $out.'
            </select>
          </div>
		</div>';
}
function LineSELECT2($ls, $label, $name, $value) {
	global $symbls, $templates, $admin;
	$indent = 12 + ($ls - 1) * 24;
    $out = '
        <div class="row">
          <div class="col-md-7 d-inline-block pt-3 align-middle">
            <label for="'.$name.'" style="padding-left:'.$indent.'px;"><b>'.$symbls[$ls].'</b>&nbsp;&nbsp;'.$label.'</label>
          </div>
          <div class="col-md-5 d-inline-block">
            <select class="form-control" style="max-width:180px;" id="'.$name.'" name="'.$name.'">
              <option value="ALL"'.Selected($value == 'ALL').'>'.$admin[1363].'</option>';
    foreach ($templates as $key=>$val) {
        $out .= '
              <option value="'.$key.'"'.Selected($value != 'ALL' && $value == $key).'>'.$key.' - '.$val.'</option>';
    }
    return $out.'
            </select>
          </div>
		</div>';
}
function LineSELECT3($ls, $label, $name, $value) {
	global $symbls, $admin;
    $groupes = array(''=>' -- '.$admin[1604].' -- ', 'all_members'=>$admin[690]);
    $dbgroup = ReadDBFields(GROUPS);
	$indent  = 12 + ($ls - 1) * 24;
    $out = '
        <div class="row">
          <div class="col-md-7 d-inline-block pt-3 align-middle">
            <label for="'.$name.'" style="padding-left:'.$indent.'px;"><b>'.$symbls[$ls].'</b>&nbsp;&nbsp;'.$label.'</label>
          </div>
          <div class="col-md-5 d-inline-block">
            <select class="form-control" style="max-width:180px;" id="'.$name.'" name="'.$name.'">
              <option value=""'.Selected($value == '').'> -- '.$admin[1604].' -- </option>
              <option value="all_members"'.Selected($value == 'all_members').'>'.$admin[690].'</option>';
    foreach ($dbgroup as $key=>$groupe) {
        $out .= '
              <option value="'.$groupe[0].'"'.Selected($value != '' && $value == $groupe[0]).'>'.$groupe[0].'</option>';
    }
    return $out.'
            </select>
          </div>
		</div>';
}

$headinc .= '
    <script>
        function setnewsdaysweb1(form) {
            if (form.serviz117.checked) 
				{
					form.serviz118.checked = "";
					form.serviz118.disabled = true;
					form.serviz119.disabled = true;
					form.serviz120.disabled = true;
				}
			else
			{
					
					form.serviz118.disabled = false;
					form.serviz118.checked = "checked";
					form.serviz119.disabled = false;
					form.serviz120.disabled = false;
			}
        }
        function setnewsdaysweb2(form) {
            if (form.serviz118.checked) 
				{
					form.serviz117.checked = "";
					form.serviz117.disabled = true;
					form.serviz119.disabled = false;
					form.serviz120.disabled = false;
				}
			else
				{
					form.serviz117.checked = "checked";
					form.serviz117.disabled = false;
					form.serviz119.disabled = true;
					form.serviz120.disabled = true;					
				}
        }
        function setnewsdaysmob1(form) {
            if (form.serviz162.checked) form.serviz163.checked = "";
        }
        function setnewsdaysmob2(form) {
            if (form.serviz163.checked) form.serviz162.checked = "";
        }
    </script>
';

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[176]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[176], '100%');

// Préparation des tableaux d'options utilisés pour les selects
$options1      = array($admin[897], $admin[770], $admin[13], $admin[9], $admin[624]);
$options2      = array('off' => $admin[1604], 'on' => $admin[1605], 'mbr' => $admin[690]);
$options27     = array($admin[594], $admin[595], $admin[596], $admin[597], $admin[598], $admin[599]);
$option36      = array('' => $admin[1604], 'on' => $admin[1605].' + '.$admin[745], 'no' => $admin[1605].', '.$admin[916]);
$options39     = array(
    900 => $admin[613], 1800 => $admin[614], 3600 => $admin[615], 10800 => $admin[616],
    21600 => $admin[617], 43200 => $admin[618], 86400 => $admin[619]);
$options58     = array('' => $admin[1604], 'on' => $admin[1605], '2cols' => $admin[1605].', 2cols');
$options175    = array(0 => $admin[1604], 1 => $admin[1605], 2 => $admin[690], 3 => $admin[714].','.$admin[711]);
$optionsdelaiconservationlog = array(1 =>1, 2 =>2, 5 =>5, 10 =>10);		
$optionsdelaibeforeclose = array(30 =>30, 60 =>60, 90 =>90, 120 =>120);														   
$confirm2      = array(0 => $admin[55], 1 => $admin[61]);
$confirm3      = array(2 => $admin[1604], 0 => $admin[55], 1 => $admin[61]);
$types_menu    = array($admin[397], $admin[398], $admin[504], $admin[621]);
$types_captcha = array(0 => $admin[1185], 2 => $admin[1187], 3 => $admin[1372]);
$types_waf     = array(1 => $admin[1878]);
$types_diapo   = array(0 => 'Tiles', 1 => 'Carousel', 2 => 'Slider');
// types_affichage_tri_galeries
$types_affichage_tri_galeries   = array(0 => $admin[1804], 1 => $admin[1805], 2 => $admin[1806]);
$types_affichage_tri_photos_galeries   = array(0 => $admin[1840], 1 => $admin[1841]);
$expireuser    = array('0'=>'-- '.$admin[1604].' --', '900'=>'15 mn', '1800'=>'30 mn', '3600'=>'1 h', '7200'=>'2 h', '21600'=>'6 h', '86400'=>'24 h', '172800'=>'48 h', '259200'=>'72 h');
$modesaveprefs = array('0'=>$admin[1750], '1'=>$admin[1751]);
$tmpls         = array(0 => $admin[875]);
$nbrtemplateabsent = 0;
for ($ij=0; $ij < count($templates); $ij++)
{
	if (!isset($templates[$ij])) 
		$nbrtemplateabsent++;
}
for ($i = 1; $i < count($templates)+$nbrtemplateabsent; $i++) {
	if (isset($templates[$i]))
	  $tmpls[$i] = $templates[$i];
}
$carspec1 = array('&nbsp;','!','#','*','+','-','.',':','&lt;','=','&gt;','@','^','~',
'&deg;','&curren;','&middot;','&ordm;','&plusmn;','&raquo;','&times;','&bull;');
$carspec2 = array();
foreach ($carspec1 as $car) {
    $carspec2[htmlentities($car, ENT_QUOTES, $charset)] = $car;
}
unset($carspec1);
$car1     = '&nbsp;&nbsp;<span class="texte" style="font-weight:bold;border:1px solid;" > &nbsp; '.$texte[3].' &nbsp; </span>';
$sknCKE   = array('moono'=>'Moono', 'moono_blue'=>'Moono Blue', 'moonocolor_v1.1'=>'Moono Color', 'moono-dark'=>'Moono Dark', 'moono-lisa'=>'Moono Lisa');
$dbadmins = ReadDBFields(ADMINS);
$dbadms   = array(''=>'');
foreach ($dbadmins as $row) $dbadms[] = $row[0];

echo '<div class="alert alert-primary" role="alert">
	  '.$admin[1887].'
	</div>';

echo '
<div style="text-align:center;">
  <p>'.ToHelp($lng, '&pg=954&mnuid=937&tconfig=0#z2').$admin[177].'</p>
</div>
<div class="w-100 m-auto">
  <form name="conf5" action="admin.php?lng='.$lng.'&amp;pg=config5" method="post">
    <input type="hidden" name="regit" value="1" />';
	
echo '
    <div class="m-auto" id="accordion" style="max-width:660px;">
      <div class="row quest p-0 mb-2 w-100 m-auto">
	    <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
        <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
      </div>';
// ADMINISTRATION
if (!isset($droitaccegroupredac)) $droitaccegroupredac = '';
if (is_file(CHEMIN.'install/install.php') || (isAdmin($userprefs[1]) && ($userprefs[1] == $serviz[31] || $userprefs[1] == $serviz[176]))) {
    echo '
      <div class="card">
        <a class="card-header card-link" data-toggle="collapse" href="#collapse1">'.$admin[810].'</a>
        <div class="collapse show" data-parent="#accordion" id="collapse1">
          <div class="card-body w-100 pt-2 pb-4 pr-3">';
    if ($userprefs[1] == $serviz[31] || $userprefs[1] == $serviz[176] || is_file(CHEMIN.'install/install.php'))
        echo '
			'.LineTXT(1, $admin[1731], 'serviz31', trim($serviz[31]), 300).'
			'.LineTXT(2, $admin[1732], 'serviz176', trim($serviz[176]), 300).'
			'.LineCB(1, $admin[482], 'serviz32', $serviz[32], 40).'
			'.LineTXT(1, $admin[1242], 'serviz101', trim($serviz[101]), 100, 40).'
			'.LineSELECT(1, $admin[1578], 'serviz175', $options175, $serviz[175], false, 160).'
			'.LineSELECT(1, $admin[1581], 'site37', $sknCKE, $site[37]).'
            '.LineCB(1, $admin[711], 'serviz42', $serviz[42]).'
			'.LineCB(1, $admin[1837], 'droitaccegroupredac', $droitaccegroupredac).'
            '.LineCB(1, $admin[937], 'serviz3', $serviz[3]).'
          </div>
        </div>
      </div>';
}
// SERVICES Blog
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse2">
          '.$admin[217].' => '.mb_strtoupper($admin[770]).'
        </a>
        <div class="collapse" data-parent="#accordion" id="collapse2">
          <div class="card-body w-100 p-2 pb-4 pr-3">
			<div style="margin-top:8px;">'.LineCB(1, $admin[780], 'serviz53', $serviz[53]).'</div>
            '.LineSELECT3(2, $admin[932], 'members15', $members[15]).'
            '.LineSELECT(2, $admin[1221].' WEB', 'serviz59', $types_menu, $serviz[59]).'
            '.LineSELECT2(2, $admin[1552], 'menuico13', $menuico[13]).'
            '.LineSELECT(2, $admin[775], 'site30', $confirm3, $site[30]).'
            '.LineSELECT3(4, $admin[932], 'members16', $members[16]).'
            '.LineSELECT(2, $admin[774], 'serviz57', $confirm3, $serviz[57]).'
            '.LineSELECT3(4, $admin[932], 'members17', $members[17]).'
            '.LineTXT(2, $admin[1469], 'site36', trim($site[36]), 60, 40).'
            '.LineCB(2, $admin[1068], 'serviz76', $serviz[76]).'
            '.LineCB(2, $admin[1069], 'serviz114', $serviz[114]).'
            '.LineCB(2, $admin[347], 'serviz104', $serviz[104]).'
            '.LineCB(2, $admin[1379].$admin[1538], 'serviz123', $serviz[123]).'
            '.LineCB(2, $admin[1379].$admin[1539], 'serviz167', $serviz[167]).'
            '.LineCB(2, $admin[1468], 'serviz153', $serviz[153]).'
            '.LineCB(2, $admin[1775], 'serviz181', $serviz[181]).'
          </div>
        </div>
      </div>';
// SERVICES Articles
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse3">
          '.$admin[217].' => '.mb_strtoupper($admin[5]).'
        </a>
        <div class="collapse" data-parent="#accordion" id="collapse3">
          <div class="card-body w-100 p-2 pb-4 pr-3">
            '.LineSELECT3(2, $admin[932], 'members1', $members[1]).'
            '.LineSELECT(2, $admin[472], 'serviz29', $confirm3, $serviz[29]).'
            '.LineSELECT3(4, $admin[932], 'members11', $members[11]).'
            '.LineCB(2, $admin[483], 'serviz33', $serviz[33]).'
            '.LineCB(2, $admin[1064], 'serviz64', $serviz[64]).'
            '.LineCB(2, $admin[1065], 'serviz65', $serviz[65]).'
            '.LineCB(2, $admin[1066], 'serviz66', $serviz[66]).'
            '.LineCB(2, $admin[1067], 'serviz67', $serviz[67]).'
            '.LineCB(2, $admin[1068], 'serviz68', $serviz[68]).'
            '.LineCB(2, $admin[1281], 'serviz102', $serviz[102]).'
            '.LineCB(2, $admin[1346], 'serviz113', $serviz[113]).'
            '.LineCB(2, $admin[347], 'serviz105', $serviz[105]).'
            '.LineCB(2, $admin[1379].' ('.mb_strtolower($admin[5]).')', 'serviz122', $serviz[122]).'
            '.LineCB(2, $admin[1379].' ('.mb_strtolower($admin[476]).')', 'serviz166', $serviz[166]).'
          </div>
        </div>
      </div>';
// SERVICES Nouvelles - Photos
if (!isset($serviz[174])) $serviz[174] = '';
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse4">
          '.$admin[217].' => '.$admin[13].' - '.substr($admin[834], 0, -1).'
        </a>
        <div class="collapse" data-parent="#accordion" id="collapse4">
          <div class="card-body w-100 p-2 pb-4 pr-3">
            <div style="margin-top:8px;">'.LineCB(1, $admin[72], 'serviz8', $serviz[8]).'</div>
            '.LineSELECT3(2, $admin[932], 'members13', $members[13]).'
            '.LineSELECT(2, $admin[59], 'site5', $confirm3, $site[5]).'
            '.LineSELECT3(4, $admin[932], 'members8', $members[8]).'
            '.LineTXT(2, $admin[684], 'site13', trim($site[13]), 60, 40).'
            '.LineCB(2, $admin[1064], 'serviz72', $serviz[72]).'
            '.LineCB(2, $admin[1069], 'serviz73', $serviz[73]).'
            '.LineCB(2, $admin[347], 'serviz106', $serviz[106]).'
            '.LineCB(2, $admin[1379], 'serviz129', $serviz[129]).'
            '.LineCB(2, $admin[1468], 'serviz152', $serviz[152]).'
            '.LineSELECT2(2, $admin[1552], 'menuico12', $menuico[12]).'
            <div style="margin-top:8px;">'.LineCB(1, $admin[74], 'serviz9', $serviz[9]).'</div>
            '.LineSELECT3(2, $admin[932], 'members2', $members[2]).'
            '.LineCB(2, $admin[1057], 'serviz81', $serviz[81]).'
			'.LineSELECT(2, $admin[1803], 'serviz182', $types_affichage_tri_galeries, $serviz[182], $mwidth=250).'
			'.LineSELECT(2, $admin[1842], 'serviz174', $types_affichage_tri_photos_galeries, $serviz[174], $mwidth=350).'
            '.LineTXT(2, $admin[1037], 'serviz87', trim($serviz[87]), 40, 40).'
            '.LineSELECT(2, $admin[1456], 'serviz83', $types_diapo, $serviz[83]).'
            '.LineCB(2, $admin[1379], 'serviz130', $serviz[130]).'
            '.LineCB(2, $admin[1482], 'serviz156', $serviz[156]).'
            '.LineSELECT2(2, $admin[1552], 'menuico14', $menuico[14]).'
          </div>
        </div>
      </div>';
// SERVICES Téléchargements - FAQ
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse5">
          '.$admin[217].' => '.$admin[18].' - '.$admin[206].'
        </a>
        <div class="collapse" data-parent="#accordion" id="collapse5">
          <div class="card-body w-100 p-2 pb-4 pr-3">
            <div style="margin-top:8px;">'.LineCB(1, $admin[73], 'serviz10', $serviz[10]).'</div>
            '.LineSELECT3(2, $admin[932], 'members7', $members[7]).'
            '.LineCB(2, $admin[484], 'serviz35', $serviz[35]).'
            '.LineCB(2, $admin[1068], 'serviz77', $serviz[77]).'
            '.LineCB(2, $admin[1057], 'serviz78', $serviz[78]).'
            '.LineCB(2, $admin[1379], 'serviz124', $serviz[124]).'
            '.LineSELECT2(2, $admin[1552], 'menuico15', $menuico[15]).'
            <div style="margin-top:8px;">'.LineCB(1, $admin[207], 'serviz14', $serviz[14]).'</div>
            '.LineSELECT3(2, $admin[932], 'members4', $members[4]).'
            '.LineCB(2, $admin[1064], 'serviz69', $serviz[69]).'
            '.LineCB(2, $admin[1065], 'serviz70', $serviz[70]).'
            '.LineCB(2, $admin[1068], 'serviz71', $serviz[71]).'
            '.LineCB(2, $admin[1057], 'serviz79', $serviz[79]).'
            '.LineCB(2, $admin[1379], 'serviz125', $serviz[125]).'
            '.LineSELECT2(2, $admin[1552], 'menuico17', $menuico[17]).'
          </div>
        </div>
      </div>';
// SERVICES Liens - Livre d'or - Forum
$selectdisablecloture = 'disabled';
if (!isset($serviz[183])) $serviz[183] = '';
if (!isset($serviz[184])) $serviz[184] = '';
if ($serviz[183] != '') $selectdisablecloture = '';

echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse6">
          '.$admin[217].' => '.$admin[10].' - '.$admin[11].' - '.$admin[9].'
        </a>
        <div class="collapse" data-parent="#accordion" id="collapse6">
          <div class="card-body w-100 p-2 pb-4 pr-3">
		    <div style="margin-top:8px;">'.LineCB(1, $admin[75], 'serviz11', $serviz[11]).'</div>
            '.LineSELECT3(2, $admin[932], 'members3', $members[3]).'
			'.LineCB(2, $admin[1057], 'serviz80', $serviz[80]).'
			'.LineCB(2, $admin[1379], 'serviz127', $serviz[127]).'
			'.LineSELECT2(2, $admin[1552], 'menuico16', $menuico[16]).'
			<div style="margin-top:8px;">'.LineCB(1, $admin[76], 'serviz12', $serviz[12]).'</div>
            '.LineSELECT3(2, $admin[932], 'members12', $members[12]).'
			'.LineSELECT(2, $admin[454], 'serviz40', $confirm2, $serviz[40]).'
            '.LineSELECT3(4, $admin[932], 'members9', $members[9]).'
			'.LineCB(2, $admin[1379], 'serviz128', $serviz[128]).'
			'.LineSELECT2(2, $admin[1552], 'menuico18', $menuico[18]).'
			<div style="margin-top:8px;">'.LineCB(1, $admin[164], 'serviz13', $serviz[13]).'</div>
            '.LineSELECT3(2, $admin[932], 'members5', $members[5]).'
			'.LineSELECT(2, $admin[454], 'serviz41', $confirm2, $serviz[41]).'
            '.LineSELECT3(4, $admin[932], 'members10', $members[10]).'
			'.LineCB(2, $admin[347], 'serviz107', $serviz[107]).'
			'.LineCB(2, $admin[1379], 'serviz126', $serviz[126]).'
			'.LineSELECT2(2, $admin[1552], 'menuico19', $menuico[19]).'
			'.LineCB(2, $admin[1908], 'serviz183', $serviz[183]).'
			'.LineSELECT(2, $admin[1909], 'serviz184', $optionsdelaibeforeclose, $serviz[184], false, 70, $selectdisablecloture).'	
				<script language="JavaScript">
						 $(\'#serviz183\').on(\'click\', function(){
						if( $(this).is(\':checked\') ){
							$(\'#serviz184\').attr(\'disabled\', false);
					}else{
						  $(\'#serviz184\').attr(\'disabled\', true);
					}			});
				</script>
          </div>
        </div>
      </div>';
// SERVICES Agenda - Recommander - Newsletter - Contact
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse7">
          '.$admin[217].' => '.$admin[624].' - '.$admin[249].' - '.$admin[546].' - '.substr($admin[1180], 0, -1).'
        </a>
        <div class="collapse" data-parent="#accordion" id="collapse7">
          <div class="card-body w-100 p-2 pb-4 pr-3">'.LineCB(1, $admin[651], 'serviz47', $serviz[47]).'
            '.LineSELECT3(2, $admin[932], 'members14', $members[14]).'
			'.LineCB(2, $admin[1379], 'serviz121', $serviz[121]).'
            '.LineSELECT2(2, $admin[1552], 'serviz171', $serviz[171]).'
			'.LineCB(1, $admin[249], 'serviz62', $serviz[62]).'
			<div style="margin-top:8px;">'.LineCB(1, $admin[546], 'serviz36', $serviz[36]).'</div>
            '.LineSELECT3(2, $admin[932], 'members18', $members[18]).'
            '.LineCB(2, $admin[1062], 'serviz63', $serviz[63]).'
            '.LineTXT(2, $admin[777], 'serviz58', $serviz[58], 40).'
            '.LineTXT(2, $admin[1382], 'serviz168', $serviz[168], 40).'
			<div style="margin-top:8px;">'.LineCB(1, $admin[1179], 'serviz21', $serviz[21]).'</div>
            '.LineSELECT3(2, $admin[932], 'members20', $members[20]).'
			'.LineCB(2, $admin[1180], 'serviz99', $serviz[99]).'
			'.LineSELECT2(2, $admin[1552], 'menuico21', $menuico[21]).'
          </div>
        </div>
      </div>';
// SERVICES Boîte espace membre
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse8">
          '.$admin[217].' => '.$admin[450].'
        </a>
        <div class="collapse" data-parent="#accordion" id="collapse8">
          <div class="card-body w-100 p-2 pb-4 pr-3">
			<div style="margin-top:6px;">'.LineCB(1, $admin[1455], 'serviz149', $serviz[149]).'</div>
			<div style="margin-top:6px;">'.LineCB(1, $admin[449], 'serviz18', $serviz[18]).'</div>
			<div style="margin-top:2px;">'.LineCB(1, $admin[1461], 'serviz84', $serviz[84]).'</div>
			'.LineTXT(2, $admin[1464], 'serviz85', $serviz[85], 300).'
			<div style="margin-top:6px;">'.LineCB(1, $admin[1465], 'serviz86', $serviz[86]).'</div>
            '.LineSELECT3(2, $admin[932], 'serviz179', $serviz[179]).'
			'.LineCB(2, $admin[690], 'serviz180', $serviz[180]).'
			'.LineCB(1, $admin[1467], 'serviz111', $serviz[111]).'
			'.LineTXT(1, $admin[1488], 'serviz148', $serviz[148], 60).'
			'.LineCB(1, $admin[1532], 'serviz158', $serviz[158]).'
			'.LineCB(1, $admin[1533], 'serviz159', $serviz[159]).'
            '.LineSELECT2(1, $admin[1552], 'serviz172', $serviz[172]).'
            '.LineSELECT2(1, $admin[1335], 'serviz173', $serviz[173]).'
			'.LineTXT(1, $admin[243], 'serviz177', $serviz[177], 80).'
			'.LineSELECT(2, $admin[1335], 'serviz178', $templates, $serviz[178]).'
			'.LineTXT(1, $admin[257], 'siteNTC', $site['NTC'], 40).'
          </div>
        </div>
      </div>';
// SERVICES Divers - Rotation - Nouveautés
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse9">
          '.$admin[217].' => '.str_replace('...', '', $admin[1116]).' - '.$admin[1425].'
        </a>
        <div class="collapse" data-parent="#accordion" id="collapse9">
          <div class="card-body w-100 p-2 pb-4 pr-3">
			'.LineSELECT(1, $admin[71], 'serviz1', $options1, $serviz[1]).'
			'.LineSELECT(1, $admin[480].$car1, 'texte3', $carspec2, $texte[3]).'
			<div style="margin-top:8px;">'.LineCB(1, $admin[355], 'serviz25', $serviz[25]).'</div>
			'.LineTXT(2, $admin[356], 'serviz26', $serviz[26], 40).'
			<div style="margin-top:8px;">'.LineCB(1, $admin[496], 'serviz108', $serviz[108]).'</div>
			'.LineTXT(2, $admin[497], 'serviz109', $serviz[109], 40).'
			<div style="margin-top:8px;">'.LineCB(1, $admin[1374], 'serviz116', $serviz[116]).'</div>
            '.LineSELECT3(2, $admin[932], 'members22', $members[22]).'
			'.LineCB(2, $admin[1375], 'serviz117', $serviz[117], ' onchange="setnewsdaysweb1(this.form)"').'
			'.LineCB(2, $admin[1376], 'serviz118', $serviz[118], ' onchange="setnewsdaysweb2(this.form)"').'
			'.LineTXT(2, $admin[1377], 'serviz119', $serviz[119], 40).'
			'.LineTXT(2, $admin[1378], 'serviz120', $serviz[120], 40).'
			'.LineCB(2, $admin[1547], 'serviz169', $serviz[169]).'
			<div style="margin-top:8px;">'.LineCB(1, $admin[1892], 'serviz132', $serviz[132]).'</div>
			<div style="margin-top:8px;">'.LineTXT(1, $admin[1426], 'serviz135', $serviz[135], 40).'</div>
			'.LineSELECT(2, $admin[1427].$admin[5].' :', 'serviz136', $options2, $serviz[136]).'
			'.LineSELECT(2, $admin[1427].$admin[1474].' :', 'serviz154', $options2, $serviz[154]).'
			'.LineSELECT(2, $admin[1427].$admin[13].' :', 'serviz137', $options2, $serviz[137]).'
			'.LineSELECT(2, $admin[1427].$admin[770].' :', 'serviz138', $options2, $serviz[138]).'
			'.LineSELECT(2, $admin[1427].$admin[9].' :', 'serviz139', $options2, $serviz[139]).'
			'.LineSELECT(2, $admin[1427].$admin[206].' :', 'serviz140', $options2, $serviz[140]).'
			'.LineSELECT(2, $admin[1427].$admin[11].' :', 'serviz141', $options2, $serviz[141]).'
			'.LineSELECT(2, $admin[1427].$admin[834], 'serviz142', $options2, $serviz[142]).'
			'.LineSELECT(2, $admin[1427].$admin[18].' :', 'serviz143', $options2, $serviz[143]).'
			'.LineSELECT(2, $admin[1427].$admin[10].' :', 'serviz144', $options2, $serviz[144]).'
			'.LineSELECT(2, $admin[1427].$admin[624].' :', 'serviz145', $options2, $serviz[145]).'
          </div>
        </div>
      </div>';
// SERVICES Nombre d'éléments des listes
echo '  
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse10">
          '.$admin[217].' => '.$admin[239].'
        </a>
        <div class="collapse" data-parent="#accordion" id="collapse10">
          <div class="card-body w-100 p-2 pb-4 pr-3">
			'.LineTXT(1, $admin[472], 'serviz30', $serviz[30], 60, '', '65%').'
			'.LineTXT(1, $admin[72], 'serviz2', $serviz[2], 60, '', '65%').'
			<div style="margin-top:8px;">'.LineTXT(1, $admin[780], 'serviz55', $serviz[55], 60, '', '65%').'</div>
			'.LineTXT(2, $admin[749], 'serviz61', $serviz[61], 60, '', '65%').'
			<div style="margin-top:8px;">'.LineTXT(1, $admin[73], 'serviz4', $serviz[4], 60, '', '65%').'</div>
			'.LineTXT(2, $admin[1459], 'serviz150', $serviz[150], 60, '', '65%').'
			'.LineTXT(2, $admin[1460], 'serviz151', $serviz[151], 60, '', '65%').'
			'.LineTXT(1, $admin[75], 'serviz5', $serviz[5], 60, '', '65%').'
			'.LineTXT(1, $admin[207], 'serviz6', $serviz[6], 60, '', '65%').'
			'.LineTXT(1, $admin[76], 'serviz7', $serviz[7], 60, '', '65%').'
			<div style="margin-top:8px;">'.LineTXT(1, $admin[164], 'serviz17', $serviz[17], 60, '', '65%').'</div>
			'.LineTXT(2, $admin[98], 'serviz20', $serviz[20], 60, '', '65%').'
			<div style="margin-top:8px;">'.LineTXT(1, $admin[651], 'serviz46', $serviz[46], 60, '', '65%').'</div>
			'.LineTXT(1, $admin[682], 'serviz48', $serviz[48], 60, '', '65%').'
			'.LineTXT(1, $admin[683], 'serviz50', $serviz[50], 60, '', '65%').'
			'.LineTXT(1, $admin[1079], 'serviz75', $serviz[75], 60, '', '65%').'
			'.LineTXT(1, $admin[1309], 'serviz110', $serviz[110], 60, '', '65%').'
          </div>
        </div>
      </div>';
// SERVICES RSS
echo '		  
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse11">
          '.$admin[1304].'
        </a>
        <div class="collapse" data-parent="#accordion" id="collapse11">
          <div class="card-body w-100 p-2 pb-4 pr-3">
			<div style="margin-top:8px;">'.LineCB(1, $admin[602].' ('.$admin[1676].')', 'serviz27', $serviz[27]).'</div>
			'.LineCB(1, $admin[781], 'serviz54', $serviz[54]).'
			'.LineTXT(2, $admin[781], 'serviz56', $serviz[56], 80).'
			<div style="margin-top:8px;">'.LineCB(1, $admin[795], 'serviz60', $serviz[60]).'</div>
			'.LineTXT(2, $admin[1060], 'serviz82', max(1, $serviz[82]), 80).'
			<div style="margin-top:8px;">'.LineCB(1, $admin[242], 'serviz16', $serviz[16]).'</div>
			'.LineTXT(2, $admin[242], 'serviz37', $serviz[37], 80).'
			<div style="margin-top:8px;">'.LineCB(1, $admin[1305], 'serviz103', $serviz[103]).'</div>
			'.LineSELECT(1, $admin[612], 'serviz39', $options39, $serviz[39]).'
			'.LineTXT(1, $admin[1410], 'serviz133', $serviz[133], 80).'
          </div>
        </div>
      </div>';
// SERVICES SITEMAP
echo '		  
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse12">
          '.$admin[217].' => '.$admin[1412].'
        </a>
        <div class="collapse" data-parent="#accordion" id="collapse12">
          <div class="card-body w-100 p-2 pb-4 pr-3">
			<div style="margin-top:8px;">'.LineCB(1, $admin[1413], 'sitemapSM', $sitemap['SM']).'</div>
			'.LineCB(2, $admin[780], 'sitemapBL', $sitemap['BL']).'
			'.LineCB(2, $admin[5].' :', 'sitemapAR', $sitemap['AR']).'
			'.LineCB(2, $admin[72], 'sitemapNE', $sitemap['NE']).'
			'.LineCB(2, $admin[74], 'sitemapPH', $sitemap['PH']).'
			'.LineCB(2, $admin[73], 'sitemapDN', $sitemap['DN']).'
			'.LineCB(2, $admin[207], 'sitemapFA', $sitemap['FA']).'
			'.LineCB(2, $admin[75], 'sitemapLI', $sitemap['LI']).'
			'.LineCB(2, $admin[76], 'sitemapGB', $sitemap['GB']).'
			'.LineCB(2, $admin[164], 'sitemapFR', $sitemap['FR']).'
			'.LineCB(2, $admin[651], 'sitemapAG', $sitemap['AG']).'
			<div style="margin:8px 16px 0 16px;">'.$admin[1414].'</div>
			<div style="text-align:center;margin:auto;">
              <textarea class="form-control w-80 m-auto" style="height:160px;" name="urlsmap">'.$mapurls.'</textarea>
            </div>
          </div>
        </div>
      </div>';
// CONTRIBUTION VISITEURS & ANTI-SPAM
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse13">'.$admin[600].' & '.$admin[761].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse13">
          <div class="card-body w-100 p-2 pb-4 pr-3">
            <div style="margin:4px auto auto auto; padding:0 0 5px 0;">
              '.LineTXT(1, $admin[1549], 'site10', $site[10], 300).'
              '.LineSELECT(2, $admin[1335], 'site12', $templates, $site[12]).'
            </div>
            <div style="margin:4px auto auto auto; padding:0 0 5px 0;">
              '.LineTXT(1, $admin[762], 'serviz51', $serviz[51], 80).'
              '.LineTXT(1, $admin[763], 'serviz52', $serviz[52], 60).'
            </div>
            <div style="margin:4px auto auto auto; padding:0 0 5px 0;">
              <label style="padding-left:12px;"><b>'.$symbls[1].'</b>&nbsp;&nbsp;'.$admin[1390].'</label><br />
              '.LineTXT(2, $admin[1391].'postguest.php :', 'sitepg', $site['PG'], 200).'
              '.LineTXT(2, $admin[1391].'blogs.php :', 'sitebls', $site['BLS'], 200).'
              '.LineTXT(2, $admin[1391].'blog.php :', 'sitebl', $site['BL'], 200).'
              '.LineTXT(2, $admin[1391].'forum.php :', 'sitefr', $site['FR'], 200).'
              '.LineTXT(2, $admin[1391].'fortopic.php :', 'sitefrt', $site['FRT'], 200).'
              '.LineTXT(2, $admin[1391].'thread.php :', 'siteth', $site['TH'], 200).'
              '.LineTXT(2, $admin[1391].'guestbk.php :', 'sitegb', $site['GB'], 200).'
              '.LineTXT(2, $admin[1391].'news.php :', 'sitene', $site['NE'], 200).'
              '.LineTXT(2, $admin[1391].'user.php :', 'siteusr', $site['USR'], 200).'
              '.LineTXT(2, $admin[1391].'newsletter.php :', 'sitenl', $site['NL'], 200).'
              '.LineTXT(2, $admin[1391].'download.php :', 'sitedn', $site['DN'], 200).'
              '.LineTXT(2, $admin[1391].'contact.php :', 'sitect', $site['CT'], 200).'
            </div>
          </div>
        </div>
      </div>';
// CAPTCHA
$n34 = getLabel($nom[34 + ($lng == $lang[0] ? 0 : 1)]);
if (mb_substr($n34, 0, 1, $charset) == '§') $n34 = mb_substr($n34, 1, 999, $charset);
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse14">'.$admin[1174].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse14">
          <div class="card-body w-100 p-2 pb-4 pr-3">
			'.LineSELECT(1, $admin[1184], 'serviz100', $types_captcha, $serviz[100]).'
			'.LineCB(2, $admin[1373], 'serviz115', $serviz[115]).'
			'.LineCB(1, $n34, 'serviz88', $serviz[88]).'
			'.LineCB(1, getLabel($nom[7 + ($lng == $lang[0] ? 0 : 10)]), 'serviz89', $serviz[89]).'
			'.LineCB(1, getLabel($nom[9 + ($lng == $lang[0] ? 0 : 10)]), 'serviz90', $serviz[90]).'
			'.LineCB(1, getLabel($nom[22 + ($lng == $lang[0] ? 0 : 1)]), 'serviz91', $serviz[91]).'
			'.LineCB(1, mb_substr($admin[472], 0, -1, $charset), 'serviz92', $serviz[92]).'
			'.LineCB(1, $admin[1679], 'serviz93', $serviz[93]).'
			'.LineCB(1, $admin[249], 'serviz94', $serviz[94]).'
			'.LineCB(1, $admin[1175], 'serviz95', $serviz[95]).'        
			'.LineCB(1, getLabel($nom[42 + ($lng == $lang[0] ? 0 : 1)]), 'serviz96', $serviz[96]).'
			'.LineCB(1, $admin[774], 'serviz97', $serviz[97]).'        
			'.LineCB(1, $admin[1176], 'serviz98', $serviz[98]).'        
          </div>
        </div>
      </div>';
// SUPERVISION
$readonly = 'readonly';
$selectdisable = 'disabled';
if (!isset($smtp[6])) 
{
	$smtp[6] = '';
	$smtp[7]  = '';
	
}
if ($smtp[6] != '') $selectdisable = '';							
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse15">'.$admin[229].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse15">
          <div class="card-body w-100 p-2 pb-4 pr-3">
			'.LineTXT(2, $admin[230], 'supervision5', 'phpmailer', 100,'text', '60%', $readonly).'
			<fieldset style="margin:2px 20px;">'.legend('<span  style="font-weight:normal;">'.$admin[1416].'</span>').'
			  '.LineTXT(2, $admin[1417], 'smtp0', $smtp[0], 300).'
			  '.LineCB(2, $admin[1418], 'smtp1', 'on', '','disabled').'
			  '.LineTXT(2, $admin[1419], 'smtp2', $smtp[2], 60).'
			  '.LineTXT(2, $admin[1420], 'smtp3', $smtp[3], 60).'
			  '.LineTXT(2, $admin[1421], 'smtp4', $smtp[4], 300).'
			  '.LineTXT(2, $admin[1422].':', 'smtp5', '', 300, 'password').'
			  '.LineTXT(2, $admin[1680].':', 'smtp5_2', '', 300, 'password').'
			  '.LineCB(2, $admin[1807], 'smtp6', $smtp[6]).'
			  '.LineSELECT(2, $admin[1808], 'smtp7', $optionsdelaiconservationlog, $smtp[7], false, 70, $selectdisable).'		  
<script language="JavaScript">
		 $(\'#smtp6\').on(\'click\', function(){
		if( $(this).is(\':checked\') ){
    		$(\'#smtp7\').attr(\'disabled\', false);
    }else{
    	  $(\'#smtp7\').attr(\'disabled\', true);
    }			});
				</script>				  
			</fieldset>
			<div style="margin-top:8px;">'.LineCB(1, $admin[234], 'supervision0', $supervision[0]).'</div>
			'.LineTXT(2, $admin[1063], 'supervision1', $supervision[1], 60).'
			<div style="margin-top:8px;">'.LineCB(1, $admin[1306], 'supervision11', $supervision[11]).'</div>
			'.LineCB(1, $admin[1307], 'supervision12', $supervision[12]).'
			'.LineCB(1, $admin[473], 'supervision6', $supervision[6]).'
			'.LineCB(1, $admin[235], 'supervision2', $supervision[2]).'
			'.LineCB(1, $admin[786], 'supervision8', $supervision[8]).'
			'.LineCB(1, $admin[1424], 'supervision9', $supervision[9]).'
			'.LineCB(1, $admin[236], 'supervision3', $supervision[3]).'
			'.LineCB(1, $admin[237], 'supervision4', $supervision[4]).'
			'.LineCB(1, $admin[520], 'supervision7', $supervision[7]).'
			<div style="padding-top:12px;">'.LineTXT2($admin[1083], 'supervision10', $supervision[10]).'</div>
          </div>
        </div>
      </div>';
// parefeu
if (!isset($actionwaf)) $actionwaf = '1';
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse16">'.$admin[1875].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse16">
          <div class="card-body w-100 p-2 pb-4 pr-3">
			'.LineSELECT(1, $admin[1876], 'actionwaf', $types_waf, $actionwaf,$wkey=false, $mwidth=180,'disabled').'
          </div>
        </div>
      </div>
	  
      <div class="row quest p-0 mt-2 w-100 m-auto">
	    <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
        <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
      </div>
    </div>
  </form>
  <script> document.conf5.serviz31.focus(); </script>
</div>';

btable();
include CHEMIN.'admin/bpage.inc';
?>