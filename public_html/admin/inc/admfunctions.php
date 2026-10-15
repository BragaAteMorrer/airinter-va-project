<?php
/*******************************************************************************
 *   Admin functions
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
 * v6.00.24 (August 12, 2026) : New function mir à jour plugins
 ******************************************************************************/

if (stristr($_SERVER["SCRIPT_NAME"], "admfunctions.php")) {
  header("location:../index.php");
  die();
}
function Verifmajpluginetphp($repplugin, $nomplugin, $iconchx='')
{
	global $lng;
// LOg 
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');
$nomficlog = CHEMIN.'data/log/admfunctions'.'_'.$dateheureminiutesecl.'.log';

// fin de log
	$icon = '';
//	echo 'replugin: '.$repplugin.' nomplugin: '.$nomplugin.' Guppy version: '.MP_VERSION.'<br />';
//	file_put_contents($nomficlog, 'ligne 35 repplugin: '.$repplugin."\n", FILE_APPEND | LOCK_EX);
	if (file_exists(CHEMIN.'admin/plugins/'.$repplugin.'/version/version.inc'))
	{
		include (CHEMIN.'admin/plugins/'.$repplugin.'/version/version.inc');
//		file_put_contents($nomficlog, 'ligne 38 repplugin: '.$repplugin."\n", FILE_APPEND | LOCK_EX);
	}
	elseif (CHEMIN.'admin/plugins/'.$repplugin.'/version.inc')
	{
		include (CHEMIN.'admin/plugins/'.$repplugin.'/version.inc');
//		file_put_contents($nomficlog, 'ligne 43 repplugin: '.$repplugin."\n", FILE_APPEND | LOCK_EX);
	}
//	echo "version['url']: ".$version['url']."<br />";
	if (is_file(CHEMIN.'inc/lang/'.$lng.'-pvlang.inc')) {
		include (CHEMIN.'inc/lang/'.$lng.'-pvlang.inc');
	}
	else {
		include (CHEMIN.'inc/lang/en-pvlang.inc');
	}
	$flagdanger  = "<i class='fas fa-flag-checkered' title='".$pv_lgversion[22]."' alt='".$pv_lgversion[22]."' style='font-size:16px;vertical-align:middle;color:red'></i>"; // 
	$flagorange = "<i class='fas fa-flag-checkered' itle='".$pv_lgversion[22]."' alt='".$pv_lgversion[22]."' style='font-size:16px;vertical-align:middle;color:orange'></i>"; // 
	$flagvert = "<i class='fas fa-flag-checkered' title='".$pv_ok4."' alt='".$pv_ok4."' style='font-size:16px;vertical-align:middle;color:green'></i>";
	$version['errors'] = "";
	$version['ok'] = "";
	$load_ok = false;
	$lines = array();
	if (!ini_get('allow_url_fopen')) 
	{
//		echo "Function non autorisée: <br />";
		if ($iconchx != '')
		{
			$icon = $flagdanger;
			// file_put_contents($nomficlog, 'ligne 62 repplugin: '.$repplugin.'icon: '.$icon."\n", FILE_APPEND | LOCK_EX);
			return $icon;
		}
	}
	if (ini_get('allow_url_fopen')) {
		$lines   = @file($version['url']);
		$load_ok = true;
/*
		echo 'lect lines<br />';
		var_dump($lines);
		echo '<br />';
*/

	}
	elseif ($curl = @curl_init($version['url'])){
		$timeout = 5; // set to zero for no timeout
		curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
		$version_content = curl_exec($curl);
		curl_close($curl);
		if ($version_content != false) {
			$lines = explode("\n", $version_content);
			$load_ok=true;
		}
	}
	else {
		$version['errors'] = $pv_error1;
	}
	$pv_guppyminvers   = $vpv_guppymaxvers = $pv_phpminvers   = $vpv_phpmaxvers = '';

//	if (isset(($lines[0])))
	if ($lines[0] !== null)
	{
		if ($load_ok) {
				// file_put_contents($nomficlog, 'ligne 95 repplugin: '.$repplugin.'icon: '.$icon."\n", FILE_APPEND | LOCK_EX);
//				echo '<br /><br />suite pv_lgversion[17] '.$pv_lgversion[17].'<br />';
			if (isset($lines[0]) && $lines[0] != '') 
			{
				$version['latest'] = trim($lines[0]);
//				echo "apres suite line 0 version['latest']: ".$version['latest']."<br/>";
//				echo '<br />';
			}
//			if (isset($lines[1]) && $lines[1] != '')
			if ($lines[1] !== null && $lines[1] != '')
			{
				$version['latest'] = trim($lines[1]);
				$mrversiontab = explode('=', trim($lines[1]));
				$mrversiontab[1] =  str_replace('"','', $mrversiontab[1]);
				$mrversiontab[1] =  str_replace(';','', $mrversiontab[1]);
				$version['latest'] = $mrversiontab[1];
//				echo " if ligne 66 line 1 version['latest']: ".$version['latest']."<br/>";
//				echo '<br />';
			}
//			if (isset($lines[6]) && $lines[6] != '')
			if ($lines[6] !== null && $lines[6] != '')
			{
				$minim_guppy_v = trim($lines[6]);
				$minim_guppy_vtab = explode('=', trim($lines[6])); 
				$minim_guppy_vtab[1] = str_replace('"','', $minim_guppy_vtab[1]);
				$pv_guppyminvers = str_replace(';','', $minim_guppy_vtab[1]);
				$vpv_guppyminvers = str_replace('.','',$pv_guppyminvers);
	//			echo 'version Min Guppy: '.$pv_guppyminvers.'<br />';
			}
//			if (isset($lines[7]) && $lines[7] != '')
			if ($lines[7] !== null && $lines[7] != '')
			{
				$max_guppy_v = trim($lines[7]);
				$max_guppy_vtab = explode('=', trim($lines[7])); 
				$max_guppy_vtab[1] = str_replace('"','', $max_guppy_vtab[1]);
				$pv_guppymaxvers   = str_replace(';','', $max_guppy_vtab[1]);
				$vpv_guppymaxvers = str_replace('.','',$pv_guppymaxvers);
	//			echo 'version Max Guppy: '.$pv_guppymaxvers.'<br />';
			}
//			if (isset($lines[8]) && $lines[8] != '')
			if ($lines[8] !== null && $lines[8] != '')
			{			
				$phpminvers = trim($lines[8]);
				$phpminvers_vtab = explode('=', trim($lines[8])); 
				$phpminvers_vtab[1] = str_replace('"','', $phpminvers_vtab[1]);
				$pv_phpminvers      = str_replace(';','', $phpminvers_vtab[1]);
	//			echo 'version Min Php: '.$pv_phpminvers.'<br />';
			}
//			if (isset($lines[9])&& $lines[9] != '')
			if ($lines[9] !== null && $lines[9] != '')
			{			
				$phpmaxvers = trim($lines[9]);
				$phpmaxvers_vtab = explode('=', trim($lines[9])); 
				$phpmaxvers_vtab[1] = str_replace('"','', $phpmaxvers_vtab[1]);
				$pv_phpmaxvers      = str_replace(';','', $phpmaxvers_vtab[1]);
//				echo 'version Max Php: '.$pv_phpmaxvers.'<br />';
			}
//			echo 'ligne 153 version latest: '.$version['latest'].'<br />';
			// file_put_contents($nomficlog, 'ligne 148 repplugin: '.$repplugin.' pv_phpmaxvers: '.$pv_phpmaxvers .' icon: '.$icon."\n", FILE_APPEND | LOCK_EX);
			switch ($version['latest'])
			{
				case 'error2': 
					$version['errors'] = $pv_error2; 
					if ($iconchx != '')
					{	
						$icon = $flagdanger;
						// file_put_contents($nomficlog, 'ligne 156 repplugin: '.$repplugin.' pv_phpmaxvers: '.$pv_phpmaxvers .' icon: '.$icon."\n", FILE_APPEND | LOCK_EX);
						return $icon;
					}
					break;
				case 'error3': 
					$version['errors'] = $pv_error3;
					if ($iconchx != '')
					{	
						$icon = $flagdanger;
						// file_put_contents($nomficlog, 'ligne 165 repplugin: '.$repplugin.' pv_phpmaxvers: '.$pv_phpmaxvers .' icon: '.$icon."\n", FILE_APPEND | LOCK_EX);
						return $icon;
					}
					break;
				case 'error4': 
					$version['errors'] = $pv_error4; 
					if ($iconchx != '')
					{	
						$icon = $flagdanger;
						// file_put_contents($nomficlog, 'ligne 174 repplugin: '.$repplugin.' pv_phpmaxvers: '.$pv_phpmaxvers .' icon: '.$icon."\n", FILE_APPEND | LOCK_EX);
						return $icon;
					}
					break;
				case 'error5': 
					$version['errors'] = $pv_error5;
					if ($iconchx != '')
					{	
						$icon = $flagdanger;
						// file_put_contents($nomficlog, 'ligne 183 repplugin: '.$repplugin.' pv_phpmaxvers: '.$pv_phpmaxvers .' icon: '.$icon."\n", FILE_APPEND | LOCK_EX);
						return $icon;
					}
					break;					
				default:
				$vg = '';

			// Comparaison version GuppY actuelle et attendue
				if (version_compare(MP_VERSION, $vpv_guppymaxvers, "<") && version_compare(MP_VERSION, $vpv_guppyminvers, "<")){
//					echo 'ligne 132 test < version guppy currente: '.MP_VERSION.' Version maxmum Guppy: '.$pv_guppymaxvers.'<br />';
					$vg .= '<br /><br />'.$pv_lgversion[11].'<a href="https://www.freeguppy.org/fgy6dn.php?lng=fr&tconfig=0#z2" target="_blank"><b>GuppY'.
						$pv_lgversion[20].$pv_guppyminvers.$pv_lgversion[21].$pv_guppymaxvers.'</b></a><br />('.$pv_lgversion[12].MP_VERSION.')</font><br />';
					if ($iconchx != '')
					{	
						$icon = $flagvert;
						// file_put_contents($nomficlog, 'ligne 198 repplugin: '.$repplugin.' pv_phpmaxvers: '.$pv_phpmaxvers .' icon: '.$icon."\n", FILE_APPEND | LOCK_EX);
					}
				}
				elseif((version_compare(MP_VERSION, $vpv_guppyminvers, ">")) && (version_compare($version['current'], $version['latest'], "=")))
				{
//					echo 'ligne 138 test > version guppy currente: '.MP_VERSION.' Version minimum Guppy: '.$pv_guppymaxvers.'<br />';
					$vg .= $pv_lgversion[14].$pv_guppyminvers.'<br />'.$pv_lgversion[13].'<b>'.MP_VERSION.'</b><br />';
					if ($iconchx != '')
					{	
						$icon = $flagvert;
						// file_put_contents($nomficlog, 'ligne 208 repplugin: '.$repplugin.' pv_phpmaxvers: '.$pv_phpmaxvers .' icon: '.$icon."\n", FILE_APPEND | LOCK_EX);
					}
					
				}
				elseif (version_compare($vpv_guppymaxvers, MP_VERSION, ">"))
				{
//					echo 'ligne 226 test > version max guppy: '.$pv_guppymaxvers.' Version current Guppy: '.MP_VERSION.'<br />';
					$vg .= '<font color="red"><br />'.$pv_lgversion[10].'<a href="https://www.freeguppy.org/fgy6dn.php?lng=fr&tconfig=0#z2" target="_blank"><b>GuppY</b></a> '
					.$pv_ok4.'<b>'.$pv_guppyminvers.'</b><br />('.$pv_lgversion[11].MP_VERSION.')</font><br />';
					if ($iconchx != '')
					{
						$icon = $flagdanger;
						// file_put_contents($nomficlog, 'ligne 220 repplugin: '.$repplugin.' pv_phpmaxvers: '.$pv_phpmaxvers .' icon: '.$icon."\n", FILE_APPEND | LOCK_EX);
						return $icon;
					}
				}	
				if (version_compare(PHP_VERSION, $pv_phpminvers, ">") && version_compare(PHP_VERSION, $pv_phpmaxvers, ">")){
//					echo 'ligne 149 test > version PHP currente: '.PHP_VERSION.' Version php minimum requis: '.$pv_phpminvers.'<br />';
//					echo 'ligne 150 test > version PHP currente: '.PHP_VERSION.' Version php maximum requis: '.$pv_phpmaxvers.'<br />';
					$vg .= $pv_lgversion[11].$pv_lgversion[18].'<b>'.$pv_phpminvers.'</b>'.$pv_lgversion[19].'<b>'.$pv_phpmaxvers.'</b></font><br />';
					if ($iconchx != '')
					{
						$icon = $flagdanger;
						// file_put_contents($nomficlog, 'ligne 231 repplugin: '.$repplugin.' pv_phpmaxvers: '.$pv_phpmaxvers .' icon: '.$icon."\n", FILE_APPEND | LOCK_EX);
						return $icon;
					}
					
				}
				if (version_compare(PHP_VERSION, $pv_phpminvers, "<") && version_compare(PHP_VERSION, $pv_phpmaxvers, "<")){
//					echo 'ligne 156 test < version PHP currente: '.PHP_VERSION.' Version php minimum requis: '.$pv_phpminvers.'<br />';
//					echo 'ligne 157 test < version PHP currente: '.PHP_VERSION.' Version php maximum requis: '.$pv_phpmaxvers.'<br />';
					$vg .= $pv_lgversion[11].$pv_lgversion[18].'<b>'.$pv_phpminvers.'</b>'.$pv_lgversion[19].'<b>'.$pv_phpmaxvers.'</b></font><br />';
					if ($iconchx != '')
					{
						$icon = $flagdanger;
						// file_put_contents($nomficlog, 'ligne 243 repplugin: '.$repplugin.' pv_phpmaxvers: '.$pv_phpmaxvers .' icon: '.$icon."\n", FILE_APPEND | LOCK_EX);
						return $icon;
					}
					
				}					
				// Comparaison version locale et actuelle, plugin
				if(version_compare($version['current'], $version['latest'], ">="))
				{
//					echo 'ligne 163 test version current et latest <br />';
					if ($iconchx != '')
					{
						$icon = $flagvert;
						// file_put_contents($nomficlog, 'ligne 255 repplugin: '.$repplugin.' pv_phpmaxvers: '.$pv_phpmaxvers .' icon: '.$icon."\n", FILE_APPEND | LOCK_EX);
						return $icon;
					}
						return $pv_ok2.' <b>'.$version["current"].'</b> ('.$pv_ok6.')'.$vg;
				}
				else
				{
//					echo 'ligne 168 else test version current et latest '.$pv_lgversion[15].'<br />';
					if ($iconchx != '')
					{
						$icon = $flagdanger;
						// file_put_contents($nomficlog, 'ligne 266 repplugin: '.$repplugin.' pv_phpmaxvers: '.$pv_phpmaxvers .' icon: '.$icon."\n", FILE_APPEND | LOCK_EX);
						return $icon;
					}
					return '<font color="red">'.$pv_lgversion[15].'<b>'.$version["current"].'</b>'.$pv_lgversion[16].'<b>
						'.$pv_lgversion[17].'('.$version["latest"].')</b></font>'.$vg;
						
				}
			}
		}
	} 
	else 
	{
		if ($iconchx != '')
		{
			$icon = $flagdanger;
			// file_put_contents($nomficlog, 'ligne 281 repplugin: '.$repplugin.' pv_phpmaxvers: '.$pv_phpmaxvers .' icon: '.$icon."\n", FILE_APPEND | LOCK_EX);
			return $icon;
		}
		return $pv_error2;
	}
}
/*******************************************************************************
 * function SelectTimeZone
 *
 * retourne le code HTML de la balise "select" permettant de choisir un TimeZone
 *
 * @param string $select_TZ   : nom du TimeZone sélectionné
 *     si vide alors remplacé par le TimeZone du serveur
 * @param string $select_name : valeur de l'attribut "name" de la balise "select"
 *     si vide alors remplacé par la chaine "TZ"
 * @param string $select_id   : valeur de l'attribut "id" de la balise "select"
 *     si vide alors remplacé par la chaine "TZ"
 * @param numeric $size       : valeur de l'attribut "size" de la balise "select"
 *     si vide ou incorrect alors la taille utilisée vaut 1
 * @return string             : retourne le code HTML de la balise "select"
 ******************************************************************************/
