<?php
/*******************************************************************************
 *   RGPD
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

if (stristr($_SERVER['SCRIPT_NAME'], 'rgpd.inc')) {
    header('location:../index.php');
    die();
}

if (is_file(REDACREP.$userprefs[1].INCEXT)) {
    include REDACREP.$userprefs[1].INCEXT;
    if ($drtuser[51] == '') {
        $nomzone = $admin[1776];
        include 'access.inc';
        exit();
    }
}

// Récupération des variables
$regit = import('regit');
$act   = import('act');
if ($serviz[177] == '') $pgmentionslegales = '17';
else
 $pgmentionslegales = $serviz[177];
if ($regit == 1) {
	// Récupération des variables soumises
	$rgpd  = import('rgpd', 'POST');
    $rgpd["privacyUrl"] = 'articles.php?lng='.$lng.'&pg='.$pgmentionslegales;	
	/**
	 * Contrôle des variables récupérées
	 */
	$rgpd["groupServices"] = boolval($rgpd["groupServices"]) ? 'true' : 'false';
	$rgpd["showIcon"] = boolval($rgpd["showIcon"]) ? 'true' : 'false';
	$rgpd["DenyAllCta"] = boolval($rgpd["DenyAllCta"]) ? 'true' : 'false';
	$rgpd["AcceptAllCta"] = boolval($rgpd["AcceptAllCta"]) ? 'true' : 'false';
	$rgpd["hashtag"] = !empty($rgpd["hashtag"]) ? trim($rgpd["hashtag"]) : '#rgpd';
	$rgpd["cookieName"] = !empty($rgpd["cookieName"]) ? trim($rgpd["cookieName"]) : 'tarteaucitron';
	$rgpd["orientation"] = in_array($rgpd["orientation"], array('top', 'bottom')) ? $rgpd["orientation"] : 'top';
	$rgpd["iconSrc"] = !empty($rgpd["iconSrc"]) ? str_replace(SITE3, '', trim($rgpd["iconSrc"])) : '';	
	$rgpd["iconPosition"] = in_array($rgpd["iconPosition"], array('BottomRight', 'BottomLeft', 'TopRight', 'TopLeft')) ? $rgpd["iconPosition"] : 'BottomLeft';
	
	$rgpd["servicesCode"] = !empty($rgpd["servicesCode"]) ? trim(preg_replace('`</?script[^>]*>`i', '', $rgpd["servicesCode"])) : '';
	//
	
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
}

include CONFIGREP.'rgpd.inc';

################################################################################
// code pour enregitrer les services soumis au rgpd et configurer le script tarteaucitron

