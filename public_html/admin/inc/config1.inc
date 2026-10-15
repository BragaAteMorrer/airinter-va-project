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
 * v6.00.19 (August 26, 2024) : Tohelp modify
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "config1.inc")) {
    header("location:../index.php");
    die();
}
// voir les lignes à partir de 180 pour Opcache
// Pour libérer et vider le cache voir à la fin du script

// FONCTIONS DE MISE EN PAGE /////////////////////////////////////////////////////////////////////////
function checkboxConfigGuppy($inputName, $inputValue, $libelle = '', $lang = 'fr', $flag = '', $readonly='') {
	if ($readonly != '') $readonly='disabled';
	return '
    <div class="row pt-2">
	  <div class="col-md-6 text-left py-2 px-4">'.
	    ($flag != '' ? '<i class="flg-'.$lang.'" title="'.$lang.'"></i>' : '').'<label for="'.$inputName.'">'.$libelle.'</label>
	  </div>
	  <div class="col-md-6 text-left py-2 px-4">
	    <input type="checkbox" name="'.$inputName.'" id="'.$inputName.'" '.$readonly.' '.Checked($inputValue).' />
      </div>
	</div>
    ';
}

function inputConfigGuppy($inputName, $inputValue, $width, $libelle = '', $lang = 'fr', $flag = '', $readonly='') {
	global $charset;
    $btnlabel = ($inputName == 'siteMF' || $inputName == 'siteMD');
	if ($readonly != '') $readonly='disabled';
	return '
    <div class="row">
	  <div class="col-md-'.($btnlabel ? '8' : '6').' text-left pt-3">'.$libelle.'</div>
	  <div class="col-md-'.($btnlabel ? '4' : '6').' text-left">
        '.($flag != '' ? '<div class="d-inline-block m-0 px-0"><i class="flg-'.$lang.' mt-2" title="'.$lang.'"></i></div>' : '').'
	    <div class="d-inline-block w-88">
          <input class="form-control texte'.($btnlabel ? ' w-40' : ' w-100').'" type="text" name="'.$inputName.'" id="'.$inputName.'" '.$readonly.'
            style="max-width: '.$width.';" value="'.htmlentities($inputValue, ENT_QUOTES, $charset).'" />
        </div>
	  </div>
	</div>';
}

function inputURLConfigGuppy($inputName, $inputValue, $width, $libelle = '', $lang = 'fr', $flag = '') {
    global $realurl, $lng, $admin, $charset;
    if ($realurl == $inputValue) {
        $msg = '';
    } else {
        $msg = $admin[919].'
	<span style="font-weight:bold;">'.$realurl.'</span>
    <a href="admin.php?lng='.$lng.'&amp;pg=config1&amp;replace='.$inputName.'" title="'.$admin[920].'">&nbsp;
      <i class="fas fa-external-link-square-alt c-pointer text-danger align-middle" style="font-size: 1.8rem;" title="'.$admin[920].'"></i>
    </a>';
    }
	return '
    <div class="row mt-3">
      <div class="col-md-6 text-left pt-3">
        '.($flag != '' ? '<i class="flg-'.$lang.'" title="'.$lang.'"></i>' : '').$libelle.'
	  </div>
	  <div class="col-md-6 text-left">
	    <input class="form-control texte" type="text" name="'.$inputName.'" id="'.$inputName.'" value="'.htmlentities($inputValue, ENT_QUOTES, $charset).'" 
          style="max-width:'.$width.';" />
	  </div>
    </div>'.($msg == '' ? '' : '<div class="text-center bg-yellow text-danger mx-1 py-2">'.$msg.'</div>');
}

function textareaConfigGuppy($textareaName, $textareaValue, $width, $height, $libelle = '', $lang = 'fr', $flag = '') {
	return '
    <div class="row">
	  <div class="col-md-6 text-left pl-3 pt-2">'.
	    ($flag != '' ? '<i class="flg-'.$lang.'" title="'.$lang.'"></i>' : '').$libelle.'
	  </div>
	  <div class="col-md-6 text-left">
	    <textarea name="'.$textareaName.'" id="'.$textareaName.'" 
          class="form-control align-middle w-100" style="max-width:'.$width.';height:'.$height.';margin:4px 5px 0 0;" rows="6" cols="30">
          '.$textareaValue.'
        </textarea>
	  </div>
	</div>
	';
}