function SelectTimeZone($selected_TZ = '', $select_name = '', $select_id = '', $size = 1) {
    $select_TZ = empty($select_TZ) ? date_default_timezone_get() : $selected_TZ;
    $select_name = empty($select_name) ? 'TZ' : $select_name;
    $select_id = empty($select_id) ? 'TZ' : $select_id;
    $size = !empty($size) && is_numeric($size) && $size > 0 ? $size : 1;
    $list_TZ = version_compare(phpversion(), '5.2', '>=') ? DateTimeZone::listIdentifiers() : array($select_TZ);
    $html = '
<select class="form-control" id="'.$select_id.'" name="'.$select_name.'" size="'.$size.'" style="vertical-align:top">';
    $current_zone = '';
    foreach($list_TZ as $TZ) {
        $tmp = explode('/', $TZ);
        $zone = $tmp[0];
        $city = isset($tmp[1]) ? $tmp[1] : '';
        if ($current_zone == $zone) {
            $html .= '
    <option value="'.$TZ.'"'.Selected($selected_TZ == $TZ).'>'.$TZ.'</option>';
        } else {
            if (!empty($current_zone)) {
                $html .= '
  </optgroup>';
            }
            if (empty($city)) {
                $current_zone = '';
            } else {
                $current_zone = $zone;
                $html .= '
  <optgroup label="'.$current_zone.'">';
            }
            $html .= '
    <option value="'.$TZ.'"'.Selected($selected_TZ == $TZ).'>'.$TZ.'</option>';
        }
    }
    if (!empty($current_zone)) {
        $html .= '
  </optgroup>';
    }
    $html .= '
</select>';
    return $html;
}