if($act == 1) {
	if ($serviz[177] == '') $pgmentionslegales = '17';
	else
	 $pgmentionslegales = $serviz[177];
     $rgpd["privacyUrl"] = 'articles.php?lng='.$lng.'&pg='.$pgmentionslegales;
	$topmess = '<a href="admin.php?lng='.$lng.'">Admin</a> > '.str_replace('Admin - ', '', $admin[1776]);
	include CHEMIN.'admin/hpage.inc';
	if ($wri == 'admin') include CHEMIN.'admin/administrateur.php';
	if ($wri == $userprefs[1]) include CHEMIN.'admin/adminredac.php';
    htable($admin[1776], '100%');

    echo '
  <p style="text-align:center;">'.ToHelp($lng, '&pg=1102&mnuid=937&tconfig=0#z2').$admin[1777].'</p>
  <hr /> 
  <div class="w-100 m-auto" style="max-width:510px;">
    <form name="form_rgpd" action="admin.php?lng='.$lng.'&amp;pg=rgpd&amp;act=1" method="post">
    <input type="hidden" name="regit" value="1" />
	<div class="quest" style="padding:2px 20px;">
	<div style="float:right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div>
	<div style="clear:both"></div>
	</div>
	<br />
    <div class="table-responsive-md">
	<fieldset class="w-100">
		<legend>'.$admin[1778].'</legend>
		<p>'.$admin[1779].'</p>
		<p><label class="w-100">'.$admin[1780].'&nbsp;:&nbsp;
		<textarea name="rgpd[servicesCode]" class="w-100" style="max-width:510px;" rows="10">'.stripslashes($rgpd["servicesCode"]).'</textarea>
		</label>
		</p>
	</fieldset>

	<fieldset class="w-100">
	<legend>'.$admin[1781].'</legend>
		<p><label class="w-100">'.$admin[1782].'&nbsp;:&nbsp;
		<input type="text" name="rgpd[privacyUrl]" value="'.$rgpd["privacyUrl"].'" style="min-width:200px;" placeholder="'.$admin[1783].'" disabled readonly="readonly"/>
		</label>
		</p>
		<p><label class="w-100">'.$admin[1784].'&nbsp;:&nbsp;
		<input type="text" name="rgpd[hashtag]" value="'.$rgpd["hashtag"].'" style="max-width:80px;" placeholder="'.$admin[1785].'" />
		</label>
		</p>
		<p><label  class="w-100">'.$admin[1786].'&nbsp;:&nbsp;
		<input type="text" name="rgpd[cookieName]" value="'.$rgpd["cookieName"].'" placeholder="'.$admin[1787].'" />
		</label>
		</p>		
		<p><label>'.$admin[1788].'&nbsp;:&nbsp;
		<select name="rgpd[orientation]">
			<option value="top"'.Selected($rgpd["orientation"] == 'top').'>'.$admin[1789].'</option>
			<option value="bottom"'.Selected($rgpd["orientation"] == 'bottom').'>'.$admin[1790].'</option>
		</select>
		</label>
		</p>		
		<p><label>'.$admin[1791].'&nbsp;:&nbsp;
		<select name="rgpd[DenyAllCta]">
			<option value="1"'.Selected($rgpd["DenyAllCta"]).'>'.$admin[1605].'</option>
			<option value="0"'.Selected(!$rgpd["DenyAllCta"]).'>'.$admin[1604].'</option>
		</select>
		</label>
		</p>		
		<p><label>'.$admin[1792].'&nbsp;:&nbsp;
		<select name="rgpd[AcceptAllCta]">
			<option value="1"'.Selected($rgpd["AcceptAllCta"]).'>'.$admin[1605].'</option>
			<option value="0"'.Selected(!$rgpd["AcceptAllCta"]).'>'.$admin[1604].'</option>
		</select>
		</label>
		</p>		
		<p><label>'.$admin[1793].'&nbsp;:&nbsp;
		<select name="rgpd[groupServices]">
			<option value="0"'.Selected(!$rgpd["groupServices"]).'>'.$admin[1604].'</option>
			<option value="1"'.Selected($rgpd["groupServices"]).'>'.$admin[1605].'</option>
		</select>
		</label>
		</p>
		<fieldset>
		<legend>'.$admin[1794].'</legend>
			<p><label>'.$admin[1795].'&nbsp;:&nbsp;
			<select name="rgpd[showIcon]">
				<option value="0"'.Selected(!$rgpd["showIcon"]).'>'.$admin[1604].'</option>
				<option value="1"'.Selected($rgpd["showIcon"]).'>'.$admin[1605].'</option>
			</select>
			</label>
			</p>		
			<p><label>'.$admin[1796].'&nbsp;:&nbsp;
			<input type="text" name="rgpd[iconSrc]" value="'.$rgpd["iconSrc"].'" class="w-100" placeholder="'.$admin[1797].'" />
			</label>
			</p>	
			<p><label>'.$admin[1798].'&nbsp;:&nbsp;
			<select name="rgpd[iconPosition]">
				<option value="BottomRight"'.Selected($rgpd["iconPosition"] == 'BottomRight').'>'.$admin[1799].'</option>
				<option value="BottomLeft"'.Selected($rgpd["iconPosition"] == 'BottomLeft').'>'.$admin[1800].'</option>
				<option value="TopRight"'.Selected($rgpd["iconPosition"] == 'TopRight').'>'.$admin[1801].'</option>
				<option value="TopLeft"'.Selected($rgpd["iconPosition"] == 'TopLeft').'>'.$admin[1802].'</option>
			</select>
			</label>
			</p>
		</fieldset>
	</fieldset>
	<br />
	<div class="quest" style="padding:2px 20px;">
	<div style="float:right">'.SubmitButton($admin[38], '', '', $imgdroite).'</div>
	<div style="clear:both"></div>
	</div>
	</form>
  </div>
 ';
}
btable();
include CHEMIN.'admin/bpage.inc';
?>