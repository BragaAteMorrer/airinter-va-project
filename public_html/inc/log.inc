<?php
/*******************************************************************************
 *   Activity Log
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
  * v6.00.21 (December 17, 2024) : corrections for log smtp
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'log.inc')) {
    header('location:../index.php');
    die();
}

function BotDetected() {
    if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider|qwantify/i', $_SERVER['HTTP_USER_AGENT'])) 
        return TRUE;
    else 
        return FALSE;
}
$isBotDetected = BotDetected();
date_default_timezone_set('UTC'); // Les journaux sont basés sur le temps universel.

if (!isset($_SERVER['REQUEST_URI'])) {
    $REQUEST_URI = $_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'];
    $log1 = $_SERVER['SCRIPT_NAME'];
} else {
    $log1 = strip_tags($_SERVER['REQUEST_URI']);    
}
$log2  = $_SERVER["HTTP_USER_AGENT"];
$ulog2 = mb_strtoupper($log2, $charset);//strtoupper($log2);//
if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $log3 = $_SERVER['HTTP_X_FORWARDED_FOR'];
} elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
    $log3 = $_SERVER['HTTP_CLIENT_IP'];
} else {
    $log3 = $_SERVER['REMOTE_ADDR'];
}
$log3 = getIPadress($log3);
$yr   = (integer) date('Y');
$mh   = (integer) date('m');
$dy   = (integer) date('d');
$hr   = (integer) date('H');

if (
    (strstr($log2, 'Nav')) || (strstr($log2, 'Gold')) || (strstr($log2, 'X11')) ||
    (strstr($log2, 'Netscape')) AND (!strstr($log2, 'MSIE'))
   ) $browser = 0; // Netscape
elseif (strstr($log2,  'Avant Browser')) $browser = 10;
elseif (strstr($log2,  'Crazy Browser')) $browser = 11;
elseif (strstr($ulog2, 'MAXTHON')) $browser = 12;
elseif (strstr($log2,  'MSIE') || (strstr($log2,  'Edg'))) $browser = 1; // MSIE Edge
elseif (strstr($log2,  'Flock')) $browser = 20;
elseif (strstr($log2,  'Iceweasel')) $browser = 21;
elseif (strstr($log2,  'Firefox')) $browser = 19;
elseif (strstr($log2,  'Chrome')) $browser = 22;
elseif (strstr($log2,  'Nintendo wii')) $browser = 18;
elseif (strstr($log2,  'Opera')) $browser = 4;
elseif (strstr($log2,  'Lynx')) $browser = 13;
elseif (strstr($log2,  'Safari')) $browser = 14;
elseif (strstr($log2,  'Camino')) $browser = 23;
elseif (strstr($log2,  'Konqueror')) $browser = 6;
elseif (strstr($log2,  'Dillo')) $browser = 24;
elseif (strstr($log2,  'Element Browser')) $browser = 25;
elseif (strstr($log2,  'Epiphany')) $browser = 26;
elseif (strstr($log2,  'Galeon')) $browser = 28;
elseif (strstr($log2,  'K-Meleon')) $browser = 29;
elseif (strstr($log2,  'Kazehakase')) $browser = 30;
elseif (strstr($log2,  'Minefield')) $browser = 31;
elseif (strstr($ulog2, 'SEAMONKEY')) $browser = 15;
elseif (strstr($log2,  'Playstation portable')) $browser = 16;
elseif (strstr($log2,  'PLAYSTATION 3')) $browser = 17;
elseif (strstr($log2,  'Wget')) $browser = 9;
elseif (strstr($log2,  'DA')) $browser = 3;
elseif (strstr($log2,  'iCab')) $browser = 5;
elseif (
    (strstr($log2, 'bot')) || (strstr($log2, 'Google')) || (strstr($log2, 'Slurp')) ||
    (strstr($log2, 'Seeker')) || (strstr($log2, 'Scooter')) ||
    (strstr($log2, 'Spider')) || (strstr($log2, 'Infoseek'))
       ) $browser = 7;
elseif (strstr($log2,  'Mozilla')) $browser = 2;
else $browser = 8;

if (FileDBExist(DBLOGH)) {
	$dblog = ReadDBFields(DBLOGH);
}
else {
	$dblog = InitDBlog(1);
	$dblog[0][0] = $hr;
}

if ($hr != $dblog[0][0] && !$isBotDetected) {
    $dblog[7][0] = 0;
    WriteDBFields(DBLOGH,$dblog);
    if (FileDBExist(DBLOGD)) {
        $dblod = ReadDBFields(DBLOGD);
    }
    else {
        $dblod = InitDBlog(STATS_NBHEURE);
        $dblod[0][0] = $dy;
    }
	for ($i = 0; $i < STATS_NBPAGE; $i++) {
        $dblod[1][$i] = (isset($dblod[1][$i]) ? (is_numeric($dblod[1][$i]) ? $dblod[1][$i] : 0) : 0) + (isset($dblog[1][$i]) ? (is_numeric($dblog[1][$i]) ? $dblog[1][$i] : 0) : 0);
    }
    for ($i = 0; $i < STATS_NBLANGUE; $i++) {
        $dblod[2][$i] = (isset($dblod[2][$i]) ? (is_numeric($dblod[2][$i]) ? $dblod[2][$i] : 0) : 0) + (isset($dblog[2][$i]) ? (is_numeric($dblog[2][$i]) ? $dblog[2][$i] : 0) : 0);
    }
    for ($i = 0; $i < STATS_NBBROWSER; $i++) {
        $dblod[3][$i] = (isset($dblod[3][$i]) ? (is_numeric($dblod[3][$i]) ? $dblod[3][$i] : 0) : 0) + (isset($dblog[3][$i]) ? (is_numeric($dblog[3][$i]) ? $dblog[3][$i] : 0) : 0);
    }
    for ($i = 0; $i < STATS_NBOS; $i++) {
        $dblod[4][$i] = (isset($dblod[4][$i]) ? (is_numeric($dblod[4][$i]) ? $dblod[4][$i] : 0) : 0) + (isset($dblog[4][$i]) ? (is_numeric($dblog[4][$i]) ? $dblog[4][$i] : 0) : 0);
    }
    $dblod[5][$dblog[0][0]] = $dblod[5][$dblog[0][0]]+$dblog[5][0];
    $dblod[6][$dblog[0][0]] = $dblod[6][$dblog[0][0]]+$dblog[6][0];
    WriteDBFields(DBLOGD,$dblod);
    $dblog = InitDBlog(1);
    $dblog[0][0] = $hr;
    WriteDBFields(DBLOGH,$dblog);
    if ($dy != $dblod[0][0]) {
        SaveOldDbLog($dblod);
        if (FileDBExist(DBLOGM)) {
            $dblom = ReadDBFields(DBLOGM);
        } else {
            $dblom = InitDBlog(STATS_NBJOUR);
            $dblom[0][0] = $mh;
        }
        for ($i = 0; $i < STATS_NBPAGE; $i++) {
			$dblom[1][$i] = empty($dblom[1][$i]) ? 0 : $dblom[1][$i];
			$dblod[1][$i] = empty($dblod[1][$i]) ? 0 : $dblod[1][$i];
            $dblom[1][$i] = $dblom[1][$i] + $dblod[1][$i];
        }
        for ($i = 0; $i < STATS_NBLANGUE; $i++) {
			$dblom[2][$i] = empty($dblom[2][$i]) ? 0 : $dblom[2][$i];
			$dblod[2][$i] = empty($dblod[2][$i]) ? 0 : $dblod[2][$i];
            $dblom[2][$i] = $dblom[2][$i] + $dblod[2][$i];
        }
        for ($i = 0; $i < STATS_NBBROWSER; $i++) {
			$dblom[3][$i] = empty($dblom[3][$i]) ? 0 : $dblom[3][$i];
			$dblod[3][$i] = empty($dblod[3][$i]) ? 0 : $dblod[3][$i];
            $dblom[3][$i] = $dblom[3][$i] + $dblod[3][$i];
        }
        for ($i = 0; $i < STATS_NBOS; $i++) {
			$dblom[4][$i] = empty($dblom[4][$i]) ? 0 : $dblom[4][$i];
			$dblod[4][$i] = empty($dblod[4][$i]) ? 0 : $dblod[4][$i];
            $dblom[4][$i] = $dblom[4][$i] + $dblod[4][$i];
        }
        for ($i = 0; $i < STATS_NBHEURE; $i++) {
            $dblom[5][$dblod[0][0]-1] = (int)$dblom[5][$dblod[0][0]-1] + (int)$dblod[5][$i];
        }
        for ($i = 0; $i < STATS_NBHEURE; $i++) {
            $dblom[6][$dblod[0][0]-1] = (int)$dblom[6][$dblod[0][0]-1] + (int)$dblod[6][$i];
        }
        WriteDBFields(DBLOGM,$dblom);
        $dblod = InitDBlog(STATS_NBHEURE);
        $dblod[0][0] = $dy;
        WriteDBFields(DBLOGD,$dblod);
        if ($mh != $dblom[0][0]) {
            SaveOldDbLog($dblom);
            if (FileDBExist(DBLOGY)) {
                $dbloy = ReadDBFields(DBLOGY);
            } else {
                $dbloy = InitDBlog(STATS_NBMOIS);
                $dbloy[0][0] = $yr;
            }
            for ($i = 0; $i < STATS_NBPAGE; $i++) {
				$dbloy[1][$i] = empty($dbloy[1][$i]) ? 0 : $dbloy[1][$i];
				$dblom[1][$i] = empty($dblom[1][$i]) ? 0 : $dblom[1][$i];
                $dbloy[1][$i] = $dbloy[1][$i] + $dblom[1][$i];
            }
            for ($i = 0; $i < STATS_NBLANGUE; $i++) {
				$dbloy[2][$i] = empty($dbloy[2][$i]) ? 0 : $dbloy[2][$i];
				$dblom[2][$i] = empty($dblom[2][$i]) ? 0 : $dblom[2][$i];
                $dbloy[2][$i] = $dbloy[2][$i] + $dblom[2][$i];
            }
            for ($i = 0; $i < STATS_NBBROWSER; $i++) {
				$dbloy[3][$i] = empty($dbloy[3][$i]) ? 0 : $dbloy[3][$i];
				$dblom[3][$i] = empty($dblom[3][$i]) ? 0 : $dblom[3][$i];
                $dbloy[3][$i] = $dbloy[3][$i] + $dblom[3][$i];
            }
            for ($i = 0; $i < STATS_NBOS; $i++) {
				$dbloy[4][$i] = empty($dbloy[4][$i]) ? 0 : $dbloy[4][$i];
				$dblom[4][$i] = empty($dblom[4][$i]) ? 0 : $dblom[4][$i];
                $dbloy[4][$i] = $dbloy[4][$i] + $dblom[4][$i];
            }
            for ($i = 0; $i < STATS_NBJOUR; $i++) {
                $dbloy[5][$dblom[0][0]-1] = (int)$dbloy[5][$dblom[0][0]-1] + (int)$dblom[5][$i];
            }
            for ($i = 0; $i < STATS_NBJOUR; $i++) {
                $dbloy[6][$dblom[0][0]-1] = (int)$dbloy[6][$dblom[0][0]-1] + (int)$dblom[6][$i];
            }
            WriteDBFields(DBLOGY,$dbloy);
            $dblom = InitDBlog(STATS_NBJOUR);
            $dblom[0][0] = $mh;
            WriteDBFields(DBLOGM,$dblom);
            if ($yr != $dbloy[0][0]) {
                SaveOldDbLog($dbloy);
                $dbloy = InitDBlog(STATS_NBMOIS);
                $dbloy[0][0] = $yr;
                WriteDBFields(DBLOGY,$dbloy);
            }
        }
    }
}

$page2type = array(
    'articles.php'      => 0, $urlrw[20] => 0,
    $site['DN'].'.php'  => 1, $urlrw[11] => 1,
    'faq.php'           => 2, $urlrw[4]  => 2,
    $site['FR'].'.php'  => 3, $site['TH'].'.php' => 3, $site['FRT'].'.php' => 3, $urlrw[5] => 3,
    $site['GB'].'.php'  => 4, $urlrw[7] => 4,
    'index.php'         => 5, $urlrw[0].'1' => 5,
    'links.php'         => 6, $urlrw[6] => 6,
    $site['NE'].'.php'  => 7, $urlrw[8] => 7,
    'photos.php'        => 8,
    'photorama.php'     => 8,
    'photorama2.php'    => 8,
    $urlrw[9]           => 8,
    'search.php'        => 10,
    $urlrw[12]          => 10,
    'admin.php'         => 12, $urlrw[0].'-19'     => 12,
    $site['BL'].'.php'  => 13, $site['BLS'].'.php' => 13, $urlrw[1] => 13,
    $site['NL'].'.php'  => 14, $urlrw[18] => 14,
    'newsrss.php'       => 15,
    'blogrss.php'       => 16,
    'agenda.php'        => 17, $site['CT'].'.php' => 17, $urlrw[17] => 17, $urlrw[2] => 17,
    'plugins/'          => 18,
    'pages/'            => 19
);
$pgtype = FALSE;
foreach($page2type as $key => $val) {
    if (1 == preg_match('!'.$key.'!', $log1)) {
        $pgtype = $val;
        break;
    }
}
unset($page2type);
if (FALSE === $pgtype && $log1[strlen($log1)-1] == "/") $pgtype = 5;

if (FALSE !== $pgtype && !$isBotDetected) {
    if ('on' == $configlog[$pgtype]) {
        $loguser = array();
		$compteur++;
		WriteCounter(DBCOUNTERLOGBK,$compteur);
        if (FileDBExist(DBLOGBOOK)) {
            $loguser[0] = GetCurrentDateTimeSec();
            $loguser[1] = $log3;
            $loguser[2] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $loguser[3] = @gethostbyaddr($log3);
            $loguser[4] = $browser;
            $loguser[5] = rawurldecode(htmlspecialchars(substr($log1, 1)));
			$loguser[6] = $log2;
			$loguser[7] = $_SERVER['SCRIPT_NAME'];
			$loguser[8] = $_SERVER['QUERY_STRING'];
            AppendDBFields(DBLOGBOOK, $loguser);
        }
        else {
            $loguser[0][0] = GetCurrentDateTimeSec();
            $loguser[0][1] = $log3;
            $loguser[0][2] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $loguser[0][3] = @gethostbyaddr($log3);
            $loguser[0][4] = $browser;
            $loguser[0][5] = rawurldecode(htmlspecialchars(substr($log1, 1)));
			$loguser[0][6] = $log2;
			$loguser[0][7] = $_SERVER['SCRIPT_NAME'];
			$loguser[0][8] = $_SERVER['QUERY_STRING'];
            WriteDBFields(DBLOGBOOK, $loguser);
        }
        $amsglog = CountDBFields(DBLOGBOOK);
        while ($amsglog > $configlogNB) {
            DeleteDBFieldById(DBLOGBOOK);
            $amsglog = CountDBFields(DBLOGBOOK);
        }
    }

    $dblog[1][$pgtype]++;
    $dblog[5][0]++;

    if(empty($lang[1])) {
        $dblog[2][0]++;
    } elseif(strstr($log1, 'lng='.$lang[1])) {
        $dblog[2][1]++;
    } else {
        $dblog[2][0]++;
    }
    
    $var_bool = false;
    $subdata = array();
    $subdata = ReadDBFields(DBIPSTATS);
    for ($i = 0; $i < count($subdata); $i++) {
        if ($log3 == $subdata[$i][0]) {
            $now_time = time();
            list($zjour, $zmois, $zannee) = explode('/', substr($subdata[$i][1], 0, 10));
            list($zheure, $zminute, $zseconde) = explode(':', substr($subdata[$i][1], 10, 18));
            if ($now_time < mktime((int)$zheure, (int)$zminute, (int)$zseconde, (int)$zmois, (int)$zjour, (int)$zannee) + HIT_TIME) {
                $var_bool = true;
            }
        }
    }
    if ($var_bool == false) {
        $dblog[6][0]++;
        $dblog[3][$browser]++;

        if (strstr($log2, 'Win')) $dblog[4][0]++;
        elseif ((strstr($log2, 'Mac')) || (strstr($log2, 'PPC'))) $dblog[4][1]++;
        elseif (strstr($log2, 'Linux')) $dblog[4][2]++;
        elseif (strstr($log2, 'FreeBSD')) $dblog[4][3]++;
        elseif (strstr($log2, 'SunOS')) $dblog[4][4]++;
        elseif (strstr($log2, 'IRIX')) $dblog[4][5]++;
        elseif (strstr($log2, 'BeOS')) $dblog[4][6]++;
        elseif (strstr($log2, 'OS/2')) $dblog[4][7]++;
        elseif (strstr($log2, 'AIX')) $dblog[4][8]++;
        else $dblog[4][9]++;
    }
    WriteDBFields(DBLOGH,$dblog);
}
$tabcounter = CompteVisites(DBIPSTATS, DBSTATS);
if ($tabcounter[0] > 0 && ($tabcounter[0]/10) == intval($tabcounter[0]/10)) {
    WriteCounter(DBSTATSBK, $tabcounter[0]);
}
if ($tabcounter[0] < 10) {
    $oldcount = ReadCounter(DBSTATSBK);
    if ($oldcount > 10) {
        WriteCounter(DBSTATS,$oldcount);
        $tabcounter[0] = $oldcount;
    }
}
if ($supervision[0] == 'on' && $supervision[1] > 0) {
    if (($tabcounter[0]/$supervision[1]) == intval($tabcounter[0]/$supervision[1])) {
        $old_count = ReadCounter(DBCOUNTER);
        if ($tabcounter[0] != $old_count) {
            WriteCounter(DBCOUNTER,$tabcounter[0]);
            $eSub = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' - '.$web[102];
            $eMsg = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' - '.$web[103].' '.$web[102].' '.$web[104].' '.date('d/m/Y H:i:s').'.<br />';
            $eMsg .= $web[105].' '.$tabcounter[0].'.';
            eMailHtmlTo($eSub,$eMsg);
        }
    }
}

date_default_timezone_set($site['TZ']);

// lecture dans le dossier data/log et prendre les details des fichiers
$meslogsmtp = CHEMIN."data/log/"; // Dossier contenant les logs SMTP
$file_names = array();
$file_dates = array();
$handler = opendir($meslogsmtp);
$i = 0;
if (!isset($smtp[6]))  $smtp[6] = '';
if ($smtp[6] == 'on') // Activation LOG SMTP 
{	
	if (!isset($smtp[7]))  $smtp[7] = 0;
	if (!is_numeric($smtp[7])) $smtp[7] = 0;
	while ($file = readdir($handler)) {  
	    $debutnamefile = substr($file,0,5);
        if ($file != '.' && $file != '..' && $file != "robots.txt" && $file != ".htaccess" && $debutnamefile == 'smtp_'){
			$i++;
			$currentModified = filectime($meslogsmtp."/".$file);
			$file_names[] = $file;
			$file_dates[] = $currentModified;
		}    
	}
	closedir($handler);
	asort($file_dates); // tri du plus ancien
	//Match file_names array to file_dates array
		$file_names_Array = array_keys($file_dates);
		foreach ($file_names_Array as $idx => $name) $name=$file_names[$name];
		$file_dates = array_merge($file_dates);
		
		$i = 0;

		//Loop through dates array and then echo the list
		foreach ($file_dates as $file_dates){
			$date = $file_dates;
			$j = $file_names_Array[$i];
			$file = $file_names[$j];
			$i++;
			$datefichier = date("Ymd.", $date);	
			$datejour = date("Ymd"); 		
	 //       echo  'File name: '.$file.' - Date Added: '. $datefichier.'<br/>';   
		
	 //       echo 'Date du jour: '.$datejour.'<br/>'; 
			$diffjoursdate = $datejour-$datefichier;
			
	 //      echo 'Diff en nombre de jours: '.$diffjoursdate.'<br/>';  
			if ($diffjoursdate > $smtp[7])
			{
	//			echo 'Suppression : '.$file.'<br />';
				SetChmod($meslogsmtp."/".$file);
				@unlink($meslogsmtp."/".$file);
			}
			
		}
} // PAs Activation de log SMTP
?>