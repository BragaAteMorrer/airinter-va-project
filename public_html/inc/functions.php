<?php
/*******************************************************************************
 *   Functions
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
 * v6.00.24 (August 12, 2026) : Delete lines log modify $pos !== false
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'functions.php')) {
  header('location:../index.php');
  die();
}

include_once CHEMIN.'inc/constantes.php';
global $phpmailer;

// (Re)create it, if it's gone missing.
  // lance les classes de PHPMailer
	use PHPMailer\PHPMailer\PHPMailer;
	// use PHPMailer\PHPMailer\Exception;
	// path du dossier PHPMailer % fichier d'envoi du mail
	require_once 'PHPMailer/src/PHPMailer.php';
	require_once 'PHPMailer/src/SMTP.php';
	require_once 'PHPMailer/src/Exception.php';
/*
md5crypt.php5
--------------

Written by

   - Dennis Riehle <selfhtml@riehle-web.com>

Based on

   - perl's Crypt::PasswdMD5 by Luis Munoz (lem@cantv.net)
   - phyton's md5crypt.py by Michal Wallace http://www.sabren.com/
   - /usr/src/libcrypt/crypt.c from FreeBSD 2.2.5-RELEASE

Many thanks to

   - Fabian Steiner <info@fabis-site.net>
   - Jorg Reinholz <http://www.fastix.de/>

USAGE

  $cryptedpassword = Md5Crypt::unix   ($password [, $salt [, $magicstring ]);
  $apachepassword  = Md5Crypt::apache ($password [, $salt]);

DESCRIPTION

  unix_md5_crypt() provides a crypt()-compatible interface to the
  rather new MD5-based crypt() function found in modern operating systems.
  It's based on the implementation found on FreeBSD 2.2.[56]-RELEASE and
  contains the following license in it:

   "THE BEER-WARE LICENSE" (Revision 42):
   <phk@login.dknet.dk> wrote this file.  As long as you retain this notice you
   can do whatever you want with this stuff. If we meet some day, and you think
   this stuff is worth it, you can buy me a beer in return.   Poul-Henning Kamp

  apache_md5_crypt() provides a function compatible with Apache's
  .htpasswd files. This was contributed by Bryan Hart <bryan@eai.com>.

*/
class Md5Crypt
{
    public static $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    // [a-zA-Z0-9./]

    protected static function to64($v, $n)
    {
        $itoa64 = self::$itoa64;
        $ret = '';

        while (--$n >= 0) {
            $ret .= $itoa64[$v & 0x3F];
            $v = $v >> 6;
        }

        return $ret;
    }

    public static function apache($pw, $salt = null)
    {
        $Magic = '$apr1$';

        return self::unix($pw, $salt, $Magic);
    }

    public static function unix($pw, $salt = null, $Magic = '$1$')
    {
        $itoa64 = self::$itoa64;

        if (null !== $salt) {
            // Take care of the magic string if present
            if (substr($salt, 0, strlen($Magic)) == $Magic) {
                $salt = substr($salt, strlen($Magic), strlen($salt));
            }
            // Salt can have up to 8 characters
            $parts = explode('$', $salt, 1);
            $salt = substr($parts[0], 0, 8);
        } else {
            $salt = '';
            mt_srand((float) (microtime(true) * 10000000));

            while (strlen($salt) < 8) {
                $salt .= $itoa64[mt_rand(0, strlen($itoa64) - 1)];
            }
        }

        $ctx = $pw . $Magic . $salt;

        $final = pack('H*', md5($pw . $salt . $pw));

        for ($pl = strlen($pw); $pl > 0; $pl -= 16) {
            $ctx .= substr($final, 0, ($pl > 16) ? 16 : $pl);
        }

        // Now the 'weird' xform
        for ($i = strlen($pw); $i; $i >>= 1) {
            if ($i & 1) {				// This comes from the original version,
                $ctx .= pack('C', 0);   // where a memset() is done to $final
            } else {					// before this loop
                $ctx .= $pw[0];
            }
        }

        $final = pack('H*', md5($ctx)); // The following is supposed to make
        // things run slower

        for ($i = 0; $i < 1000; ++$i) {
            $ctx1 = '';
            if ($i & 1) {
                $ctx1 .= $pw;
            } else {
                $ctx1 .= substr($final, 0, 16);
            }
            if ($i % 3) {
                $ctx1 .= $salt;
            }
            if ($i % 7) {
                $ctx1 .= $pw;
            }
            if ($i & 1) {
                $ctx1 .= substr($final, 0, 16);
            } else {
                $ctx1 .= $pw;
            }
            $final = pack('H*', md5($ctx1));
        }

        // Final xform
        $passwd = '';
        $passwd .= self::to64((intval(ord($final[0])) << 16)
                        | (intval(ord($final[6])) << 8)
                        | intval(ord($final[12])), 4);
        $passwd .= self::to64((intval(ord($final[1])) << 16)
                        | (intval(ord($final[7])) << 8)
                        | intval(ord($final[13])), 4);
        $passwd .= self::to64((intval(ord($final[2])) << 16)
                        | (intval(ord($final[8])) << 8)
                        | intval(ord($final[14])), 4);
        $passwd .= self::to64((intval(ord($final[3])) << 16)
                        | (intval(ord($final[9])) << 8)
                        | intval(ord($final[15])), 4);
        $passwd .= self::to64(intval(ord($final[4]) << 16)
                        | (intval(ord($final[10])) << 8)
                        | intval(ord($final[5])), 4);
        $passwd .= self::to64(intval(ord($final[11])), 2);

        // Return the final string
        return $Magic . $salt . '$' . $passwd;
    }
}

