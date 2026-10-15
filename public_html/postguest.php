<?php
/*******************************************************************************
 *   Guest Post Routine
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
 *  v6.00.18 (February 11, 2024) : add crypt in unlock post
 ******************************************************************************/

header('Pragma: no-cache');
define('CHEMIN', '');
include CHEMIN.'inc/includes.inc';

$crypt   = isset($_POST['crypt']) ? strip_tags($_POST['crypt']) : NULL;
$cryptok = (!$crypt) ? false : chk_crypt($crypt);
$actioncomplete = '';
function TestCoherenceSoumission($typ, $pg, $num = null) {
    global $type, $fielda1;
    switch ($typ) {
    case TYP_REACT :
        if (FileDbExist(DBBASE.TestFileId($pg).INCEXT)) {
            ReadDoc($pg);
            return $type == TYP_ART;
        } else {
            return FALSE;
        }
        break;
    case TYP_REBLOG :
        if (FileDbExist(DBBASE.TestFileId($pg).INCEXT)) {
            ReadDoc($pg);
            return $type == TYP_BLOG;
        } else {
            return FALSE;
        }
        break;
    case TYP_THREAD :
        if (FileDbExist(DBBASE.TestFileId($pg).INCEXT)) {
            ReadDoc($pg);
            if ($type != TYP_FORUM) {
                return FALSE;
            } else {
                return $fielda1 == $num;
            }
        } else {
            return FALSE;
        }
        break;
    default :
        return TRUE;
    }
}

function FindIndexDBFields($fic, $submit0, $submit1, $submit2) {
    if (FileDBExist($fic)) {
        $DataDB = ReadDBFields($fic);
        for ($i = 0; $i < count($DataDB); $i++) {
            if (@stristr($DataDB[$i][0],$submit0)
            &&  @stristr($DataDB[$i][1],$submit1)
            &&  @stristr($DataDB[$i][2],$submit2)) {
                return $i;
            }
        }
    }
    return FALSE;
}

function ctrlMail($pseudo, $pemail) {
    global $userprefs;
    if ($pseudo == $userprefs[1]) {
        return ($pemail == $userprefs[2]);
    } else {
        $dbmail = array();
 //       $bad    = array('.', '..', 'index.php');
 //       $files  = scandir(MSGREP);
 //       $files  = array_diff($files, $bad);
		$files = opendir(MSGREP);
	    $retour = true;
		$retoura = '';
		while ($file = readdir($files)) 
		{
			if (substr($file, -3) == 'dtb') {
				$userdb = ReadDBFields(MSGREP.$file);
				if (count($userdb) > 1 ) 
				{		
                    if (isset($userdb[1][3]))
					{
					    $pos = strpos(@$userdb[1][3], "@");
						if ($pos != false)
						{
							if (@$userdb[1][3] == $pemail) $retour = FALSE;
						$retoura .= '
user: '.$file.' nbre ligne : '.count($userdb).' Mail: '.@$userdb[1][3]
;
						}
						else
						$retoura .= '
Erreur formatage zone pas de adresse user: '.$file.' nbre ligne : '.count(@$userdb).' Mail: '.@$userdb[1][3]
;
					}
					else $retoura .= '
Erreur formatage zone non présente user: '.$file.' nbre ligne : '.count($userdb).' Mail: '.@$userdb[1][3]
;
				}
				else
				 $retoura .= '
Erreur nombre de lignes user: '.$file.' nbre ligne : '.count($userdb)
;
					
				WriteFullDB(CONFIGREP.'suivipemail.dtb', $retoura);

			}
        }
		closedir($files);
        return $retour;
    }
}