function selectConfigGuppy($selectName,$options,$optionsValueSelect = '',$libelle = '', $lang = 'fr', $flag = '', $top = 0, $left = 0) {
	global $charset;
	$out = '
    <div class="row mt-1">
      <div class="col-md-6 text-left pt-3"'.($left > 0 ? ' style="padding-left:'.$left.'px;"' : '').'>
        '.($flag != '' ? '<i class="flg-'.$lang.'" title="'.$lang.'"></i>' : '').$libelle.'
      </div>
	  <div class="col-md-6 text-left">
	    <select class="form-control w-80" id="'.$selectName.'" name="'.$selectName.'" size="1">';
	foreach($options as $optionsName => $optionsValue) {
		$optionsName = strip_tags(str_replace("<!--", "", str_replace("-->", " ", $optionsName)));
		$out .= '
		  <option value="'.htmlentities($optionsValue, ENT_QUOTES, $charset).'" '.Selected($optionsValueSelect == stripslashes($optionsValue)).'>
            '.stripslashes($optionsName).'
          </option>';
	}
	return $out.'
	    </select>
	  </div>
	</div>';
}

//////////////////////////////////////////////////////////////////////////////////////////////////////

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[0] == '') {
        $nomzone = $admin[7];
        include 'access.inc';
        exit();
    }
}

$proto   = is_ssl() ? 'https://' : 'http://';
$realurl = $proto.str_replace("/admin","/",$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])); //real site url

function checkURL($actual) {
    global $realurl;
    if (empty($actual)) {
        /// URL vide => on change
        return $realurl;
    }
    $actual .= '/' != $actual[strlen($actual) - 1] ? '/' : '';
    if ($realurl == $actual) {
        return $actual;
    } else {
        if (strpos($realurl, 'http://localhost/') !== 0
        and
           (strpos($actual, 'http://127.0.0.1/') === 0
        or  strpos($actual, 'http://localhost/') === 0))
        {   /// Le site n'est pas en local, mais l'url est restée en local => on change
            return $realurl;
        } else {
            return $actual;
        }
    }
}

function replaceURLart() {
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
        $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2,
        $fieldweb, $fieldmail, $fieldmod;
	global $lang, $urlrw;
	$dbw = ReadDBFields(DBOPTMENU);
	foreach($dbw as $opt) {
		ReadDoc($opt[4]);
		if ($fieldc1 == '_SELF') {
			$urls    = explode('||', $fieldd1);
			$id      = substr($urls[0], strpos($urls[0], 'pg=') + 3);
			$pos     = strpos($id, '&');
			$id      = $pos === false ? $id : substr($id, 0, $pos);
            $afld    = explode('|', $fieldmail);
			$fielda1 = addslashes($fielda1);
			$fielda2 = addslashes($fielda2);
			$fieldb1 = addslashes($fieldb1);
			$fieldb2 = addslashes($fieldb2);
			$fieldd1 = 'articles.php?lng='.$lang[0].'&pg='.$id.'&mnuid='.$opt[5].'&tconfig='.$afld[0].'||'.$lang[0].'-'.$urlrw[20].'-103-'.$id.'-'.$opt[5].'-'.$afld[0];
			$fieldd2 = 'articles.php?lng='.$lang[1].'&pg='.$id.'&mnuid='.$opt[5].'&tconfig='.$afld[0].'||'.$lang[1].'-'.$urlrw[20].'-103-'.$id.'-'.$opt[5].'-'.$afld[0];
			WriteDoc();
			UpdateDocdates('mod', $opt[4]);
		}
	}
}

$regit = import('regit');
include CONFIG;