/*******************************************************************************
 * $boxes est le tableau des boîtes disponibles
 *
 *  label    => nom de la boîte
 *  accept   => emplacements possibles pour cette boîte
 *              L(eft) R(ight)  : empacements latéraux
 *              T(op)           : emplacements en haut du site
 *              A(bove) U(nder) : emplacements au-dessus et au-dessous de la partie principale
 *              B(ottom)        : emplacements en bas du site
 *              E(ditorial)     : emplacements de la page Editorial
 *  include  => nom du fichier à inclure
 *  function => nom de la fonction à exécuter (facultatif)
 *  args => arguments (jusqu'à 5 séparés par une virgule) qui seront passés à la fonction
 ******************************************************************************/
if (!isset($nom[58])) $nom[58] = '';
if (!isset($nom[59])) $nom[59] = '';
$boxes = array(
    array( 'label'=>$admin[335]   ,'accept'=>'LRAUTBE'   ,'include'=>'' ,'function'=>''              ,'args'=>''),
    array( 'label'=>$admin[1543] ,'accept'=>'LRAUTBE'    ,'include'=>'' ,'function'=>'fbox_empty'    ,'args'=>''),
    array( 'label'=>$admin[364]   ,'accept'=>'LRAUTBE'   ,'include'=>'' ,'function'=>'fbox_search'   ,'args'=>''),
    array( 'label'=>$admin[364].' 2','accept'=>'LRAUTBE' ,'include'=>'' ,'function'=>'fbox_search2'  ,'args'=>''),
    array( 'label'=>$admin[1446] ,'accept'=>'LRTBE'      ,'include'=>'' ,'function'=>'fbox_contact'  ,'args'=>''),
    array( 'label'=>$admin[369]  ,'accept'=>'LRTB'       ,'include'=>'' ,'function'=>'fbox_count'    ,'args'=>''),
    array( 'label'=>$admin[450]  ,'accept'=>'LRTB'       ,'include'=>'' ,'function'=>'fbox_user'     ,'args'=>''),
    array( 'label'=>$admin[367]  ,'accept'=>'LRTB'       ,'include'=>'' ,'function'=>'fbox_webm'     ,'args'=>''),
    array( 'label'=>$admin[408]  ,'accept'=>'LRTB'       ,'include'=>'' ,'function'=>'fbox_cal'      ,'args'=>''),
    array( 'label'=>$admin[547]  ,'accept'=>'LRTB'       ,'include'=>'' ,'function'=>'fbox_newslet'  ,'args'=>''),
    array( 'label'=>$admin[1456] ,'accept'=>'LRAUTBE'    ,'include'=>'' ,'function'=>'fbox_photobox' ,'args'=>''),
    array( 'label'=>$admin[609]  ,'accept'=>'LRTBE'      ,'include'=>'' ,'function'=>'fbox_rss'      ,'args'=>''),
    array( 'label'=>$admin[876]  ,'accept'=>'LRTBE'      ,'include'=>'' ,'function'=>'fbox_menu'     ,'args'=>''),
    array( 'label'=>$admin[1766] ,'accept'=>'TB'         ,'include'=>'' ,'function'=>'fbox_sidemenu' ,'args'=>''),
    array( 'label'=>$admin[879]  ,'accept'=>'LRTBE'      ,'include'=>'' ,'function'=>'fbox_logo'     ,'args'=>''),
    array( 'label'=>$admin[6]    ,'accept'=>'LRAUTBE'    ,'include'=>'' ,'function'=>'fbox_cita'     ,'args'=>''),
    array( 'label'=>$admin[263]  ,'accept'=>'AUTBE'      ,'include'=>'' ,'function'=>'fbox_ban'      ,'args'=>''),
    array( 'label'=>$admin[466]  ,'accept'=>'E'          ,'include'=>'' ,'function'=>'fbox_forum'    ,'args'=>''),
    array( 'label'=>$admin[468]  ,'accept'=>'E'          ,'include'=>'' ,'function'=>'fbox_news'     ,'args'=>''),
    array( 'label'=>$admin[336]  ,'accept'=>'LRAUTBE'    ,'include'=>'' ,'function'=>'fbox_socnet'   ,'args'=>''),
    array( 'label'=>($lng == $lang[0] ? $nom[42] : $nom[43]).' - '.$admin[1672]
                                 ,'accept'=>'LRE'        ,'include'=>'' ,'function'=>'fbox_blogcat'  ,'args'=>''),
    array( 'label'=>($lng == $lang[0] ? $nom[42] : $nom[43]).' - '.$admin[1673]
                                 ,'accept'=>'LRE'        ,'include'=>'' ,'function'=>'fbox_bloglast' ,'args'=>''),
    array( 'label'=>($lng == $lang[0] ? $nom[42] : $nom[43]).' - '.$admin[1674]
                                 ,'accept'=>'LRE'        ,'include'=>'' ,'function'=>'fbox_blogcom'  ,'args'=>''),
    array( 'label'=>($lng == $lang[0] ? $nom[42] : $nom[43]).' - '.$admin[1675]
                                 ,'accept'=>'LRE'        ,'include'=>'' ,'function'=>'fbox_blogcal'  ,'args'=>''),
    array( 'label'=>($lng == $lang[0] ? $nom[42] : $nom[43]).' - '.$admin[1676]
                                 ,'accept'=>'LRE'        ,'include'=>'' ,'function'=>'fbox_blogrss'  ,'args'=>''),
    array( 'label'=>($lng == $lang[0] ? $nom[42] : $nom[43]).' - '.$admin[1675].' '.$admin[770]
                                 ,'accept'=>'LRE'        ,'include'=>'' ,'function'=>'fbox_blogarch' ,'args'=>''),
    array( 'label'=>$admin[779]  ,'accept'=>'E'          ,'include'=>'' ,'function'=>'fbox_blog'     ,'args'=>''),
    array( 'label'=>$admin[1668] ,'accept'=>'AUTB'       ,'include'=>'' ,'function'=>'fbox_ariane'   ,'args'=>''),
    array( 'label'=>$admin[1084] ,'accept'=>'LRAUTB'     ,'include'=>'' ,'function'=>'fbox_print'    ,'args'=>''),
    array( 'label'=>($lng == $lang[0] ? $nom[54] : $nom[55])
                                 ,'accept'=>'LRAUTBE'    ,'include'=>'' ,'function'=>'fbox_lastdoc'  ,'args'=>''),
    array( 'label'=>($lng == $lang[0] ? $nom[58] : $nom[59])
                                 ,'accept'=>'LRAUTBE'    ,'include'=>'' ,'function'=>'fbox_alldocuments'  ,'args'=>''),
    );

