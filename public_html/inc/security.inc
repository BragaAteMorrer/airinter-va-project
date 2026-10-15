<?php
/*******************************************************************************
 *   Security
 *******************************************************************************
 *   GuppY PHP Script - version 5.0
 *   CeCILL Copyright (C) 2004-2013 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = http://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V5 developed by Lud Bienaimé
 *      with the participation of Jean-Michel Misrachi and the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.24 (August 12, 2026) : Delete lines log
 * 
 ******************************************************************************/

define('SECURITY_REP',  CHEMIN.DATAREP.'security/');
define('BAN_REP',       SECURITY_REP.'bannis/');
define('BLA_REP',       SECURITY_REP.'blacklist/');
define('BAN_CFG',       SECURITY_REP.'ban_cfg.ini');
define('BLA_CFG',       SECURITY_REP.'bla_cfg.ini');
define('BAN_DEF',       SECURITY_REP.'ban_def.ini');
define('BLA_DEF',       SECURITY_REP.'bla_def.ini');
define('BAN_REPCOUNTERS', SECURITY_REP.'counters/');

$sec_retention = array(
    0  => (isset($web) ? $web[506] : $admin[1739]),
	1  => '1'.(isset($web) ? $web[699] : $admin[1342]),
	2  => '2'.(isset($web) ? $web[507] : $admin[1157]),
	3  => '3'.(isset($web) ? $web[507] : $admin[1157]),
    4  => '4'.(isset($web) ? $web[507] : $admin[1157]),
    8  => '8'.(isset($web) ? $web[507] : $admin[1157]),
    12 => '12'.(isset($web) ? $web[507] : $admin[1157]),
    24 => '24'.(isset($web) ? $web[507] : $admin[1157]),
    52 => '52'.(isset($web) ? $web[507] : $admin[1157]),
);
$sec_active = array(
    ''   => isset($web) ? $web[508] : $admin[1737],
    'on' => isset($web) ? $web[509] : $admin[1738],
);
$sec_pathlgmessage = array(
	100 => '100'.(isset($web) ? $web[697] : $admin[1050]),
	150 => '150'.(isset($web) ? $web[697] : $admin[1050]),
	200 => '200'.(isset($web) ? $web[697] : $admin[1050]),
	350 => '350'.(isset($web) ? $web[697] : $admin[1050]),
);
$sec_querylgmessage = array(
	100 => '100'.(isset($web) ? $web[697] : $admin[1050]),
	150 => '150'.(isset($web) ? $web[697] : $admin[1050]),
	200 => '200'.(isset($web) ? $web[697] : $admin[1050]),
	350 => '350'.(isset($web) ? $web[697] : $admin[1050]),
	400 => '400'.(isset($web) ? $web[697] : $admin[1050]),
	500 => '500'.(isset($web) ? $web[697] : $admin[1050]),
	600 => '600'.(isset($web) ? $web[697] : $admin[1050]),
	700 => '700'.(isset($web) ? $web[697] : $admin[1050]),
);
$sec_seuilmaxip = array(
    100 => '100',
    150 => '150',
    200 => '200',
    250 => '250',
	300 => '300',
);

function ReadSecurityConfig($file) {
    if (is_file($file)) {
        return parse_ini_file($file);
    } else {
        $cfg = array(
            'nextid' => 1,
			'inforparmail' => '',
            'status' => '',
            'record' => '',
            'retention' => '0',
			'seuilmaxip' => '100'
            );
        WriteSecurityConfig($file, $cfg);
        return $cfg;
    }
}

function WriteSecurityConfig($file, $datas) {
    $out = '';
    foreach ($datas as $key => $value) {
        $out .= $key.'="'.addslashes(trim($value))."\"\n";
    }
    file_put_contents($file, $out);
}

function ReadSecurityDatas($file) {
    if (is_file($file)) {
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');

        return parse_ini_file($file, TRUE);
    } else {
        return array();
    }
}