$valeuropcacheforce = '';
$site[7] = '';
$mrsite8 = $site[8];
if (function_exists('opcache_get_configuration')) // si OPcache est actif
{
/*
// Appel du tableau des configurations OPcache enregistrées dans php.ini
	$mropcacheconfiguration = opcache_get_configuration();
	echo '<br />';
	var_dump($mropcacheconfiguration); // Affichage du tableau
	echo '<br />';
	echo 'Dans constantes.php Opcachedisable pour les disques 1 pour oui, 0 pour enable?: '.OPCACHEDISABLE.'<br />';
	echo 'Dans constantes.php Opcachetempo en seconde: '.OPCACHETEMPOSECONDE.'<br />';
	echo 'Dans constantes.php Mise en place des Ini_set pour oui Disable 1/Enable =>0 : '.OPCACHEINISET.'<br />';
	// Affichage de la valeur activation de opcache 1 pour enable
	echo 'opcache enable: '.$mropcacheconfiguration["directives"]["opcache.enable"].'<br />';
	// Affichage de la fréquence ou temporisation
	echo 'opcache Frequence temp defini dans php.ini: '.$mropcacheconfiguration["directives"]["opcache.revalidate_freq"].'<br />';
	// Version de Php
	echo 'opcache version: '.$mropcacheconfiguration["version"]["version"].'<br />';
*/
	if (OPCACHEDISABLE == 1) // si opcache 
	{
		if (OPCACHEINISET == 1) // Gestion des options caches enable/disable
		{	
			if (ini_get('opcache.enable') == 1) 
			{
				ini_set('opcache.enable', '0');
//				echo ' OPcache désactivé fait<br />';
			}
		}
			$site[7] = $valeuropcacheforce = 'on';
			// Appel du tableau des configurations OPcache enregistrées dans php.ini
			$mropcacheconfiguration = opcache_get_configuration();
			if (($site[8] == '') || ($site[8] == ' ') || ($site[8] < $mropcacheconfiguration["directives"]["opcache.revalidate_freq"])
				|| ($mropcacheconfiguration["directives"]["opcache.revalidate_freq"] > OPCACHETEMPOSECONDE))
			{
				$mropcacheconfiguration["directives"]["opcache.revalidate_freq"] = OPCACHETEMPOSECONDE;
				$mrsite8 = $site[8] = $mropcacheconfiguration["directives"]["opcache.revalidate_freq"];
		// initialisation de la temporisation à de la variable déclarée dans constantes.php
			ini_set('opcache.revalidate_freq', OPCACHETEMPOSECONDE);
			}
	}
}
// Fin pour OPcache

$siteMD = substr(sprintf('%o', fileperms(CHEMIN.'admin')), -4);
$siteMF = substr(sprintf('%o', fileperms(CHEMIN.'admin/admin.php')), -4);

$keyfile   = CHEMIN.'data/config/securekey.dtb';
$securekey = is_file($keyfile) ? ReadFullDB($keyfile) : 'qO_83;pwY9';