/*******************************************************************************
 * Fonction permettant de déclarer une boîte capable de prendre place dans un
 * des emplacements disponible
 *
 * @param string $label nom de la boîte
 * @param string $include chemin du fichier à inclure contenant la fonction
 * @param string $function nom de la fonction créant le code de la boite
 * @param mixed $args tableau ou chaine contenant les arguments à passer à la fonction
 * @param string $accept liste des emplacements pouvant recevoir la boite
 * @return void
 ******************************************************************************/
function AddBoxToBoxesList($label, $include, $function, $args = '', $accept = 'LRAUTBE') {
    global $boxes;
    $boxes[] = array(
        'label'     => $label,
        'include'   => $include,
        'function'  => $function,
        'args'      => is_array($args) ? implode(',', $args) : $args,
        'accept'    => empty($accept) ? 'LRAUTBE' : $accept
    );
}

/*******************************************************************************
 * Fonction ajoutant les boites libres à la liste des boites disponibles pour
 * les emplacements
 ******************************************************************************/
function AddFreeBoxes() {
    global $lng, $lang, $admin;
    $fbs = ReadDbFields(DBFREEBOX);
    foreach ($fbs as $fb) {
        $boxname = $lng == $lang[0] ? $fb[2] : $fb[3];
        if (empty($boxname)) $boxname = '&lt; '.$admin[1088].' &gt;';
        AddBoxToBoxesList(
            sprintf('FB %d : %s', $fb[4], $boxname),
            '',
            'fbox_freebox',
            $fb[4],
            $fb[0]
        );
    }
}