function WriteSecurityDatas($file, $sections) {
    $out = '';
    foreach ($sections as $section => $datas) {
        $out .= '['.$section.']'."\n";
        foreach ($datas as $key => $value) {
            $out .= $key.'="'.trim($value)."\"\n";
        }
        $out .= "\n";
    }
   file_put_contents($file, $out);
}
function SelectBANReadSecurityDatas($ip, $nomhost) {
	$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');

	$defs = ReadSecurityDatas(BAN_DEF);
	$rechdeftrouve = '';
	foreach ($defs as $id => $def) {
		if (empty($def['status'])) continue;

		if ($def['ip'] != '' && $def['ip'] == $ip)
			$rechdeftrouve = $def['ip'];
		else
		{
			if ($def['host'] != '' && $def['host'] == $nomhost)
			$rechdeftrouve = $def['host'];
		}
		
	}
	return $rechdeftrouve;
}
function Select5BANReadSecurityDatas($nomhost) { // rech Host

	$defs = ReadSecurityDatas(BAN_DEF);
	$rechdeftrouve = '';
	$rejet1 = '';
	
/*	if (!filter_var($nomhost, FILTER_VALIDATE_IP)) // IP????
		$nomhostlimit   = substr($nomhost, 0, 9);
*/
	foreach ($defs as $id => $def) {
		if (empty($def['status'])) continue;
		if (!$rechdeftrouve)
		{				
			if ($def['host'] != '')
			{
				$nomhosts = explode(',', $def['host']);
				$nbrehosts = count($nomhosts);		
				for ($ijnbrehost = 0; $ijnbrehost < $nbrehosts; $ijnbrehost++)
				{
					if ($rechdeftrouve == '')
					{
						$rejet1 = substr_count($nomhost, $nomhosts[$ijnbrehost]);
						if ($rejet1 != 0) 
							{
								$rechdeftrouve = $nomhosts[$ijnbrehost];
							}
					}		
				}
			}
		}
		
	}
	return $rechdeftrouve;
}
function Select6BANReadSecurityDatas($path) {  //rech path
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');

	$defs = ReadSecurityDatas(BAN_DEF);
	$rechdeftrouve = '';
	$rejet = false;

	foreach ($defs as $id => $def) {
		if (empty($def['status'])) continue;
		if ($rechdeftrouve == '')
		{				
			if ($def['path'] != '')
			{
				
				$def['path'] = stripslashes($def['path']);
				$paths = explode(',', $def['path']);
				
				$nbrepaths = count($paths);		
				for ($ijnbrepath = 0; $ijnbrepath < $nbrepaths; $ijnbrepath++)
				{
					if ($rechdeftrouve == '')
					{	
						$rejet1 = substr_count($path, $paths[$ijnbrepath]);

						if ($rejet1 != 0) 
							{
								$rechdeftrouve = $paths[$ijnbrepath];

							}
					}		
				}
			}
		}
		
	}
	return $rechdeftrouve;
}
function Select7BANReadSecurityDatas($mot) {    // rech Mots
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');

	$defs = ReadSecurityDatas(BAN_DEF);
	$rechdeftrouve = '';

	foreach ($defs as $id => $def) {
		if (empty($def['status'])) continue;
		if ($rechdeftrouve == '')
		{				
			if ($def['mots'] != '')
			{
				$def['mots'] = stripslashes($def['mots']);
				$defmots = $def['mots'];			
				$mots = explode(',', $def['mots']);
				$nbremots = count($mots);		
				for ($ijnbremots = 0; $ijnbremots < $nbremots; $ijnbremots++)
				{
					if ($rechdeftrouve == '')
					{
						$rejet1 = substr_count($mot, $mots[$ijnbremots]);

						if ($rejet1 != 0) 
							{
								$rechdeftrouve = $mots[$ijnbremots];
								
							}
					}		
				}
			}
		}
		
	}
	return $rechdeftrouve;
}
function Select8BANReadSecurityDatas($rechagent) {   // rech Agent
	$defs = ReadSecurityDatas(BAN_DEF);
	$rechdeftrouve = '';

	foreach ($defs as $id => $def) {
		if (empty($def['status'])) continue;
		if (!$rechdeftrouve)
		{				
			if ($def['agent'] != '')
			{
				$defagent = $def['agent'];				
				$agent = explode(',', $def['agent']);
				$nbreagent = count($agent);		
				for ($ijnbreagent = 0; $ijnbreagent < $nbreagent; $ijnbreagent++)
				{
					if ($rechdeftrouve == '')
					{	
						$rejet1 = substr_count($rechagent, $agent[$ijnbreagent]);	
						if ($rejet1 != 0) 
							{
								$rechdeftrouve = $agent[$ijnbreagent];
							}
					}		
				}
			}
		}
		
	}
	return $rechdeftrouve;
}

function CutField($field, $max = 15, $more = '...') {
	global $charset;
    $len = strlen($field);
    if ($len > $max) {
        return '<acronym title="'.htmlentities($field, ENT_COMPAT, $charset).'" class="cursorHelp">'.substr($field, 0, $max).$more.'</acronym>';
    } else {
        return $field;
    }
}