if ($regit == 1) {
	$ctrl1 = '';
	foreach($urlrw as $st) $ctrl1 .= $st;
	$old_u20 = $urlrw[20];
	
	$site[0] = import('site0'); // Titre du site pour l'Editorial langue principale
	$site[1] = trim(RemoveBR(import('site1'))); // Meta Description du site
	$site[2] = trim(RemoveBR(import('site2'))); // Meta Mots-clés du site
	$site[3] = checkURL(import('site3'));
	$site[6] = import('site6'); // Hébergement derière un proxy
	$site[7] = import('site7'); // Hébergement avec mise en cache
// force a enable si opcache est activé
    $site[7] = $valeuropcacheforce;
	$site[8] = max(2, import('site8')); // Temporisation en s de lecture du cache
	$site[9] = RemoveQuote(import('site9')); // Meta Auteur du site	
	$site[11] = addslashes(import('site11')); // Titre du site pour l'Editorial langue secondaire
	$site190  = import('site190'); // Format Dates langue 0
	$site191  = import('site191'); // Format Dates langue 1
	$site[19] = $site190.'|'.$site191; // Format Dates
	$site[22] = import('site22'); // Format Heure
	$site[23] = import('site23'); // Séparateur Date & Heure
	$site[24] = import('site24'); // Logo Copyright
	$site[25] = import('site25'); // Position Logo Copyright
//	$site[26] = import('site26'); // Compression des Pages
	$site[26] = '';
	$site[35] = import('site35'); // Style de Copyright
	$site['TZ'] = import('siteTZ'); // Fuseau horaire
	$site['URLR'] = import('siteURLR'); // URL rewriting
	$site['MD'] = substr('0000'.import('siteMD', 'POST', true, $siteMD), -4); // Mode préconisé pour les dossiers
	$site['MF'] = substr('0000'.import('siteMF', 'POST', true, $siteMF), -4); // Mode préconisé pour les fichiers

	$urlrw[0] = import('urlrw0'); // Accueil / Admin / Error
	$urlrw[1] = import('urlrw1'); // Blog
	$urlrw[2] = import('urlrw2'); // Contact
	$urlrw[3] = import('urlrw3'); // Connexion / Compte
	$urlrw[4] = import('urlrw4'); // FAQ
	$urlrw[5] = import('urlrw5'); // Forum
	$urlrw[6] = import('urlrw6'); // Liens
	$urlrw[7] = import('urlrw7'); // Livre d'or
	$urlrw[8] = import('urlrw8'); // Nouvelles
	$urlrw[9] = import('urlrw9'); // Photos
	$urlrw[11] = import('urlrw11'); // Téléchargements
	$urlrw[12] = import('urlrw12'); // Recherches
	$urlrw[13] = import('urlrw13'); // Messages privés
	$urlrw[14] = import('urlrw14'); // Mesages publics
	$urlrw[15] = import('urlrw15'); // Utilisateurs
	$urlrw[17] = import('urlrw17'); // Calendrier / Agenda
	$urlrw[18] = import('urlrw18'); // Lettre d'information
	$urlrw[19] = import('urlrw19'); // Documents RSS
	$urlrw[20] = import('urlrw20'); // Articles
	
	$ctrl2 = '';
	foreach($urlrw as $st) $ctrl2 .= $st;
	
	$lang[0] = import('lang0'); // Langue principale du site
	$lang[1] = import('lang1'); // Deuxième langue du site
	$user[0] = import('user0'); // Webmaster
    $user[0] = KeepGoodChars(RemoveConnector(RemoveQuote(stripslashes(CutLongWord($user[0], $serviz[148])))));
	$user[1] = import('user1'); // e-mail du responsable du site
	$user[3] = import('user3'); // formulaire mail PHP  ON / OFF
	
	include 'inc/confupdt.inc';
	if ($lng != $lang[0] && $lng != $lang[1]) {
  		$lng = $lang[0];
	}
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
	
	if ($ctrl1 != $ctrl2) setURLrewriting('');
	setURLrewriting($site['URLR']); // active/désactive l'URL rewriting
	if ($old_u20 != $urlrw[20]) replaceURLart();
    
    $keysecure = import('securekey');
    if (!empty($keysecure) && $keysecure != $securekey) {
        WriteFullDB($keyfile, $keysecure);
        setcookie(USER_COOKIE, '');
        setcookie(CNCT_COOKIE, '');
        setcookie('GuppYCrypt', '');
        $location = CHEMIN.($site['URLR'] == 'on'? $lng.'-'.$urlrw[0]: 'index.php?lng='.$lng.'&securekey=on');
        echo BeginJavascript().'
setTimeout(\'window.location="'.$location.'"\', 1000);'
.EndJavascript();
        exit();
    }
    
    if ($site[7] == 'on' || PHP700) {
        @ob_flush();
        sleep(PHP700 ? 3 : $mrsite8);
        echo BeginJavascript().'window.location="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=config1";'.EndJavascript();
    }
} else {
    $site[3] = checkURL($site[3]);
    if ('site3' == $replace) {
        $site[3] = $realurl;
    }
    include 'inc/confupdt.inc';
}

$adm = array($admin[1322], $admin[780], $admin[1323], $admin[1324], $admin[207], $admin[164], $admin[75], 
             $admin[76], $admin[72], $admin[74], '', $admin[73], $admin[1325], $admin[1326], $admin[1327],
			 $admin[1328], '', $admin[1330], $admin[1331], $admin[1332], $admin[1333]);

$err_user0 = empty($user[0]) && $regit == 1;
$err_user1 = (empty($user[1]) || 1 != preg_match('/^[^\.\s]+(\.[^\.\s]+)*@[^\.\s]+(\.[^\.\s]+)+$/', $user[1])) && $regit == 1;
for ($i = 0; $i < count($urlrw); $i++) {
    if (!empty($adm[$i])) $err_urlrw[$i] = (empty($urlrw[$i]) || 1 != preg_match('/^[a-zA-Z0-9_-]{3,32}$/', $urlrw[$i])) && $regit == 1;
}
$erreur    = '';
$erreur   .= $err_user0 ? '<li>'.$admin[1585].$admin[63].$admin[1586].'</li>': '';
$erreur   .= $err_user1 ? '<li>'.$admin[1585].$admin[64].$admin[1587].'</li>': '';
if ($site['URLR'] == 'on') {
	for ($i = 0; $i < count($urlrw); $i++) {
		if (!empty($adm[$i])) $erreur .= $err_urlrw[$i] ? '<li>'.$admin[1585].$adm[$i].$admin[1587].'</li>': '';
	}
}
$activateItem = 4;
$activateItem = $err_user0 || $err_user1 ? 6 : $activateItem;
if ($site['URLR'] == 'on') {
	for ($i = 0; $i < count($urlrw); $i++) {
		if (@$err_urlrw[$i]) {
			$activateItem = 5;
			break;
		}
	}
}

$langue  = array();
$dossier = opendir(CHEMIN.'inc/lang/');
while ($fichier = readdir($dossier)) {
	if (is_file(CHEMIN."inc/lang/".$fichier)) {
  		$basefic = basename($fichier);
  		$path_parts = substr($basefic,strrpos($basefic,".")+1);
  		if ($path_parts == "inc") {
    		if (strpos($basefic, "-web") !== false) {
      			$langue[] = substr($basefic,0,strlen($basefic)-8);
    		}
  		}
	}
}
closedir($dossier);
@sort($langue);

if (is_file ($meskin.'confskin.inc')) {
    include $meskin.'confskin.inc';
} else {
    include CHEMIN.'skins/no_skin/confskin.inc';
}

$headinc .= BeginJavascript().'
function VerifyForm() {
    var sto    = "";
    var erreur = false;
    if (document.conf1.user0.value == "") {
        sto += "  - '.addslashes($admin[1585].$admin[63].$admin[1586]).'\n";
		document.conf1.user0.className = "errorInputText";
        erreur = true;
    } else {
        regexp = /^[-a-zA-Z0-9_]{2,'.$serviz[148].'}$/;
        if (!regexp.test(document.conf1.user0.value)) {
            sto += "  - '.str_replace('"', '\"', stripslashes($admin[1588])).'\n";
            document.conf1.user0.className = "errorInputText";
            erreur = true;
        }
    }
    if (document.conf1.siteMD.value == "") {
        sto += "  - '.addslashes($admin[1585].$admin[1453].$admin[1586]).'\n";
		document.conf1.siteMD.className = "errorInputText";
        erreur = true;
    }
    if (document.conf1.siteMF.value == "") {
        sto += "  - '.addslashes($admin[1585].$admin[1454].$admin[1586]).'\n";
		document.conf1.siteMF.className = "errorInputText";
        erreur = true;
    }
    regexp = /^[^\.\s]+(\.[^\.\s]+)*@[^\.\s]+(\.[^\.\s]+)+$/;
    if (!regexp.test(document.conf1.user1.value)) {
		sto += \'  - '.addslashes($admin[1585].$admin[64].$admin[1587]).'\n\';
		document.conf1.user1.className = "errorInputText";
        erreur = true;
    }';
for ($i = 0; $i < count($urlrw); $i++) {	
	$headinc .= '
	if (document.conf1.siteURLR.checked == true) {
		regexp = /^[-a-zA-Z0-9_]{3,32}$/;
		if (!regexp.test(document.conf1.urlrw'.$i.'.value)) {
			sto += "  - '.addslashes($admin[1585].$adm[$i].$admin[1587]).'\n";
			document.conf1.urlrw'.$i.'.className = "errorInputText";
			erreur = true;
		}
	}';
}
$headinc .= '
    if (erreur == true) {
        sto = "'.addslashes($admin[1589]).'\n\n" + sto;
        alert(sto);
        return false;
    }
}
function setMD(md) {
    document.conf1.siteMD.value = md;
    return false;
}
function setMF(mf) {
    document.conf1.siteMF.value = mf;
    return false;
}
function generate() {
    var chars = new Array( "abcdefghijklmnopqrstuvwxyz".split(""), 
                           "abcdefghijklmnopqrstuvwxyz".toUpperCase().split(""), 
                           "0123456789".split(""), 
                           "!#$%&()*+,-./:;<=>?@[]^_{}".split(""));
    var level = 4;
    var long = 10;
 
    var rep = new Array();
    myrand = new Date().getMilliseconds();
 
    while(level > 1) { 
        templong = Math.ceil(Math.random(myrand)*(long-(--level)));
        rep.push(templong);
        long = long-templong;
	}
	rep.push(long);
    var password = new Array();
    var i = -1;
    while(rep[++i]) {
        BaseL = chars[i].length;
        j = -1;
        while ((j++ < rep[i] - 1) && (password.push (chars[i][Math.floor(Math.random(Math.pow(new Date().getMilliseconds(),3))*BaseL)]))){}
	}
    document.getElementById("securekey").value = password.sort(sortRand).reverse().sort(sortRand).sort(sortRand).reverse().join("");
}
function sortRand() { return (Math.round(Math.random(myrand))-0.5); }
'.EndJavascript();

$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[46]);
include CHEMIN.'admin/hpage.inc';
if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
htable($admin[46], "100%");
if (!empty($erreur))
	echo displayErrorMsg($admin[1589], $erreur);