/*******************************************************************************
 * Fonction ajoutant les boites menus à la liste des boites disponibles pour
 * les emplacements
 ******************************************************************************/
function AddMenuBoxes() {
    global $lng, $lang, $admin;
    $mbs = ReadDbFields(DBMENUBOX);
    foreach ($mbs as $mb) {
        $boxname = $lng == $lang[0] ? $mb[2] : $mb[3];
        AddBoxToBoxesList(
            sprintf('MNU %d : %s', $mb[4], $boxname),
            '',
            'fbox_menubox',
            $mb[4],
            $mb[0]
        );
    }
}

/*******************************************************************************
 * Fonction ajoutant les boites menus déroulants à la liste des boites 
 * disponibles pour les emplacements
 ******************************************************************************/
function AddDDMenuBoxes() {
    global $lng, $lang, $admin;
    $mbs = ReadDbFields(DBDDMENUBOX);
    foreach ($mbs as $mb) {
        $boxname = $lng == $lang[0] ? $mb[2] : $mb[3];
        AddBoxToBoxesList(
            sprintf('%s %d : %s', $mb[1], $mb[4], $boxname),
            '',
            'fbox_ddmenubox',
            $mb[4],
            $mb[0]
        );
    }
}

/******************************************************************************/

function getLabelBox($boxes, $fct) {
	$args = '';
	$pos  = strrpos($fct, CONNECTOR);
	if ($pos > 0) {
		$args = substr($fct, $pos + 2);
		$fct  = substr($fct, 0, $pos);
	}
	$label = '';
    foreach ($boxes as $box) {
        if ($fct == $box['function'] && $args == $box['args']) {
            $label = $box['label'];
        }
    }
    return $label;
}