function is_ssl() {
    if (isset($_SERVER['HTTPS'])) {
        if ('on' == strtolower($_SERVER['HTTPS'])) return true;
        if ('1' == $_SERVER['HTTPS']) return true;
    } elseif (isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] )) {
        return true;
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && ('https' == strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']))) {
        return true;
    } elseif (isset($_SERVER['X_FORWARDED_PROTO']) && ('https' == strtolower($_SERVER['X_FORWARDED_PROTO']))) {
        return true;
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && 'on' == strtolower($_SERVER['HTTP_X_FORWARDED_SSL'])) {
        return true;
    } elseif (isset($_SERVER['X_FORWARDED_SSL']) && 'on' == strtolower($_SERVER['X_FORWARDED_SSL'])) {
        return true;
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_HTTPS']) && 'on' == strtolower($_SERVER['HTTP_X_FORWARDED_HTTPS'])) {
        return true;
    } elseif (isset($_SERVER['X_FORWARDED_HTTPS']) && 'on' == strtolower($_SERVER['X_FORWARDED_HTTPS'])) {
        return true;
    }
    return false;
}
if (!isset($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST'] = 'localhost';
if (!isset($_SERVER['HTTPS'])) $_SERVER['HTTPS'] = '';
$_DOMAIN   = (strtolower($_SERVER['HTTP_HOST']) != 'localhost' ? $_SERVER['HTTP_HOST'] : false);
$_SECURE   = (is_ssl() ? true : false);
$_HTTPONLY = (!empty($_SERVER['HTTPS']) ? true : false);

function ClearCookies() {
    global $_DOMAIN, $_SECURE, $_HTTPONLY;
    if (isset($_COOKIE[USER_COOKIE])) @setcookie(USER_COOKIE, '', -1, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
    if (isset($_COOKIE[PREF_COOKIE])) @setcookie(PREF_COOKIE, '', -1, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
    if (isset($_COOKIE[ADMP_COOKIE])) @setcookie(ADMP_COOKIE, '', -1, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
    if (isset($_COOKIE[CTCT_COOKIE])) @setcookie(CTCT_COOKIE, '', -1, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
    if (isset($_COOKIE[CNCT_COOKIE])) @setcookie(CNCT_COOKIE, '', -1, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
    if (isset($_COOKIE[POST_COOKIE])) @setcookie(POST_COOKIE, '', -1, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
    if (isset($_COOKIE['GuppYCrypt'])) @setcookie('GuppYCrypt', '', -1, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
    if (isset($_COOKIE[COOKIE_COOKIE])) @setcookie(COOKIE_COOKIE, '', -1, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
    return true;
}

$show_progbar  = FALSE;
$widepage      = '';
$headinc       = '';
$meskin        = '';
$skinCopyright = '';

$souriez = array(
    array('|:-)', '&#x1F60E;',     'cool'),
    array(';-)',  '&#x1F609;',     'wink'),
    array(':-))', '&#x1F601;',  'biggrin'),
    array(':-)',  '&#x1F60A;',    'smile'),
    array(':-o',  '&#x1F62B;',    'frown'),
    array(':o)',  '&#x1F60F;',      'eek'),
    array(':-((', '&#x1F608;',      'mad'),
    array(':-(',  '&#x1F621;', 'confused'),
    array('8-)',  '&#x1F628;', 'rolleyes'),
    array(':-p',  '&#x1F61C;',   'tongue'),
    array(';-(',  '&#x1F62D;',      'cry')
	);


function souriez($chaine) {
    global $souriez;
    foreach($souriez as $item) {
        $chaine = str_replace($item[0], '<span title="'.$item[2].'">'.$item[1].'</span>', $chaine);
    }
    return $chaine;
}

function replaceimg($chaine) {
    global $souriez, $site;
    foreach($souriez as $item) {
        $chaine = str_replace($item[0], '<span title="'.$item[2].'">'.$item[1].'</span>', $chaine);
    }
    return $chaine;
}

function DrawSmileys($num) {
    global $souriez;
    for ($i = 0; $i < count($souriez); $i++) {
        echo '<a href="JavaScript:AddSmiley'.$num.'(\''.$souriez[$i][0].'\')" class="clsCursor" title="'.$souriez[$i][0].'">'.$souriez[$i][1].'</a>&nbsp;';
    }
}

function DejaVote($file_ip, $d_time = 0) {
    global $userprefs, $site;
    $remoteadr   = getIPadress($site[6] == 'on' ? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']): $_SERVER['REMOTE_ADDR']);
    $nbr_connect = 0;
    $var_bool    = FALSE;
    $user_bool   = FALSE;
    $subdata     = array();
    $var_ip      = array();
    $k           = 0;
    if ($d_time == 0) {
        $d_time = HIT_TIME;
    }
    $subdata  = @ReadDBFields($file_ip);
    $now_time = time();
    foreach($subdata as $sub) {
        list($jour, $mois, $annee) = explode('/', substr($sub[1], 0, 10));
        list($heure, $minute, $seconde) = explode(':', substr($sub[1], 10, 18));
        if ($now_time < mktime((int)$heure, (int)$minute, (int)$seconde, (int)$mois, (int)$jour, (int)$annee) + $d_time) {
            $nbr_connect++;
            $var_ip[$k][0] = $sub[0];
            if ($remoteadr == $sub[0]) {
                $var_bool = TRUE;
            }
            $var_ip[$k][1] = $sub[1];
            if ($userprefs[5] != '') {
                $uprefname = $userprefs[1];
            }
            else {
                $uprefname = '';
            }
            if ($remoteadr == $sub[0] && $uprefname != $sub[2]) {
                $user_bool = TRUE;
                $var_ip[$k][2] = $uprefname;
            }
            else {
                $var_ip[$k][2] = @$sub[2];
            }
            $k++;
        }
    }
    if (!$var_bool) {
        $var_ip[$k][0] = $remoteadr;
        $var_ip[$k][1] = date('d/m/Y H:i:s');
        $var_ip[$k][2] = $userprefs[1];
        WriteDBFields($file_ip,$var_ip);
        $nbr_connect++;
    }
    elseif ($user_bool) {
        WriteDBFields($file_ip, $var_ip);
    }
    return array($var_bool, $nbr_connect);
}

function CompteVisites($file_ip, $file_counter) {
    $subdata = DejaVote($file_ip);
    $nbr_visit = ReadCounter($file_counter);
    if (!$subdata[0] && ($nbr_visit >= 0)) {
        $nbr_visit++;
        WriteCounter($file_counter,$nbr_visit);
    }
    return array($nbr_visit, $subdata[1]);
}

function IsImage($extn) {
    $extn = strtolower($extn);
	if (version_compare(PHP_VERSION, '8.1.0') >= 0 && version_compare(PHP_VERSION, '8.2.0') < 0) {
			if ($extn == 'gif' || $extn == 'jpg' || $extn == 'jpeg' || $extn == 'png' || $extn == 'bmp' || $extn == 'webp' || $extn == 'avif') {
				return TRUE;
			} else {
				return FALSE;
			}
	}
	else
	{
		if ($extn == 'gif' || $extn == 'jpg' || $extn == 'jpeg' || $extn == 'png' || $extn == 'bmp' || $extn == 'webp') {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

function ExtImage($extn){
    switch(strtolower($extn)) {
    case 'bat'  : return 'bat';
    case 'bmp'  : return 'bmp';
    case 'com'  : return 'com';
    case 'css'  : return 'css';
    case 'doc'  : return 'doc';
    case 'exe'  : return 'exe';
    case 'gif'  : return 'gif';
    case 'js'   : return 'js';
    case 'mid'  : return 'mid';
    case 'mp3'  : return 'mp3';
	case 'mp4'  : return 'mp4';
    case 'pdf'  : return 'pdf';
    case 'ppt'  : return 'ppt';
    case 'png'  : return 'png';
    case 'swf'  : return 'swf';
    case 'xls'  : return 'xls';

    case 'com'  :
    case 'pif'  : return 'com';

    case 'htm'  :
    case 'html' : return 'html';
    case 'jpeg' :
    case 'jpg' : return 'jpg';
	case 'webp' : return 'webp';
	case 'avif' : return 'avif';
    
    case 'odf'  : // OpenOffice
    case 'odg'  :
    case 'odm'  :
    case 'odp'  :
    case 'ods'  :
    case 'odt'  : return 'ooo';

    case 'inc'  :
    case 'php'  :
    case 'php3' :
    case 'php4' :
    case 'php5' :
    case 'phtml' : return 'php';

    case 'dtb'  :
    case 'ini'  :
    case 'nfo'  :
    case 'txt'  : return 'txt';

    case 'avi'  :
    case 'mpeg' :
    case 'mpg'  :
    case 'mov'  :
    case 'wav'  : return 'wav';

    case 'ace'  :
    case 'cab'  :
    case 'gz'   :
    case 'rar'  :
    case 'tar'  :
    case 'tgz'  :
    case 'zip'  : return 'zip';
    
    default     : return 'inconnu';
    }
}

function PathAbs($matches) {
    if(!preg_match('`^(https?|ftp|mailto|javascript)\:`i', $matches[3])) {
        $matches[0] = $matches[1].$matches[2].CHEMIN.$matches[3];
    }
	return $matches[0];
}

function PathToImage($text) {
    return preg_replace_callback("`( href=| src=| action=)(\"|')?([^ >]+)`i", 'PathAbs', $text);
}

function CutLongWord($textin, $textlen = 50) {
	return (mb_strlen($textin) > $textlen ? mb_substr(mb_substr($textin, 0, $textlen), 0, strrpos(mb_substr($textin, 0, $textlen), ' ')) : $textin);
}

function WrapLongWords($textin, $textlen = 50, $textrep = ' ') {
    return wordwrap($textin, $textlen, $textrep, 1);
}

function FormatDateStamp($datein) {
    global $site, $lng, $lang;
    $l       = ($lng == $lang[0] ? 0 : 1);
    $asite19 = explode('|', $site[19]);
    if ($asite19[$l] == 'E1') {
        $formatout = 'd/m/Y';
    }
    elseif ($asite19[$l] == 'E2') {
        $formatout = 'd.m.Y';
    }
    elseif ($asite19[$l] == 'U1') {
        $formatout = 'm/d/Y';
    }
    elseif ($asite19[$l] == 'U2') {
        $formatout = 'm.d.Y';
    }
    elseif ($asite19[$l] == 'C1') {
        $formatout = 'Y/m/d';
    }
    else {
        $formatout = 'Y.m.d';
    }
    $formatout .= ' '.$site[23].' ';
    if ($site[22] == 'H1') {
        $formatout .= 'H:i';
    }
    elseif ($site[22] == 'H2') {
        $formatout .= 'H\hi';
    }
    else {
        $formatout .= 'h:i A';
    }
    $dateout = date($formatout,$datein);
    return $dateout;
}

function FormatDate($datein) {
    global $site, $lng, $lang;
    $l       = ($lng == $lang[0] ? 0 : 1);
    $asite19 = explode('|', $site[19]);
	if ($datein != '')
	{
		$jour    = substr($datein,6,2);
		$mois    = substr($datein,4,2);
		$annee   = substr($datein,0,4);
		$heure   = substr($datein,8,2);
		$minute  = substr($datein,10,2);
		if ($asite19[$l] == 'E1') {
			$dateout = $jour.'/'.$mois.'/'.$annee;
		}
		elseif ($asite19[$l] == 'E2') {
			$dateout = $jour.'.'.$mois.'.'.$annee;
		}
		elseif ($asite19[$l] == 'U1') {
			$dateout = $mois.'/'.$jour.'/'.$annee;
		}
		elseif ($asite19[$l] == 'U2') {
			$dateout = $mois.'.'.$jour.'.'.$annee;
		}
		elseif ($asite19[$l] == 'C1') {
			$dateout = $annee.'/'.$mois.'/'.$jour;
		}
		else {
			$dateout = $annee.'.'.$mois.'.'.$jour;
		}
		if ($heure.$minute != '') {
			$dateout .= ' '.$site[23].' ';
			$suffixe  = '';
			if ($site[22] == "H1") {
				$separe   = ':';
			}
			elseif ($site[22] == "H2") {
				$separe = 'h';
			}
			else {
				$separe = ':';
				if ($heure < 12) {
					$suffixe = ' AM';
				}
				else {
					$suffixe = ' PM';
					$heure = $heure - 12;
				}
			}
			$dateout .= $heure.$separe.$minute.$suffixe;
		}
	}
	else $dateout = '';
    return $dateout;
}
function FormatDateSec($datein) {
    global $site, $lng, $lang;
    $l       = ($lng == $lang[0] ? 0 : 1);
    $asite19 = explode('|', $site[19]);
	if ($datein != '')
	{
		$jour    = substr($datein,6,2);
		$mois    = substr($datein,4,2);
		$annee   = substr($datein,0,4);
		$heure   = substr($datein,8,2);
		$minute  = substr($datein,10,2);
		$seconde = substr($datein,12,2);
		if ($seconde == '' ) $seconde = '00';
			
		if ($asite19[$l] == 'E1') {
			$dateout = $jour.'/'.$mois.'/'.$annee;
		}
		elseif ($asite19[$l] == 'E2') {
			$dateout = $jour.'.'.$mois.'.'.$annee;
		}
		elseif ($asite19[$l] == 'U1') {
			$dateout = $mois.'/'.$jour.'/'.$annee;
		}
		elseif ($asite19[$l] == 'U2') {
			$dateout = $mois.'.'.$jour.'.'.$annee;
		}
		elseif ($asite19[$l] == 'C1') {
			$dateout = $annee.'/'.$mois.'/'.$jour;
		}
		else {
			$dateout = $annee.'.'.$mois.'.'.$jour;
		}
		if ($heure.$minute.$seconde != '') {
			$dateout .= ' '.$site[23].' ';
			$suffixe  = '';
			if ($site[22] == "H1") {
				$separe   = ':';
			}
			elseif ($site[22] == "H2") {
				$separe = 'h';
			}
			else {
				$separe = ':';
				if ($heure < 12) {
					$suffixe = ' AM';
				}
				else {
					$suffixe = ' PM';
					$heure = $heure - 12;
				}
			}
			$dateout .= $heure.$separe.$minute.$separe.$seconde.$suffixe;
		}
	}
	else $dateout = '';
    return $dateout;
}

function GetCurrentDateTime() {
    return date('YmdHi');
}
function GetCurrentDateTimeSec() {
    return date('YmdHis');
}
function FileSizeInKb($fic) {
    $taille = @filesize($fic);
    if ($taille !== false) {
        $taille = round($taille/1024);
    }
    return $taille;
}

function FileDBExist($fic) {
	if ($fic != '')
		return is_file($fic);
	else
		return false;
}

function DestroyDBFile($fic) {
    if (FileDBExist($fic)) {
        SetChmod($fic);
        SetChmod($fic);
        @unlink($fic);
    }
}

function ReadCounter($fic) {
    $DataDB = 0;
    if (FileDBExist($fic)) {
        $fhandle = fopen($fic, 'r');
        if (filesize($fic)) {
            $DataDB = trim(fgets($fhandle, filesize($fic)));
        }
        fclose($fhandle);
    }
    return $DataDB;
}

function WriteCounter($fic, $DataDB) {
    $fhandle = fopen($fic, 'w');
    fputs($fhandle, $DataDB."\n");
    fclose($fhandle);
}

function ReadFullDB($fic) {
    $DataDB = Array();
    if (FileDBExist($fic)) {
        $DataDB = implode('', file($fic));
    }
    return $DataDB;
}

function WriteFullDB($fic, $DataDB) {
    $fhandle = fopen($fic, 'w');
    fputs($fhandle, $DataDB);
    fclose($fhandle);
}

function AppendFullDB($fic, $DataDB) {
    $fhandle = fopen($fic, 'a');
    fputs($fhandle, $DataDB);
    fclose($fhandle);
}

function CountDBFields($fic) {
    $DataNB = 0;
    if (FileDBExist($fic)) {
        $DataDB = file($fic);
        $DataNB = count($DataDB);
    }
    return $DataNB;
}

function ReadDBFields($fic) {
    $DataDB = array();
    $Fields = array();
	if (!empty($fic))
	{
		if (FileDBExist($fic)) {
			$DataDB = file($fic);
			$i = 0;
			foreach($DataDB as $Data) {
				$Fields[$i] = explode(CONNECTOR, trim($Data));
				$i++;
			}
		}
	}
    return $Fields;
}

function WriteDBFields($fic, $Fields) {
    $fhandle = fopen($fic, 'w');
    $out = '';
    foreach($Fields as $row) {
        $out .= @implode(CONNECTOR, (array)$row)."\n";
    }
    fputs($fhandle, $out);
    fclose($fhandle);
}

function AppendDBFields($fic, $Fields) {
    $fhandle = fopen($fic, 'a');
    $DataDB = '';
    for ($i = 0 ; $i < (count($Fields)-1); $i++) {
		if (isset($Fields[$i]))
			$DataDB .= trim($Fields[$i]).CONNECTOR;
    }
    $DataDB .= trim($Fields[count($Fields)-1])."\n";
    fputs($fhandle, $DataDB);
    fclose($fhandle);
}

function DeleteDBFieldById($fic, $id = 0) {
    $array  = explode("\n", fread(fopen($fic, 'r'), filesize($fic)));
    $delete = array_pop($array);
    unset($array[$id]);
    $newarray = array_values($array);
    $fhandle  = fopen($fic,'w');
    foreach($newarray as $new){
        fwrite($fhandle, $new."\n");
    }
    fclose($fhandle);
}

function CheckDB1Field($fic, $submit, $FieldNB) {
    if (FileDBExist($fic)) {
        $DataDB = ReadDBFields($fic);
        foreach($DataDB as $Data) {
            if (@stristr($Data[$FieldNB], $submit)) {
                return TRUE;
            }
        }
    }
    return FALSE;
}

function CheckDB2Fields($fic,$submit1,$FieldNB1,$submit2,$FieldNB2) {
    if (FileDBExist($fic)) {
        $DataDB = ReadDBFields($fic);
        foreach($DataDB as $Data) {
            if (@stristr($Data[$FieldNB1],$submit1) && @stristr($Data[$FieldNB2],$submit2)) {
                return TRUE;
            }
        }
    }
    return FALSE;
}

function SelectDBFieldsByID($Fields,$id) {
    $DataDB = array();
    foreach ($Fields as $row) {
        if ($row[1] == $id) {
            $DataDB[] = $row;
        }
    }
    return $DataDB;
}

function SelectDBFieldsByField($Fields, $index, $value) {
    $DataDB = array();
    foreach ($Fields as $row) {
        if (isset($row[$index]))
			if ($row[$index] == $value) {
            $DataDB[] = $row;
        }
    }
    return $DataDB;
}

function IndexDBFieldsByField($Fields, $index) {
    $DataDB = array();
    foreach ($Fields as $row) {
        $DataDB[$row[$index]] = $row;
    }
    return $DataDB;
}

function SelectDBFieldsByType($Fields, $type) {
    $DataDB = array();
    foreach ($Fields as $row) {
        if ($row[0] == $type) {
            $DataDB[] = $row;
        }
    }
    return $DataDB;
}

function SelectDBFieldsByNotStatus($Fields, $status) {
    $DataDB = array();
    foreach ($Fields as $row) {
        if ($row[2] != $status) {
            $DataDB[] = $row;
        }
    }
    return $DataDB;
}

function SelectDBFieldsByStatus($Fields, $status) {
    $DataDB = array();
    foreach ($Fields as $row) {
        if ($row[2] == $status) {
            $DataDB[] = $row;
        }
    }
    return $DataDB;
}

function SelectDBFields($type,$status,$id,$docid=DOCID) {
    if (!empty($status) && !empty($id)) {
        return SelectDBFieldsByID(SelectDBFieldsByStatus(SelectDBFieldsByType(ReadDBFields($docid), $type), $status), $id);
    }
    elseif (!empty($status)) {
        return SelectDBFieldsByStatus(SelectDBFieldsByType(ReadDBFields($docid), $type), $status);
    }
    elseif (!empty($id)) {
        return SelectDBFieldsByID(SelectDBFieldsByType(ReadDBFields($docid), $type), $id);
    }
    else {
        return SelectDBFieldsByType(ReadDBFields($docid), $type);
    }
}

function RemoveQuote($chaine) {
    return str_replace(array('"', "'"), '', $chaine);
}

function RemoveConnector($chaine) {
    return str_replace(CONNECTOR, '', $chaine);
}

function RemoveBR($chaine) {
    return preg_replace('!<br />!i', "\n", $chaine);
}

function PutBR($chaine) {
    return str_replace(array(chr(10), chr(13)), array("\n", "\r"), $chaine);
}

function ReplaceDolar($chaine) {
    return str_replace('$','&#36;',$chaine);
}

function TestFileId($id, $format='%08d') {
	if ($id =='') return FALSE;
		if (1 == preg_match('|^[0-9]+$|', $id)) {
			return sprintf($format, $id);
		} else {
			return FALSE;
		}
}

function ReadDocCounter($id, $dir = DBCOUNT) {
    $id = TestFileID($id);
    if ($id === FALSE) {
        return FALSE;
    } else {
        return ReadCounter($dir.$id.DBEXT);
    }
}

function WriteDocCounter($id, $DataDB, $dir = DBCOUNT) {
    WriteCounter($dir.$id.DBEXT, $DataDB);
}

function UpdateDocCounter($id) {
    $id = TestFileID($id);
    if ($id === FALSE) return FALSE;
    $DataDB = ReadDocCounter($id);
    $vote = DejaVote(DBIPBASE.$id.DBEXT,300);
    if ($vote[0] === FALSE) {
        $DataDB++;
        WriteDocCounter($id, $DataDB);
    }
    return $DataDB;
}

function ReadDoc($id, $dir = DBBASE) {
    global $type, $fileid, $status, $creadate, $moddate, $author, $email, 
         $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
         $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
    $type       = '';
    $fileid     = '';
    $status     = '';
    $creadate   = '';
    $moddate    = '';
    $author     = '';
    $email      = '';
    $fielda1    = '';
    $fielda2    = '';
    $fieldb1    = '';
    $fieldb2    = '';
    $fieldc1    = '';
    $fieldc2    = '';
    $fieldd1    = '';
    $fieldd2    = '';
    $fieldweb   = '';
    $fieldmail  = '';
    $fieldmod   = '';
    $id = TestFileID($id);
    if ($id === FALSE) return FALSE;
    if (FileDBExist($dir.$id.INCEXT)) {
        include($dir.$id.INCEXT);
    }
    return TRUE;
}

function WriteDoc($action = '', $dir = DBBASE) {
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
         $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
         $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
    $id = TestFileID($fileid);
    if ($id === FALSE) return FALSE;
	if ($action != 'migr' && in_array($type, array(TYP_ART, TYP_BLOG, TYP_FAQ, TYP_FREEBOX, TYP_MENUBOX, TYP_OPTDDMENU))) {
		$fieldc1 = str_replace('&#36;', '$', $fieldc1);
		$fieldc2 = str_replace('&#36;', '$', $fieldc2);
	}
	$rec = "<?php
\$type = '".$type."';
\$fileid = '".$fileid."';
\$status = '".$status."';
\$creadate = '".$creadate."';
\$moddate = '".$moddate."';
\$author = stripslashes('".$author."');
\$email = stripslashes('".$email."');
\$fielda1 = stripslashes('".$fielda1."');
\$fielda2 = stripslashes('".$fielda2."');
\$fieldb1 = stripslashes('".$fieldb1."');
\$fieldb2 = stripslashes('".$fieldb2."');
\$fieldc1 = stripslashes('".$fieldc1."');
\$fieldc2 = stripslashes('".$fieldc2."');
\$fieldd1 = stripslashes('".$fieldd1."');
\$fieldd2 = stripslashes('".$fieldd2."');
\$fieldweb = stripslashes('".$fieldweb."');
\$fieldmail = stripslashes('".$fieldmail."');
\$fieldmod = stripslashes('".$fieldmod."');
?>
";
    WriteFullDB($dir.$id.INCEXT, $rec);
    return TRUE;
}

function UpdateDBdtb($dtb) {
    global $type,$fileid,$status,$creadate,$moddate,$author,$email,
        $fielda1,$fielda2,$fieldb1,$fieldb2,$fieldd1,$fieldd2,$fieldweb,$fieldmail,$fieldmod;
    $db  = SelectDBFields($dtb, 'a', '');
    $dba = array();
    $j   = 0;
    foreach($db as $dbs) {
        ReadDoc($dbs[1]);
		if ($fielda1 != '' || $fielda2 != '' || $fieldb1 != '' || $fieldb2 != '')
		{	
			$dba[$j]= array(
				RemoveConnector($fielda1),
				RemoveConnector($fielda2),
				RemoveConnector($fieldb1),
				RemoveConnector($fieldb2),
				$fileid,
				$creadate,
				$fieldmod,
				$type
				);
			if ($dtb == TYP_SOCNET) {
				$dba[$j][2] = $fieldb1;
				$dba[$j][6] = $fieldd2;
			}
			if ($dtb == TYP_ART) {
				$dba[$j][5] = RemoveConnector($fieldd1);
			} elseif ($dtb == TYP_OPTMENU || $dtb == TYP_OPTDDMENU) {
				$dba[$j][5] = $fieldweb;
			}
			$j++;
		}
    }
    WriteDBFields(DBINDEX.$dtb.DBEXT, $dba);
}

function UpdateDBreact($action, $id) {
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
        $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2,
        $fieldweb, $fieldmail, $fieldmod;
    $db = array();
    if ($action == 'add' || $action == 'migr') {
        ReadDoc($id);
		if ($status == 'a') {
			$db = array_pad(array(), 7, '');
			$db[0] = $fileid;
			$db[1] = $fielda2;
			ReadDoc($fielda2);
			$db[2] = $fieldb1;
			$db[3] = $fieldb2;
			$db[5] = $creadate;
			$db[6] = $fieldmod;
			$db[7] = TYP_REACT;
			AppendDBFields(DBREACT, $db);
		}
    }
    else {
        $db = SelectDBFields(TYP_REACT,'a','');
        sort($db);
        $dbart = ReadDBFields(DBART);
        $dbf = array();
		$i   = 0;
        foreach($db as $dbs) {
            $dbf[$i] = array_pad(array(), 7, '');
            ReadDoc($dbs[1]);
            $dbf[$i][0] = $fileid;
            $dbf[$i][1] = $fielda2;
            $art = SelectDBFieldsByField($dbart, 4, $fielda2);
			if (!empty($art)) {
				$dbf[$i][2] = $art[0][2];
				$dbf[$i][3] = $art[0][3];
				$dbf[$i][5] = $art[0][5];
				$dbf[$i][6] = $art[0][6];
				$dbf[$i][7] = TYP_REACT;
			}
			$i++;
        }
        WriteDBFields(DBREACT, $dbf);
    }
}

function UpdateDBreblog($action, $id) {
    global $type, $fileid, $status, $creadate, $moddate, $author, $email, 
        $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2,
        $fieldweb, $fieldmail, $fieldmod;
    $db = array();
    if ($action == 'add' || $action == 'migr') {
        ReadDoc($id);
		if ($status == 'a') {
			$db = array_pad(array(), 7, '');
			$db[0] = $fileid;
			$db[1] = $fielda2;
			ReadDoc($fielda2);
			$db[2] = $fieldb1;
			$db[3] = $fieldb2;
			$db[5] = $creadate;
			$db[6] = $fieldmod;
			$db[7] = TYP_REBLOG;
			AppendDBFields(DBREBLOG, $db);
		}
    }
    else {
        $db = SelectDBFields(TYP_REBLOG,'a','');
        sort($db);
        $dbblog = ReadDBFields(DBBLOG);
        $dbf = array();
		$i   = 0;
        foreach($db as $dbs) {
            $dbf[$i] = array_pad(array(), 7, '');
            ReadDoc($dbs[1]);
            $dbf[$i][0] = $fileid;
            $dbf[$i][1] = $fielda2;
            $blog = SelectDBFieldsByField($dbblog, 4, $fielda2);
			if (!empty($blog)) {
				$dbf[$i][2] = $blog[0][2];
				$dbf[$i][3] = $blog[0][3];
				$dbf[$i][5] = $blog[0][5];
				$dbf[$i][6] = $blog[0][6];
				$dbf[$i][7] = TYP_REBLOG;
			}
			$i++;
        }
        WriteDBFields(DBREBLOG, $dbf);
    }
}

function UpdateDBforum($action, $id) {
    global $type,$fileid,$status,$creadate,$moddate,$author,$email,
    $fielda1,$fielda2,$fieldb1,$fieldb2,$fieldc1,$fieldc2,$fieldd1,$fieldd2,$fieldweb,$fieldmail;$fieldmod;
    $db = array();
    if ($action == 'add' || $action == 'migr') {
        ReadDoc($id);
		if ($status == 'a') {
			$db[0]  = $creadate;
			$db[1]  = $fielda1;
			$db[2]  = $fielda2;
			$db[3]  = $fileid;
			$db[4]  = RemoveConnector($author);
			$db[5]  = RemoveConnector($email);
			$db[6]  = RemoveConnector($fieldb1);
			$db[7]  = $fieldd1;
			$db[8]  = getForumCatFull($fieldb2);
			$db[9]  = $fieldmail;
			$db[10] = TYP_FORUM;
			AppendDBFields(DBTHREAD, $db);
		}
    } else {
        $db = SelectDBFields(TYP_FORUM, 'a', '');
        sort($db);
        $dbf    = array();
        $ns2cat = array();
		$i      = 0;
        foreach($db as $dbs) {
            ReadDoc($dbs[1]);
            $dbf[$i][0]  = $creadate;
            $dbf[$i][1]  = $fielda1;
            $dbf[$i][2]  = $fielda2;
            $dbf[$i][3]  = $fileid;
            $dbf[$i][4]  = RemoveConnector($author);
            $dbf[$i][5]  = RemoveConnector($email);
            $dbf[$i][6]  = RemoveConnector($fieldb1);
            $dbf[$i][7]  = $fieldd1;
            $dbf[$i][8]  = getForumCatFull($fieldb2);
            $dbf[$i][9]  = $fieldmail;
			$dbf[$i][10] = TYP_FORUM;
            if (0 == $fielda2) {
                $ns2cat[$fielda1] = $fieldb2;
            } elseif (isset($ns2cat[$fielda1])) {
                $dbf[$i][8] = $ns2cat[$fielda1];
            }
			$i++;
        }
        @sort($dbf, SORT_REGULAR);
        WriteDBFields(DBTHREAD, $dbf);
    }
    $db  = array();
    $db  = ReadDBFields(DBTHREAD);
    $dbf = array();
    $i   = 0;
    $j   = 0;
    foreach ($db as $dbs) {
        if ($db[$i][2] == '0') {
            $dbf[$j][0]  = $dbs[0];
            $dbf[$j][1]  = $dbs[1];
            $dbf[$j][2]  = $dbs[3];
            $dbf[$j][3]  = $dbs[4];
            $dbf[$j][4]  = $dbs[5];
            $dbf[$j][5]  = $dbs[6];
            $dbf[$j][6]  = $dbs[3] == ($fileid ? $creadate : $dbs[0]);
            $dbf[$j][7]  = GetLastChildNumber(TYP_FORUM, $dbs[1]);
            $dbf[$j][8]  = '';
            $dbf[$j][9]  = '';
            $dbf[$j][10] = $dbs[7];
            $dbf[$j][11] = '';
            $dbf[$j][12] = $dbs[8];
			$dbf[$j][13] = $dbs[10];
			$k           = 0;
            foreach ($db as $dbk) {
                if ($dbk[1] == $dbf[$j][1]) {
                    $dbf[$j][0]  = ($dbk[0] > $dbf[$j][0] ? $dbk[0] : $dbf[$j][0]);
                    $dbf[$j][8]  = $dbk[4];
                    $dbf[$j][9]  = $dbk[5];
                    $dbf[$j][11] = $dbk[7];
                }
				$k++;
            }
            $j++;
        }
		$i++;
    }
    @rsort($dbf, SORT_REGULAR);
    WriteDBFields(DBFORUM, $dbf);
}

function UpdateDocid($id, $status) {
    $db = ReadDBFields(DOCID);
    for ($i = 0; $i < count($db); $i++) {
        if ($db[$i][1] == $id) {
            $db[$i][2] = $status;
            break;
        }
    }
    WriteDBFields(DOCID,$db);
    unset($db);
}

function UpdateDocdates($action, $id, $mdate='', $cdate='') {
    $db = ReadDBFields(DOCDATES);
    switch ($action) {
    case 'add'  :
    case 'migr' :
		$datespub    = explode('-', $cdate);
		$datespub[0] = (empty($datespub[0]) ? GetCurrentDateTime() : $datespub[0]);
		$datespub[1] = (empty($datespub[1]) ? '' : $datespub[1]);
		$cdate       = $datespub[0].'-'.$datespub[1];
		$mdate       = ($action == 'add' ? $datespub[0] : $mdate);
        $db[]        = array($id, $cdate, $mdate);
        break;
    case 'mod' :
		$trouveid = 0;				
        for ($i = 0; $i < count($db); $i++) {
            if ($db[$i][0] == $id) {
				if ($cdate != '' && $db[$i][1] != $cdate) {
					$datespub    = explode('-', $cdate);
					$datespub[0] = (empty($datespub[0]) ? GetCurrentDateTime() : $datespub[0]);
					$datespub[1] = (empty($datespub[1]) ? '' : $datespub[1]);
					$db[$i][1]   = $datespub[0].'-'.$datespub[1];
				}
                $db[$i][2] = ($mdate == '' ? GetCurrentDateTime() : $mdate);
				$trouveid = 1;				  
                break;
            }
        }
		if ($trouveid != 1)
		{
			$datespub    = explode('-', $cdate);
			$datespub[0] = (empty($datespub[0]) ? GetCurrentDateTime() : $datespub[0]);
			$datespub[1] = (empty($datespub[1]) ? '' : $datespub[1]);
			$db[]        = array($id, $cdate, $mdate);
		}
        break;
    case 'del' :
        for ($i = 0; $i < count($db); $i++) {
            if ($db[$i][0] == $id) {
                unset($db[$i]);
                break;
            }
        }
        break;
    }
    sort($db);
    WriteDBFields(DOCDATES, $db);
    unset($db);
}

function GetLastChildNumber($type, $parentId = NULL) {
    $db = SelectDBFieldsByField(ReadDBFields(CHILDREN), 1, $type);
    if (isset($parentId)) {
        $db = SelectDBFieldsByField($db, 3, $parentId);
    }
    if (count($db) > 0) {
        rsort($db);
        return $db[0][2];
    } else {
        return 0;
    }
    unset($db);
}

function GetNextChildNumber($type, $parentId = NULL) {
    return 1 + GetLastChildNumber($type, $parentId);
}

function UpdateChildren($action, $data) {
    $db = IndexDbFieldsByField(ReadDBFields(CHILDREN), 0);
    switch ($data[0]) {
    case TYP_GUESTBK :
    case TYP_REACT :
    case TYP_REBLOG :
        switch ($action) {
        case 'add' :
            $db[$data[1]] = array($data[1], $data[0], $data[7], $data[8]);
            WriteDbFields(CHILDREN, $db);
            break;
        case 'del' :
            unset($db[$data[1]]);
            WriteDbFields(CHILDREN, $db);
            break;
        }
        break;
    case TYP_FORUM :
        if (0 == $data[8]) break;
        switch ($action) {
        case 'add' :
            $db[$data[1]] = array($data[1], $data[0], $data[8], $data[7]);
            WriteDbFields(CHILDREN, $db);
            break;
        case 'del' :
            if (isset($db[$data[1]])) unset($db[$data[1]]);
            WriteDbFields(CHILDREN, $db);
            break;
        }
        break;
    }
    unset($db);
}

function UpdateDocFromDoc($action, $id, $newStatus) {
    global $type,$fileid,$status,$creadate,$moddate,$author,$email,
    $fielda1,$fielda2,$fieldb1,$fieldb2,$fieldc1,$fieldc2,$fieldd1,$fieldd2,
    $fieldweb,$fieldmail,$fieldmod, $lang, $urlrw;
    ReadDoc($id);
    $status    = $newStatus;
    $author    = addslashes($author);
    $email     = addslashes($email);
    $fielda1   = addslashes($fielda1);
    $fielda2   = addslashes($fielda2);
    $fieldb1   = addslashes($fieldb1);
    $fieldb2   = addslashes($fieldb2);
    $fieldc1   = addslashes($fieldc1);
    $fieldc2   = addslashes($fieldc2);
    $fieldd1   = addslashes($fieldd1);
    $fieldd2   = addslashes($fieldd2);
    $fieldweb  = addslashes($fieldweb);
    $fieldmail = addslashes($fieldmail);
    $fieldmod  = addslashes($fieldmod);
    WriteDoc($action);
    if ($type == TYP_ART) {
        $mnu_id = substr($fieldd1, 4);
        $dbw    = SelectDBFieldsByField(ReadDBFields(DBOPTMENU), 5, $mnu_id);
        foreach ($dbw as $item) {
            ReadDoc($item[4]);
			$urls = explode('||', $fieldd1);
			if (!empty($urls[0]) && strpos($urls[0], 'articles.php') !== false) {
				$pg  = substr($urls[0], strpos($urls[0], 'pg=') + 3);
				$pos = strpos($pg, '&');
				$pg  = ($pos === false ? $pg : substr($pg, 0, $pos));
			}
            if ($pg == $id && $fieldweb == $mnu_id) {
                $opt_id = $item[4];
                UpdateDocid($opt_id, $newStatus);
                UpdateDocFromDoc($action, $opt_id, $newStatus);
                UpdateDBdtb(TYP_OPTMENU);
                break;
            }                
        }
        unset($dbw);
    }
}

function UpdateDocFromInput($action, $id, $data) {
    global $site,$type,$fileid,$status,$creadate,$moddate,$author,$email,
    $fielda1,$fielda2,$fieldb1,$fieldb2,$fieldc1,$fieldc2,$fieldd1,$fieldd2,
    $fieldweb,$fieldmail,$fieldmod;
    ReadDoc($id);
    if (in_array($action, array('add', 'migr'))) {
        $type   = $data[0];
        $fileid = $id;
        $status = $data[2];
		
		$datespub    = explode('-', $data[3]);
		$datespub[0] = (empty($datespub[0]) ? GetCurrentDateTime() : $datespub[0]);
		$datespub[1] = (empty($datespub[1]) ? '' : $datespub[1]);
		$creadate    = $datespub[0].'-'.$datespub[1];
		$moddate     = ($action == 'add' ? $datespub[0] : $data[4]);
    } else {
        /// $action == 'mod'
		if ($creadate != $data[3] && $data[0] != TYP_FORUM) {
			$datespub    = explode('-', $data[3]);
			$datespub[0] = (empty($datespub[0]) ? GetCurrentDateTime() : $datespub[0]);
			$datespub[1] = (empty($datespub[1]) ? '' : $datespub[1]);
			$creadate    = $datespub[0].'-'.$datespub[1];
		}
        $moddate = $data[4];
    }
    $author  = addslashes(stripslashes($data[5]));
    $email   = addslashes(stripslashes($data[6]));
    $fielda1 = addslashes(stripslashes($data[7]));
    $fielda2 = addslashes(stripslashes($data[8]));
    $fieldb1 = addslashes(stripslashes($data[9]));
    $fieldb2 = addslashes(stripslashes($data[10]));
    if ($data[0] == TYP_NWL) {
        $fieldc1 = addslashes(stripslashes(replaceimg(ReplaceDolar(PutBR($data[11])))));
        $fieldc2 = addslashes(stripslashes(replaceimg(ReplaceDolar(PutBR($data[12])))));
    }
    else {
        $fieldc1 = addslashes(stripslashes(souriez(ReplaceDolar(PutBR($data[11])))));
        $fieldc2 = addslashes(stripslashes(souriez(ReplaceDolar(PutBR($data[12])))));
    }
   if (isset($data[13])) $fieldd1   = addslashes(stripslashes($data[13]));
	else
		$fieldd1   = '';
    if (isset($data[14])) $fieldd2   = addslashes(stripslashes($data[14]));
		else
		$fieldd2   = '';
    if (isset($data[15]))$fieldweb  = addslashes(stripslashes($data[15]));
		else
		$fieldweb   = '';
    if (isset($data[16]))$fieldmail = addslashes(stripslashes($data[16]));
		else
		$fieldmail   = '';
    if (isset($data[17])) $fieldmod  = addslashes(stripslashes(@$data[17]));
		else
		$fieldmod   = '';				   
    WriteDoc($action);
}

function DeactivateInCascade($type, $parentId) {
    // Désactivation en cascade des réactions, réponses et commentaires actifs
    switch ($type) {
    case TYP_ART :
        $db1 = SelectDBFieldsByField(ReadDBFields(DBREACT), 1, $parentId);
        foreach ($db1 as $db) {
            ActionOnFields('act', array(TYP_REACT, $db[0], 'i'), FALSE);
        }
        if (count($db1) > 0) {
            ActionOnFields('reindex', array(TYP_REACT, 0));
        }
        unset($db1, $db);
        break;
    case TYP_BLOG :
        $db1 = SelectDBFieldsByField(ReadDBFields(DBREBLOG), 1, $parentId);
        foreach ($db1 as $db) {
            ActionOnFields('act', array(TYP_REBLOG, $db[0], 'i'), FALSE);
        }
        if (count($db1) > 0) {
            ActionOnFields('reindex', array(TYP_REBLOG, 0));
        }
        unset($db1, $db);
        break;
    case TYP_FORUM :
        global $fielda1, $fielda2;
        if (0 == $fielda2) {
            $db1 = SelectDBFieldsByField(ReadDBFields(DBTHREAD), 1, $fielda1);
            foreach ($db1 as $db) {
                if ($db[2] > 0) {
                    ActionOnFields('act', array(TYP_FORUM, $db[3], 'i'), FALSE);
                }
            }
            if (count($db1) > 1) {
                ActionOnFields('reindex', array(TYP_FORUM, 0));
            }
            unset($db1, $db);
        }
        break;
    }
}

function DeleteInCascade($type, $parentId) {
    // Suppression en cascade des réactions, réponses et commentaires actifs
    $db1 = array();
    $typechildren = array(TYP_ART=>TYP_REACT, TYP_BLOG=>TYP_REBLOG);
    switch ($type) {
    case TYP_ART :
    case TYP_BLOG :
        $db1 = SelectDBFieldsByField(SelectDBFieldsByField(ReadDBFields(CHILDREN),1, $typechildren[$type]), 3, $parentId);
        foreach ($db1 as $db) {
            ActionOnFields('del', array($typechildren[$type], $db[0]), FALSE);
        }
        break;
    case TYP_FORUM :
        global $fielda1, $fielda2;
        if (0 == $fielda2) {
            $db1 = SelectDBFieldsByField(SelectDBFieldsByField(ReadDBFields(CHILDREN),1, $type), 3, $fielda1);
            foreach ($db1 as $db) {
                ActionOnFields('del', array($type, $db[0]), FALSE);
            }
        }
        break;
    }
    if (count($db1) > 0) {
        ActionOnFields('reindex', array($type, 0));
    }
    unset($db1);
}

function DestroyInCascade($type, $parentId) {
    // Destruction définitive en cascade des réactions, réponses et commentaires actifs
    $db1 = array();
    $typechildren = array(TYP_ART=>TYP_REACT, TYP_BLOG=>TYP_REBLOG);
    switch ($type) {
    case TYP_ART :
    case TYP_BLOG :
        $db1 = SelectDBFieldsByField(SelectDBFieldsByField(ReadDBFields(CHILDREN),1, $typechildren[$type]), 3, $parentId);
        foreach ($db1 as $db) {
            ActionOnFields('sup', array($typechildren[$type], $db[0]), FALSE);
        }
        break;
    case TYP_FORUM :
        global $fielda1, $fielda2;
        if (0 == $fielda2) {
            $db1 = SelectDBFieldsByField(SelectDBFieldsByField(ReadDBFields(CHILDREN),1, $type), 3, $fielda1);
            foreach ($db1 as $db) {
                ActionOnFields('sup', array($type, $db[0]), FALSE);
            }
        }
        break;
    }
    if (count($db1) > 0) {
        ActionOnFields('reindex', array($type, 0));
    }
}
	
function CreateSitemapFile($file, $db) {
	global $charset;
	$smtxt = '<?xml version="1.0" encoding="'.$charset.'"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
	foreach($db as $url) {
		$smtxt .= '
  <url>
    <loc>'.$url['LOC'].'</loc>
    <lastmod>'.$url['LM'].'</lastmod>
    <changefreq>'.$url['CF'].'</changefreq>
    <priority>'.$url['PR'].'</priority>
  </url>';
	}
	$smtxt .= '
</urlset>';
	WriteFullDB($file, $smtxt);
}

function UpdateSitemaps($typ) {
	global $lang, $site, $urlrw, $sitemap, $charset, $z2, 
	$type, $fileid, $status, $creadate, $moddate, $author, $email, $fielda1, $fielda2, $fieldb1,
	$fieldb2, $fieldc1, $fieldc2, $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
	
	$smtyps  = array(TYP_BLOG, TYP_ART, TYP_NEWS, TYP_PHOTO, TYP_DNLOAD, TYP_FAQ, TYP_LINKS, TYP_GUESTBK, TYP_FORUM, TYP_AGENDA);
    $smfiles = array(TYP_BLOG=>SM_BLOG, TYP_ART=>SM_ART, TYP_NEWS=>SM_NEWS, TYP_PHOTO=>SM_PHOTO, TYP_DNLOAD=>SM_DNLOAD, 
                     TYP_FAQ=>SM_FAQ, TYP_LINKS=>SM_LINKS, TYP_GUESTBK=>SM_GUESTBK, TYP_FORUM=>SM_FORUM, TYP_AGENDA=>SM_AGENDA);
	
    if (!in_array($typ, array_merge($smtyps, array('URLS', 'NONE')))) return;
	
	if ($typ == 'NONE') {
        @unlink(SITEMAPS);
        foreach($smtyps as $smtyp) @unlink($smfiles[$smtyp]);
        @unlink(SM_URLS);
    }
    
	$dbws = array();
	if ($sitemap['SM'] == 'on') {
		$home = array('index.php?lng='.$lang[0], ($lang[1] != '' ? 'index.php?lng='.$lang[1] : NULL));
		$urls = file_exists(MAPSURLS) ? array_merge(file(MAPSURLS), $home) : $home;
		
		$smslcts = array(TYP_BLOG=>$sitemap['BL'], TYP_ART=>$sitemap['AR'], TYP_NEWS=>$sitemap['NE'], TYP_PHOTO=>$sitemap['PH'], TYP_DNLOAD=>$sitemap['DN'], 
		                 TYP_FAQ=>$sitemap['FA'], TYP_LINKS=>$sitemap['LI'], TYP_GUESTBK=>$sitemap['GB'], TYP_FORUM=>$sitemap['FR'], TYP_AGENDA=>$sitemap['AG']);
		$smfreqs = array(TYP_BLOG=>'weekly', TYP_ART=>'monthly', TYP_NEWS=>'weekly', TYP_PHOTO=>'monthly', TYP_DNLOAD=>'monthly', 
		                 TYP_FAQ=>'yearly', TYP_LINKS=>'monthly', TYP_GUESTBK=>'monthly', TYP_FORUM=>'daily', TYP_AGENDA=>'monthly');
		
		foreach($smtyps as $smtyp) if ($smslcts[$smtyp] != 'on') @unlink($smfiles[$smtyp]);

		$dbw = array();
		foreach($urls as $url) {
		if ($url != '')
			{
				$url = str_replace('&amp;', '&', $url);
				$url = str_replace('&', '&amp;', $url);
				$dbw[] = array('LOC'=>$site[3].$url, 'LM'=>date('Y-m-d'), 'CF'=>'yearly', 'PR'=>'0.5');
			}
		}
		CreateSitemapFile(SM_URLS, $dbw);
		
		foreach($smtyps as $smtyp) {
			if ($typ != $smtyp || $smslcts[$smtyp] != 'on') continue;
			$dbw  = array();
			$docs = SelectDBFields($smtyp, 'a', '');
			foreach ($docs as $doc) {
				ReadDoc($doc[1]);
				if ($typ == TYP_FORUM && $fielda2 != '0') continue;
				$fieldmail = (empty($fieldmail) ? 0 : $fieldmail);
				if (isAuthorizedPublication($creadate) && $fieldmod == '') {
					foreach($lang as $lng) {
						if ($lang[1] == $lng && ($fieldc2 == '' || in_array(array(TYP_GUESTBK, TYP_FORUM), $smtyps))) continue;
						switch ($smtyp) {
						case TYP_BLOG    : $smloc = ($site['URLR'] == 'on' 
                          ? $lng.'-'.$urlrw[1].'-4-'.$fileid.$z2 
                          : $site['BL'].'.php?lng='.$lng.'&amp;pg='.$fileid.$z2);
                          break;
						case TYP_ART     : $smloc = ($site['URLR'] == 'on' 
                          ? $lng.'-'.$urlrw[20].'-102-'.$fileid.'-'.$fieldmail.$z2 
                          : 'articles.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$fieldmail.$z2);
                          break;
						case TYP_NEWS    : $smloc = ($site['URLR'] == 'on' 
                          ? $lng.'-'.$urlrw[8].'-2-'.$fileid.$z2 
                          : $site['NE'].'.php?lng='.$lng.'&amp;pg='.$fileid.$z2);
                          break;
						case TYP_PHOTO   : $smloc = ($site['URLR'] == 'on' 
                          ? $lng.'-'.$urlrw[9].'-2-'.$fileid.'-'.$fieldmail.$z2 
                          : 'photorama2.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$fieldmail.$z2);
                          break;
						case TYP_DNLOAD  : $smloc = ($site['URLR'] == 'on' 
                          ? $lng.'-'.$urlrw[11].'-4-'.$fileid.'-'.$fieldmail.$z2 
                          : $site['DN'].'.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;tconfig='.$fieldmail.$z2);
                          break;
						case TYP_FAQ     : $smloc = ($site['URLR'] == 'on' 
                          ? $lng.'-'.$urlrw[4].'-3-'.$fileid.$z2 
                          : 'faq.php?lng='.$lng.'&amp;pg='.$fileid.$z2);
                          break;
						case TYP_LINKS   : $smloc = ($site['URLR'] == 'on' 
                          ? $lng.'-'.$urlrw[6].'-1-'.$fileid.$z2 
                          : 'links.php?lng='.$lng.'&amp;pg='.$fileid.$z2);
                          break;
						case TYP_GUESTBK : $smloc = ($site['URLR'] == 'on' 
                          ? $lng.'-'.$urlrw[7].'-3-'.$fileid.$z2 
                          : $site['GB'].'.php?lng='.$lng.'&amp;pg='.$fileid.$z2);
                          break;
						case TYP_FORUM   : $smloc = ($site['URLR'] == 'on' 
                          ? $lng.'-'.$urlrw[5].'-500-'.$fileid.'-'.$fielda1.'-1'.$z2 
                          : $site['TH'].'.php?lng='.$lng.'&amp;pg='.$fileid.'&amp;cat='.$fielda1.'&amp;fid=1'.$z2);
                          break;
						case TYP_AGENDA  : $smloc = ($site['URLR'] == 'on' 
                          ? $lng.'-'.$urlrw[17].'-22-'.$fileid.'-'.$fileid.'-1-'.$fieldmail.$z2 
                          : 'agenda.php?lng='.$lng.'&amp;idpg='.$fileid.'-'.'&amp;pg='.$fileid.'&amp;agv=1&amp;tconfig='.$fieldmail.$z2);
                          break;
						}
						$dbw[] = array(
							'LOC'=>$site[3].$smloc, 
							'LM'=>substr($moddate, 0, 4).'-'.substr($moddate, 4, 2).'-'.substr($moddate, 6, 2), 
							'CF'=>$smfreqs[$smtyp], 'PR'=>'0.5');
					}
				}
			}
			if (!empty($dbw)) CreateSitemapFile($smfiles[$smtyp], $dbw); else @unlink($smfiles[$smtyp]);
		}
        $len = strlen(CHEMIN);
		if (file_exists(SM_URLS))    $dbws[] = array('LOC'=>substr(SM_URLS, $len), 'LM'=>date('Y-m-d', filemtime(SM_URLS)));
		if (file_exists(SM_BLOG))    $dbws[] = array('LOC'=>substr(SM_BLOG, $len), 'LM'=>date('Y-m-d', filemtime(SM_BLOG)));
		if (file_exists(SM_ART))     $dbws[] = array('LOC'=>substr(SM_ART, $len), 'LM'=>date('Y-m-d', filemtime(SM_ART)));
		if (file_exists(SM_NEWS))    $dbws[] = array('LOC'=>substr(SM_NEWS, $len), 'LM'=>date('Y-m-d', filemtime(SM_NEWS)));
		if (file_exists(SM_PHOTO))   $dbws[] = array('LOC'=>substr(SM_PHOTO, $len), 'LM'=>date('Y-m-d', filemtime(SM_PHOTO)));
		if (file_exists(SM_DNLOAD))  $dbws[] = array('LOC'=>substr(SM_DNLOAD, $len), 'LM'=>date('Y-m-d', filemtime(SM_DNLOAD)));
		if (file_exists(SM_FAQ))     $dbws[] = array('LOC'=>substr(SM_FAQ, $len), 'LM'=>date('Y-m-d', filemtime(SM_FAQ)));
		if (file_exists(SM_LINKS))   $dbws[] = array('LOC'=>substr(SM_LINKS, $len), 'LM'=>date('Y-m-d', filemtime(SM_LINKS)));
		if (file_exists(SM_GUESTBK)) $dbws[] = array('LOC'=>substr(SM_GUESTBK, $len), 'LM'=>date('Y-m-d', filemtime(SM_GUESTBK)));
		if (file_exists(SM_FORUM))   $dbws[] = array('LOC'=>substr(SM_FORUM, $len), 'LM'=>date('Y-m-d', filemtime(SM_FORUM)));
		if (file_exists(SM_AGENDA))  $dbws[] = array('LOC'=>substr(SM_AGENDA, $len), 'LM'=>date('Y-m-d', filemtime(SM_AGENDA)));
		if (!empty($dbws)) {
			$smtxt = '<?xml version="1.0" encoding="'.$charset.'"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
			foreach($dbws as $sm) {
				$smtxt .= '
  <sitemap>
    <loc>'.$site[3].$sm['LOC'].'</loc>
    <lastmod>'.$sm['LM'].'</lastmod>
  </sitemap>';
			}
			$smtxt .= '
</sitemapindex>';
			WriteFullDB(SITEMAPS, $smtxt);
		}
 	}
	if (file_exists(CHEMIN.'robots.txt')) {
		$robots = file(CHEMIN.'robots.txt');
		$dbr    = array();
		foreach($robots as $line)
			if (substr($line, 0, 8) != 'Sitemap:') $dbr[] = $line;
		if (!empty($dbws)) $dbr[] = 'Sitemap: '.$site[3].substr(SITEMAPS, 3);
		$rh = fopen(CHEMIN.'robots.txt', 'w');
		foreach($dbr as $line) fwrite($rh, $line);
		fclose($rh);
	}
}

function ActionOnFields($action, $data, $reindex = TRUE) {
    global $site,$type,$fileid,$status,$creadate,$moddate,$author,$email,
    $fielda1,$fielda2,$fieldb1,$fieldb2,$fieldc1,$fieldc2,$fieldd1,$fieldd2,
    $fieldweb,$fieldmail,$fieldmod;
	$id = ($action == 'reindex' ? false : $data[AOF_ID]);
    switch ($action) {
    case 'add'  :
    case 'migr' :
        $id = $data[AOF_ID] = IncrNextID();
        AppendDBFields(DOCID, array($data[AOF_TYPE], $id, $data[AOF_STATUS]));
        UpdateDocFromInput($action, $id, $data);
        UpdateDocdates($action, $id, $data[4], $data[3]);
        UpdateChildren($action, $data);
        break;
    case 'mod' :
        UpdateDocFromInput($action, $id, $data);
        UpdateDocdates($action, $id, $data[4], $data[3]);
        break;
    case 'act' :
        UpdateDocid($data[AOF_ID], $data[AOF_STATUS]);
        UpdateDocFromDoc($action, $id, $data[AOF_STATUS]);
        if ('i' == $data[AOF_STATUS]) {
            DeactivateInCascade($data[AOF_TYPE], $id);
        }
        break;
    case 'del' :
        UpdateDocid($id, 'd');
        UpdateDocFromDoc($action, $id, 'd');
        UpdateDocdates($action, $id);
        DeleteInCascade($data[AOF_TYPE], $id);
        break;
    case 'sup' :
        ReadDoc($id);
        DestroyDBFile(DBBASE.TestFileId($id).INCEXT);
        DestroyDBFile(DBBASE.TestFileId($id).DBEXT);
        DestroyDBFile(DBIPBASE.TestFileId($id).DBEXT);
        $dbdocid = IndexDbFieldsByField(ReadDBFields(DOCID), 1);
        unset($dbdocid[$id]);
        WriteDBFields(DOCID, $dbdocid);
        $dbdates = IndexDbFieldsByField(ReadDBFields(DOCDATES), 0);
        unset($dbdates[$id]);
        WriteDBFields(DOCDATES, $dbdates);
        $dbchild = IndexDbFieldsByField(ReadDBFields(CHILDREN), 0);
        unset($dbchild[$id]);
        WriteDBFields(CHILDREN, $dbchild);
        DestroyInCascade($data[AOF_TYPE], $id);
		if ($data[AOF_TYPE] == TYP_ART) DestroyKeywords($id);
        break;
    }
    if ($reindex) {
        switch ($data[0]) {
        case TYP_LINKS     :
        case TYP_PHOTO     :
        case TYP_FAQ       :
        case TYP_DNLOAD    :
        case TYP_RSS       :
        case TYP_BSS       :
        case TYP_AGENDA    :
        case TYP_FREEBOX   :
        case TYP_MENUBOX   :
        case TYP_OPTMENU   :
        case TYP_DDMENUBOX :
        case TYP_OPTDDMENU :
        case TYP_MENUICO   :
		case TYP_SOCNET    :
        case TYP_BRUB      :
            UpdateDBdtb($data[0]);
            break;
        case TYP_ART :
            UpdateDBdtb($data[0]);
            include_once CHEMIN.INCREP."funcrss.php";
            UpdateRSSart();
			UpdateRSSall();
            break;
        case TYP_BLOG :
            UpdateDBdtb($data[0]);
            include_once CHEMIN.INCREP."funcrss.php";
            UpdateRSSblog();
			UpdateRSSall();
            break;
        case TYP_NEWS :
            UpdateDBdtb($data[0]);
            include_once CHEMIN.INCREP."funcrss.php";
            UpdateRSSnews();
			UpdateRSSall();
            break;
        case TYP_REACT :
            UpdateDBreact($action, $id);
            break;
        case TYP_REBLOG :
            UpdateDBreblog($action, $id);
            break;
        case TYP_FORUM :
        case TYP_THREAD :
            UpdateDBforum($action, $id);
            break;
        }
		UpdateSitemaps($data[0]);
    }
    return $id;
}

function InitDBlog($typ) {
    global $site;
    $db = array();
    date_default_timezone_set('UTC');
    switch ($typ) {
    case 1  : $db[0] = array(date('H'), 'H', date('Y-m-d-H '));  break;
    case 12 : $db[0] = array(date('Y'), 'Y', date('Y'));         break;
    case 24 : $db[0] = array(date('D'), 'D', date('Y-m-d'));     break;
    case 31 : $db[0] = array(date('M'), 'M', date('Y-m'));       break;
    default : $db[0] = array(0,         '?', date('Y-m-d-H-i')); break;
    }
    date_default_timezone_set($site['TZ']);
    $db[1] = array_pad(array(), STATS_NBPAGE, 0);
    $db[2] = array_pad(array(), STATS_NBLANGUE, 0);
    $db[3] = array_pad(array(), STATS_NBBROWSER, 0);
    $db[4] = array_pad(array(), STATS_NBOS, 0);
    $db[5] = array_pad(array(), $typ, 0);
    $db[6] = array_pad(array(), $typ, 0);
    $db[7] = array(1);
    return $db;
}

function SaveOldDbLog($db) {
    global $statscfg;
	if (!empty($db[0][1])) {
		switch ($db[0][1]) {
		case 'D' :
		case 'M' :
			if ($statscfg[$db[0][1]] == 'on') {
				WriteDbFields(OLDSSTATSREP.$db[0][2].DBEXT, $db);
			}
			break;
		case 'Y' :
			if ($statscfg[$db[0][1]] == 'on') {
				WriteDbFields(OLDSSTATSREP.$db[0][2].DBEXT, $db);
				$last = substr($db[0][2], 0, 4) - $statscfg['NBY'];
				$files = ExploreFile(OLDSSTATSREP);
				foreach ($files as $file) {
					if (substr($file, 0, 4) <= $last) {
						DestroyDbFile(OLDSSTATSREP.$file);
					}
				}
			}
			break;
		}
	}
}

function sendmail($eTo, $eFrom ,$objet, $contenu, $piece_jointe, $actioncomplete, $destcopy, $eAR = '', $eReply = '', $form1 = '') {  

	global $lang, $lng, $smtp, $user, $charset, $phpmailer, $fdest, $fname, $f2name, $supervision;
	// on crée une nouvelle instance de la classe
	$phpmailer = new PHPMailer(true);
	// puis on l’exécute avec un 'try/catch' qui teste les erreurs d'envoi
  try {
	/* DONNEES SERVEUR */
	#####################

	$phpmailer->setLanguage($lng, 'PHPMailer/language/');   // pour avoir les messages d'erreur en FR
	$phpmailer->SMTPDebug = 0;            // en production (sinon "2")
	if ($smtp[6] == 'on') 
		$phpmailer->SMTPDebug = 2;
		$phpmailer->Debugoutput = function($str, $level) {
		$datjourl = new DateTime('now');
		$dateheureminiutesecl = $datjourl->format('Y_m_d_H');
		$nomficlog = CHEMIN.'data/log/smtp'.'_'.$dateheureminiutesecl.'.log';
		file_put_contents($nomficlog, date('Y-m-d H:i:s'). "\t$level\t$str\n", FILE_APPEND | LOCK_EX);
		};																				   

	$phpmailer->isSMTP();                                                            // envoi avec le SMTP du serveur
	$phpmailer->CharSet    = $charset;
	$phpmailer->Host       = $smtp[0];                          // Serveur Smtp  / sets SMTP server
	$phpmailer->SMTPSecure = $smtp[2];                          // sets the prefix to the server ('', ssl, tls)
	$phpmailer->SMTPAuth   = $smtp[1] == 'on' ? true : false;   // enable SMTP authentication
	$phpmailer->Port       = $smtp[3];                          // N° de port 25 par défaut / set the SMTP port
	$phpmailer->Username   = $smtp[4]; 
	$phpmailer->Password   = gy_decrypt(CRYPT_KEY, $smtp[5]);	// Votre adresse mail / MAIL adress 
	$phpmailer->setFrom($eFrom);  
    if ($eAR != '')
	{
			if ($actioncomplete == 'C')
				$phpmailer->addCustomHeader("Disposition-Notification-To: $eReply");	
			else
				$phpmailer->addCustomHeader("Disposition-Notification-To: $eFrom");	
	}
 	if (!empty($eReply))
	{
		$verifpos = strrpos($eReply, "<");
		$pseudo = '';
		if (strlen($verifpos) !=0)
		{
			$adressemailetpseudo2 = substr($eReply, strripos($eReply, "<") + 1);
			$adressemail = substr($adressemailetpseudo2, 0, strrpos($adressemailetpseudo2, ">"));
			$pseudo = substr($eReply, 0, strrpos($eReply, "<"));
			$phpmailer->AddReplyTo($adressemail,$pseudo);
		}	
		else
			if ($form1 != '')	
				$phpmailer->AddReplyTo($eReply, $form1);
			else
				$phpmailer->AddReplyTo($eReply);
	}
	/* DONNEES DESTINATAIRES */
	##########################

	$fnom = '';
	$fmail0 = '';
	if ($actioncomplete == 'M' || $actioncomplete == 'C' || $actioncomplete == 'E')  // M Pour les plugins ou  E pour emailhtmlto function avec destinataire en autres demandes
	{
		if ($destcopy != '')
		{
			$allCC    = explode(' ', trim($destcopy));
			$nbrencopy = count($allCC);
			for ($i = 0; $i < $nbrencopy; $i++)
			{
				$phpmailer->addCC($allCC[$i]); 
			}
		}
	}
	$allCC    = explode(' ', trim($eTo));
	$nbrencopy = count($allCC);
	if (count($allCC) > 1)
	{
		for ($i = 0; $i < $nbrencopy; $i++)
		{
			$phpmailer->addAddress($allCC[$i]); 
		}
			

	}
	else
	{
		$phpmailer->AddAddress(trim($eTo));
	}
	/* PIECES JOINTES */
	##########################
	if ($piece_jointe != '')
	{
		$nomfichier = explode(' ',$piece_jointe);
		for ($i=0; $i < count($nomfichier)-1; $i++)
		{
			$phpmailer->addAttachment('fic/'.$nomfichier[$i]);         // Pièces jointes en gardant le nom du serveur
		}
	}

	/* CONTENU DE L'EMAIL*/
	##########################
	$phpmailer->isHTML(true);                                      // email au format HTML
	$phpmailer->Subject = mb_convert_encoding($objet,"ISO-8859-1", "UTF-8");      // Objet du message (éviter les accents là, sauf si mb_convert_encoding)
	$phpmailer->Body    = $contenu;          // corps du message en HTML - Mettre des slashes si apostrophes
	// $phpmailer->AltBody = 'Contenu au format texte pour les clients e-mails qiui ne le supportent pas'; // ajout facultatif de texte sans balises HTML (format texte)
    $phpmailer->send();
 
  }
  // si le try ne marche pas > exception ici
  catch (Exception $e) {
    return false;
  }  
} // fin de la fonction sendmail   

function eMailTo($eSubject, $eMessage, $eTo)  {
	global $lng, $lang, $supervision, $user, $charset, $smtp, $actioncomplete;
    eMailHtmlTo($eSubject, "", $eTo, "", $eMessage);
}

function eMailHtmlTo($eSubject, $eMsgHtml, $eTo = '', $eFrom = '', $eMsgTxt = '', $eReply = '')  {
    global $lng, $lang, $supervision, $user, $charset, $smtp, $actioncomplete;
    if (empty($eFrom)) $eFrom = trim($user[1]);
    $eFromF = $eFrom;
    $FromTo = $user[0];
    if ($eMsgTxt == '') {
        $eMsgText = strip_tags(preg_replace("!<br />|<br />|</p>!i", "\n", preg_replace("!<hr>|<hr />!i", "\n \n", $eMsgHtml)));
    }
    if ($eMsgHtml == '') {
        $eMsgHtml = str_replace("\n", "<br />", str_replace("\n \n", "<hr />", $eMsgTxt));
    }

    $eSubject      = strtr($eSubject, array('à'=>'a', 'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e', 'î'=>'i', 'ï'=>'i', 'ô'=>'o', 'û'=>'u', 'û'=>'u'));
    $eSubject      = preg_replace('![^-a-zA-Z0-9_ ]!i', '.', $eSubject);

	$allTo = ($eTo == '' ? explode(' ', $user[1].' '.trim($supervision[10])) : explode(' ', $eTo));
	$allTo = array_unique($allTo);
    $eTo   = (count($allTo) > 0 ? $allTo[0] : $user[1]);
    $cque  = '';
    foreach ($allTo as $adr) if ($adr != $eTo) $cque .= $adr.' ';
    $eAR = '';
	if (!isset($actioncomplete)) $actioncomplete = 'L';
	$efilesattaches = '';
	$destinataireencopy = $cque;
	if ($destinataireencopy != '') $actioncomplete = 'E';
    sendmail($eTo, $eFrom,$eSubject, $eMsgHtml, $efilesattaches, $actioncomplete, $destinataireencopy, $eAR, $eReply);
}

function BreakEMail($eminput) {
    $eminput = trim($eminput);
    $em1 = strpos($eminput,"@");
    $em2 = strrpos($eminput,".");
    $emoutput[0] = substr($eminput,0,$em1);
    $emoutput[1] = substr($eminput,$em1+1,$em2-$em1-1);
    $emoutput[2] = substr($eminput,$em2+1);
    return $emoutput;
}

function StartTimer() {
    global $starttime;
    $starttime = microtime(TRUE);
}

function StopTimer() {
    global $starttime;
    return round ((microtime(TRUE) - $starttime), 2);
}

function MakeSeed() {
    return ((int)microtime(true)*1000000);
}

function GenerateUID() {
	$lolo = array(1=>'abcdefghijklmnopqrstuvwxyz',2=>'ABCDEFGHIJKLMNOPQRSTUVWXYZ',3=>'0123456789',4=>'-*€£§@{}[](),.;!:_°µ|éèçàù');
    $sequ = array(1, 2, 4, 3, 3, 4, 1, 1, 2, 3);
    $len  = array(1=>26, 2=>26, 3=>10, 4=>26);
	$newUID = '';
//    srand((int)microtime()*1000000);
	srand((float)microtime(true)*1000000);
    foreach ($sequ as $seq) {
        $rand = rand(0, $len[$seq] - 1);
        $newUID .= ($seq == 4 && ord($lolo[$seq][$rand]) > 127 ? ':' : $lolo[$seq][$rand]);
    }
    return $newUID;
}

function KeepGoodChars($textin) {
    $textin  = strtr($textin, array(' '=>'_', 'à'=>'a', 'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e', 'î'=>'i', 'ï'=>'i', 'ô'=>'o', 'û'=>'u', 'û'=>'u'));
    $textout = preg_replace("![^-a-zA-Z0-9_]!i","",$textin);
    return $textout;
}

function ValUnique($tableau) {
    if (empty($tableau)) return array();
    for ($i = 0, $n = count($tableau); $i < $n; $i++) {
        $NewTableau[$tableau[$i]] = 1;
    }
    @reset($NewTableau);
	$KeyUnique = array();
    for ($i = 0, $n = count($NewTableau); $i < $n; $i++) {
        $KeyUnique[] = key($NewTableau);
        @next($NewTableau);
    }
    return $KeyUnique;
}

function compare_id1($a, $b) {
    return ($a[1] < $b[1] ? -1 : +1 );
}

function compare_db2($a, $b) {
    if ($a[1] == $b[1]) {
        return ($a[2] < $b[2] ? -1 : +1);
    } else {
        return ($a[1] < $b[1] ? -1 : +1);
    }
}

function GetNavBar(
    $url,
    $maxItem,
    $currentPage = 1,
    $nbItemPage = 10,
    $ancre = '',
    $imgBegin = '',
    $imgPrev = '',
    $imgNext = '',
    $imgEnd = '',
    $imgBeginN = '',
    $imgPrevN = '',
    $imgNextN = '',
    $imgEndN = '',
    $imgMinus = '',
    $imgPlus = '',
    $decade = 10,
	$tri = ''
    )
{
    global  $page, $site, $lng, $web, $serviz, $aml, $wbm;
	if (!is_numeric($currentPage))    $currentPage = 1;
	if (!is_numeric($decade))    $decade = 10;
    if (empty($web)) include_once CHEMIN.INCREP.'lang/'.$lng.'-web'.INCEXT;
    $out = $txtPrevPage = $txtSuivPage = '';
    if ($maxItem > $nbItemPage) {
        $imgBegin  = (empty($imgBegin)  ? '<i class="fas fa-angle-double-left cur" title="'.$web[339].'"></i>' 
                                        : '<img src="'.$imgBegin.'" alt="'.$web[339].'" title="'.$web[339].'" />');
        $imgPrev   = (empty($imgPrev)   ? '<i class="fas fa-angle-left cur" title="'.$web[32].'"></i>' 
                                        : '<img src="'.$imgPrev.'" alt="'.$web[32].'" title="'.$web[32].'" />');
        $imgNext   = (empty($imgNext)   ? '<i class="fas fa-angle-right cur" title="'.$web[34].'"></i>' 
                                        : '<img src="'.$imgNext.'" alt="'.$web[34].'" title="'.$web[34].'" />');
        $imgEnd    = (empty($imgEnd)    ? '<i class="fas fa-angle-double-right cur" title="'.$web[338].'"></i>' 
                                        : '<img src="'.$imgEnd.'" alt="'.$web[338].'" title="'.$web[338].'" />');
        $imgBeginN = (empty($imgBeginN) ? '<i class="fas fa-angle-double-left curN"></i>' 
                                        : '<img src="'.$imgBeginN.'" alt="'.$web[339].'" />');
        $imgPrevN  = (empty($imgPrevN)  ? '<i class="fas fa-angle-left curN"></i>' 
                                        : '<img src="'.$imgPrevN.'" alt="'.$web[32].'" />');
        $imgNextN  = (empty($imgNextN)  ? '<i class="fas fa-angle-right curN"></i>' 
                                        : '<img src="'.$imgNextN.'" alt="'.$web[34].'" />');
        $imgEndN   = (empty($imgEndN)   ? '<i class="fas fa-angle-double-right curN"></i>' 
                                        : '<img src="'.$imgEndN.'" alt="'.$web[338].'" />');
        $imgMinus  = (empty($imgMinus)  ? '<i class="far fa-minus-square imgMinusW" title="'.$txtPrevPage.'"></i>' 
                                        : '<img src="'.$imgMinus.'" alt="'.$txtPrevPage.'" title="'.$txtPrevPage.'" class="imgMinusW" />');
        $imgPlus   = (empty($imgPlus)   ? '<i class="far fa-plus-square imgPlusW" title="'.$txtSuivPage.'"></i>' 
                                    : '<img src="'.$imgPlus.'" alt="'.$txtSuivPage.'" title="'.$txtSuivPage.'" class="imgPlusW" />');
        $nbPage = ($nbItemPage > 0 ? (ceil($maxItem/$nbItemPage)) : 1);
        $out .= '<div class="text-center">';
        $out .= '<div class="table-responsive-md"><table class="text-center m-auto"><tr>';
        if ($currentPage > 1) {
            $out .= '<td class="px-2 navbarLeftW"><a href="'.$url.'1'.$ancre.'">'.$imgBegin.'</a></td>';
            $out .= '<td class="px-2 navbarLeftW"><a href="'.$url.($currentPage-1).$ancre.'">'.$imgPrev.'</a></td>';
        }
        else {
            $out .= '<td class="px-2 navbarLeftW">'.$imgBeginN.'</td>';
            $out .= '<td class="px-2 navbarLeftW">'.$imgPrevN.'</td>';
        }
        $out .= '<td class="px-3 navbarLeftW">';
        $nbpg = 1;
        $pgDebut = (int)floor((float)($currentPage / $decade)) * $decade;
        $pgFin = (int)ceil((float)($currentPage / $decade)) * $decade;
        if ($pgDebut == $pgFin) {
            $pgDebut = $pgFin - $decade;
        }
        if ($currentPage > $pgDebut && $currentPage <= $pgFin) {
            $decadeDebut = $pgDebut;
            $decadeFin   = $pgFin;
            $paq = ($nbPage >= $decadeFin ? $decade : $nbPage - $decadeDebut);
        }
        if ($currentPage > $decade && $decadeDebut < $nbPage) {
            if ($nbPage - $decade + $decadeFin > $decade) {
                $nbPrevPage = $decade;
            }
            else {
                $nbPrevPage = $nbPage - $decade + $decadeFin;
            }
            if ($nbPrevPage > 1) {
                $txtPrevPage = $nbPrevPage." ".$web[340];
            }
            else {
                $txtPrevPage = $nbPrevPage." ".$web[341];
            }
            $out .= '<a href="'.$url.($currentPage + $decadeDebut - $decadeFin).$ancre.'">'.$imgMinus.'</a>';
        }
        $out .= ' [ ';
        for($i = 1; $i <= $paq; $i++) {
            if ($i + $decadeDebut <> $currentPage) {
                $out .= '<a href="'.$url.($i + $decadeDebut).$ancre.'">'.($nbpg + $decadeDebut).'</a> ';
            } else {
                $out .= '
<style>
  .currNum {
      text-decoration: underline;
      font: bold '.$page[2].' '.$page[1].';
  }
</style>
                <span class="currNum">'.($nbpg + $decadeDebut).'</span> ';
            }
            $nbpg++;
        }
        $out .= '] ';
        if($currentPage <= $decadeFin && $decadeFin < $nbPage) {
            if($nbPage - $decade - $decadeDebut > $decade) {
                $nbSuivPage = $decade;
            }
            else {
                $nbSuivPage = $nbPage - $decade - $decadeDebut;
            }
            if ($nbSuivPage > 1) {
                $txtSuivPage = $nbSuivPage." ".$web[337];
            }
            else {
                $txtSuivPage = $nbSuivPage." ".$web[336];
            }
            $out .= '<a href="'.$url.$tri.($i + $decadeDebut).$ancre.'">'.$imgPlus.'</a>';
        }
        $out .='</td>';
        if ($currentPage < $nbPage) {
            $out .= '<td class="px-2 navbarRightW"><a href="'.$url.($currentPage + 1).$ancre.'">'.$imgNext.'</a></td>';
            $out .= '<td class="px-2 navbarRightW"><a href="'.$url.$nbPage.$ancre.'">'.$imgEnd.'</a></td>';
        }
        else {
            $out .= '<td class="px-2 navbarRightW">'.$imgNextN.'</td>';
            $out .= '<td class="px-2 navbarRightW">'.$imgEndN.'</td>';
        }
        $out .= '</tr></table></div></div>';
    }
    return $out;    
}

function Formatage($format, $in) {
    $out = '';
    if (!empty($in) && !empty($format)) {
        $out = str_replace(' ', ' ', sprintf($format, $in));
    }
    return $out;
}
function Controlimport($varacontroler)
{
	global $admin;
	global $charset;
	$varacontrole = '';	
	$handle = fopen(MOTSINTERDITS, "r");
	$size = filesize(MOTSINTERDITS);
	$motdenied = array();
	if ($size !== false)    
	{
		$motdeniedlect  = fread($handle, filesize(MOTSINTERDITS));
		$motdenied = explode("\n", $motdeniedlect);
	}

	$datjourl = new DateTime('now');
	$dateheureminiutesecl = $datjourl->format('Y_m_d');
	$nomficlog = CHEMIN.'data/log/controlimport'.'_'.$dateheureminiutesecl.'.log';
			
	if (is_array($varacontroler))
	{
		foreach ($varacontroler as $varacontrole2) {
			if (is_array($varacontrole2)) 
			{
				foreach ($varacontrole2 as $varacontrole3) {
					$v3 = htmlspecialchars($varacontrole3, ENT_QUOTES, $charset);
					$v3 = strip_tags($v3);
					for ($ij=0; $ij < count($motdenied); $ij++)
					{					
						$pos = strpos($v3, trim($motdenied[$ij]));
						if ($v3 == '' && $pos !== false)
						{
							$v3 = '';
						}
						
					}
				}
			}
			else
			{
				if ($varacontrole2 == null) $varacontrole2 = '';
				$varacontrole2 = htmlspecialchars($varacontrole2, ENT_QUOTES, $charset);
				$varacontrole2 = strip_tags($varacontrole2);
				for ($ij=0; $ij < count($motdenied); $ij++)
				{
					if (!empty($motdenied[$ij]))
					{
						$pos = strpos($varacontrole2, trim($motdenied[$ij]));
						if ($varacontrole2 == '' && $pos !== false)
						{
							$varacontrole2 = '';
						}
					}

				}
				
			}
		}
		$varacontrolerspecial = $varacontroler;
	}
	else
	{
		$varacontrolerspecial = htmlspecialchars($varacontroler, ENT_QUOTES, $charset);
		$varacontrolerspecial = strip_tags($varacontrolerspecial);
		if ($varacontrolerspecial != '' && $varacontrolerspecial != null )
		{
			for ($ij=0; $ij < count($motdenied); $ij++)
			{
				$pos = substr_count(mb_strtolower($varacontrolerspecial, $charset), mb_strtolower(trim($motdenied[$ij]), $charset));
				if ($varacontrolerspecial != '' && $pos != 0)
				{
					$varacontroler = '';
				}
				
			}
		}
	}
	return $varacontroler;
}
function import($name, $origine = '', $striptags = TRUE, $value='') {
    switch ($origine) {
    case 'POST' :
        $var = (isset($_POST[$name]) ? (empty($_POST[$name]) && $value != '' ? $value : $_POST[$name]) : $value);
        break;
    case 'GET':
        $var = (isset($_GET[$name]) ? (empty($_GET[$name]) && $value != '' ? $value : $_GET[$name]) : $value);
        break;
    default :
        $var = (isset($_POST[$name]) 
          ? (empty($_POST[$name]) && $value != '' ? $value : $_POST[$name]) 
          : (isset($_GET[$name]) ? (empty($_GET[$name]) && $value != '' ? $value : $_GET[$name]) : $value));
    }
	if (is_array($var)) $striptags = TRUE;

    if (is_array($var) && $striptags) {
        foreach ($var as &$v1) {
            if (is_array($v1)) {
                foreach ($v1 as $v2) {
                    if (is_array($v2)) {
                        foreach ($v2 as $v3) {
							$v3 = Controlimport($v3);
                        }
                    } else {
						$v2 = Controlimport($v2);                        
                    }
                }
            } else {
					$v1 = Controlimport($v1);                
            }
        }
        return $var;
   } else {
			
			$calpos = strpos($var, '<iframe');
			if ($calpos !== false)
			{
				$calpos = strpos($var, 'allowfullscreen=""');
				if ($calpos !== false) 
					$var = str_replace('allowfullscreen=""', 'allowfullscreen', $var);
			}
			if ($var != '')
			{
				$var = Controlimport($var);
				return ($striptags ? strip_tags($var) : $var);
			}
			else
				return ($var = Controlimport($var));
    }
}

function IncrNextID() {
    $nextidbk = ReadCounter(NEXTIDBK);
    $nextid   = ReadCounter(NEXTID) + 1;
    if ($nextid < $nextidbk) {
        $nextid = $nextidbk + 10;
    }
    while (file_exists(DBBASE.TestFileId($nextid).INCEXT)) {
        $nextid++;
    }
    WriteCounter(NEXTID, $nextid);
    if ($nextid > $nextidbk) {
        WriteCounter(NEXTIDBK, $nextid);
    }
    return $nextid;
}

function AsciiCompare($left, $right) {
    return @strcasecmp((string)$left, (string)$right);
}

function SearchOption($serviz_actif, $members_inactif, $typ_code, $typ_nom) {
    global $userprefs, $members;
    if (!$serviz_actif) return;
    if ($userprefs[1] != '' || $members_inactif) {
        return '
        <option value="'.$typ_code.'">'.$typ_nom.'</option>';
    }
}

function AddCHEMIN($dir) {
    if (empty(CHEMIN)) return $dir;
    $pos = strpos($dir, CHEMIN);
    if (0 === $pos) {
        return $dir;
    } else {
        return CHEMIN.$dir;
    }
}

function ExploreDir($dir) {
    $dir = AddCHEMIN($dir);
    $array = array();
    if (is_dir($dir)) {
        $dossier = opendir($dir);
        while ($fichier = readdir($dossier)) {
            if ($fichier != "." && $fichier != ".." && is_dir($dir.$fichier) ) {
                $array[] = $fichier;
            }
        }
        closedir($dossier);
    }
    sort($array);
    return $array;
}

function ExploreFile($dir) {
    $dir = AddCHEMIN($dir);
    $array = array();
    $dossier = opendir($dir);
    while ($fichier = readdir($dossier)) {
        if (is_file($dir.$fichier) && $fichier != 'index.php') {
            $array[] = $fichier;
        }
    }
    closedir($dossier);
    sort($array);
    return $array;
}

function ExploreImg($dir) {
    $dir = AddCHEMIN($dir);
    $array = array();
    $dossier = opendir($dir);
    while ($fichier = readdir($dossier)) {
        if (is_file($dir.$fichier) && $fichier != 'index.php') {
            $path_parts = basename($fichier);
            $path_parts = substr($path_parts,strrpos($path_parts,".")+1);
            if (IsImage($path_parts)) {
                $array[] = $fichier;
            }
        }
    }
    closedir($dossier);
    sort($array);
    return $array;
}

function ForceToAbsolute_callback($m) {
    global $site;
    if (preg_match('!^(http|https|ftp|mailto)s?:!', $m[2])) {
        return $m[0];
    } else {
        if (strpos('#', $m[2]) === 0 ) {
            return $m[0];
        } else {
            return $m[1].$site[3].$m[2];
        }
    }
}

function ForceToAbsolute($s) {
    $s = preg_replace_callback('!( src=")([^"]*")!', 'ForceToAbsolute_callback', $s);
    $s = preg_replace_callback('!( href=")([^"]*")!', 'ForceToAbsolute_callback', $s);
    return $s;
}

function ReadFrSigns() {
    $tmp = parse_ini_file(FRSIGNS_INI, TRUE);
    ksort($tmp);
    return $tmp;
}

function ReplaceFrSigns($sign) {
	$sign    = str_replace(array(chr(10), chr(13)), '<br />', $sign);
	$sign = str_replace('<br />', ' sautligne ', $sign);
	$listesign = explode(' ',$sign);;
    $frsigns = ReadFrSigns();
	$countsign = count($listesign);
	$codesignimage = array();
	foreach ($frsigns as $code => $frsign) 
	{
		if ('a' == $frsign['status']) 
		{
			if ('i' == $frsign['origine']) 
			{

				$codesignimage[$code] = $frsign['image'];

			}
		}
	}
	$signcomplet = '';
	for ($i=0 ; $i < $countsign; $i++)
	{
		if ($listesign[$i] != '')
		{	
			
			if (array_key_exists($listesign[$i], $codesignimage))
			{
				$codecomplet = $codesignimage[$listesign[$i]];

				$signcomplet .= '<img src="'.FRSIGNS_IMG.$codecomplet.'" alt="" title="" /> ';
			}
				else $signcomplet .= $listesign[$i].' ';
		}
	}
    $signcomplet = str_replace(' sautligne ','<br />', $signcomplet);
    return $signcomplet;
}

function SelectDBThreadByThread($Fields, $id) {
    $DataDB = array();
    foreach ($Fields as $Field) {
        if ($Field[1] == $id && $Field[2] != '0') {
            $DataDB[] = $Field;
        }
    }
    return $DataDB;
}

function SelectDBForumByID($Fields, $id) {
    $DataDB = array();
    foreach ($Fields as $Field) {
        if ($Field[2] == $id) {
            $DataDB[] = $Field;
        }
    }
    return $DataDB;
}

function SelectDBForumByCat($Fields, $cat) {
    $DataDB = array();
    foreach ($Fields as $Field) {
		$temp = explode(',', $Field[12]);
        if ($cat == $temp[0]) {
            $DataDB[] = $Field;
        }
    }
    return $DataDB;
}

function SubmitButton($title, $onclick = '', $left = '', $right = '', $typ = 'submit', $disabled='', $classbutton='btn btn-info') {
    global $boutonleft, $boutonright;
    $onclick = (empty($onclick) ? ''           : ' onclick="'.$onclick.'"');
    $left    = (empty($left)    ? $boutonleft  : $left);
    $right   = (empty($right)   ? $boutonright : $right);
    return $left.'<button type="'.$typ.'" class="'.$classbutton.'" '.$disabled.' title="'.$title.'"'.$onclick.'>'.$title.'</button>'.$right;
}

function ImgSubmitButton($title, $onclick = '', $left = '', $right = '', $background = '') {
    return $left.'" style="vertical-align:top;" alt="" />'.
'<button type="submit" style="background:transparent url('.$background.
') repeat-x;  height:22px; margin:0; padding:0; border-radius:0; cursor:pointer;" title="'.$title.'">
'.$title.'</button><img src="'.$right.'" style="vertical-align:top;" alt="" />';
}
function ImgSubmitButtonOnclick($title, $onclick = '', $left = '', $right = '', $background = '') {
	$onclick = (empty($onclick) ? ''           : ' onclick="'.$onclick.'"');
    return 
'<button type="submit" style="background:transparent url('.$background.
') repeat-x;  height:22px; margin:0; padding:0; border-radius:0; cursor:pointer;" title="'.$title.'"'.$onclick.'">
'.$title.'</button><img src="'.$right.'" style="vertical-align:top;" alt="" />';
}

function JavascriptFile($file) {
    return '
<script src="'.$file.'"></script>';
}

function BeginJavascript() {
    return '
<script>
';
}

function EndJavascript() {
    return '
</script>
';
}

function ExecFunctionBox($pos, $box) {
    global $lng, $lang, $members, $nom, $page, $site, $serviz, $userprefs,
        $endurl, $npage, $pgtype, $topmess, $skn_logo, $flag1, $flag2,
        $tabcounter, $plugin_admin_name, $plugin_bar_name;
    $out = FALSE;
    if (!empty($box)) {
        $temp = explode(CONNECTOR, $box);
        $incl = (isset($temp[0]) ? $temp[0] : '');
        $fnct = (isset($temp[1]) ? $temp[1] : '');
        $args = (isset($temp[2]) ? $temp[2] : '');
        if (!empty($incl) && is_file(CHEMIN.$incl.INCEXT)) {
            include_once CHEMIN.$incl.INCEXT;
        }
        if (!empty($fnct)) {
            if (empty($args)) {
                $args = array_pad(array(), 5, '');
            } else {
                $args = (FALSE === strpos($args, ',') ? array($args) : explode(',', $args));
                $args = array_pad($args, 5, '');
            }
            $out = $fnct($pos, $args);
        }
    }
    return $out;
}

function Checked($bool) {
    return ($bool ? ' checked="checked"' : '');
}

function Selected($bool) {
    return ($bool ? ' selected="selected"' : '');
}

function IsNotEmptyTitle($var) {
    global $lng, $lang;
    return ($lng == $lang[0] ? !empty($var[2]) : !empty($var[3]));
}

function BreakIRC($ircinput) {
    $ircinput = trim($ircinput);
    $idirc = explode('@', $ircinput, 2);
    $ircoutput[0] = $idirc[0];
    $idtemp = explode('.', $idirc[1], 9);
    $ircoutput[1] = $idtemp[0];
    $ircoutput[2] = $idtemp[1];
    return $ircoutput;
}
    
// Utilisé pour trouver l'ID à partir du numéro de sujet (évite les &thrd=123 remplacé par pg=234)
function GetThreadId($thrd, $file = DBTHREAD) {
    $db = ReadDBFields($file);
    foreach ($db as $dbs) {
        if ($dbs[1] == $thrd && 0 == $dbs[2]) {
            return $dbs[3];
        }
    }
    return FALSE;
}

function getThreadName($thrd) {
	$db = ReadDBFields(DBFORUM);
    foreach ($db as $dbs) {
        if ($dbs[1] == $thrd) {
            return $dbs[5];
        }
    }
    return FALSE;
}

// Utilisé pour éviter des urls contenant &cat=&id=
function GetOptionnalArg($name, $value = '') {
    if (empty($value)) return '';
    return '&amp;'.$name.'='.$value;
}

// Utilisé avec array_reduce($array , 'EstNonVide') pour savoir si le tableau est vide
function EstNonVide ($r, $i) {
    return $r && empty($i);
}

function legend($legend) {
	return '<legend class="legend">'.$legend.'</legend>';
}

function getForumCat() {
    static $db_frcat = NULL;
    if (!isset($db_frcat)) {
        $frcat = ReadDBFields(DBFORUMCAT);
        $db_frcat = array();
        foreach ($frcat as $frc) {
            if (FALSE !== strpos($frc[0], ',')) {
                $temp = explode(',', $frc[0]);
                $db_frcat[$temp[0]] = $frc;
            } else {
                $db_frcat[$frc[0]] = $frc;
            }
        }
    }
    return $db_frcat;
}

function getForumCatName($cat) {
    global $lng, $lang;
    $cat = getForumCatId($cat);
    $db_frcat = getForumCat();
    if (isset($db_frcat[$cat])) {
        return ($db_frcat[$cat][$lng == $lang[0] ? 1 : 2]);
    } else {
        return '--unknown--';
    }
}

function getForumCatFull($cat) {
    $cat = getForumCatId($cat);
    $db_frcat = getForumCat();
    if (isset($db_frcat[$cat])) {
        return $db_frcat[$cat][0];
    } else {
        return $cat;
    }
}

function getForumCatId($cat) {
    if (FALSE !== strpos((string)$cat, ',')) {
        $temp = explode(',', (string)$cat);
        $cat = $temp[0];
    }
    return $cat;
}

function AddPluginBox($label= '', $include = NULL, $function = '', $accept = 'LR', $args = '') {
    global $boxes;
    if ('' == $label || '' == $function) return FALSE;
    $boxes[] = array(
        'label' => $label,
        'include' => $include,
        'accept' => $accept,
        'function' => $function,
        'args' => $args
        );
    return TRUE;
}

function checkUserWebsiteUrl($url) {
	$url = trim($url);
	if (preg_match('/(ftp|http|https):\/\/[a-z0-9à-ö-]+(.[a-z0-9à-ö-]+)*(:[0-9]+)?(\/.*)?$/i', $url)) {
		return $url;
	}
    return 'https://';    
}

function checkEmail($email) {
    $email = trim($email);
    return (filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '');
}

function displayWriteEmail($org, $author, $email, $img='', $class='') {
	global $web, $meskin, $user;
    $lbl   = ($img == '' ? ' <b>'.addslashes($author).'</b>' : $img);
    $class = (empty($class) ? $org : $class);
    $em    = BreakEMail($email);
    $out   = '
        <a data-target="#'.$class.'" data-toggle="modal" class="'.$class.' c-pointer" title="'.$web[173].' '.$user[0].'"> '.$lbl.'</a>
      <style>
      .modal-header { padding: 15px; }
      </style>
      <div class="modal fade" id="'.$class.'" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h2>'.$web[173].' '.$author.'</h2>
              <button class="close" data-dismiss="modal" type="button"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
              <p class="text-center popupMail">'.$web[644].'</p>
              <p class="text-center popupMail">
                <a href="javascript:showEmails'.$org.'(\''.$em[0].'\',\''.$em[1].'\',\''.$em[2].'\');" 
                   title="'.$author.'" class="text-center popupMail">'.$author.'</a>
              </p>
              <p class="text-center popupMail"><b><span id="lblemail'.$org.'">(--------------)</span></b></p>
            </div>
          </div>
        </div>
      </div>
      <script>
      function showEmails'.$org.'(em1, em2, em3) {
        var emailFinal = "<a href=\'mailto:" + em1 + "@" + em2 + "." + em3 + "\'>" + em1 + "@" + em2 + "." + em3 + "</a>";
        document.getElementById("lblemail'.$org.'").innerHTML = emailFinal;
      }
      </script>';
	return $out;
}

function getLabel($lbl, $idx=0) {
	if ($lbl == '' || $idx > 1) return '';
	$db = explode(CONNECTOR, $lbl);
	if (empty($db[$idx])) $db[$idx] = $db[0];
	return trim($db[$idx]);
}

function displayErrorMsg($label, $msg) {
	$sto = str_replace('<li>', '- ', str_replace('</li>', '\n', $msg));
	$out = '
<noscript>
  <fieldset class="bord dispError">
    <div dispError2>'.$label.'</div>
    <ul class="text-start">'.$msg.'</ul>
  </fieldset>
</noscript>'.
BeginJavascript().'
sto = "'.$label.'\n\n" + "'.$sto.'";
alert(sto);
'.EndJavascript();
	return $out;
}

function DestroyKeywords($id) {
	$db = ReadDBFields(DBKEYWORDS);
	if (!empty($db)) {
		sort($db);
		$n = count($db);
		$j = 0;
		while ($db[$j][0] != $id && $j < $n - 1) $j++;
		for ($i = $j; $i < $n; $i++) {
			if ($db[$i][0] == $id) unset($db[$i]); else break;
		}
		WriteDBFields(DBKEYWORDS, $db);
	}
}

function UpdateKeywords($pg, $t1, $t2='', $k1='', $k2='', $tc=0) {
	$t1   = strip_tags($t1);
	$t2   = strip_tags($t2);
	$k1   = (strtolower(substr($k1, -1) == ';' ? substr($k1, 0, -1) : $k1));
	$k2   = (strtolower(substr($k2, -1) == ';' ? substr($k2, 0, -1) : $k2));
    $atc  = explode('|', $tc);
	$db   = ReadDBFields(DBKEYWORDS);
	$i    = 0;
	$find = false;
	foreach ($db as $dbs) {
		if ($pg == $dbs[0]) {
			$db[$i][1] = $t1;
			$db[$i][2] = $t2;
			$db[$i][3] = $k1;
			$db[$i][4] = $k2;
			$db[$i][5] = $atc[0];
			$find      = true;
			if (trim($k1.$k2) == '') unset($db[$i]);
			break;
		}
		$i++;
	}
	if (!$find) {
		$i         = count($db);
		$db[$i][0] = $pg;
		$db[$i][1] = $t1;
		$db[$i][2] = $t2;
		$db[$i][3] = $k1;
		$db[$i][4] = $k2;
		$db[$i][5] = $atc[0];
	}
	WriteDBFields(DBKEYWORDS, $db);
}

function GetKeywords($pg) {
	global $lang, $lng, $site, $urlrw, $z2, 
	$fileid,$status,$creadate,$moddate,$author,$email,
    $fielda1,$fielda2,$fieldb1,$fieldb2,$fieldc1,$fieldc2,$fieldd1,$fieldd2,
    $fieldweb,$fieldmail,$fieldmod;
	$keywords = '';
	$dbks     = ReadDBFields(DBKEYWORDS);
	$dbw      = array();
	foreach ($dbks as $dbk) {
		if ($pg == $dbk[0]) {
			$dbw = (explode(';', ($lng == $lang[0] ? $dbk[3] : $dbk[4])));
			break;
		}
	}
	if (!empty($dbw)) {
		$dbwords = array();
		$i       = 0;
		$j       = -1;
		foreach ($dbks as $dbk) {
			if ($pg == $dbk[0]) continue;
			$kws = (explode(';', ($lng == $lang[0] ? $dbk[3] : $dbk[4])));
			foreach ($kws as $kw) {
				$dbwords[$i][0] = $kw;
				$dbwords[$i][1] = $dbk[0];
				$dbwords[$i][2] = ($lng == $lang[0] ? $dbk[1] : $dbk[2]);
				$dbwords[$i][3] = (empty($dbk[5]) ? 0 : $dbk[5]);
				$i++;
			}
		}
		sort($dbwords);
		$wexist = false;
		$words  = '';
		$i      = count($dbwords);
		foreach ($dbw as $word) {
			$kw = ' ';
			$nk = 0;
			if (!empty($dbwords)) {
				$j = 0;
				while ($dbwords[$j][0] != $word && $j < $i - 1) $j++;
				for ($k = $j; $k < $i; $k++) {
					if ($dbwords[$k][0] != $word) break;
					ReadDoc($dbwords[$k][1]);
					if (isAuthorizedPublication($creadate) && isAccessGranted($fieldmod) && $status == 'a') {
						$dbwo = ReadDBFields(DBOPTMENU);
						foreach ($dbwo as $item) {
							ReadDoc($item[4]);
							if (!isAuthorizedPublication($creadate) || !isAccessGranted($fieldmod)) continue;
							$urls = (explode('||', $lng == $lang[0] ? $fieldd1 : $fieldd2));
							$pg   = substr($urls[0], strpos($urls[0], 'pg=') + 3);
							$pos  = strpos($pg, '&');
							$pg   = ($pos === false ? $pg : substr($pg, 0, $pos));
							if ($pg == $dbwords[$k][1]) {
								$url0 = 'articles.php?lng='.$lng.'&pg='.$pg.'&mnuid='.$item[5].'&tconfig='.$fieldmail.$z2;
								$url1 = $lng.'-'.$urlrw[20].'-103-'.$pg.'-'.$item[5].'-'.$fieldmail.$z2;
								$url = ($site['URLR'] == 'on' ? $url1 : $url0);
								$kw .= '<a href="'.$url.'">'.$dbwords[$k][2].'</a><br />';
								$nk++;
								$wexist = true;
								break;
							}
						}
					}
				}
			}
			$kw = (trim($kw) != '' ? '<div>'.$kw.'</div>' : '');
			if ($nk > 5) $class = 'keyword3';
			elseif ($nk > 3) $class = 'keyword2';
			elseif ($nk > 1) $class = 'keyword1';
			else $class = 'keyword0';
			if (trim($word) != '' && $nk > 0) {
				if ($kw != '') 
					$word = '
	  <li class="dir"><span class="'.$class.'">'.$word.'</span><ul><li>'.$kw.'</li></ul></li>';
				else
					$word = '
	  <li><span class="'.$class.'">'.$word.'</span></li>';
				$words .= $word;
			}
		}
		if ($words != '')
			$keywords = '
	<ul class="keyword keyword-horizontal">
	  '.$words.'
	</ul>';
	}
	return $keywords;
}

function displayQuickConfig($pos, $id = false, $edit = '', $desact = '', $del = '', $drt = false) {
	global $lng, $page, $web, $userprefs, $serviz, $drtuser;
    $drtuser[42] = ($serviz[31] == $userprefs[1] || $serviz[176] == $userprefs[1] ? 'admin' : '');
    if (is_file(CHEMIN.'admin/'.REDACREP.$userprefs[1].INCEXT)) include CHEMIN.'admin/'.REDACREP.$userprefs[1].INCEXT;
    $isdrt  = ($drt ? (@$drtuser[$drt] == 'on') : false);
    $id     = TestFileID($id);
    if ($drtuser[42] != 'admin' && $id !== FALSE && FileDBExist(DBBASE.$id.INCEXT)) {
        include(DBBASE.$id.INCEXT);
        $temp   = explode('|', $author);
        $author = $temp[0];
        $grpc   = (isset($temp[1]) ? $temp[1] : '');
        $access = ($grpc == '' ? ($author == $userprefs[1]) : isAccessGrantedCol($grpc));
    }
    $access = true;
    if ($serviz[32] == "on" && ($drtuser[42] == 'admin' || $isdrt)) {
        $config = ($pos == BOX_EDITO ? 'config3' : 'config6');
        $img    = ($pos == BOX_EDITO ? 'cfgaccueil' : 'cfgboites');
        $txt    = ($pos == BOX_EDITO ? $web[686] : $web[689]);
        $ret = '
    <div class="float-right mr-2">';
        $ret .= (!$access || in_array($drtuser[42], array('redac1', 'redac2')) || is_null($pos) ? '' : '
      <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg='.$config.'&amp;selskin='.$page[14].'">
        <i class="gen-'.$img.' align-middle mr-2" title="'.$txt.'"></i>
      </a>');
        $ret .= (!$access || $edit == '' ? '' : '
      <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg='.$edit.'">
        <i class="far fa-edit align-middle mr-2" style="font-size: 1.4rem;" title="'.$web[308].'"></i>
      </a>');
        $ret .= (!$access || $desact == '' ? '' : '
      <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg='.$desact.'">
        <i class="fas fa-power-off text-danger align-middle mr-2" style="font-size: 1.4rem;" title="'.$web[333].'"></i>
      </a>');
        $ret .= (!$access || $del == '' ? '' : '
      <a href="'.CHEMIN.'admin/admin.php?lng='.$lng.'&amp;pg='.$del.'">
        <i class="fas fa-times text-danger align-middle mr-2" title="'.$web[324].'"></i>
      </a>');
        $ret .= '
    </div>';
    } else
        $ret = '';
    return $ret;
}

function getNewUpdateDoc($crea, $mod, $accept='off') {
	global $userprefs, $web, $serviz;
	if ($accept == 'off' || ($accept == 'mbr' && $userprefs[1] == '') || $serviz[135] <= 0) return '';
	$img     = '';
	$ctrlday = date('Ymdhi', strtotime('-'.$serviz[135].' day'));
	if ($mod > $crea && $mod > $ctrlday) {
        $img = 'majdoc';
        $cls = 'text-primary';
    }
	if ((intval($mod) - intval($crea)) == 0 && $crea > $ctrlday) {
        $img = 'newdoc';
        $cls = 'text-danger';
    }
	$title = ($img == 'majdoc' ? $web[196] : $web[371]);
	return (empty($img) ? '' : '<i class="fas fa-eye '.$cls.' blink align-middle" title="'.$title.'"></i>&nbsp;');
}

function setURLrewriting($mode) {
	global $urlrw, $site;
	if (!is_file(CHEMIN.'.htaccess')) return;
	$stop     = '# URL rewriting'.chr(13).chr(10);
	$htaccess = file(CHEMIN.'.htaccess');
	switch ($mode) {
	case 'on' :
		if (in_array($stop, $htaccess)) break;
		$uheader = file(CONFIGREP.'uheader.dtb');
		$urules  = file(CONFIGREP.'urules.dtb');		
		for ($i = 0; $i < count($urules); $i++) {
			$pos = strpos($urules[$i], '##');
			$st = ($pos === false ? '' : substr($urules[$i], $pos + 2));
			$st = ($st == '' ? '' : substr($st, 0, strpos($st, '##')));
			if ($st != '') $urules[$i] = str_replace('##'.$st.'##', $site[$st], $urules[$i]);
			$pos = strpos($urules[$i], '#');
			$st = ($pos === false ? '' : substr($urules[$i], $pos + 1));
			$st = ($st == '' ? '' : substr($st, 0, strpos($st, '#')));
			if ($st != '') $urules[$i] = str_replace('#'.$st.'#', $urlrw[$st], $urules[$i]);
		}
        if ($htaccess[count($htaccess) - 1] == chr(13).chr(10)) unset($htaccess[count($htaccess) - 1]);
		$htaccess = array_merge($htaccess, $uheader, $urules);
		$dir      = CONFIGREP.'rules/';
		if (is_dir($dir)) {
			$files = scandir($dir);
			if ($files !== false) {
				foreach ($files as $file) {
					$info = pathinfo($file);
					if (isset($info['extension']) && $info['extension'] == 'dtb') {
						$rules    = file($dir.$file);
						$htaccess = array_merge($htaccess, $rules);
						unset($rules);
					}
				}
				unset($allrules);
			}
		}		
		$st = '';
		foreach ($htaccess as $hta) $st .= $hta;
		$perms = substr(sprintf('%o', fileperms(CHEMIN.'.htaccess')), -4);
		SetChmod(CHEMIN.'.htaccess');
		$handle = fopen(CHEMIN.'.htaccess', 'w');
		fwrite($handle, $st);
		fclose($handle);
		SetChmod(CHEMIN.'.htaccess', $perms);
		break;
	case '' :
	case 'off' :
		if (!in_array($stop, $htaccess)) break;
		$st = '';
		foreach ($htaccess as $hta)
			if ($hta != $stop) $st .= $hta; else break;
		$perms = substr(sprintf('%o', fileperms(CHEMIN.'.htaccess')), -4);
		SetChmod(CHEMIN.'.htaccess');
		$handle = fopen(CHEMIN.'.htaccess', 'w');
		fwrite($handle, $st);
		fclose($handle);
		SetChmod(CHEMIN.'.htaccess', $perms);
		break;
	}
}

function isAuthorizedPublication($pdate) {
	if ($pdate != '')
	{
		$datespub = explode('-', $pdate);
		$datenow  = GetCurrentDateTime();
		return ($datespub[0] <= $datenow) && (empty($datespub[1]) ? true : $datespub[1] > $datenow);
	}
	else
		return true;			  
}

function GetListCollaboratorEmail($typ) {
	global $drtuser;
	$drt    = array(TYP_NEWS => 14, TYP_BLOG => 39, TYP_REBLOG => 40, TYP_GUESTBK => 22, TYP_FORUM => 23, TYP_REACT => 16);
	$drtsv  = array(TYP_NEWS => 2,  TYP_BLOG => 3,  TYP_REBLOG => 4,  TYP_GUESTBK => 5,  TYP_FORUM => 6,  TYP_REACT => 1);
    $dbcoll = array();
	$dbwork = @scandir(CHEMIN.'admin/'.REDACREP);
    foreach($dbwork as $file) {
		$info = pathinfo($file);
		if (isset($info['extension']) && $info['extension'] == 'inc') {
			$coll = $info['filename'];
            if (is_file(CHEMIN.'admin/'.REDACREP.$coll.INCEXT)) {
                include CHEMIN.'admin/'.REDACREP.$coll.INCEXT;
                if ($drtuser[$drt[$typ]] == 'on' && $drtusersv[$drtsv[$typ]] == 'on' && file_exists(MSGREP.$coll.DBEXT)) {
                    $userdb = ReadDBFields(MSGREP.$coll.DBEXT);
                    $dbcoll[] = $userdb[1][3];
                }
            }
        }
    }
	return $dbcoll;
}

function RecursiveRemoteDir($dir) {
	if (is_dir($dir)) {
		$files = scandir($dir);
		foreach ($files as $file) {
			if ($file != '.' && $file != '..') {
				if (filetype($dir.'/'.$file) == 'dir') 
					RecursiveRemoteDir($dir.'/'.$file); 
				else
					DestroyDBFile($dir.'/'.$file);
			}
		}
		reset($files);
		SetChmod($dir);
		@rmdir($dir);
	}
}

function RecursiveDeleteDir($dir, $bad = array('index.php', '.htaccess')) {
	if (is_dir($dir)) {
		$files = scandir($dir);
		foreach ($files as $file) {
			if ($file != '.' && $file != '..') {
				if (filetype($dir.'/'.$file) == 'dir') 
					RecursiveDeleteDir($dir.'/'.$file); 
				elseif (!in_array($file, $bad))
					DestroyDBFile($dir.'/'.$file);
			}
		}
		reset($files);
	}
}
function RecursiveDeleteDirComplet($dir) {
	if (is_dir($dir)) {
		$files = scandir($dir);
		foreach ($files as $file) {
			if ($file != '.' && $file != '..') {
				$path =$dir.'/'.$file; // construction du chemin du fichier
				if (is_dir($dir.'/'.$file)) 
					RecursiveDeleteDirComplet($dir.'/'.$file); 
				elseif (is_file($dir.'/'.$file))
					DestroyDBFile($dir.'/'.$file);
				@rmdir($path);
			}
		}
		reset($files);
		@rmdir($dir);
	}
}

function DeleteDir($dir, $bad = array('index.php', '.htaccess')) {
	if (is_dir($dir)) {
		$files = scandir($dir);
		foreach ($files as $file) {
			if ($file != '.' && $file != '..') {
				if (!in_array($file, $bad))
					DestroyDBFile($dir.'/'.$file);
			}
		}
		reset($files);
		
	}
}

function recursiveCopy($src, $dest) {
    $dir = @opendir($src);
    if (!is_dir($dest)) MakeDir($dest);
    while (false !== ($file = @readdir($dir))) {
        if (($file != '.') && ($file != '..') && ($file != '')) {
            if (is_dir($src.'/'.$file))
                recursiveCopy($src.'/'.$file, $dest.'/'.$file);
            else
                @copy($src.'/'.$file, $dest.'/'.$file);
        }
    }
    closedir($dir);
}

function ReplaceSpecialChars($text) {
	$chars_in  = array('$', '%', '.', '/', ':', '<', '=', '>', '?', '[', ']', '^', '{', '}');
	$chars_out = array('&#036;', '&#037;', '&#046;', '&#047;', '&#058;', '&#060;', '&#061;', '&#062;', '&#063;', '&#091;', '&#093;', '&#094;', '&#123;', '&#125;');
	return str_replace($chars_in, $chars_out, $text);
}

function SetChmod($file, $mode='') {
	global $site;
	$site['MF'] = (empty($site['MF']) ? '0644' : $site['MF']);
	$site['MD'] = (empty($site['MD']) ? '0755' : $site['MD']);
	$typ        = (is_dir($file) ? 'dir' : 'file');
	$value      = (empty($typ) || $typ == 'file' ? $site['MF'] : $site['MD']);
	$value      = (empty($mode) ? $value : $mode);
	return @chmod($file, intval($value, 8));
}

function MakeDir($dir, $mode='') {
	global $site;
	$value = (empty($site['MD']) ? '0755' : $site['MD']);
	$value = (empty($mode) ? $value : $mode);
	return @mkdir($dir, intval($value, 8));
}

function DisplayDownArrow($dynbox, $id='') {
    $rarrow = '<span class="dispArrow"'.($id == '' ? '' : ' id="Ar'.$id.'"').' title="">&#xe807;</span>';
    $uarrow = '<span class="dispArrow"'.($id == '' ? '' : ' id="Ar'.$id.'"').' title="">&#xe808;</span>';
    return ($dynbox == 'off' ? '' : ($dynbox == 'open' ? $uarrow : $rarrow));
}

function getIPadress($adress) {
    if (strstr($adress, ', ')) {
        $ips = explode(', ', $adress);
        $adress = $ips[0];
    }
    return $adress;
}

function UpdateCollaboratorPass($redac, $pass) {
	$filedroits = CHEMIN.'admin/'.REDACREP.$redac.INCEXT;
	if (!is_file($filedroits)) return false;
	@include $filedroits;
	$drtuser[38] = $pass;
    $rightsname  = $redac;
    @include CHEMIN.'admin/inc/rightsupdt'.INCEXT;
	return true;
}

function UpdateProtectMember($memb, $pass) {
    if (!is_file(CHEMIN.'data/config/.htpasswd')) return false;
    $htpasswd = @file(CHEMIN.'data/config/.htpasswd');
    $countht  = count($htpasswd);
    if ($countht >= 2) {
        $htpass = '';
        for ($i = 0; $i <= $countht; $i++) {

			if (isset($htpasswd[$i]))
				$verifpos1 = strrpos($htpasswd[$i], ":");
			else
				$verifpos1 = false;
			if ($verifpos1 === false) $verifpos1 = 0;
			if (!is_numeric($verifpos1)) $verifpos1 = 0;			
			if ($verifpos1 != '0' )
			{
				$aht = explode(':', $htpasswd[$i]);
				if ($aht[0] == $memb) $htpasswd[$i] = $aht[0].':'.$pass.chr(10);
				$htpass .= $htpasswd[$i];
			}

        }
        WriteFullDB(CHEMIN.'data/config/.htpasswd', $htpass);
    }
	return true;
}
    
function DeleteMemberProtect($membprot) {
    if (!is_file(CHEMIN.'data/config/.htpasswd') || !is_file(CHEMIN.'admin/'.REDACREP.$membprot.INCEXT)) return false;
    $htpasswd = @file(CHEMIN.'data/config/.htpasswd');
    $countht  = count($htpasswd);
    $htpass   = $htpasswd[0].$htpasswd[1];
    for ($i = 2; $i < $countht; $i++) {
        $aht = explode(':', $htpasswd[$i]);
        if ($membprot != $aht[0] && !empty($htpasswd[$i])) $htpass .= $htpasswd[$i];
    }
    if (mb_substr($htpass, -1) == chr(13)) $htpass = mb_substr($htpass, 0, -1);
    if (mb_substr($htpass, -1) == chr(10)) $htpass = mb_substr($htpass, 0, -1);
    WriteFullDB(CHEMIN.'data/config/.htpasswd', $htpass);
	return true;
}

function DeleteAdmin($memb) {
    $admins  = ReadDBFields(ADMINS);
    $dbadmin = array();
    foreach ($admins as $adm) {
        if ($adm[0] != $memb) $dbadmin[] = $adm;
    }
    WriteDBFields(ADMINS, $dbadmin);
}

function ctrlPasswordOK($pwd) {
	$pwd = strip_tags($pwd);
	if (strlen($pwd) < 8) return false;
    return (1 == preg_match('/^\S{8,40}$/', $pwd) ? true : false);
}

function getPGTXT($stxt='') {
    $txt = '';
    while(!empty($stxt)) {
        $pos0 = mb_strpos($stxt, '<pre class=');
        $pos1 = mb_strlen($stxt);
        $txt0 = htmlspecialchars_decode(mb_substr($stxt, 0, ($pos0 !== false ? $pos0 : $pos1)));
        if (!empty($txt0)) {
            $simg = $txt0;
            $txt1 = '';
            while(!empty($simg)) {
                $sp0 = mb_strpos($simg, '<img');
                $sp1 = mb_strlen($simg);
                if ($sp0 !== false) {
                    $sp1  = mb_strpos(mb_substr($simg, $sp0), '>');
                    $img  = mb_substr($simg, $sp0, $sp1 + 1);
                    $txt0 = (preg_match('!https?://|ftps?://|www.!', $img) ? str_replace($img, '', $txt0) : $txt0);
                }
                $simg = trim(mb_substr($simg, $sp1 + 1));
            }
        }
        $txt .= $txt0;
        if ($pos0 !== false) {
            $pos1 = mb_strpos($stxt, '</pre>') + 6;
            $txt .= mb_substr($stxt, $pos0, $pos1 - $pos0);
        }
        $stxt = trim(mb_substr($stxt, $pos1));
    }
    return $txt;
}

function isAuthorImg($pseudo) {
    global $serviz;
    switch ($serviz[175]) {
    case 0 : return false; break;
    case 1 : return true; break;
    case 2 : return !empty($pseudo); break;
    case 3 : 
        if ($pseudo == $serviz[31] || $pseudo == $serviz[176]) return TRUE;
        if ($serviz[42] == 'on') {
            static $admins = null;
            if (!isset($admins)) $admins = ReadDBFields(ADMINS);
            foreach ($admins as $admin) if (($pseudo != '' && $admin[0] != '') && $pseudo == $admin[0]) return TRUE;
        }
        break;
    }    
    return FALSE;
}

function gy_crypt($key, $str) {
	$key    = md5($key);
	$letter = -1;
	$new    = '';
	$strlen = strlen($str);
	for ($i = 0; $i < $strlen; $i++) {
		$letter++;
		if ($letter > 31) $letter = 0;
		$neword = ord($str[$i]) + ord($key[$letter]);
		if ($neword > 255) $neword -= 256;
		$new .= chr($neword);
	}
	return base64_encode($new);
}

function gy_decrypt($key, $str) {
	$key    = md5($key);
	$letter = -1;
	$new    = '';
	if ($str != '')
	{
		$str    = base64_decode($str);
		$strlen = strlen($str);
		for ($i = 0; $i < $strlen; $i++) {
			$letter++;
			if ($letter > 31) $letter = 0;
			$neword = ord($str[$i]) - ord($key[$letter]);
			if ($neword < 1) $neword += 256;
			$new .= chr($neword);
		}
	}
	return $new;
}

function setRGPDdate() {
    global $userprefs, $members, $serviz, $_DOMAIN, $_SECURE;
    $userdb = ReadDBFields(MSGREP.$userprefs[1].DBEXT);
    $userdb[1][28] = date('Ymd');
    WriteDBFields(MSGREP.$userprefs[1].DBEXT, $userdb);
    $userprefs[27] = $userdb[1][28];
    $userdata = '';
    for ($i = 0; $i < count($userprefs); $i++) {
        $stri      = sprintf("%02d", $i);
        $strlen    = (empty($userprefs[$i]) ? '03' : sprintf("%02d", strlen($userprefs[$i])));
        $userdata .= ($stri.$strlen.(empty($userprefs[$i]) ? 'off' : $userprefs[$i]));
    }
//    $usertime = (!empty($members[21]) ? intval($members[21]) : 157680000);
//   $expires  = ((is_file(CHEMIN.'admin/'.REDACREP.$userprefs[1].INCEXT) || $userprefs[1] == $serviz[31] || $userprefs[1] == $serviz[176]) 
//      ? 157680000 : $usertime);
 //avant   @setcookie(USER_COOKIE, gy_crypt(CRYPT_KEY, $userdata), time() + $expires, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
	@setcookie(USER_COOKIE, gy_crypt(CRYPT_KEY, $userdata), strtotime( '+365 days' ), '/', $_DOMAIN, $_SECURE, $_HTTPONLY);	
}

function OK_RGPD_date($user='') {
    if (empty($user)) return false;
    if (!is_file(MSGREP.$user.DBEXT)) return false;
	$userdb = ReadDBFields(MSGREP.$user.DBEXT);
    if (empty($userdb[1][28])) return false;
//    $diff = (int)date('Ymd') - (int)substr($userdb[1][28], 0, 8);
	$origin = date_create((int)substr($userdb[1][28], 0, 8));
	$target = date_create((int)date('Ymd'));
	$diff = date_diff($origin, $target);
	$diff = $diff->format("%a");
	if ($diff <= 365) 
	{
		return true;
	
	}
	else
	{
		return false;
		
	}

}

function CreateDir($dir) {
    if (is_dir($dir)) return true;
    @mkdir($dir);
    @SetChmod($dir);
    if (!is_dir($dir)) return false;
}
/* 
 ***************************************************    
 Cloture des Posts Forum dont le délai est dépassé
################################################################################
*/
function ClosepostForum ($validaction = '')
{
    global $lng, $lang, $nom, $page, $serviz, $site, $userprefs, $web, $urlrw, $tconfig, 
	       $charset, $drtuser, $dynabox, $members, $z2;
    global $type, $fileid, $status, $creadate, $moddate, $author, $email,
         $fielda1, $fielda2, $fieldb1, $fieldb2, $fieldc1, $fieldc2,
         $fieldd1, $fieldd2, $fieldweb, $fieldmail, $fieldmod;
/*
	$datjourl = new DateTime('now');
	$dateheureminiutesecl = $datjourl->format('Y_m_d_H');
	$nomficlog = CHEMIN.'data/log/function_ClosepostForum'.'_'.$dateheureminiutesecl.'.log';
*/
    if ($userprefs[1] == '') return false;
	if (!isAdmin($userprefs[1])) return false;
	if ($serviz[183] != 'on') return false;

    $out   = '';			
	$db   = array();
	$db = ReadDBFields(DBFORUM);
	rsort($db);
	$dbfr = array();
	foreach ($db as $item) {
			ReadDoc($item[2]);
			if (isForumAccessGranted($fieldb2) && $fieldmod == '') {
// Controle date avec le delai paramétré pour la colture					
				$nbrejours = $serviz[184];  // Délai avant cloture
				$jourmod    = substr($moddate,6,2);
				$moismod    = substr($moddate,4,2);
				$anneemod   = substr($moddate,0,4);
				$datemod = strtotime($anneemod.$moismod.$jourmod);
				$datejour = date('Ymd');
				$datejour = strtotime($datejour);
				$diffdate = floor(($datejour-$datemod)/86400);
				$idat   = $serviz[169] == 'on' ? '' : substr(FormatDate($moddate), 0, 10).' :';
				if ($diffdate >= $nbrejours)
				{
					if ($validaction == 'oui') /* Action validée */
					{
						$fieldmod = stripslashes('s#'.$serviz[31].'#'.date("YmdHi"));
						$fielda1 = addslashes(stripslashes($fielda1));
						$fielda2 = addslashes(stripslashes($fielda2));
						$fieldb1 = addslashes(stripslashes($fieldb1));
						$fieldb2 = addslashes(stripslashes($fieldb2));
						$fieldc1 = addslashes(stripslashes($fieldc1));
						$fieldc2 = addslashes(stripslashes($fieldc2));
						$fieldd1 = addslashes(stripslashes($fieldd1));
						$fieldd2 = addslashes(stripslashes($fieldd2));
						WriteDoc();
					}
					$dbw  = SelectDBFieldsByField(ReadDBFields(DBFORUM), 1, $fielda1);
					$fid  = $item[2];
					$urw0 = empty($fieldb2) ? '-41-'.$fid : '-4-'.$fid.'-'.getForumcatId($fieldb2);
					$url0 = $site['TH'].'.php?lng='.$lng.'&amp;pg='.$fid.'&amp;cat='.getForumcatId($fieldb2);
					$url1 = $lng.'-'.$urlrw[5].$urw0.$z2;
					$lbl0 = ($fielda2 == '0' ? 'S' : 'R').stripslashes($fielda1);
					$lbl1 = stripslashes($fieldb1);
					$link = $idat.' <a href="'.CHEMIN.($site['URLR'] == 'on' ? $url1 : $url0).'" target="_self" title="">'.$lbl0.' - '.strip_tags($lbl1).'</a>';
					$dbfr[] = array($nom[$lng == $lang[0] ? 22 : 23], $link);
				}
			}
	}
	unset($db);
	$dbw = array($dbfr);
	foreach ($dbw as $db) {
		if (!empty($db)) {
			foreach ($db as $item) {
				$out .= '
					<div class="py-1">'.$item[1].'</div>';
			}
		
		}
	}
	unset($dbfr);
	if ($out != '') {
		return $out;

	} else
		return '';
}
/*
   Données Caroussel générées
*/
function WriteDocCarousel($action = '', $dir = DBBASECarousel) {
    global $fileidcarousel, $statuscarousel,$controlpanelcarousel,$couleurfondcarousel,$opacityfondcarousel,$coulbordurecarousel,
	$taillebordurecarousel,$arrondbordurecarousel,$margebassecarousel,$buttoncarousel,$largbuttoncarousel,$hautbuttoncarousel,$outlinecarousel,
	$outlineoffsetcarousel, $outlinecolorcarousel,$intervaltempscarousel,$hauteurimgcarousel,$largeurimgcarousel,$commentairephotocarousel,$vignettescarousel,$commentairecarousel,
	$photoschoisiescarousel, $nomphotoschoisiescarousel, $arrondformbuttoncarousel, $btnradio, $largbutton, $hautbutton, $largbuttonrem,  $hautbuttonrem,
	$taillepxrembordur, $coulpxrembutton, $arrondpxremformbutton, $hauteurimg, $largeurimg, $arrondimg, $arrondformimg, $tailleimg, $arrondbuttoncarousel, 
	$taillepxrembordure, $margeleftbutton, $margerightbutton;
	$rec = "<?php
\$fileidcarousel 		 = '".$fileidcarousel."';
\$statuscarousel 		 = '".$statuscarousel."';
\$controlpanelcarousel   = '".$controlpanelcarousel."';
\$couleurfondcarousel 	 = '".$couleurfondcarousel."';
\$opacityfondcarousel	 = '".$opacityfondcarousel."';			
\$coulbordurecarousel	 = '".$coulbordurecarousel."';
\$taillebordurecarousel	 = '".$taillebordurecarousel."';
\$arrondbordurecarousel	 = '".$arrondbordurecarousel."';
\$arrondbuttoncarousel	   = '".$arrondbuttoncarousel."';
\$arrondformbuttoncarousel  = '".$arrondformbuttoncarousel."';
\$margebassecarousel	    = '".$margebassecarousel."';
\$btnradio				    = '".$btnradio."';
\$buttoncarousel		    = '".$buttoncarousel."';
\$largbutton	 			= '".$largbutton."';
\$hautbutton				= '".$hautbutton."';
\$largbuttonrem			    = '".$largbuttonrem."';
\$hautbuttonrem			    = '".$hautbuttonrem."';
\$taillepxrembordure	 	= '".$taillepxrembordure."';
\$coulpxrembutton		    = '".$coulpxrembutton."';
\$margerightbutton	        = '".$margerightbutton."';
\$margeleftbutton	        = '".$margeleftbutton."';
\$arrondpxremformbutton	    = '".$arrondpxremformbutton."';
\$outlinecarousel		    = '".$outlinecarousel."';
\$outlineoffsetcarousel     = '".$outlineoffsetcarousel."'; 
\$outlinecolorcarousel      = '".$outlinecolorcarousel."';         
\$intervaltempscarousel     = '".$intervaltempscarousel."';
\$hauteurimg	 = '".$hauteurimg."';
\$largeurimg	 = '".$largeurimg."';
\$arrondimg			       = '".$arrondimg."';
\$arrondformimg			   = '".$arrondformimg."';		
\$tailleimg			   	   = '".$tailleimg."';
\$commentairephotocarousel  = '".$commentairephotocarousel."';
\$vignettescarousel		 = '".$vignettescarousel."';
\$commentairecarousel	 = '".$commentairecarousel."';
\$nomphotoschoisiescarousel = '".$nomphotoschoisiescarousel."';

?>
";
    WriteFullDB($dir.$fileidcarousel.INCEXT, $rec);
    return TRUE;
}
function ReadDocCarousel($idcarousel, $dir = DBBASECarousel) {
    global $fileidcarousel, $statuscarousel,$controlpanelcarousel,$couleurfondcarousel,$opacityfondcarousel,$coulbordurecarousel,
	$taillebordurecarousel,$arrondbordurecarousel,$margebassecarousel,$buttoncarousel,$largbuttoncarousel,$hautbuttoncarousel,$outlinecarousel,
	$outlineoffsetcarousel, $outlinecolorcarousel,$intervaltempscarousel,$hauteurimgcarousel,$largeurimgcarousel,$commentairephotocarousel,$vignettescarousel,$commentairecarousel,
	$photoschoisiescarousel, $nomphotoschoisiescarousel, $arrondformbuttoncarousel, $btnradio, $largbutton, $hautbutton, $largbuttonrem,  $hautbuttonrem,
	$taillepxrembordur, $coulpxrembutton, $arrondpxremformbutton, $hauteurimg, $largeurimg, $arrondimg, $arrondformimg, $tailleimg, $arrondbuttoncarousel, 
	$taillepxrembordure, $margeleftbutton, $margerightbutton;
#################### LOg 
$datjourl = new DateTime('now');
$dateheureminiutesecl = $datjourl->format('Y_m_d');
$nomficlog = CHEMIN.'data/log/ReadDocCarousel.inc'.'_'.$dateheureminiutesecl.'.log';
// file_put_contents($nomficlog, ' fileidcarousel: '.$idcarousel."\n", FILE_APPEND | LOCK_EX);
################# fin des description LOg

$fileidcarousel 		 	= '';
$statuscarousel 		 	= '';
$controlpanelcarousel   	= '';
$couleurfondcarousel 	 	= '';
$opacityfondcarousel	 	= '';			
$coulbordurecarousel	 	= '';
$taillebordurecarousel	 	= '';
$arrondbordurecarousel	 	= '';
$arrondbuttoncarousel	  	= '';
$arrondformbuttoncarousel  	= '';
$margebassecarousel	 		= '';
$btnradio				   	= '';
$buttoncarousel		 		= '';
$largbutton	 				= '';
$hautbutton	 				= '';
$largbuttonrem				= '';
$hautbuttonrem			   	= '';
$taillepxrembordure		   	= '';
$arrondpxrembutton		   	= '';
$arrondpxremformbutton	   	= '';
$outlinecarousel		 	= '';
$outlineoffsetcarousel  	= ''; 
$outlinecolorcarousel  		= '';         
$intervaltempscarousel  	= '';
$hauteurimg	 				= '';
$largeurimg	 				= '';
$arrondimg			       	= '';
$arrondformimg			   	= '';		
$tailleimg			   	   	= '';
$commentairephotocarousel  	= '';
$vignettescarousel		 	= '';
$commentairecarousel	 	= '';
$nomphotoschoisiescarousel 	= '';
if (FileDBExist($dir.$idcarousel.INCEXT)) {
	include($dir.$idcarousel.INCEXT);
}
return TRUE;
}
################################################################################

?>