function SelectSecurity($name, $value, $options) {
    $out = "\n".'<select name="'.$name.'">'."\n";
    foreach ($options as $key=>$option) {
        $out .= '<option value="'.$key.'"'.Selected($key == $value).'>'.$option.'</option>'."\n";
    }
    $out .= '</select>'."\n";
    return $out;
}
// Journal Bannissement 
function SelectBANReadJournalBannis($file, $ip) { 
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');

	$journalbannis = ReadDBFields($file);
	$rechiptrouve = '';
	if (is_array($journalbannis))
	{
		foreach ($journalbannis as $journalban) {
			if ($journalban[2] == $ip)
				$rechiptrouve = $journalban[2];		
		}

	}
		return $rechiptrouve;  
}
function ExecuteBannishment() {
    global $userprefs, $pseudo, $pemail, $purl, $log1, $site, $charset, $lang, $web, $lng, $actionwaf;
	$datjourl = new DateTime('now');
	$dateheureminiutesecl = $datjourl->format('Y_m_d');
	$nomficlog = CHEMIN.'data/log/security'.'_'.$dateheureminiutesecl.'.log';

	if (!isset($actionwaf)) $actionwaf = '1';
	$this_url = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'?'.$_SERVER["QUERY_STRING"].';';
	$ptit = $_SERVER["QUERY_STRING"];
	$cfg = ReadSecurityConfig(BAN_CFG);
	date_default_timezone_set('UTC');
	$typ = isset($_REQUEST['typ']) ? $_REQUEST['typ'] : '';
	$add = isset($_REQUEST['add']) ? $_REQUEST['add'] : '';
	if (empty($log1) || $log1 == null) $log1 = '';
	$datas = array(
		date('Ymd His'),
		'-',
		getIPadress($_SERVER[$site[6] == 'on' ? 'HTTP_X_FORWARDED_FOR' : 'REMOTE_ADDR']),
		sprintf('typ=%s add=%s', $typ, $add),
		$pseudo,
		$pemail,
		$purl,
		@gethostbyaddr(getIPadress($_SERVER[$site[6] == 'on' ? 'HTTP_X_FORWARDED_FOR' : 'REMOTE_ADDR'])),
		isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
		isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
		rawurldecode(htmlspecialchars($log1))
	);
	// Vérification pour atteinte du seuil toléré en cas de Spam, Vérifier le compteur de l'adresse IP , et bannissement dur
	if (!isset($cfg['seuilmaxip'])) $cfg['seuilmaxip'] = 100;
	$ipvisiteur = getIPadress($_SERVER[$site[6] == 'on' ? 'HTTP_X_FORWARDED_FOR' : 'REMOTE_ADDR']);
	$filecompteursip = BAN_REPCOUNTERS.$ipvisiteur.'_'.date('Y-W').DBEXT;
	$ipcompteur = ReadCounter($filecompteursip);
	if (file_exists($filecompteursip))
	{
		if (trim($ipcompteur+1) >= trim($cfg['seuilmaxip']))
		{
			header('location:'.'https://www.interpol.int/');
			exit();
		}
	}
	date_default_timezone_set($site['TZ']);
	$rejet = FALSE;
	if ('on' == $cfg['status']) {
		$defs = ReadSecurityDatas(BAN_DEF);
		foreach ($defs as $id => $def) {
			if (empty($def['status'])) continue;
			$url = $def['url'];
			$msg = $def['msg'];
			$motif = 'BAN('.$id;
			if (!empty($def['ip'])) {
				$rejet = $rejet || (getIPadress($_SERVER[$site[6] == 'on' ? 'HTTP_X_FORWARDED_FOR' : 'REMOTE_ADDR']) == $def['ip']);
				if ($rejet) {
					$motif .= ',ip)';
						break;
				}
			}
			if (!$rejet && !empty($def['pseudo'])) {
				if (!isset($pseudo)) $pseudo = '';
				$rejet = substr_count(mb_strtolower($userprefs[1], $charset), mb_strtolower($def['pseudo'], $charset));

				if ($rejet!= 0) {
					$motif .= ',pseudo)';
					$rejet = true;
					break;
				}
			}
			if (!$rejet && !empty($def['email'])) {
				if (!isset($email)) $email = '';
				$rejet = substr_count(mb_strtolower($userprefs[2], $charset), mb_strtolower($def['email'], $charset));
				 if ($rejet!= 0) {
					$motif .= ',email)';
					$rejet = true;
						break;
				}
			}
			if (!$rejet && !empty($def['agent'])) {
				$rejet = $rejet || (mb_substr_count(mb_strtolower($_SERVER['HTTP_USER_AGENT'], $charset), mb_strtolower($def['agent'], $charset), $charset) != 0);
				if ($rejet) {
					$motif .= ',agent)';
						break;
				}
			}
			if (!$rejet && !empty($def['host'])) {
				$host = mb_strtolower(@gethostbyaddr(getIPadress($_SERVER[$site[6] == 'on' ? 'HTTP_X_FORWARDED_FOR' : 'REMOTE_ADDR'])), $charset);
				$values = explode(",", $def['host']);
				$rejet = false;
			
				for ($ij = 0; $ij < count($values); $ij++)
				{
					if (!$rejet)
					{
						$rejet1 = substr_count($host, $values[$ij]);	
						if ($rejet1 != 0) 
						{
							$rejet = true;
						}
					}
					
				}
				if ($rejet) {
					$motif .= ',host)';
						break;
				}
			}
			if (!isset($def['path'])) $def['path'] = '';
			if (!$rejet && !empty($def['path'])) {
				$def['path'] = stripslashes($def['path']);							  
				$partieurl = $_SERVER['PHP_SELF'];
				$partieurl = rawurldecode($partieurl);
				$values = explode(",", $def['path']);
				$rejet = false;			
				for ($ij = 0; $ij < count($values); $ij++)
				{
					if (!$rejet)
					{	
						$rejet1 = substr_count($partieurl, $values[$ij]);	
						if ($rejet1 != 0) 
						{
							$rejet = true;
						}
					}
				}
				if ($rejet) {
					$motif .= ',path)';
					break;
				}
// test longueur de SCRIPT Name				}
				if (!$rejet && strlen($_SERVER['SCRIPT_NAME']) > $cfg['pathlgmessage'])
				{
						$rejet = true;
						$motif .=',Path lg= '.strlen($_SERVER['SCRIPT_NAME']).' : '.$web[698].')';
						break;
				}
// fin ajout
			}
// ajout Linuxmr
			if (!$rejet && !empty($def['mots'])) {
				$def['mots'] = stripslashes($def['mots']);							  
				$ptext = $ptit;
				$values = explode(",", $def['mots']);
				$rejet = false;
				$mottrouve = '';
				$ptext = rawurldecode($ptext);
				for ($ij = 0; $ij < count($values); $ij++)
				{
					if (!$rejet)
					{
						$rejet1 = substr_count($ptext, $values[$ij]);	
						if ($rejet1 != 0) 
						{
							$rejet = true;
							$mottrouve =  $values[$ij];
							break;
						}
					}
					
				}
				if ($rejet) {
					$motif .= ',mot='.$mottrouve.')';
					break;
				}	
			}
// test longueur de SCRIPT
			if (!$rejet && strlen($_SERVER["QUERY_STRING"]) > $cfg['querylgmessage'])
			{
					$rejet = true;
					$motif .= ',Query_string lg= '.strlen($_SERVER["QUERY_STRING"]).' : '.$web[698].')';
					break;
			}
// fin ajout
		}
	}
	
	$nomficlog = CHEMIN.'data/log/security'.'_'.$dateheureminiutesecl.'.log';
	$datas[1] = $rejet ? $motif : '';
	$ipvisiteur = getIPadress($_SERVER[$site[6] == 'on' ? 'HTTP_X_FORWARDED_FOR' : 'REMOTE_ADDR']);
	if ('on' == $cfg['record'] && $datas[1] != '' && $rejet) 
	{
//		file_put_contents($nomficlog, date('Y-m-d H:i:s').'Bannissement ipvisiteur: '.$ipvisiteur."\n", FILE_APPEND | LOCK_EX);
		$rechecheip = '';
		$ipcompteur=1;
		$filecompteursip = BAN_REPCOUNTERS.$ipvisiteur.'_'.date('Y-W').DBEXT;
		$file = BAN_REP.date('Y-W').DBEXT;
//		 file_put_contents($nomficlog, date('Y-m-d H:i:s').'Test file: '.$file.' ipvisiteur: '.$ipvisiteur."\n", FILE_APPEND | LOCK_EX);
		if (FileDBExist($file)) 
		{ // test ficihier bannis exist
//			file_put_contents($nomficlog, date('Y-m-d H:i:s').'File exists: '.$file.' ipvisiteur: '.$ipvisiteur."\n", FILE_APPEND | LOCK_EX);
			$rechecheip = SelectBANReadJournalBannis($file, $ipvisiteur);
			if (file_exists($filecompteursip)) //si fichier Compteur par IP exists
			{
//				file_put_contents($nomficlog, date('Y-m-d H:i:s').'Exists Increment readcounter filecompteursip: '.$filecompteursip.' ipvisiteur: '.$ipvisiteur."\n", FILE_APPEND | LOCK_EX);
				$ipcompteur = ReadCounter($filecompteursip);
				$ipcompteur++;
			}
			if ($rechecheip != '' && trim($ipcompteur) >= trim($cfg['seuilmaxip']))
			{
//				file_put_contents($nomficlog, date('Y-m-d H:i:s').'Test pour ipvisiteur: '.$ipvisiteur.' ipcompteur: '.$ipcompteur.' cfg seuilmaxip'.$cfg['seuilmaxip']."\n", FILE_APPEND | LOCK_EX);
//				file_put_contents($nomficlog, date('Y-m-d H:i:s').'Renvoi vers https://www.interpol.int/ pour ipvisiteur: '.$ipvisiteur.' ipcompteur: '.$ipcompteur.' cfg seuilmaxip'.$cfg['seuilmaxip']."\n", FILE_APPEND | LOCK_EX);
				header('location:'.'https://www.interpol.int/');
				exit();
			}
			else
			{
//				file_put_contents($nomficlog, date('Y-m-d H:i:s').' readcounter filecompteursip: '.$filecompteursip.' ipvisiteur: '.$ipvisiteur.' ipcompteur: '.$ipcompteur."\n", FILE_APPEND | LOCK_EX);
				WriteCounter($filecompteursip,$ipcompteur);	
//				file_put_contents($nomficlog, date('Y-m-d H:i:s').'Ecr  Compteuripvisiteur: '.$ipvisiteur.' rechecheip: '.$rechecheip.' ipcompteur: '.$ipcompteur."\n", FILE_APPEND | LOCK_EX);
				AppendDbFields($file, $datas);
//				file_put_contents($nomficlog, date('Y-m-d H:i:s').'Bannis ajout Apres ecr ipvisiteur: '.$ipvisiteur.' rechecheip: '.$rechecheip.' ipcompteur: '.$ipcompteur."\n", FILE_APPEND | LOCK_EX);
			}
		}
		else
		{
//			file_put_contents($nomficlog, date('Y-m-d H:i:s').'File absent: '.$file.' ipvisiteur: '.$ipvisiteur."\n", FILE_APPEND | LOCK_EX);
			WriteDbFields($file, array($datas));
//			file_put_contents($nomficlog, date('Y-m-d H:i:s').'Bannis créé Apres ecr ipvisiteur: '.$ipvisiteur.' rechecheip: '.$rechecheip.' ipcompteur: '.$ipcompteur."\n", FILE_APPEND | LOCK_EX);
			WriteCounter($filecompteursip,$ipcompteur);
//			file_put_contents($nomficlog, date('Y-m-d H:i:s').'Compteur créé apres bannis ecr ipvisiteur: '.$ipvisiteur.' rechecheip: '.$rechecheip.' ipcompteur: '.$ipcompteur."\n", FILE_APPEND | LOCK_EX);
			
		}	
		PurgeSecurityLog(BAN_REP, $cfg['retention']);
		PurgeSecurityCounters(BAN_REPCOUNTERS, $cfg['retention'], $filecompteursip);		
	}
	if ($rejet) {
		if (!empty($msg) && ('on' == $cfg['inforparmail'])) {
			$eSub = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' '.$web[451].$msg;
			$eMsg = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'<hr /><strong>'.$msg.'</strong><hr />'.$web[452].$web[478].'<br />';
			$eMsg .= $web[453].$datas[0].'.<br />';
			$eMsg .= $web[454].$datas[1].'.<br />';
			$eMsg .= $web[455].$datas[2].'.<br />';
			$eMsg .= $web[456].$datas[3].'.<br />';
			$eMsg .= $web[457].$datas[4].'.<br />';
			$eMsg .= $web[458].$datas[5].'.<br />';
			$eMsg .= $web[459].$datas[6].'.<br />';
			$eMsg .= $web[460].$datas[7].'.<br />';
			$eMsg .= $web[461].$datas[8].'.<br />';
			$eMsg .= $web[462].$datas[9].'.<br />';
			$eMsg .= $web[463].$datas[10].'.<br />';
			$eMsg .= '<hr />';
			eMailHtmlTo($eSub, $eMsg, '');
		}
		switch($url) 
		{
			case 'error403.php' :
				switch($msg)
				{
					case '401' :
							//HTTP/1.0 401 Unauthorized";
					case '403' :
						//HTTP/1.0 403 Forbidden";
					case '404' :
					case '406' :
						// HTTP/1.0 404 Not Found";
					case '410' :
						//HTTP/1.0 410 Gone";	

					$url .='?err='.$msg;
					header('location:'.$site[3].$url);
					exit();
				}       
			 default :
				header('location:'.$url);
				exit();
		}
	}
}
function ExploreFileSemaine($dir) {
    $dir = AddCHEMIN($dir);
    $array = array();
    $dossier = opendir($dir);
    while ($fichier = readdir($dossier)) {
        if (is_file($dir.$fichier) && $fichier != 'index.php') {
		    $path_parts = pathinfo($fichier);
			$fichier = $path_parts['filename'];
/*
			Enlever toute la partie et conserver annee-semaine
*/
			$anneesemaine = substr($fichier, -7);
			$fichiersepasemaine = explode('-', $anneesemaine);
			if (isset($fichiersepasemaine[1]))
				$array[] = $anneesemaine;
        }
    }
    closedir($dossier);
    sort($array);
    return $array;
}
function ExploreFileSemainechoisi($dir, $semainechoisi) {
    $dir = AddCHEMIN($dir);
    $array = array();
    $dossier = opendir($dir);
	$nombre = 0;
    while ($fichier = readdir($dossier)) {
        if (is_file($dir.$fichier) && $fichier != 'index.php') {
		    $path_parts = pathinfo($fichier);
			$fichiernom = $path_parts['filename'];
/*
			Enlever recherche de - pour avoir la position
			enlever la partie dtb  pour l'année et la semaine
			enlever 2 caractère pour avoir la semaine
*/
			$posrecherche = strpos($fichiernom, '-');
			if ($posrecherche != '')
			{
				$posrecherche = $posrecherche - 5;
				$nomfic= substr($fichiernom, 0,$posrecherche);
//				echo 'nomfic: '.$nomfic.'<br />';
				$posrechercheautre = strpos($fichier, '.dtb');
				if ($posrechercheautre != '')
				{				
					$semaine= substr($fichier, 0,$posrechercheautre);
					$semaine = substr($semaine, -2);
//					echo 'semaine: '.$semaine.'<br />';
					if ($semaine == $semainechoisi)
					{
						$array[$nombre][0] = date ("d/m/Y H:i:s", filemtime(BAN_REPCOUNTERS.$fichier));
						$array[$nombre][1] = $nomfic;
						$array[$nombre][2] = ReadCounter(BAN_REPCOUNTERS.$fichier);
						$nombre++;
						
					}
				}
			}
        }
    }
    closedir($dossier);
	$datas = array_reverse($array);
    return $datas;
}
// Destruction des anciens logs 
function PurgeSecurityLog($dir, $retention) {
    if (0 == $retention) return;
    $lastyear = date('Y');
    $lastweek = date('W') - $retention;
    if ($lastweek < 1) {
        $lastyear--;
        $lastweek += 52;
    }
    $lastfile = sprintf('%4d-%02d.dtb', $lastyear, $lastweek);
    $files = ExploreFile($dir);
    foreach ($files as $file) {
        if ($file < $lastfile) {
            DestroyDBFile($dir.$file);
        }
    }
}
// Suppression des anciens compteurs
function PurgeSecurityCounters($dir, $retention) {
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');

    if (0 == $retention) return;
    $lastyear = date('Y');
    $lastweek = date('W') - $retention;
    if ($lastweek < 1) {
        $lastyear--;
        $lastweek += 52;
    }
    $lastfile = sprintf('%4d-%02d.dtb', $lastyear, $lastweek);
    $files = ExploreFile($dir);
    foreach ($files as $filew) {

		$possuite = strpos($filew, '.dtb');
		$annesemainrech= substr($filew, 0,$possuite);

		$file = substr($annesemainrech, -7).'.dtb';

        if ($file < $lastfile) {

            DestroyDBFile($dir.$filew);
        }
    }
}
?>