/******************************************************************************/

function WriteSelectBox($name, $value, $boxes, $center=false, $border=false, $wname='', $wvalue='') {
    $style = ' style="';
    if ($center && !$border) {
        $style .= 'text-align:center;"';
    } elseif (!$center && $border) {
        $style .= '" class="quest"';
    } elseif ($center && $border) {
        $style .= 'text-align:center;" class="quest"';
    } else {
        $style .= '"';
    }
    echo '
        <div '.$style.'>
          <div class="d-inline-block">
            <select class="form-control" name="'.$name.'" style="width:145px;">';
    foreach ($boxes as $label=>$code) {
        echo '
              <option value="'.$code.'"'.Selected($value == $code).'>'.$label.'</option>';
    }
    echo '
            </select>
          </div>';
	if (!empty($wname)) 
		echo '
          <div class="d-inline-block">
			&nbsp;&nbsp;L= <input name="'.$wname.'" class="texte" style="max-width:36px;" type="text" size="1" value="'.$wvalue.'" />
          </div>';
	echo '
        </div>';
}


/******************************************************************************/

function WriteColumnSelectBoxes($basename, $values, $boxes, $center=false, $border=false, $wbname='', $wbvalues=array()) {
    echo '
        <div style="text-align:center;padding:5px 1px;">';
    foreach($values as $key => $value) {
		$wbvalue = !empty($wbvalues) ? $wbvalues[$key] : '';
		$wname  = empty($wbname) ? '' : $wbname.'['.$key.']';
        echo WriteSelectBox($basename.'['.$key.']', $value, $boxes, $center, $border, $wname, $wbvalue);
    }
    echo '
        </div>';
}


/******************************************************************************/

function TestPosBoxes(&$box, $min, $free=0) {
    if ($free != 0) {
        /// on modifie le tableau pour qu'il y ait $free emplacements libres en fin de tableau
        $nfree = 0;
        foreach ($box as $item) {
            if ($item == '') $nfree++; else $nfree = 0;
        }
        if ($nfree < $free) {
            /// on ajoute les emplacements manquants
            while ($nfree < $free) {
                $box[] = '';
                $nfree++;
            }
        } elseif ($nfree > $free) {
            /// on supprime les emplacements surnuméraires
            while ($nfree > $free) {
                array_pop($box);
                $nfree--;
            }
        }
    }
    /// on ajoute des emplacements s'il ni en a pas le nombre minimal $min
    while(count($box) < $min) $box[] = '';
}


/******************************************************************************/

function TestSizeBoxes(&$box1, &$box2, &$box3 = NULL, &$box4 = NULL, &$box5 = NULL) {
    if (isset($box3)) {
        $max = max(count($box1), count($box2), count($box3));
        if (isset($box4)) $max = max($max, count($box4));
        if (isset($box5)) $max = max($max, count($box5));
        while (count($box1) < $max) $box1[] = '';
        while (count($box2) < $max) $box2[] = '';
        while (count($box3) < $max) $box3[] = '';
        if (isset($box4)) while (count($box4) < $max) $box4[] = '';
        if (isset($box5)) while (count($box5) < $max) $box5[] = '';
    } else {
        $max = max(count($box1), count($box2));
        while (count($box1) < $max) $box1[] = '';
        while (count($box2) < $max) $box2[] = '';
    }
}