echo '
<p style="text-align:center;">'.ToHelp($lng, '&pg=952&mnuid=937&tconfig=0#z2').$admin[47].'<br /><br />'. $admin[318].'</p>
<hr style="margin-top:15px;" />
<form name="conf1" id="config1" action="admin.php?lng='.$lng.'&amp;pg=config1" method="post" onsubmit="return VerifyForm(); return false;">
  <input type="hidden" name="regit" value="1" />
  <div class="conf1" style="margin-right:auto;margin-left:auto;width:auto;">';
echo '
    <div class="m-auto" id="accordion" style="max-width:580px;">
    <div class="row quest p-0 mb-2 w-100 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
    </div>';

// OPTIMISATION VITESSE
/*
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse1">'.$admin[506].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse1">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            '.checkboxConfigGuppy('site26', $site[26], '&bull;&nbsp;'.$admin[505]).'
          </div>
        </div>
      </div>';
*/
// FUSEAU HORAIRE
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse2">'.mb_strtoupper($admin[935]).'</a>
        <div class="collapse" data-parent="#accordion" id="collapse2">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <div class="row">
                <div class="col-md-6 pt-3 pl-4">&bull;&nbsp;'.$admin[936].'</div>
                <div class="col-md-6">'.SelectTimeZone($site['TZ'], 'siteTZ', 'siteTZ', 16).'</div>
            </div>
          </div>
        </div>
      </div>';