$REMOTE_ADDR     = getIPadress($site[6] == 'on' ? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']): $_SERVER['REMOTE_ADDR']);
$PHP_SELF        = $_SERVER['PHP_SELF'];
$HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
$SERVER_NAME     = $_SERVER['SERVER_NAME'];

if (import('add', 'GET') != NULL) die('STOP ! Variable $add : illegal origine');
$add = import('add', 'POST');
$lng = import('lng');
$typ = import('typ');

switch ($add) {
case NULL :
    $token      = uniqid(rand(), true);
    $token_time = time();
    setcookie(POST_COOKIE, $token.CONNECTOR.$token_time, time() + (30 * 60), '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
    break;
case 1 :
	if (isset($_COOKIE[POST_COOKIE]))
	{
		$tokens     = explode(CONNECTOR, $_COOKIE[POST_COOKIE]);
		$token_time = time() - (15 * 60);
	}
	else
	{
		$tokens[0] = '';
		$tokens[1] = '0';
	}
	if (empty($tokens[0]) || $tokens[1] < time() - (30 * 60)) {
        echo BeginJavascript().'
var sto = "'.str_replace('<br />', '\n', stripslashes($web[681])).'";
alert(sto);
'.EndJavascript();
        $add = -1;
        $token      = uniqid(rand(), true);
        $token_time = time();
        setcookie(POST_COOKIE, $token.CONNECTOR.$token_time, time() + (30 * 60), '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
    }
	elseif ($tokens[1] + 2 > time()) {
        echo BeginJavascript().'
var sto = "'.str_replace('<br />', '\n', stripslashes($web[682])).'"; // Pour lutter contre les robots spammeurs, 
alert(sto);
'.EndJavascript();
        $add = -1;
        $token      = uniqid(rand(), true);
        $token_time = time();
        setcookie(POST_COOKIE, $token.CONNECTOR.$token_time, time() + (30 * 60), '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
    }
    break;
default :
    die('STOP ! Variable $add : illegal value ('.$add.')');
}
$dbcat = array();
if ($serviz[13] == 'on')
	$dbcat = ReadDBFields(DBFORUMCAT);
$okfr  = false;
if (!empty($dbcat)) 
{
	foreach ($dbcat as $cat) 
	{
		if (!isset($cat[5])) $cat[5] = '';
		if ($cat[5] == 'on') 
		{ 
			$okfr = true; break;
		}
	}
}

$cryptdisp = '';
switch ($typ) {
case TYP_FORUM :
case TYP_THREAD :
    if ($serviz[13] == '' || !$okfr) {
        die('STOP ! Forum is not active !');
    }
    $cryptdisp = $serviz[91];
    $tconfig   = $menuico[19] == 'ALL' ? import('tconfig', '', true, 0) : $menuico[19];
    break;
case TYP_GUESTBK :
    if ($serviz[12] == '') {
        die('STOP ! Guest book is not active !');
    }
    $cryptdisp = $serviz[90];
    $tconfig   = $menuico[18] == 'ALL' ? import('tconfig', '', true, 0) : $menuico[18];
    break;
case TYP_NEWS :
    if ($serviz[8] == '' || $site[5] == 2) {
        die('STOP ! News submit is not active !');
    }
    $cryptdisp = $serviz[89];
    $tconfig   = $menuico[12] == 'ALL' ? import('tconfig', '', true, 0) : $menuico[12];
    break;
case TYP_BLOG :
    if ($serviz[53] == '' || $site[30] == 2) {
        die('STOP ! Blog submit is not active !');
    }
    $cryptdisp = $serviz[96];
    $tconfig   = $menuico[13] == 'ALL' ? import('tconfig', '', true, 0) : $menuico[13];
    break;
case TYP_REBLOG :
    if ($serviz[57] == 2) {
        die('STOP ! Comment is not active !');
    }
    $cryptdisp = $serviz[97];
    $tconfig   = $menuico[13] == 'ALL' ? import('tconfig', '', true, 0) : $menuico[13];
    break;
case TYP_REACT :
    if ($serviz[29] == 2) {
        die('STOP ! Reaction is not active !');
    }
    $cryptdisp = $serviz[92];
    $tconfig   = import('tconfig', '', true, 0);
    break;
case TYP_RECO :
    if ($serviz[62] == '') {
        die('STOP ! Recommander is not active !');
    }
    $cryptdisp = $serviz[94];
    $tconfig   = import('tconfig', '', true, 0);
    break;
case TYP_MAIL :
    if ($user[3] == '') {
        die('STOP ! Mail is not active !');
    }
    $cryptdisp = $serviz[95];
    $tconfig   = import('tconfig', '', true, 0);
    break;
default :
    die('STOP ! Variable $typ : illegal value ('.$typ.')');
}

switch ($lng) {
case $lang[0] :
case $lang[1] :
    break;
default :
    die('STOP ! Variable $lng : illegal value ('.$lng.')');
}

$pseudo    = import('pseudo', 'POST', true, '');
$ctrl_spam = import('code_pseudo', 'POST');
$pemail    = import('pemail', 'POST');
$pehide    = import('pehide', 'POST', true, '');
$pfmail    = import('pfmail', 'POST');
$ptit      = import('ptit', 'POST', false);
$purl      = import('purl', 'POST');
$cat       = import('cat');
$tocat     = import('tocat');
$catforum  = getForumCatFull(import('catforum', 'POST'));
$rub       = import('rub', 'POST', false);
$num       = import('num');
$antispam  = import('antispam', 'POST');
$mod       = import('mod');
$group     = import('group', 'POST');
$index     = import('index');
$replymail = import('replymail');
$noreply   = import('noreply');
$filesign  = import('filesign', 'POST');
$pg        = import('pg');
$rgpd      = import('rgpd', 'POST', true, '');

require CHEMIN.'inc/ckeditor_config/editors_functions.php';
require_once CHEMIN.'inc/htmlpurifier/library/HTMLPurifier.auto.php';
$configpg = HTMLPurifier_Config::createDefault();
$configpg->set('HTML.Allowed', '
  p[class|id|style],div[class|id|style],strong,em,u,b,sub,sup,span[class|style],pre[class],blockquote[class|style],
  a[href|target|title|style],img[src|alt|style],ul[style],ol[start|style],li');
$configpg->set('Attr.AllowedFrameTargets', array('_blank', '_self'));
$purifier = new HTMLPurifier($configpg);
$ptxt     = import('ptxt', 'POST', FALSE);
$ptxt     = $purifier->purify(isAuthorImg($userprefs[1]) ? $ptxt : getPGTXT($ptxt));
$pmess    = str_replace("inc/img", $site[3]."inc/img", $ptxt);

if (1 != $add && -1 != $add && !TestCoherenceSoumission($typ, $pg, $num)) die('STOP ! Problème de cohérence !');

$data   = array_pad(array(), 18, '');
$pseudo = KeepGoodChars(RemoveConnector(RemoveQuote(stripslashes(CutLongWord(strip_tags($pseudo), max($serviz[148], 80))))));
$pemail = checkEmail(CutLongWord($pemail));
$pfmail = checkEmail(CutLongWord($pfmail));
$purl   = checkUserWebsiteUrl(CutLongWord($purl, 80));
$ptit   = WrapLongWords(CutLongWord($ptit, 100));
$ptit   = ReplaceSpecialChars($ptit);

$err_pseudo = empty($pseudo) && ($add == 1 || $add == -1) ? true : false;
$err_pemail = empty($pemail) && ($add == 1 || $add == -1) ? true : false;
$err_pfmail = empty($pfmail) && ($add == 1 || $add == -1) && $typ == TYP_RECO ? true : false;
$err_ptit   = empty($ptit) && empty($mod) && ($add == 1 || $add == -1) ? true : false;
$err_crypt  = $serviz[100] != '0' && $cryptdisp == 'on' && ($userprefs[1] == '' || $serviz[98] == '') && !$cryptok && ($add == 1 || $add == -1) ? true : false;
$err_ptxt   = empty($ptxt) && ($add == 1 || $add == -1) && !in_array($mod, array('lock', 'up', 'raz', 'top')) && $typ != TYP_RECO ? true : false;
$err_rgpd   = !empty($serviz[177]) && empty($rgpd) && !OK_RGPD_date($userprefs[1]) && ($add == 1 || $add == -1) ? true: false;

$erreur  = '';
$erreur .= $err_pseudo ? '<li>'.$web[266].'</li>': ''; // Caractères non valides (2 à  caractères comprenant lettres non accentuées, chiffres et _-
$erreur .= $err_pemail ? '<li>'.$web[42].'</li>': '';
$erreur .= $err_pfmail ? '<li>'.$web[122].'</li>': '';
$erreur .= $err_ptit   ? '<li>'.$web[123].'</li>': '';
$erreur .= $err_ptxt   ? '<li>'.$web[43].'</li>': '';
$erreur .= $err_crypt  ? '<li>'.$web[529].'</li>': ''; //code de sécurité invalide !
$erreur .= $err_rgpd   ? '<li>'.$web[405].'</li>': ''; // l'acceptation des conditions est requise

$add = (empty($ptit) && empty($mod)) || $err_ptxt || $err_pseudo || $err_pemail || $err_crypt || !empty($ctrl_spam) ? null : $add;

if ($mod == 'top') {
    $forum6   = import('forum6');
    $forum[6] = empty($forum6) ? $forum[6] : $forum6;
}

$ok    = $web[45];
$infos = 'IP: '.$REMOTE_ADDR.' ('.gethostbyaddr($REMOTE_ADDR).')<br />Referer : '.$PHP_SELF.'<br />Browser : '.$HTTP_USER_AGENT;

if ($add == 1 && empty($erreur)) {
    
    if (!empty($log3)) {
        $url  = parse_url($log3);
        $host = isset($url['host']) ? $url['host'] : '';
        $ip   = gethostbyname($host);
        if (!empty($host) && $ip != $host) {
            if ($host != $SERVER_NAME && $host != 'www.'.$SERVER_NAME) {
                die("STOP ! Illegal action from '$host' (IP=$ip)");
            }
        }
    }
    
    $ctrl_ident = true; 
	if (!in_array($typ, array(TYP_RECO, TYP_MAIL)) && is_file(MSGREP.$pseudo.DBEXT) && $pseudo != $userprefs[1]) {
		$erreur .= '<li>'.$web[392].'</li>'; // Ce pseudo est déjà utilisé !!!
        $ctrl_ident = false;
	}
    if (!ctrlMail($pseudo, $pemail)) {
		$erreur .= '<li>'.$web[375].'</li>'; // Ce courriel est inconnu ou est déjà utilisé !!!
        $ctrl_ident = false;
    }
	if ($ctrl_ident) {
		$index = FindIndexDBFields(DBANTISPAM, $antispam, $REMOTE_ADDR, $typ);
		if ($index === false && $typ != TYP_RECO) {
			/*******************************************************************
			 * Créez le fichier inc/postguest_antispam.inc pour ajouter ici
			 * la ou les actions que vous souhaitez
			 * par exemple : mail à l'administrateur, inscription dans un fichier...
			 ******************************************************************
			 * Create a file inc/postguest_antispam.inc
			 * to add here the action which you wish
			 * for example: e-mail with the administrator, inscription in a file...
			 ******************************************************************/
			if (is_file('inc/postguest_antispam.inc')) include 'inc/postguest_antispam.inc';
			die('STOP ! Anti-SPAM !');
		}
		else {
			DeleteDBFieldById(DBANTISPAM, $index);
			if ($typ == TYP_THREAD && !preg_match('!^[1-9][0-9]*$!', $num)) {
				die('STOP ! num : '.$num.' illegal value!');
			}
			if ($userprefs[1] == '') {
				switch ($typ) {
				case TYP_NEWS :
					if (!isAccessGranted($members[8])) die('STOP ! News : '.$web[342].' !');
					break;
				case TYP_BLOG :
					if (!isAccessGranted($members[16])) die('STOP ! Blog : '.$web[342].' !');
					break;
				case TYP_GUESTBK :
					if (!isAccessGranted($members[9])) die('STOP ! Guest book : '.$web[342].' !');
					break;
				case TYP_FORUM :
				case TYP_THREAD :
					if (!isAccessGranted($members[10])) die('STOP ! Forum : '.$web[342].' !');
					break;
				case TYP_REACT :
					if (!isAccessGranted($members[11]) || !isAccessGranted($members[1])) die('STOP ! Reaction : '.$web[342].' !');
					break;
				case TYP_REBLOG :
					if (!isAccessGranted($members[17]) || !isAccessGranted($members[15])) die('STOP ! Comments : '.$web[342].' !');
					break;
				}
			}
		}
        
        if (!empty($userprefs[1]) && $rgpd == 'on') setRGPDdate();
        
		$datefrmt = FormatDate(GetCurrentDateTime());

		if ($mod == '' && !$erreur) {
			if ($typ == TYP_THREAD && $serviz[41] != '1') {
				$dbworkmail = ReadDBFields(DBFORUM);
				for ($i = 0; $i < count($dbworkmail); $i++) {
					if ($num == $dbworkmail[$i][1]){
						$sujetthread = $dbworkmail[$i][5];
						$catthread   = $dbworkmail[$i][12];
						break;
					}
				}
				$dbmailuniq = Array();
				$dbworkuniq = ReadDBFields(DBTHREAD);
				for ($i = 0; $i < count($dbworkuniq); $i++) {
					if ($num == $dbworkuniq[$i][1] && $dbworkuniq[$i][2] == 0) {
						$pgview = $dbworkuniq[$i][3];
					}
					if ($num == $dbworkuniq[$i][1] && $dbworkuniq[$i][9] == 'on') {
						$dbmailuniq[] = $dbworkuniq[$i][5];
					}
				}
                if ($noreply == 'on') {
                    for ($i = 0; $i < count($dbworkuniq); $i++) {
                        if ($num == $dbworkuniq[$i][1] && $dbworkuniq[$i][5] == $pemail && $dbworkuniq[$i][9] == 'on') {
                            $dbworkuniq[$i][9] = '';
                        }
                    }
                    WriteDBFields(DBTHREAD, $dbworkuniq);
                }
				$mailuniq = ValUnique($dbmailuniq);
				for ($i = 0; $i < count($mailuniq); $i++) {
					$to    = $mailuniq[$i];
					$sujet = $web[345].' '.$sujetthread;
					$bodycorps  = $web[346].$sujetthread.'<br /><br />';
					$bodycorps .= $web[109].'<hr />'.ForceToAbsolute(stripslashes($ptxt)).'<hr /><br />';
					$bodycorps .= $web[347].'<br />';
					$bodycorps .= '<a href="'.$site[3].($site['URLR'] == 'on' ? $lng.'-'.$urlrw[5].'-5-'.$pgview.'-'.$catthread.$z2 : $site['TH'].'.php?lng='.$lng.'&pg='.$pgview.'&cat='.$catthread.$z2).'">';
					$bodycorps .= $site[3].($site['URLR'] == 'on' ? $lng.'-'.$urlrw[5].'-5-'.$pgview.'-'.$catthread.$z2 : $site['TH'].'.php?lng='.$lng.'&pg='.$pgview.'&cat='.$catthread.$z2);
					$bodycorps .= '</a><br /><br />';
					$bodycorps .= $web[348].'<br />'.$user[0];
					eMailHtmlTo($sujet, $bodycorps, $to);
				}
			}
			if (in_array($typ, array(TYP_NEWS, TYP_BLOG, TYP_GUESTBK, TYP_FORUM, TYP_THREAD, TYP_REACT, TYP_REBLOG))) {
				$data    = array_fill(0, 18, '');
				$data[0] = $typ == TYP_THREAD ? TYP_FORUM : $typ;
				switch ($typ) {
				case TYP_NEWS :
					$data[2] = $site[5] == 1 ? 'i' : 'a';
					$ok      = $site[5] == 1 ? $web[48] : $ok;
					break;
				case TYP_BLOG :
					$data[2] = $site[30] == 1 ? 'i' : 'a';
					$ok      = $site[30] == 1 ? $web[48] : $ok;
					break;
				case TYP_REBLOG :
					$data[2] = $serviz[57] == 1 ? 'i': 'a';
					$ok      = $serviz[57] == 1 ? $web[48] : $ok;
					break;
				case TYP_GUESTBK :
                    if ($serviz[12] == 'on') {
                        $data[2] = $serviz[40] == 1 ? 'i' : 'a';
                        $ok      = $serviz[40] == 1 ? $web[48] : '';
                    }
					break;
				case TYP_FORUM :
                case TYP_THREAD :
					if ($serviz[13] == 'on' && $okfr) {
                        $data[2] = $serviz[41] == 1 ? 'i' : 'a';
                        $ok      = $serviz[41] == 1 ? $web[48] : '';
                    }
					break;
				case TYP_REACT :
					$data[2] = $serviz[29] == 1 ? 'i' : 'a';
					$ok      = $serviz[29] == 1 ? $web[48] : $ok;
					break;
				default :
					$data[2] = 'a';
					$ok      = '';
					break;
                }
				$data[5] = $pseudo;
				$data[6] = $pemail;
				switch ($typ) {
				case TYP_BLOG :
                    $dbr = ReadDBFields(DBINDEX.TYP_BRUB.DBEXT);
                    $rb  = SelectDBFieldsByField($dbr, ($lng == $lang[0] ? 0 : 1), $rub);
					$data[7] = (empty($rb) ? $rub : $rb[0][0]);
					$data[8] = (empty($rb) ? $rub : $rb[0][1]);
					break;
				case TYP_GUESTBK :
					$data[7] = GetNextChildNumber($typ);
					break;
				case TYP_REACT :
				case TYP_REBLOG :
					$data[7] = GetNextChildNumber($typ, $pg);
					$data[8] = $pg;
					break;
				case TYP_FORUM :
					$catthread = getForumCatFull(empty($catthread) ? $catforum : $catthread);
					if ($serviz[41]) {
						$data[7] = 1;
						$dbwork  = SelectDBFieldsByNotStatus(SelectDBFields(TYP_FORUM, '', ''), 'd');
						if (count($dbwork) > 0) {
							$data[7] = ReadCounter(DBFORUMCOUNTER)+1;
							WriteCounter(DBFORUMCOUNTER,$data[7]);
						}
					}
					else {
						$dbthrd = array();
						$dbwork = ReadDBFields(DBFORUM);
						for ($i = 0; $i < count($dbwork); $i++) {
							$dbthrd[] = $dbwork[$i][1];
						}
						@rsort($dbthrd);
						if (!empty($dbthrd)) {
							$data[7] = $dbthrd[0] + 1;
							WriteCounter(DBFORUMCOUNTER,$data[7]);
						}
						else {
							$data[7] = ReadCounter(DBFORUMCOUNTER)+1;
							WriteCounter(DBFORUMCOUNTER,$data[7]);
						}
					}
					$data[8] = '0';
					break;
				case TYP_THREAD :
					$data[7]  = $num;
					$data[8]  = GetNextChildNumber(TYP_FORUM, $num);
					$dbns     = IndexDbFieldsByField(ReadDbFields(DBFORUM), 1);
					$catforum = getForumCatFull($dbns[$num][12]);
					unset($dbns);
					break;
				}
				switch ($typ) {
				case TYP_GUESTBK :
					$data[9]  = $purl;
					$data[11] = PutBR($ptxt);
					break;
				case TYP_FORUM :
                case TYP_THREAD :
					$data[9]  = $typ == TYP_FORUM ? $ptit : '';
					$data[10] = getForumCatFull($catforum);
					if (!empty($userprefs[6])) {
						$data[11] = PutBR($ptxt).'<p class="text-right thsign">'.ReplaceFrSigns($userprefs[6]).'</p>';
					}
					else {
						$data[11] = PutBR($ptxt);
					}
					$data[12] = $userprefs[8];
					if (!empty($userprefs[1])) {
						$idcountmsg = ReadCounter(COUNTMSGREP.$pseudo.DBEXT) + 1;
					}
					else {
						$idcountmsg = 'v';
					}
					$data[14] = $idcountmsg;
					$data[15] = $userprefs[9];
					$data[16] = $replymail;
					break;
				case TYP_REACT :
                case TYP_REBLOG :
					$data[11] = PutBR($ptxt);
					$data[17] = $group; /// modif accès privé
					break;
				default :
					if ($site[5] == '0') {
						$data[9]  = $ptit;
						$data[10] = $ptit;
						$data[11] = PutBR($ptxt);
						$data[12] = PutBR($ptxt);
					}
					else {
						$data[9]  = $ptit;
						$data[11] = PutBR($ptxt);
						$data[12] = PutBR($ptxt);
					}
					break;
				}
				$data[13] = $pehide == 'on' ? 'on' : '';
				if (!empty($userprefs[1]) && ($typ == TYP_FORUM) || ($typ == TYP_THREAD)) {
					$idcountmsg = ReadCounter(COUNTMSGREP.$pseudo.DBEXT) + 1;
					WriteCounter(COUNTMSGREP.$pseudo.DBEXT,$idcountmsg);
				}
				$id = ActionOnFields('add', $data);
				$ok = $web[46]; // Merci de votre contribution
                
                UpdateSitemaps($typ);
			}
			elseif ($typ != TYP_RECO) {
				$ok = $web[47]; // Cette saisie n'est pas valide !
			}
			if ($typ == TYP_MAIL || $typ == TYP_RECO) {
				$ok = $web[46]; // Merci de votre contribution
			}
		}
		else { // cas modif thread ou blog
			include 'inc/'.($typ == TYP_BLOG ? 'mod_blog' : 'mod_thread').'.inc';
			$ok = $web[46]; // Merci de votre contribution
		}

        $replyto = trim($pseudo).'<'.trim($pemail).'>';
		if ($supervision[2] == 'on' && $typ == TYP_NEWS) {
			$eSub  = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." ".$web[106];
			$eMsg  = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." - ".$web[106].", ".$web[104]." ".$datefrmt.".<br />";
			$eMsg .= $web[107].stripslashes($pseudo)." (".stripslashes($pemail).").<br />";
			$eMsg .= $web[108].stripslashes($ptit)."<br />";
			$eMsg .= '<hr />'.$web[109].'<br />'.ForceToAbsolute(stripslashes($ptxt));
			$eMsg .= "<hr />".$web[349]." : ".$web[354].$id;
			$eMsg .= "<br />".$web[360].'<a href="'.$site[3].$site['NE'].".php?lng=".$lng."&pg=".$id.$z2.'">'.$site[3].$site['NE'].".php?lng=".$lng."&pg=".$id.'</a>';
			$eMsg .= "<br />".$web[350].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=news&form=2&id=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=news&form=2&id=".$id.'</a>';
			if ($site[5] == "1") {
				$eMsg .= "<br />".$web[351].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=news&act=a&id=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=news&act=a&id=".$id.'</a>';
			} else {
				$eMsg .= "<br />".$web[352].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=news&act=i&id=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=news&act=i&id=".$id.'</a>';
			}
			$eMsg .= "<br />".$web[353].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=news&del=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=news&del=".$id.'</a>';
			$eMsg .= "<hr />".$infos;
			$maillist = GetListCollaboratorEmail(TYP_NEWS);
			eMailHtmlTo($eSub, $eMsg, trim(implode(' ', $maillist).' '.trim($supervision[10]).' '.$user[1]), $user[1], '', $replyto);
		}
		if ($supervision[8] == "on" && $typ == TYP_BLOG && empty($mod)) {
			$eSub  = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." ".$web[396];
			$eMsg  = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." - ".$web[396].", ".$web[104]." ".$datefrmt.".<br />";
			$eMsg .= $web[107].stripslashes($pseudo)." (".stripslashes($pemail).").<br />";
			$eMsg .= $web[108].stripslashes($ptit)."<br />";
			$eMsg .= '<hr />'.$web[109].'<br />'.ForceToAbsolute(stripslashes($ptxt));
			$eMsg .= "<hr />".$web[349]." : ".$web[354].$id;
			$eMsg .= "<br />".$web[360].'<a href="'.$site[3].$site['BL'].".php?lng=".$lng."&pg=".$id.$z2.'">'.$site[3].$site['BL'].".php?lng=".$lng."&pg=".$id.'</a>';
			$eMsg .= "<br />".$web[350].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=blog&form=2&id=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=blog&form=2&id=".$id.'</a>';
			if ($site[30] == "1") {
				$eMsg .= "<br />".$web[351].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=blog&act=a&id=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=blog&act=a&id=".$id.'</a>';
			} else {
				$eMsg .= "<br />".$web[352].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=blog&act=i&id=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=blog&act=i&id=".$id.'</a>';
			}
			$eMsg .= "<br />".$web[353].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=blog&del=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=blog&del=".$id.'</a>';
			$eMsg .= "<hr />".$infos;
			$maillist = GetListCollaboratorEmail(TYP_BLOG);
			eMailHtmlTo($eSub, $eMsg, trim(implode(' ', $maillist).' '.trim($supervision[10]).' '.$user[1]), $user[1], '', $replyto);
		}
		if ($supervision[9] == "on" && $typ == TYP_REBLOG) {
			$eSub  = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." ".$web[379]." ".$web[381];
			$eMsg  = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." - ".$web[379]." ".$web[381].", ".$web[104]." ".$datefrmt.".<br />";
			$eMsg .= $web[107].stripslashes($pseudo)." (".stripslashes($pemail).").<br />";
			ReadDoc($pg);
			$eMsg .= $web[183]." - ".$fieldb1."<br />";
			$eMsg .= '<hr />'.$web[109].'<br />'.ForceToAbsolute(stripslashes($ptxt));
			$eMsg .= "<hr />".$web[349]." : ".$web[39].$data[7]." - ".$web[355].$fileid." - ".$web[356].$id;
			$eMsg .= "<br />".$web[360].'<a href="'.$site[3].$site['BL'].".php?lng=".$lng."&pg=".$fileid."&reblog=".$id.$z2.'">'.$site[3].$site['BL'].".php?lng=".$lng."&pg=".$fileid."&reblog=".$id.'</a>';
			$eMsg .= "<br />".$web[350].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=reblog&form=2&id=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=reblog&form=2&id=".$id.'</a>';
			if ($serviz[57] == "ok") {
				$eMsg .= "<br />".$web[351].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=reblog&act=a&id=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=reblog&act=a&id=".$id.'</a>';
			} else {
				$eMsg .= "<br />".$web[352].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=reblog&act=i&id=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=reblog&act=i&id=".$id.'</a>';
			}
			$eMsg .= "<br />".$web[353].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=reblog&del=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=reblog&del=".$id.'</a>';
			$eMsg .= "<hr />".$infos;
			$maillist = GetListCollaboratorEmail(TYP_REBLOG);
			eMailHtmlTo($eSub, $eMsg, trim(implode(' ', $maillist).' '.trim($supervision[10]).' '.$user[1]), $user[1], '', $replyto);
		}
		if ($supervision[3] == "on" && $typ == TYP_GUESTBK) {
			$eSub  = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." ".$web[113];
			$eMsg  = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." - ".$web[113].", ".$web[104]." ".$datefrmt.".<br />";
			$eMsg .= $web[107].stripslashes($pseudo)." (".stripslashes($pemail).").<br />";
			$eMsg .= $web[112].stripslashes($purl)."<br />";
			$eMsg .= '<hr />'.$web[109].'<br />'.ForceToAbsolute(stripslashes($ptxt));
			$eMsg .= "<hr />".$web[349]." : ".$web[39].$data[7]." - ".$web[354].$id;
			$eMsg .= "<br />".$web[360].'<a href="'.$site[3].$site['GB'].".php?lng=".$lng."&pg=".$id.$z2.'">'.$site[3].$site['GB'].".php?lng=".$lng."&pg=".$id.'</a>';
			$eMsg .= "<br />".$web[350].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=guestbk&form=2&id=".$id.'">'."admin/admin.php?lng=".$lng."&pg=guestbk&form=2&id=".$id.'</a>';
			if ($serviz[40] != "") {
				$eMsg .= "<br />".$web[351].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=guestbk&act=a&id=".$id.'">'."admin/admin.php?lng=".$lng."&pg=guestbk&act=a&id=".$id.'</a>';
			} else {
				$eMsg .= "<br />".$web[352].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=guestbk&act=i&id=".$id.'">'."admin/admin.php?lng=".$lng."&pg=guestbk&act=i&id=".$id.'</a>';
			}
			$eMsg .= "<br />".$web[353].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=guestbk&del=".$id.'">'."admin/admin.php?lng=".$lng."&pg=guestbk&del=".$id.'</a>';
			$eMsg .= "<hr />".$infos;
			$maillist = GetListCollaboratorEmail(TYP_GUESTBK);
			eMailHtmlTo($eSub, $eMsg, trim(implode(' ', $maillist).' '.trim($supervision[10]).' '.$user[1]), $user[1], '', $replyto);
		}
		if ($supervision[6] == "on" && $typ == TYP_REACT) {
			$eSub  = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." ".$web[182];
			$eMsg  = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." - ".$web[182].", ".$web[104]." ".$datefrmt.".<br />";
			$eMsg .= $web[107].stripslashes($pseudo)." (".stripslashes($pemail).").<br />";
			ReadDoc($pg);
			$eMsg .= $web[183]." - ".$fieldb1."<br />";
			$eMsg .= '<hr />'.$web[109].'<br />'.ForceToAbsolute(stripslashes($ptxt));
			$eMsg .= "<hr />".$web[349]." : ".$web[39].$data[7]." - ".$web[355].$fileid." - ".$web[356].$id;
			$eMsg .= "<br />".$web[360].'<a href="'.$site[3]."articles.php?lng=".$lng."&pg=".$fileid."&react=".$id.$z2.'">'.$site[3]."articles.php?lng=".$lng."&pg=".$fileid."&react=".$id.'</a>';
			$eMsg .= "<br />".$web[350].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=react&form=2&id=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=react&form=2&id=".$id.'</a>';
			if ($serviz[29] == "1") {
				$eMsg .= "<br />".$web[351].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=react&act=a&id=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=react&act=a&id=".$id.'</a>';
			} else {
				$eMsg .= "<br />".$web[352].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=react&act=i&id=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=react&act=i&id=".$id.'</a>';
			}
			$eMsg .= "<br />".$web[353].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=react&del=".$id.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=react&del=".$id.'</a>';
			$eMsg .= "<hr />".$infos;
			$maillist = GetListCollaboratorEmail(TYP_NEWS);
			eMailHtmlTo($eSub, $eMsg, trim(implode(' ', $maillist).' '.trim($supervision[10]).' '.$user[1]), $user[1], '', $replyto);
		}
		if ($supervision[4] == "on" && ($typ == TYP_FORUM || $typ == TYP_THREAD)) {
			$eSub     = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." ".$web[114];
			$eMsg     = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." - ".$web[114].", ".$web[104]." ".$datefrmt.".<br />";
			$eMsg    .= $web[107].stripslashes($pseudo)." (".stripslashes($pemail).").<br />";
            $dbforcat = ReadDBFields(DBFORUMCAT);
            for ($i = 0; $i < count($dbforcat); $i++) {
                if ($catforum == $dbforcat[$i][0]) {
                    if ($lng == $lang[0]) {
                        $eMsg .= $web[130]." : ".$dbforcat[$i][1];
                    }
                    else {
                        $eMsg .= $web[130]." : ".$dbforcat[$i][2];
                    }
                    break;
                }
            }
			if ($typ == TYP_FORUM) {
				$eMsg .= " - ".$web[110]."<br />";
				$eMsg .= $web[108].stripslashes($ptit)."<br />";
			}
			else {
				$eMsg .= ' - '.$web[111]."<br />";
			}
			$catthread = (!isset($catthread) || empty($catthread)) ? $catforum : $catthread;
			$thrdid = GetThreadId($data[7]);
			$eMsg  .= '<hr />'.$web[109].'<br />'.ForceToAbsolute(stripslashes($ptxt));
			$eMsg  .= "<hr />".$web[349]." : ".$web[63].$data[7]." - ".$web[354].$fileid;
			$eMsg  .= "<br />".$web[360].'<a href="'.$site[3].$site['TH'].".php?lng=".$lng."&pg=".$thrdid.'&cat='.$catthread."&id=".ceil($data[8]/$serviz[20])."#R".$data[8].'">'.$site[3].$site['TH'].".php?lng=".$lng."&pg=".$thrdid.'&cat='.$catthread."&id=".ceil($data[8]/$serviz[20])."#R".$data[8].'</a>';
			$eMsg  .= "<br />".$web[350].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=forum&form=2&id=".$fileid.'&cat='.$catthread.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=forum&form=2&id=".$fileid.'</a>';
			if ($serviz[41] != "") {
				$eMsg .= "<br />".$web[351].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=forum&act=a&id=".$fileid.'&cat='.$catthread.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=forum&act=a&id=".$fileid.'</a>';
			} else {
				$eMsg .= "<br />".$web[352].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=forum&act=i&id=".$fileid.'&cat='.$catthread.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=forum&act=i&id=".$fileid.'</a>';
			}
			$eMsg .= "<br />".$web[353].'<a href="'.$site[3]."admin/admin.php?lng=".$lng."&pg=forum&del=".$fileid.'">'.$site[3]."admin/admin.php?lng=".$lng."&pg=forum&del=".$fileid.'</a>';
			$eMsg .= "<hr />".$infos;
			$maillist = GetListCollaboratorEmail(TYP_FORUM);
			eMailHtmlTo($eSub, $eMsg, trim(implode(' ', $maillist).' '.trim($supervision[10]).' '.$user[1]), $user[1], '', $replyto);
		}
		if ($typ == TYP_RECO) {
			$eSub  = $web[125]." ".$pseudo;
			$eMsg  = $web[125]." ".$pseudo.", ".$web[104]." ".$datefrmt.".<br /><br />";
			$eMsg .= $pseudo." - <a href=\"mailto:".$pemail."\">".stripslashes($pemail)."</a> - ".$web[126];
			$eMsg .= " ".stripslashes($site[($lang[0] == $lng ? 0 : 11)])." (<a href='".$site[3]."'>".$site[3]."</a>).<br />";
			$eMsg .= $web[127]." - <a href='mailto:".$pemail."'>".stripslashes($pemail)."</a> - ".$web[128]."<br /><br />";
			eMailHtmlTo($eSub, $eMsg, $pfmail);
		}
		if ($typ == TYP_MAIL) {
			$eSub  = $ptit;
			$eMsg  = stripslashes($site[($lang[0] == $lng ? 0 : 11)])." - ".$web[330]." ".$pseudo." - <a href=\"mailto:".$pemail."\">".stripslashes($pemail)."</a> - ".$web[104]." ".$datefrmt.".<hr />";
			$eMsg .= "<br />".ForceToAbsolute(stripslashes($pmess));
			eMailHtmlTo($eSub, $eMsg, trim($supervision[10].' '.$pfmail), $pemail, '', $replyto);
		}
	}
}
else {
    $antispam  = md5(uniqid(rand()));
    $antispams = ReadDBFields(DBANTISPAM);
    $timestamp = microtime(TRUE);
    array_push($antispams, array($antispam, $REMOTE_ADDR, $typ, $timestamp));
    $timestamp -= $serviz[52] * 60;
    while($antispams[0][3] < $timestamp) {
        array_shift($antispams);
    }
    if (count($antispams) > $serviz[51]) {
        array_shift($antispams);
    }
    WriteDBFields(DBANTISPAM, $antispams);
}

if (!empty($userprefs[1])) $pseudo = $userprefs[1];
if (!empty($userprefs[2])) $pemail = $userprefs[2];

switch ($typ) {
case TYP_GUESTBK :
    $title = $web[41];
    break;
case TYP_NEWS :
    $title = $web[4];
    break;
case TYP_BLOG :
    $title = $web[386];
    break;
case TYP_FORUM :
    $title = $web[19];
    break;
case TYP_RECO :
    $title = $web[124];
    break;
case TYP_REACT :
    $title = $web[182];
    break;
case TYP_REBLOG :
    $title = $web[380];
    break;
case TYP_MAIL :
    $title = $web[215]."  &nbsp;".$web[11];
    break;
default :
    $title = $mod != "" ? $web[308]."  ".$web[334] : $web[65];
}
$topmess = $title;

switch ($typ) {
case TYP_THREAD :
case TYP_FORUM  :
    if ($userprefs[3] =="" || $userprefs[3] == "LR") $userprefs3 = "L";
    $widepage = $forum[3];
    break;
case TYP_BLOG   :
case TYP_REBLOG :
    $widepage = '';
    break;
default :
}

$headinc .= BeginJavascript().'
var clic = 0;
function VerifyForm() {
    var sto = "";
    var messok = "";
    var erreur = false;
    var largeur = 300;
    var progbar = "'.@$site[28].'";

    regexp = /^[-a-zA-Z0-9_]{2,'.$serviz[148].'}$/; // M  Mardi
    if (!regexp.test(document.rapporter.pseudo.value)) {
        sto += "  - '.addslashes($web[266]).'\n"; // Caractères non valides (2 à  caractères comprenant lettres non accentuées, chiffres et _-
		document.rapporter.pseudo.classname.replace("texte", "errorInputText");
        erreur = true;
    }
	regexp = /^[-a-z0-9!#$%&\'*+\\/=?^_`{|}~]+(\.[-a-z0-9!#$%&\'*+\\/=?^_`{|}~]+)*@(([a-z0-9à-ö]([-a-z0-9à-ö]*[a-z0-9à-ö]+)?){1,63}\.)+([a-z0-9à-ö]([-a-z0-9à-ö]*[a-z0-9à-ö]+)?){2,63}$/i;
    if (!regexp.test(document.rapporter.pemail.value)) {
        sto += "  - '.addslashes($web[45]).'\n";
		document.rapporter.pemail.classname.replace("texte", "errorInputText");
        erreur = true;
    }
    if (!regexp.test(document.rapporter.pfmail.value)) {
        sto += "  - '.addslashes($web[122]).'\n"; //
		document.rapporter.pfmail.classname.replace("texte", "errorInputText");
        erreur = true;
    }';
$headinc .= '
    if (document.rapporter.ptit.value.search(/\w/) == -1) {
        sto += "  - '.addslashes($web[123]).'\n"; // Le titre est trop court ou inexistant.
		document.rapporter.ptit.classname.replace("texte", "errorInputText");
        erreur = true;
    }';
if ($typ == TYP_GUESTBK) {
	$headinc .= '
	regexp = /(http|https):\/\/[a-z0-9à-ö-]+(.[a-z0-9à-ö-]+)*(:[0-9]+)?(\/.*)?$/i;
    if (!regexp.test(document.rapporter.purl.value)) {
        sto += "  - '.addslashes($web[229]).'\n"; 
        document.rapporter.purl.classname.replace("texte", "errorInputText");
        erreur = true;
    }';
}
if ($serviz[100] != '0' && $cryptdisp == 'on' && ($serviz[98] == '' || $userprefs[1] == '')) {
	$headinc .= '
    regexp = /^[a-zA-Z0-9_]{2,20}$/;
    if (!regexp.test(document.rapporter.crypt.value)) {
        sto += "  - '.addslashes($web[529]).'\n"; 
		document.rapporter.crypt.classname.replace("texte", "errorInputText");
        erreur = true;
    }';
}
$headinc .= '
    if (erreur) {
        sto = "'.addslashes($web[44]).'\n\n" + sto; 
        alert(sto);
        return false;
    }
    else {
        clic++;
        if (clic == 1) {
            return true;
        }
        else {
            sto = "'.addslashes($web[220]).'" + sto;
            alert(sto);
            return false;
        }
    }
}
'.EndJavascript();

include 'inc/hpage.inc';
if (function_exists('htable1'))
    htable1($title, 'PG'.$tconfig, '100%');
else
    htable($title, '100%');

if (($typ == TYP_NEWS && !isAccessGranted($members[8])) ||
    ($typ == TYP_BLOG && !isAccessGranted($members[16])) ||
    ($typ == TYP_GUESTBK && !isAccessGranted($members[9])) ||
    (($typ == TYP_FORUM || $typ == TYP_THREAD) && (!isAccessGranted($members[10]) || !isAccessGranted($members[5]))) ||
    ($typ == TYP_REACT && (!isAccessGranted($members[11]) || !isAccessGranted($members[1]))) ||
    ($typ == TYP_REBLOG && (!isAccessGranted($members[17]) || !isAccessGranted($members[15])))) {
    echo '<p class="text-center">'.$web[342].'</p><br />';
    if ($members[19] != '0' && $members[19] != '3') {
        $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[3].'-0-'.$tconfig.$z2 : 'connect.php?lng='.$lng.'&tconfig='.$tconfig.$z2);
        $href2 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[15].'-5-new-'.$tconfig.$z2 : $site['USR'].'.php?lng='.$lng.'&uuser=new&tconfig='.$tconfig.$z2);
        echo '
  <p class="text-center">
    '.SubmitButton($web[630], 'self.location.href=\''.$href1.'\'').
      (empty($pseudo) ? '&nbsp;&nbsp;'.SubmitButton($web[160], 'self.location.href=\''.$href2.'\'') : '').'
  </p>';
    }
}
else {
    if ($ok == $web[45]) {
		$pehide = 'off';
        if ($typ == TYP_FORUM || $typ == TYP_THREAD) {
            if (!empty($pg)) {
                $dbpg = SelectDBFieldsById(ReadDBFields(DBFORUM), $num);
                if (count($dbpg) != 0) {
                    $cat = $dbpg[0][12];
                }
				if (in_array($mod, array('maj', 'stop', 'move'))) {
                    CreateDir(TEMPREP);
					ReadDoc($pg);
					$lentxt    = mb_strlen($fieldc1, $charset);
					$possign   = mb_strrpos($fieldc1, '<p class="text-right thsign">', 0, $charset);
					$signforum = $possign !== false ? mb_substr($fieldc1, $possign, $lentxt, $charset) : '';
					$mettre    = '<?php $signforum = stripslashes("'.addslashes($signforum).'"); ?>';
					$filesign  = $userprefs[1].date('Ymdhi');
					WriteFullDB(TEMPREP.$filesign, $mettre);
					$ptxt      = $signforum != '' ? mb_substr($fieldc1, 0, $lentxt - mb_strlen($signforum, $charset), $charset) : $fieldc1;
					$pehide    = $fieldd1;
					$replymail = $fieldmail;
				}
            }
        }
        if ($typ == TYP_BLOG) {
            if (!empty($pg) && $mod == 'maj') {
                ReadDoc($pg);
                $rub  = $lang[0] == $lng ? $fielda1 : $fielda2;
                $ptit = $lang[0] == $lng ? $fieldb1 : $fieldb2;
				$ptxt      = $lang[0] == $lng ? $fieldc1 : $fieldc2;
                $pehide    = $fieldd1;
            }
        }
        if (!isForumAccessGranted($cat)){
            echo '<p class="text-center font-weight-bold frnoaccess"><br />'.$web[443].'</strong><br /></p>';
        } else {
			if (!empty($erreur))
				echo displayErrorMsg($web[44], $erreur);
			echo '
<div class="w-98 mx-auto">
  <p class="text-center"><strong>'.$ok.'</strong></p>
  <form name="rapporter" action="'.($site['URLR'] == 'on' ? $urlrw[14].'-5'.$z2 : $site['PG'].'.php'.$z2).'" method="post" onsubmit="return VerifyForm(); return false;">
    <input type="hidden" name="lng" value="'.$lng.'" />
    <input type="hidden" name="typ" value="'.$typ.'" />
    <input type="hidden" name="tconfig" value="'.$tconfig.'" />
    <input type="hidden" name="antispam" value="'.$antispam.'" />
    <input type="hidden" name="add" value="1" />';
			if ($typ == TYP_FORUM && !empty($cat)) {
				echo '
    <input type="hidden" name="cat" value="'.$cat.'" />
    <input type="hidden" name="catforum" value="'.$cat.'" />';
			}
			if ($typ == TYP_THREAD) {
				echo '
    <input type="hidden" name="filesign" value="'.$filesign.'" />
    <input type="hidden" name="cat" value="'.$cat.'" />
    <input type="hidden" name="tocat" value="'.$tocat.'" />
    <input type="hidden" name="catforum" value="'.$cat.'" />
    <input type="hidden" name="num" value="'.$num.'" />';
			}
			if ($typ != TYP_RECO && $typ != TYP_MAIL) {
				echo '
    <input type="hidden" name="pfmail" value="pf@mail.fr" />';
			}
			if ($typ == TYP_MAIL) {
				echo '
    <input type="hidden" name="pfmail" value="'.$user[1].'" />';
			}
			if ($typ == TYP_GUESTBK || $typ == TYP_RECO || $typ == TYP_THREAD || $typ == TYP_REACT  || $typ == TYP_REBLOG) {
				echo '
    <input type="hidden" name="ptit" value="ptit" />';
			}
			if ($typ == TYP_REACT || $typ == TYP_REBLOG) {
				echo '
    <input type="hidden" name="pg" value="'.$pg.'" />';
			}
			if ($typ != TYP_GUESTBK) {
				echo '
    <input type="hidden" name="purl" value="http://fake" />';
			}
			echo '
    <input class="d-none" type="text" name="code_pseudo" value="" />
    <div class="table-responsive-md">
    <div class="pgtbl d-table m-auto w-100">
      <div class="d-table-row">
        <div class="d-table-cell">
          <div class="input-group pg360 m-auto pb-2">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="far fa-user"></i></span>
            </div>
            <input class="'.($err_pseudo ? 'errorInputText' : 'texte').' form-control" placeholder="'.$web[49].'" 
              type="text" name="pseudo" value="'.$pseudo.'" maxlength="'.$serviz[148].'" />
          </div>
        </div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell">
          <div class="input-group pg360 m-auto pb-3">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-at"></i></span>
            </div>
          <input class="'.($err_pemail ? 'errorInputText' : 'texte').' form-control mt-2" placeholder="'.$web[50].'" 
            type="text" name="pemail" value="'.$pemail.'" />
          </div>
        </div>
      </div>';
			if ($typ != TYP_RECO && $typ != TYP_MAIL) {
				echo '
      <div class="d-table-row">
        <div class="d-table-cell text-center form-check"><label for="pehide">'.$web[115].'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
          <input type="checkbox" class="form-check-input" id="pehide" name="pehide"'.Checked($pehide == 'on').' />
        </div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell text-center">'.$web[30].'</div>
      </div>';
			}
			if ($typ == TYP_MAIL) {
				echo '
      <div class="d-table-row">
        <div class="d-table-cell"><p>'.$web[281].'</p></div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell">
          <input class="'.($err_ptit ? 'errorInputText' : 'texte').' pg360 m-auto form-control" 
            type="text" name="ptit" value="'.$ptit.'" />
        </div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell"><p>'.$web[56].'</p></div>
      </div>';
            }
			if ($typ == TYP_NEWS) {
				echo '
      <div class="d-table-row">
        <div class="d-table-cell text-center">
          <div class="input-group pg360 m-auto py-3">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-heading"></i></span>
            </div>
            <input class="'.($err_ptit ? 'errorInputText' : 'texte').' form-control" placeholder="'.$web[54].'"
              type="text" name="ptit" value="'.$ptit.'" />
          </div>
        </div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell text-center pt-2">'.$web[55].'</div>
      </div>';
			}
			if ($typ == TYP_BLOG) {
                if ($mod == 'maj') echo '
      <input type="hidden" name="filesign" value="'.$filesign.'" />
      <input type="hidden" name="pg" value="'.$pg.'" />
	  <input type="hidden" name="mod" value="maj" />';
				echo '
      <div class="d-table-row">
        <div class="d-table-cell text-center pt-3">'.$web[21].'</div>
      </div>
      <div class="d-table-row">
	    <div class="d-table-cell w-100 mt-n2">';
                $dbw = ReadDBFields(DBBRUB);
                sort($dbw);
                if (!empty($dbw)) {
                    $j = ($lng == $lang[0]) ? 0 : 1 ;
                    echo '
          <select name="rub" class="form-control form-control-lg pg360 m-auto">
            <option value=""> -- '.$web[471].' -- </option>';
                    foreach ($dbw as $birub) {
                        $cat = str_replace('<!--', '&#60;!--', str_replace('-->', '--&#62;', $birub[$j]));
                        echo '
            <option value="'.$birub[$j].'"'.Selected($rub == $birub[$j]).'>'.$cat.'</option>';
                    }
                    echo '
		  </select>';
                }
                else {
                    echo '
		<input class="texte m-auto form-control pg360" type="text" name="rub" size="45" value="'.$rub.'" />';
                }
                echo '
        </div>
	  </div>
      <div class="d-table-row">
        <div class="d-table-cell">
          <div class="input-group pg360 m-auto pt-5">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-heading"></i></span>
            </div>
            <input class="'.($err_ptit ? 'errorInputText' : 'texte').' form-control" placeholder="'.$web[387].'" 
              type="text" name="ptit" value="'.$ptit.'" />
          </div>
        </div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell text-center pt-3">'.$web[388].'</div>
      </div>';
			}
			if ($typ == TYP_REBLOG || $typ == TYP_REACT) {
				ReadDoc($pg);
				$txtbl = ($lng == $lang[0]) ? $fieldc1 : $fieldc2 ;
				echo '
      <div class="d-table-row">
	    <div class="d-table-cell text-center">
          <div class="mt-5">'.$web[384].'</div>
		  <div class="rep m-auto w-95 text-left mt-2 pgrbra">'.stripslashes($txtbl).'</div>
		  <div class="mt-5"><b>'.$web[56].'</b></div>
          <input type="hidden" name="group" value="'.$fieldmod.'" />
	    </div>
	  </div>';
			}
			if ($typ == TYP_GUESTBK) {
                $purl = ($purl == 'https://' ? '' : $purl);
				echo '
      <div class="d-table-row">
        <div class="d-table-cell">
          <div class="input-group pg360 m-auto pt-5">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-globe"></i></span>
            </div>
            <input class="texte form-control" type="text" name="purl" value="'.$purl.'" placeholder="'.$web[51].'" />
          </div>
        </div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell text-center pt-5">'.$web[56].'</div>
      </div>';
			}
			if ($typ == TYP_FORUM) {
				echo '
      <div class="d-table-row">
        <div class="d-table-cell form-check text-center">
          <p><label for="replymail">'.$web[344].' :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
            <input type="checkbox" class="form-check-input" id="replymail" name="replymail" />
          </p>
        </div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell w-100">
          <div class="input-group pg360 m-auto pt-1">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-heading"></i></span>
            </div>
            <input class="'.($err_ptit ? 'errorInputText' : 'texte').' form-control" placeholder="'.$web[64].'" 
              type="text" name="ptit" value="'.$ptit.'" />
          </div>
        </div>
      </div>
      <div class="d-table-row">
        <div class="d-table-cell text-center pt-4">'.$web[56].'</div>
      </div>';
			}
			if ($typ == TYP_RECO) {
				echo '
      <div class="d-table-row">
        <div class="d-table-cell">
          <div class="input-group pg360 m-auto pt-4">
            <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-at"></i></span>
            </div>
            <input class="'.($err_pfmail ? 'errorInputText' : 'texte').' form-control" placeholder="'.$web[121].'" 
              type="text" name="pfmail" value="'.$pfmail.'" />
          </div>
        </div>
      </div>';
			}
			if ($typ == TYP_THREAD) {
				if (!empty($pg)) {
					$rank = ReadDBFields(ADMINS);
					$modadm = ''; $moduser = '';
					for ($y = 0; $y < count($rank); $y++) {
						if ($rank[$y][0] == $userprefs[1] && $rank[$y][2] == 'on') {
							$modadm = 'yes';
							break;
						}
					}
					if ($userprefs[1] == $serviz[31] || $userprefs[1] == $serviz[176]) {
						$modadm = 'yes';
					}
					if (count(SelectDBFields(TYP_FORUM, 'a', $pg)) != 0) {
						$dbtmp = ReadDBFields(DBTHREAD);
						for ($i = 0; $i < count($dbtmp); $i++) { // search topic id
							if ($dbtmp[$i][3] == $pg) {
								$idth = $dbtmp[$i][1];
								break;
							}
						}
						for ($i = 0; $i < count($dbtmp); $i++) {
							if ($dbtmp[$i][1] == $idth && $dbtmp[$i][6] != '') { // read topic
								ReadDoc($dbtmp[$i][3]);
								$st = explode('#',$fieldmod);
								if ($st[0] == 'c' || $st[0] == 's' || $fielda1 != $num) { // if topic closed
									if (($modadm != 'yes' || $mod != 'maj') && $mod != 'raz') {
										echo BeginJavascript().'
alert("'.addslashes($web[47]).'");
window.history.back();'.EndJavascript();
										die ('STOP !'.$web[47]);
									}
								}
								unset($dbtmp);
								break;
							}
						}
						ReadDoc($pg);
					}
                    $dbtmp = ReadDBFields(DBTHREAD);
                    foreach ($dbtmp as $tmp) {
                        if ($num == $tmp[1] && $tmp[5] == $pemail && $tmp[9] == 'on') {
                            $replymail = 'on';
                            break;
                        }
                    }
					echo '
      <div class="d-table-row">
        <div class="d-table-cell text-center form-check">';
                    if ($replymail == 'on') echo '
          <p><label for="noreply">'.$web[687].' :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
            <input type="checkbox" class="form-check-input" id="noreply" name="noreply" />
          </p>';
                    else echo '
          <p><label for="replymail">'.$web[344].' :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
            <input type="checkbox" class="form-check-input" id="replymail" name="replymail" />
          </p>';
                    echo '
        </div>
      </div>';
					if ($mod == '') { // cas normal
						echo '
      <div class="d-table-row">
        <div class="d-table-cell text-center">'.$web[141].' <strong>'.$author.'</strong> '.$web[142].'</div>
      </div>
      <div class="d-table-row">
	    <div class="d-table-cell">
		  <div class="rep position-relative m-auto w-100 text-left pgc1">
		    '.stripslashes($fieldc1).'
		  </div>
          <br />
        </div>
	  </div>';
					} else {
						if ((($author == $userprefs[1]) || $modadm == 'yes' || 
                           (!empty($serviz[31]) && $serviz[31] == $userprefs[1]) || (!empty($serviz[176]) && $serviz[176] == $userprefs[1]))) {
							echo '
      <div class="d-table-row">
	    <div class="d-table-cell">';
							echo '
		  <div class="bord m-auto w-95 text-center pgptxt form-check">';
							$ptit    = $fieldb1;
							$fieldc1 = str_replace(array("\n", "\r"), '', $fieldc1);
							$ptxt    = $typ == TYP_THREAD ? $ptxt : $fieldc1;
							if ($mod == 'maj') { //cas maj
								echo '
		    <input type="hidden" name="mod" value="maj" />';
								$text = '<strong>'.$web[369].' '.$web[7].' '.$web[334].'</strong>';
								if ($ptit != "") {
									$text .= '<div class="rep text-center">'.$ptit.'</div>';
								}
							}
							elseif ($mod == "stop") {
								echo "
		    <input type=\"hidden\" name=\"mod\" value=\"stop\" />";
								$text = "<strong>".$web[390]."</strong>";
								if ($ptit != "") {
									$text .= '<div class="rep text-center">'.$ptit.'</div>';
								}
							}
							elseif ($mod == 'raz') {
								$text = '<strong>'.$web[7].'  '.$web[334].'</strong><div class="rep text-center">'.$ptit.'</div>';
								echo '
		    <input type="hidden" name="mod" value="raz" /><b>'.$web[621].'</b>&nbsp;';  // avoir
							}
							elseif ($mod == 'move') {
								$dbfrom = SelectDBFieldsByField(ReadDBFields(DBFORUMCAT), 0, $cat);
								$dbto   = SelectDBFieldsByField(ReadDBFields(DBFORUMCAT), 0, $tocat);
								$text   = '<strong>'.$web[7].'  '.$web[334].'</strong><div class="rep text-center">'.$ptit.'</div>';
								$text  .= '<b>'.$web[208].'&nbsp;'.$dbfrom[0][1].$web[634].$dbto[0][1].'</b>';
								echo '
		    <input type="hidden" name="mod" value="move" /><b>'.$web[632].'</b>&nbsp;';
							}
							else { //cas lock-up-top
								$text = "<strong>".$web[7]."  ".$web[334]."</strong><div class='rep text-center'>".$ptit."</div>";
								if ($mod == "lock") {
									echo '
		    <input type="hidden" name="mod" value="lock" /><b>'.$web[363].'</b>&nbsp;';
								}
								else {
									if ($forum[5] != "") {
										echo '
		    <input type="radio" class="form-check-input" name="mod" value="up" checked="checked" /><b>'.$web[364].'</b>&nbsp;&nbsp;&nbsp;';
									}
									if ($forum[6] != "" && $mod == "top") {
										echo '
            <input type="hidden" name="forum6" value="'.$forum[6].'" />
		    <input type="radio" class="form-check-input" name="mod" value="top" /><b>'.$web[365].'</b>';
									}
								}
							}
							echo $text;
							echo '
            <input type="hidden" name="pg" value="'.$pg.'" />';
							echo '
          </div>';
							if ($mod == 'stop') {
								echo '<div class="text-center"><strong>'.$userprefs[1].' , '.$web[391].'</strong></div>';
							}
							echo '
        </div>
      </div>';
						}
						else {
							echo '
    </div>
    </div>
    <p class="text-center">';
							echo BeginJavascript().'
alert("'.addslashes($web[416]).'");
window.history.back();'.EndJavascript();
                            echo '
    </p>';
							die ('STOP !');
						}
					}
				}
			}
			echo '
      <div class="d-table-row">
	    <div class="d-table-cell">
      ';
			if ($mod == '' || $mod == 'maj' || $mod == 'stop' || $mod == 'move' || $mod == 'raz') { // pas d'éditeur pour lock, up, top
				echo '
		  <div class="table-responsive-md">
          <div class="d-table w-100 text-center mx-auto">';
				if ($typ != TYP_RECO) {
                    echo '
            <div class="w-100 text-center mx-auto" style="max-width: 560px;">';
                    if (in_array($typ, array(TYP_FORUM, TYP_THREAD, TYP_BLOG, TYP_REBLOG, TYP_REACT, TYP_NEWS))) {
                        $cke_toolbar = !isAuthorImg($userprefs[1]) ? array('toolbarName'=>'Guppy_out') : array('toolbarName'=>'Guppy_out_img');
                    } else {
                        $cke_toolbar = array('toolbarName'=>'Guppy_out_min');
                    }
                    echo display_editor('ptxt', '100%', 300, $ptxt, $cke_toolbar);
                    echo '
            </div>';
				}
                if (!empty($serviz[177]) && !OK_RGPD_date($userprefs[1])) echo '
            <div class="d-table-row">
              <div class="d-table-cell py-4">
                <fieldset class="w-95 m-auto text-left"><legend>'.$web[14].'</legend>
                  <p><strong>'.$web[646].'</strong></p>
                  <p>'.$web[684].'</p>
                  <div class="form-check">
                    <label for="rgpd">'.$web[415].'&nbsp;:&nbsp;</label>
                    <input type="checkbox" class="form-check-input ml-2 mt-2" id="rgpd" name="rgpd"'.Checked($rgpd).' />
                  </div>
                </fieldset>
              </div>
            </div>';
                if ($serviz[100] != '0' && $cryptdisp == 'on' && ($serviz[98] == '' || $userprefs[1] == '')) {
                    $lbl = $serviz[100] == '3' ? $web[642] : '';
                    echo '
            <div class="d-table-row">
              <div class="d-table-cell text-center mt-4 mx-auto">'.dsp_crypt(0, 1, 3, 2, $lbl, $serviz[115] == 'on' ? $web[643] : '').'</div>
            </div>
            <div class="d-table-row">
		      <div class="d-table-cell text-center">
			  '.($serviz[100] == '3' ? '' : $web[530]).'
                <input class="'.($err_crypt ? 'errorInputText' : 'texte').' inputcrypt m-auto form-control" 
                  type="text" name="crypt" onpaste="return false;" />
			  </div>
		    </div>';
                }
                echo '
		  </div>
          </div>';
			}
			echo '
        </div>
	  </div>';
			echo '
      <div class="d-table-row">
        <div class="d-table-cell text-center py-3">'.SubmitButton($web[52]).'</div>
      </div>
    </div>
  </form>
</div>';
			echo BeginJavascript().'
document.rapporter.pseudo.focus();'.EndJavascript();
        } /// modif forum privé
    }
    else {
        echo '
<p class="text-center"><strong>'.$ok.'</strong></p>';
        if ($typ == TYP_NEWS) {
            $backurl = $site['URLR'] == 'on' ? $lng.'-'.$urlrw[8].'-5-'.$tconfig.$z2 : $site['NE'].'.php?lng='.$lng.'&tconfig='.$tconfig.$z2;
        }
        elseif ($typ == TYP_BLOG) {
            $backurl = $site['URLR'] == 'on' ? $lng.'-'.$urlrw[1].'-'.$tconfig.$z2 : $site['BLS'].'.php?lng='.$lng.'&tconfig='.$tconfig.$z2;
        }
        elseif ($typ == TYP_REBLOG) {
            $backurl = $site['URLR'] == 'on' ? $lng.'-'.$urlrw[1].'-40-'.$pg.'-'.$tconfig.$z2 : $site['BL'].'.php?lng='.$lng.'&pg='.$pg.'&tconfig='.$tconfig.$z2;
        }
        elseif ($typ == TYP_GUESTBK) {
            $backurl = $site['URLR'] == 'on' ? $lng.'-'.$urlrw[7].'-'.$tconfig.$z2 : $site['GB'].'.php?lng='.$lng.'&amp;tconfig='.$tconfig.$z2;
        }
        elseif ($typ == TYP_FORUM) {
            $cats     = explode(',', $catforum);
            $catforum = $cats[0];
            if (!empty($catforum)) {
                $backurl = $site['URLR'] == 'on' ? $lng.'-'.$urlrw[5].'-20-'.$catforum.'-'.$tconfig.$z2 : $site['FR'].'.php?lng='.$lng.'&cat='.$catforum.'&amp;tconfig='.$tconfig.$z2;
            }
            else {
                $backurl = $site['URLR'] == 'on' ? $lng.'-'.$urlrw[5].'-31'.'-'.$tconfig.$z2 : $site['FR'].'.php?lng='.$lng.'&amp;tconfig='.$tconfig.$z2;
            }
        }
        elseif ($typ == TYP_THREAD) {
            $dbwork = array();
            $dbwork = ReadDBFields(DBFORUM);
            for ($i = 0; $i < count($dbwork); $i++) {
                if ($dbwork[$i][1] == $num) {
                    $catforum = $dbwork[$i][12];
                }
            }
            $cats     = explode(',', $catforum);
            $catforum = $cats[0];
            if (!empty($catforum)) {
                $backurl = $site['URLR'] == 'on' ? $lng.'-'.$urlrw[5].'-20-'.$catforum.'-'.$tconfig.$z2 : $site['FR'].'.php?lng='.$lng.'&cat='.$catforum.'&amp;tconfig='.$tconfig.$z2;
            }
            else {
                $backurl = $site['URLR'] == 'on' ? $lng.'-'.$urlrw[5].'-31'.'-'.$tconfig.$z2 : $site['FR'].'.php?lng='.$lng.'&amp;tconfig='.$tconfig.$z2;
            }
        }
        elseif ($typ == TYP_REACT) {
            $backurl = $site['URLR'] == 'on' ? $lng.'-'.$urlrw[20].'-102-'.$pg.'-'.$tconfig.$z2 : 'articles.php?lng='.$lng.'&pg='.$pg.'&tconfig='.$tconfig.$z2;
        }
        elseif ($typ == TYP_RECO || $typ == TYP_MAIL) {
            $backurl = $site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng;
        }
        echo BeginJavascript().'
var type = "'.$typ.'";
if (type == "'.TYP_RECO.'" || type == "'.TYP_MAIL.'") {
    setTimeout("window.history.go(-2);", 2500);
}
else {
    setTimeout(\'window.location="'.$backurl.'";\', 2500);
}'.EndJavascript();
    }
}
btable();
include 'inc/bpage.inc';
?>