/******************************************************************************/

function ToHelp($lng, $name, $hwin=700) {
	global $admin, $serviz;
	return '
<script>
var to = "";
var shref = "'.HLPREP.$lng.$name.'";
var rhref = "'.REMOTEHLP.$lng.$name.'";
function CreateHelp() {
	if (to == "on") whref = shref; else whref = rhref;
	wh = window.open(whref, \'help\', \'resizable=yes,status=no,location=no,toolbar=yes,menubar=no,scrollbars=yes,width=1100,height='.$hwin.',left=0,top=0\');
	wh.focus();
}
function OpenHelp() {
	if ((typeof(wh) == "undefined") || (wh.closed == true)) {
		CreateHelp();
	} else {
		wh.close();
		CreateHelp();
	}
	return;
}
</script>
<a href="'.REMOTEHLP.$lng.'-help-'.$name.'" onclick="OpenHelp(); return false;">
  <i class="fas fa-question-circle text-primary float-right c-pointer mr-2 mt-n3" style="font-size: 2.6rem;" title="'.$admin[1265].'"></i>
</a>';
}

/******************************************************************************/
	
function GetArtOptMenu($id, $typ) {
    global $lang, $lng, $admin, $userprefs, $fctwri;
	$oms = '';
    $cnt = 0;
	$dbw = SelectDBFieldsByField(ReadDBFields(DBARTMENU), 0, $id);
	if (count($dbw) > 0) {
		$opt  = 'Menu => '.$admin[1364];
		$lnks = '<span style="font-style:italic;font-size:11px;padding-bottom:6px;">'.$opt.'</span><br />';
		$w    = strlen($opt);
		foreach($dbw as $db) {
            if (file_exists(DBBASE.TestFileID($db[1]).INCEXT))
			{
				include DBBASE.TestFileID($db[1]).INCEXT;
				$temp   = explode('|', $author);
				$grpc   = isset($temp[1]) ? $temp[1] : '';
				$access = $grpc == '' ? ($temp[0] == $userprefs[1] || $fctwri == 'admin') : (isAccessGrantedCol($grpc) || $fctwri == 'admin');
				if (!$access) continue;
				$opt   = trim($db[$lng == $lang[0] ? 3 : 4].(empty($db[$lng == $lang[0] ? 5 : 6]) ? '' : ' => ').$db[$lng == $lang[0] ? 5 : 6]);
				$lnks .= '<a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg=optmenu&amp;menubox='.$db[2].'&amp;form=2&amp;tri=-id&amp;id='.$db[1].'"><b>'.$opt.'</b></a><br />';
				$w     = max($w, strlen($opt));
				$cnt++;
			}
		}
	} else {
		$lnks = $admin[681].'<br />';
		$w    = strlen($lnks) + 4;
	}
	$lnks .= '
	<hr />
	<div style="padding:4px;margin-bottom:6px;background-color:#EFEFEF;">
	  <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg=menubox&amp;act=1&amp;newopt=ok&amp;id='.$id.'&amp;typ='.$typ.'"><b>'.$admin[1225].'</b></a>
	</div>';
	$w     = max($w, strlen($admin[1225]));
	$oms = '
  <ul class="keyword keyword-horizontal">
	<li class="dir" style="margin:0 10px 0 5px;"><strong> '.$cnt.' </strong>
	  <ul>
	    <li style="width:'.($w * 7).'px;max-width:700px;">'.$lnks.'</li>
	  </ul>
	</li>
  </ul>';
	return $oms;
}

/******************************************************************************/

function ReadAuthorizedIndex($idx, $myname, $grpcol, $fctwri) {
    global $serviz;
    $ret = array();
    $dbx = ReadDBFields($idx);
	if (is_array($dbx)) 
	{
		foreach ($dbx as $db) {
			if (isset($db[4]) != '')
			{
				$id = TestFileID($db[4]);
				include(DBBASE.$id.INCEXT);
				$tmp    = explode('|', $author);
				$tmp[1] = !isset($tmp[1]) ? 'ALL' : $tmp[1];
				if ($tmp[0] == $myname || $tmp[1] == $grpcol || $fctwri != 'redac1' || ($serviz[31] != '' && $myname == $serviz[31]) || ($serviz[176] != '' && $myname == $serviz[176])) $ret[] = $db;
			}
		}
	}
    return $ret;
}

/******************************************************************************/

function ShowBlock($name, $items, $nbitem=7, $skintheme=NULL, $plug=false) {
    global $lng, $page, $admin, $serviz, $selskin, $userprefs, $sid;
    $tmp = '
<fieldset class="bg-white" style="margin:0 0 -10px 0; '.($plug ? 'width:100%; max-width:972px;' : 'max-width:'.(($nbitem+1)*136).'px;').'">';
    if (!empty($name)) $tmp .= legend($name);
    $admins = ReadDBFields(ADMINS);
    $ok     = true;
    foreach ($admins as $adm) {
        if ($adm[0] == $userprefs[1] && $adm[1] == 'redac2') $ok = false;
    }
	
    $skn = '';
    if (isset($skintheme) && $ok) {
        if (count($skintheme) > 1) {
            $tmp .= '
    <div class="text-center align-middle mb-3 py-1 text-white" style="background:#7F7F7F;">
      <form method="post" name="skin" action="admin.php?lng='.$lng.'">
        <div class="d-inline-block">'.$admin[725].' :</div>
        <div class="d-inline-block">
          <select name="selskin" class="form-control forum2" onchange="submit(); return true;">';
            foreach ($skintheme as $skin){
                $tmp .= '
            <option value="'.$skin.'"'.Selected($selskin == $skin).'>'.$skin.'</option>';
            }
            $tmp .= '
          </select>
        </div>
        <div class="d-inline-block">'.SubmitButton('Go').'</div>
      </form>
    </div>';
        } else {
            $tmp .= '
    <div class="forum2 text-center mb-3">'.$admin[725].' : <strong>'.$selskin.'</strong></div>';
        }
        $skn = '&amp;selskin='.$selskin;
    }

    $tmp .= '
  <style>
    .grp { max-width: 952px; }
    @media screen and (max-width: 952px), screen and (max-device-width: 952px) {
      .grp { min-width: 0; }
    }
  </style>
  <div class="grp w-100 my-0 mx-0 pt-2" style="max-width:'.($nbitem*($plug ? 136 : 136)).'px;">';
  
    foreach ($items as $item) {
		$src = !isset($item['src']) || empty($item['src']) ? $item['href'] : $item['src'];
		$hrf = (strpos($src, '/')  === false ? 'inc/img/admin/'.$src.'.gif' : $src);
		$txt = $item['txt'];
		if ($item['href'] == 'help') { 
			$hrf = REMOTEHLP.$lng.'&pg=944&mnuid=937&tconfig=0#z2" onclick="window.open(this.href, \'mainhlp\', 
              \'resizable=no,status=no,location=no,toolbar=no,menubar=no,fullscreen=no,scrollbars=yes,dependent=no,
              width=720,height=700,left=0,top=0\'); return false;';
		} else 
			$hrf = 'admin.php?lng='.$lng.'&amp;pg='.$item['href'].$skn;
		$tmp .= '
    <div class="d-inline-block" style="float:left; width:136px; height:'.($plug ? 112 : 92).'px; text-align:center; padding:0 8px;">
      <a href="'.$hrf.'" title="'.str_replace('<br />', ' ', $txt).'">
        '.($plug 
          ? '<img src="'.$src.'" alt="'.$txt.'" title="'.str_replace('<br />', ' ', $txt).'" style="border-style:none;height:36px;" />' 
          : '<i class="adm-'.$src.'" title="'.str_replace('<br />', ' ', $txt).'"></i>').'
        <br />'.$txt.'
      </a>
    </div>';
    }

    $tmp .= '
  </div><div style="clear:both"></div>
</fieldset><br />';
    echo $tmp;
}