// CLE DE SECURITE
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse3">'.mb_strtoupper($admin[1755]).'</a>
        <div class="collapse" data-parent="#accordion" id="collapse3">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">';
$btnSK = ' <button class="btn btn-md btn-danger text-light mt-n2 px-4" onclick="generate(); return false;"><b>'.$admin[1756].'  =></b></button>';
echo '
            <div class="row">
              <div class="col-md-6 pt-3 pl-4">&bull;&nbsp;'.$admin[1755].'</div>
              <div class="col-md-6 pt-3 text-center">
                '.$btnSK.'
                <input class="form-control texte" type="text" name="securekey" id="securekey" 
                  value="'.htmlentities($securekey, ENT_QUOTES, $charset).'" />
              </div>
            </div>
          </div>
        </div>
      </div>';
// INFOS SUR LE SITE
$menuOptionsSelect1 = $menuOptionsSelect2 = array();
$menuOptionsSelect2['- '.$admin[57].' -'] = '';
for ($i = 0; $i < count($langue); $i++) {
	$menuOptionsSelect1[$langue[$i]] = $langue[$i];
	$menuOptionsSelect2[$langue[$i]] = $langue[$i];
}
$menuOptionsSelect3 = array($admin[490] => 'H1', $admin[491] => 'H2', $admin[492] => 'H3');
$menuOptionsSelect4 = array("&nbsp;" => " ","!" => "!","#" => "#","*" => "*","+" => "+","-" => "-","." => ".",":" => ":","&lt;" => "&lt;",
							"=" => "=","&gt;" => "&gt;","@" => "@","^" => "^","~" => "~","&deg;" => "&deg;","&curren;" => "&curren;",
							"&middot;" => "&middot;","&ordm;" => "&ordm;","&plusmn;" => "&plusmn;","&raquo;" => "&raquo;","&times;" => "&times;","&bull;" => "&bull;");
