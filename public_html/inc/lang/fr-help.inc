<?php
/*******************************************************************************
 *   Help Language Strings
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
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'fr-help.inc')) {
    header('location:../index.php');
    die();
}

$help[1] = 'Les résultats de pertinence plus faible sont masqués. La valeur 0% affiche normalement tous les résultats.';
$help[2] = 'Par défaut : 10 au minimum.';
$help[3] = '<b>IMPORTANT!</b> Ne rien mettre si vous ne souhaitez pas modifier votre mot de passe. Sinon, vous devrez entrer également le mot de passe de contrôle.';
$help[4] = 'Indiquez la valeur "0" pour désactiver le contrôle.';
$help[5] = 'Indiquez une valeur entre \"1\" et \"30\", \"0\" pour un maintien permanent.';
$help[6] = "Pour inclure une image dans la signature du forum, il suffit de saisir le code de cette image à l'endroit souhaité dans le texte de la signature.<br />Formats acceptés : .gif .jpg .png 468x60 maxi.<br /><br />Le code pour l'image externe est votre pseudo préfixé par <b>fr_</b>. Exemple : <b>fr_MonPseudo</b>.";
$help[7] = 'ATTENTION! Toute action engendre l\'envoi d\'un courriel de notification à l\'intéressé.';
$help[8] = 'Pour utiliser votre avatar en provenance d\'un autre site, indiquez son adresse.<br />Formats acceptés : .gif .jpg .png 60x60 maxi.';
$help[9] = 'Pour synchroniser les adresses courriel de votre compte et de la lettre d\'information.';
$help[10] = 'L\'ID est un identifiant unique composé de 2 à 8 caractères non accentués, sans espaces.';
$help[11] = 'Indiquez les numéros d\'options à partir du début, suivi du numéro de cette option dans le sous-menu, séparés par des tirets.<br />( Ex: 2-4-1 ce qui veut dire provient de la 2ème option du menu principal, de la 4ème du sous-menu qui s\'est ouvert et c\'est l\'option n°1 du second sous-menu.)';
$help[12] = 'Pour n\'affecter aucun lien à l\'option de menu, saisir un <b>?</b>.';
$help[13] = 'Vous pouvez créer des sous-catégories en les séparant avec le symbole |.<br />Ex.: Catégorie|Niveau 1|Niveau 2';
$help[14] = 'Les dates doivent être au format AAAAMMJJhhmn (année mois jour heure minutes).<br />Ne rien mettre si on ne veut pas utiliser la publication programmée.<br />On peut indiquer seulement une date de début ou seulement une date de fin.<br />Si la date de début est omise, ce sera l\'heure d\'enregistrement de l\'évènement qui sera prise.';
$help[15] = '(videz le champ pour supprimer)';
$help[16] = '<br /><br /><strong>La catégorie ne sert que pour la construction de l\'option de menu si nécessaire.</strong>';
$help[17] = 'ATTENTION! Ne cochez que si vous utilisez la zone membre.<br />Cette option affecte la recherche, donc à utiliser si nécessaire.';
$help[18] = '<b>ATTENTION!</b> La taille du site est peut-être très importante. Dans ce cas sauvegardez en plusieurs morceaux.';
$help[19] = 'La classe <b>ZipArchive</b> nécessaire à l\'exécution de ce script n\'est pas activée.<br />Veuillez effectuer les opérations manuellement en FTP.';
$help[20] = 'GuppY est protégé par la licence libre CeCILL.<br />Supprimer ou modifier la notice de copyright, ou la masquer par quelles que méthodes que ce soit, est strictement interdit.';
$help[21] = 'Le mot de passe doit contenir 8 caractères.';
$help[22] = 'Bon mot de passe!';
$help[23] = 'Rendez votre mot de passe plus fort avec plus de lettres majuscules, plus de chiffres et de caractères spéciaux!';
$help[24] = 'Mot de passe faible, essayez d\'utiliser des chiffres, des majuscules et des caractères spéciaux.';
$help[25] = 'Pour qu\'une rotation se répète tous les ans, AAAA peut être remplacé par ---- (4 tirets), cela sera remplacé lors du traitement par l\'année courante.<br /><br />L\'heure et les minutes sur 2 digits chacune sont facultatives, ce sera remplacé par 0000.';
$help[26] = 'En cochant cette case, un bcc avec l\'adresse email du webmestre est ajouté au lien vers l\'adresse email du destinataire pour la réponse.<br /><br />C\'est utile de cocher lorsqu\'il y a le choix entre plusieurs destinataires et que l\'on veut que le webmestre reçoive toujours les réponses.';
$help[27] = 'Enregistrement de membres jusque 20 à la fois. Les champs obligatoires sont pseudo et couriel. Si le mot de passe n\'est pas indiqué, il sera généré.<br />Les identifiants seront envoyés au membre et à l\'admin.';

$errormsg['400'] = array(
	'Mauvaise requête',
	'La requête HTTP n\'a pas pu être comprise par le serveur en raison d\'une syntaxe erronée.',
	'Le problème peut provenir d\'un navigateur web trop récent ou d\'un serveur HTTP trop ancien.',
	'HTTP/1.0 400 Bad Request' );
$errormsg['401'] = array(
	'Non autorisé',
	'La requête nécessite une identification de l\'utilisateur.',
	'',
	'HTTP/1.0 401 Unauthorized' );
$errormsg['403'] = array(
	'Interdit',
	'Le serveur HTTP a compris la requête, mais refuse de la traiter. ',
	'Ce code est généralement utilisé lorsqu\'un serveur ne souhaite pas indiquer pourquoi la requête a été rejetée, ou lorsqu\'aucune autre réponse ne correspond (par exemple le serveur est un Intranet et seules les machines du réseau local sont autorisées à se connecter au serveur).',
	'HTTP/1.0 403 Forbidden' );
$errormsg['404'] = array(
	'Non trouvé',
	'Le serveur n\'a rien trouvé qui corresponde à l\'adresse (URI) demandée.',
	'Cela signifie que l\'URL que vous avez tapée ou cliquée est mauvaise ou obsolète et ne correspond à aucun document existant sur le serveur (vous pouvez essayer de supprimer progressivement les composants de l\'URL en partant de la fin pour éventuellement retrouver un chemin d\'accès existant).',
	'HTTP/1.0 404 Not Found' );
$errormsg['500'] = array(
	'Erreur interne du serveur',
	'Le serveur HTTP a rencontré une condition inattendue qui l\'a empêché de traiter la requête.',
	'Cette erreur peut par exemple être le résultat d\'une mauvaise configuration du serveur, ou d\'une ressource épuisée ou refusée au serveur sur la machine hôte.',
	'HTTP/1.0 500 Internal server error' );

?>