/******************************************************************************/

function SelectBoxes($boxes, $accept) {
    $tmp = array();
    foreach ($boxes as $box) {
        if (strpos($box['accept'], $accept) !== FALSE) {
            $label = $box['label'];
            $code = $box['include'];
            if (! empty($box['function'])) {
                $code .= CONNECTOR.$box['function'];
                if (! empty($box['args'])) {
                    $code .= CONNECTOR.$box['args'];
                }
            }
            $tmp[$label] = $code;
        }
    }
    return $tmp;
}

/******************************************************************************/

function MakeRadioGroup($groupe) {
    global $admin;
    foreach ($groupe as $index=>$element) {
        $present = $element[0];
        $futur   = $element[1];
        $id      = $element[2];
        switch ($present.$futur) {
        case "ai" :
            $img1  = 'on1';
            $img2  = 'off2';
            $title = $admin[32];
            break;
        case "ia" :
            $img1  = 'off1';
            $img2  = 'on2';
            $title = $admin[34];
            break;
        case "ad" :
            $img1  = 'supa1';
            $img2  = 'sup2';
            $title = $admin[31];
            break;
        case "id" :
            $img1  = 'supi1';
            $img2  = 'sup2';
            $title = $admin[31];
            break;
        case "ds" :
            $img1  = 'sup1';
            $img2  = 'sup2';
            $title = $admin[60];
            break;
        case "di" :
            $img1  = 'save1';
            $img2  = 'save2';
            $title = $admin[89];
            break;
        }
        $name1 = $futur . '_' . $id;
        echo ' <input type="hidden" style="display:inline-block;" name="'.$name1.'" id="'.$name1.'" value="" />';
        $args = '\'rb'.$id.'o\', \'rb'.$id.'x\', 0, '.count($groupe).', '.$index.', \'inline-block\'';
        $name = 'rb' . $id . 'o' . $index;
        echo '<i id="'.$name.'" class="c-pointer fadm-'.$img1.'" style="display:inline-block;" 
          onclick="ActiveMenu('.$args.'); ToggleValue(\''.$name1.'\');" title="'.$title.'"></i>';
        $args = '\'rb'.$id.'o\', \'rb'.$id.'x\', '.$index.', \'inline-block\'';
        $name = 'rb' . $id . 'x' . $index;
        echo '<i id="'.$name.'" class="c-pointer fadm-'.$img2.'" style="display:none;" 
          onclick="DesactiveItem('.$args.'); ToggleValue(\''.$name1.'\');" title="'.$title.'"></i>';
    }
}
?>