$menuOptionsSelectDate = array($admin[452] => 'E1', $admin[485] => 'E2', $admin[453] => 'U1', $admin[486] => 'U2', $admin[487] => 'C1', $admin[488] => 'C2');
$asite19 = explode('|', $site[19]);
$btnMD = ' <button class="mt-n2 pt-2 px-1 py-1 bg-yellow" onclick="setMD(\''.$siteMD.'\'); return false;"><b>'.$siteMD.'  =></b></button>';
$btnMF = ' <button  class="mt-n2 pt-2 px-1 py-1 bg-yellow" onclick="setMF(\''.$siteMF.'\'); return false;"><b>'.$siteMF.'  =></b></button>';
echo '
      <div class="card">
        <a class="card-header card-link" data-toggle="collapse" href="#collapse4">'.$admin[49].'</a>
        <div class="collapse show" data-parent="#accordion" id="collapse4">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            '.inputConfigGuppy('site0', $site[0], '260px', '&bull;&nbsp;'.$admin[50], $lang[0], $flag1).'
            '.($lang[1] == '' ? '' : inputConfigGuppy('site11', $site[11], '260px', '', $lang[1], $flag2)).'
            '.inputURLConfigGuppy('site3', $site[3], '260px', '&bull;&nbsp;'.$admin[53]).'
            '.selectConfigGuppy('lang0', $menuOptionsSelect1, $lang[0], '&bull;&nbsp;'.$admin[54]).'
            '.selectConfigGuppy('site190', $menuOptionsSelectDate, $asite19[0], '&bull;&nbsp;'.$admin[451], $lang[0], '', -5, 32).'
            '.selectConfigGuppy('lang1', $menuOptionsSelect2, $lang[1], '&bull;&nbsp;'.$admin[56]).'
            '.selectConfigGuppy('site191', $menuOptionsSelectDate, $asite19[1], '&bull;&nbsp;'.$admin[451], $lang[1], '', -5, 32).'
            '.inputConfigGuppy('siteMD', $site['MD'], '48px', '&bull;&nbsp;'.$admin[1453].'<span class="float-right pr-5">'.$btnMD.'</span>').'
            '.inputConfigGuppy('siteMF', $site['MF'], '48px', '&bull;&nbsp;'.$admin[1454].'<span class="float-right pr-5">'.$btnMF.'</span>').'
            '.selectConfigGuppy('site22', $menuOptionsSelect3, $site[22], '&bull;&nbsp;'.$admin[489]).'
            '.selectConfigGuppy('site23', $menuOptionsSelect4, $site[23], '&bull;&nbsp;'.$admin[493]).'
            '.checkboxConfigGuppy('site6', $site[6], '&bull;&nbsp;'.$admin[1530]).'
            '.checkboxConfigGuppy('site7', $site[7], '&bull;&nbsp;'.$admin[1523],'', '','disabled').'
            '.inputConfigGuppy('site8', $mrsite8, '60px', '&nbsp;&nbsp;&nbsp;&nbsp;&bull;&nbsp;'.$admin[1524], '', '','disabled').'
          </div>
        </div>
      </div>';
// REECRITURE D'ADRESSES
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse5">'.$admin[1321].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse5">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            '.checkboxConfigGuppy('siteURLR', $site['URLR'], $admin[1314]);
for ($i = 0; $i < count($adm); $i++) {
	if (!empty($adm[$i])) echo inputConfigGuppy('urlrw'.$i, $urlrw[$i], '230px', '&bull;&nbsp;'.$adm[$i]);
}
echo '
          </div>
        </div>
      </div>';
//INFOS SUR LE Webmestre
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse6">'.$admin[62].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse6">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            '.inputConfigGuppy('user0', $user[0], '250px', '&bull;&nbsp;'.$admin[63]).'
            '.inputConfigGuppy('user1', $user[1], '250px', '&bull;&nbsp;'.$admin[64]).'
            '.checkboxConfigGuppy('user3', $user[3], '-&nbsp;'.$admin[232]).'
          </div>
        </div>
      </div>';
//BALISES META
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse7">'.$admin[308].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse7">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            '.textareaConfigGuppy('site1', $site[1], '360px', '150px', '&bull;&nbsp;'.$admin[51]).'
            '.textareaConfigGuppy('site2', $site[2], '360px', '150px', '&bull;&nbsp;'.$admin[52]).'
            '.inputConfigGuppy('site9', $site[9], '230px', '&bull;&nbsp;'.$admin[312]).'
          </div>
        </div>
      </div>';
//COPYRIGHT GUPPY
$menuOptionsSelect0 = array($admin[1487] => 'I', $admin[394] => 'T');
$menuOptionsSelect1 = array($admin[513] => 'N', $admin[512] => '', $admin[511] => 's', $admin[510] => 't');
$menuOptionsSelect2 = array($admin[508] => 'T', $admin[494] => 'L', $admin[495] => 'R', $admin[507] => 'B');
echo '
      <div class="card">
        <a class="card-header collapsed card-link" data-toggle="collapse" href="#collapse8">'.$admin[515].'</a>
        <div class="collapse" data-parent="#accordion" id="collapse8">
          <div class="card-body" style="max-width:560px; margin:4px auto auto auto; padding:0 0 5px 0;">
            <p style="text-align:center;">'.$help[20].'</p>
            '.selectConfigGuppy('site35', $menuOptionsSelect0, $site[35], '&bull;&nbsp;'.$admin[1486]).'
            '.selectConfigGuppy('site24', $menuOptionsSelect1, $site[24], '&bull;&nbsp;'.$admin[514]).'
            '.selectConfigGuppy('site25', $menuOptionsSelect2, $site[25], '&bull;&nbsp;'.$admin[509]).'
          </div>
        </div>
      </div>';
echo '
    <div class="row quest p-0 mt-2 w-100 m-auto">
	  <div class="col-md-6">'.SubmitButton($admin[22], 'self.location.href=\'admin.php?lng='.$lng.'\'', $imggauche, '', 'button').'</div>
      <div class="col-md-6"><div class="float-right">'.SubmitButton($admin[66], '', '', $imgdroite).'</div></div>
    </div>
  </div>
</form>';

btable();
include CHEMIN.'admin/bpage.inc';
?>