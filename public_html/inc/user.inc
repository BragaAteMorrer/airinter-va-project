<?php
/*******************************************************************************
 *   Confirm User preferencies
 *******************************************************************************
 *   GuppY PHP Script - version 6
 *   CeCILL Copyright (C) 2004-2022 by Laurent Duveau
 *   Initiated by Laurent Duveau and Nicolas Alves
 *   Web site = https://www.freeguppy.org/
 *   e-mail   = guppy@freeguppy.org
 *   V6 developed by Lud Bienaimé
 *      with the participation of the GuppY Team
 *******************************************************************************
 *   Latest Changes :
 * v6.00.24 (August 12, 2026) : Delete lines log: Validation Php 8.4																
 ******************************************************************************/

if (stristr($_SERVER['SCRIPT_NAME'], 'user.inc')) {
    header('location:../index.php');
    die();
}
#[AllowDynamicProperties]
class GY_user {
    public $tconfig;
    public $topmess;
    protected $modif;
    protected $uuser;
    protected $modif_ok;
    
    public function __construct() {
        global $nom, $menuico, $serviz, $page, $charset, $userprefs;
        $this->tconfig  = $serviz[172] == 'ALL' ? import('tconfig', '', true, 0) : $serviz[172];
        $this->modif    = import('modif');
        $this->uuser    = import('uuser');
        $this->modif_ok = ($this->uuser == 'old' && $this->modif == 1 && $userprefs[1] != '');
    }
    
    public function USR_ok($pseudo, $lng) {
        global $site, $urlrw, $serviz, $web, $z2, $members;
        $out = '';
        if ($serviz[28] != 'on') {
            $out = '
    <div class="text-center web342">'.$web[143];
            $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
            $out .= '
      <p class="text-center">
        '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
      </p>
    </div>';
            return array('USR', $out);
        }
        elseif ($members[19] == '0' || ($members[19] == '3' && ($pseudo != $serviz[31] || $pseudo != $serviz[176])) && !$this->modif_ok) {
            $out .= '
<script>
alert("'.addslashes($web[512]).'");
window.location="'.CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng).'";
</script>';
            return array('USR', $out);
        }
        return array('');
    }
    
    public function USR_content() {
        global $lang, $lng, $web, $nom, $serviz, $urlrw, $site, $z2, $userprefs, $aml, $wbm, $user,
               $members, $drtuser, $supervision, $_DOMAIN, $_SECURE, $_HTTPONLY, $page, $meskin, 
               $skn_logo, $help, $charset, $headinc;
        $topmess       = getLabel($nom[($lng == $lang[0] ? 34 : 35)]);
        $this->topmess = mb_substr($topmess, 0, 1, $charset) == '§' ? mb_substr($topmess, 1, mb_strlen($topmess ,$charset) ,$charset) : $topmess;
        $crypt         = isset($_POST['crypt']) ? strip_tags($_POST['crypt']) : NULL;
        $cryptok       = (!$crypt) ? false : chk_crypt($crypt);
        $setusercookie = import('setusercookie');
        $REMOTE_ADDR   = getIPadress($site[6] == 'on' ? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']): $_SERVER['REMOTE_ADDR']);
        $out           = '';
        switch ($setusercookie) {
        case NULL :
            $token      = uniqid(rand(), true);
            $token_time = time();
            setcookie(PREF_COOKIE, $token.CONNECTOR.$token_time, time() + (30 * 60), '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
            break;
        case 1 :
            if (isset($_COOKIE[PREF_COOKIE]))
			{
				$tokens     = explode(CONNECTOR, $_COOKIE[PREF_COOKIE]);
				$token_time = time() - (15 * 60);
				$msg = $REMOTE_ADDR.'<br />'.(empty($tokens[0]) ? 'NULL' : $tokens[0]).' => '.@intval($token_time - $tokens[0] / 60);
			}
			else
			{
				$tokens[0] = '';
				$tokens[1] = '0';
			}
            if (empty($tokens[0]) || $tokens[1] < time() - (30 * 60)) {
                $out .= '
        <script>
        var sto = "'.str_replace('<br />', '\n', stripslashes($web[681])).'";
        alert(sto);
        </script>';
                $setusercookie = NULL;
                $token         = uniqid(rand(), true);
                $token_time    = time();
                setcookie(PREF_COOKIE, $token.CONNECTOR.$token_time, time() + (30 * 60), '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
            }
            elseif ($tokens[1] + 2 > time()) {
                $out .= '
        <script>
        var sto = "'.str_replace('<br />', '\n', stripslashes($web[682])).'";
        alert(sto);
        </script>';
                $setusercookie = NULL;
                $token         = uniqid(rand(), true);
                $token_time    = time();
                setcookie(PREF_COOKIE, $token.CONNECTOR.$token_time, time() + (30 * 60), '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
            }
            break;
        }
        $ctrl_spam  = import('code_pseudo', 'POST');
        $unreg      = import('unreg');
        $ulang      = import('ulang');
        $upseudo    = trim(import('upseudo'));
        $upseudo    = 1 == preg_match('/^[-a-zA-Z0-9_]{2,'.$serviz[148].'}$/', $upseudo) && mb_strtolower($upseudo) != 'admin' ? $upseudo : '';
        $uemail     = checkEmail(CutLongWord(import('uemail')));
        $uboxes     = import('uboxes');
        $ushow      = import('ushow');
        $usign      = WrapLongWords(stripslashes(import('usign')));
        $uuid       = import('uuid');
        $uuid2      = import('uuid2');
        $avatar     = import('avatar');
        $avatar     = substr($avatar, 0, 3) == 'ava' ? $avatar : 'ava-00';
        $uwebsite   = import('uwebsite');
        $uextavatar = import('uextavatar');
        $suite      = import('suite');
        $umailct    = import('umailct');
        $umpct      = import('umpct');
        $usendmail  = import('usendmail');
        $usendct    = import('usendct');
        $uredir     = import('uredir');
        $rgpd       = import('rgpd', 'POST', true, '');
        $ishttps    = 1 != preg_match('!^https!', $site[3]) ? false : true;
        $preprot    = $ishttps ? 'https://' : 'http://';
        $uwebsite   = empty($uwebsite) ? 'http://' : $uwebsite;
        $uextavatar = empty($uextavatar) ? $preprot : $uextavatar;
        $err_pseudo = (empty($upseudo) || $upseudo != import('upseudo')) && $setusercookie == 1 ? true: false;
        $notPassOK  = !empty($uuid) && (!ctrlPasswordOK($uuid));
        $err_uid    = ((empty($uuid) && $this->modif != '1') || $notPassOK) && $setusercookie == 1 ? true: false;
        $err_uid2   = $uuid != $uuid2 && $setusercookie == 1 ? true: false;
        $err_uemail = empty($uemail) && $setusercookie == 1 ? true: false;
        $err_uweb   = 'http://' != $uwebsite && 'https://' != $uwebsite && 1 != preg_match('/(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/', $uwebsite) && $setusercookie == 1 ? true : false;
        $err_uext   = $avatar == 'ava-00' && 1 != preg_match('/(http|https|inc\/img\/avatars):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/', $uextavatar) && $setusercookie == 1 ? true : false;
        $err_uext   = $err_uext && !IsImage(substr($uextavatar, -3));
        $err_crypt  = $serviz[100] != '0' && $serviz[88] == 'on' && ($userprefs[1] == '' || $serviz[98] == '') && !$cryptok && $setusercookie == 1 ? true: false;
        $err_rgpd   = !empty($serviz[177]) && empty($rgpd) && !OK_RGPD_date($userprefs[1]) && $setusercookie == 1 ? true: false;
        $erreur     = '';
        $erreur    .= $err_pseudo ? '<li>'.$web[266].'</li>': '';
        $erreur    .= $err_uid    ? '<li>'.$web[302].'</li>': '';
        $erreur    .= $err_uid2   ? '<li>'.$web[466].'</li>': '';
        $erreur    .= $err_uemail ? '<li>'.$web[42].'</li>': '';
        $erreur    .= $err_uweb   ? '<li>'.$web[545].$web[51].$web[554].'</li>': '';
        $erreur    .= $err_uext   ? '<li>'.$web[545].$web[627].$web[554].'</li>': '';
        $erreur    .= $err_crypt  ? '<li>'.$web[529].'</li>': '';
        $erreur    .= $err_rgpd   ? '<li>'.$web[405].'</li>': '';
        $uwebsite   = $err_uweb ? 'http://' : $uwebsite;
        $uextavatar = $err_uext ? $preprot : $uextavatar;
        $usermess   = '';
        // contrôle en attente déjà inscrit
        if (is_file(MSGREP.$upseudo.DBEXT)) {
            $dbmsg = ReadDBFields(MSGREP.$upseudo.DBEXT);
            $dbmsg[1][31] = empty($dbmsg[1][31]) ? '0' : $dbmsg[1][31];
            if ($dbmsg[1][31] == '1' || $dbmsg[1][31] == '2') {
                switch($suite) {
                    case '' : // inscription en attente
                        $out = '
<br />
<p class="text-center"><strong>'.$web[577].'</strong></p>';
                        break;
                    case 'ok' : // confirmation mail
                        $md5 = md5(gy_decrypt(CRYPT_KEY, $dbmsg[0][0]));
                        if ($md5 == $dbmsg[1][8] && $uuid == $dbmsg[1][8]) {
                            $dbmsg[0][0]  = $md5;
                            $dbmsg[1][31] = 'ok';
                            WriteDBFields(MSGREP.$upseudo.DBEXT, $dbmsg);
                            unset($dbmsg);
                            $out = '
<script>
window.location="'.CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[3].'-0-'.$this->tconfig.$z2 : 'connect.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'"
</script>';
                        } 
                        else {
                            $out = 'STOP : bad password';
                        }
                        break;
                    case 'go' : // confirmation webmaster
                        if ($userprefs[1] == $serviz[31] || $userprefs[1] == $serviz[176] || $drtuser[42] == 'admin') {
                            $uuid         = gy_decrypt(CRYPT_KEY, $dbmsg[0][0]);
                            $dbmsg[0][0]  = md5($uuid);
                            $dbmsg[1][31] = 'ok';
                            WriteDBFields(MSGREP.$upseudo.DBEXT, $dbmsg);
                            $out   .= sprintf($web[558], $upseudo);
                            $uemail = $dbmsg[1][3];
                            $to     = $uemail;
                            $sujet  = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' - '.$web[515];
                            $bodycorps  = $web[245].sprintf($web[516], $upseudo, stripslashes($site[($lang[0] == $lng ? 0 : 11)])).'<br /><br />';
                            $bodycorps  .= $web[49].' '.$upseudo.'<br />'.$web[300].' : '.$uuid.'<br />'.$web[517];
                            $bodycorps  .= '<a href="'.$site[3].'index.php?lng='.$lng.'">'.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'</a><br />';
                            $bodycorps  .= $web[235].'<br />'.$user[0].'<br />';
                            eMailHtmlTo($sujet,$bodycorps,$to);
                        } 
                        else {
                            $out = '
<br />
<p class="text-center"><strong>'.$web[513].'</strong></p>';                    
                        }
                        break;
                    case 'no' : // refus
                        if ($userprefs[1] == $serviz[31] || $userprefs[1] == $serviz[176] || $drtuser[42] == 'admin') {
                            DestroyDBFile(MSGREP.$upseudo.DBEXT);
                            $uemail = $dbmsg[1][3];
                            $to     = $uemail;
                            $sujet  = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' - '.$web[518];
                            $bodycorps  = $web[240].sprintf($web[519], $upseudo, stripslashes($site[($lang[0] == $lng ? 0 : 11)])).'<br /><br />';
                            $bodycorps  .= $web[235].'<br />'.$user[0].'<br />';
                            eMailHtmlTo($sujet,$bodycorps,$to);                    
                            $out = '
<br />
<p class="text-center"><strong>'.$web[518].'<br />'.$upseudo.'</strong></p>';                    
                        } 
                        else {
                            $out = '
<script> window.location="'.CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[3].'-0-0'.$z2 : 'connect.php?lng='.$lng.'&tconfig=0'.$z2).'" </script>';
                        }
                        break;
                    default: /// attente de confirmation
                        switch($members[19]) {
                        case 1 :
                            $out = '
<br />
<p class="text-center"><strong>'.$web[513].'</strong></p>';
                            break;
                        case 2 :
                            $out = '
<br />
<p class="text-center"><strong>'.$web[514].'</strong></p>';
                            break;
                        }
                        break;
                }
                return array('USR', $out);
            }
        }
        if ($suite != '') {
            switch($suite) {
                case 'ok' : // confirmation mail
                    $out = '
<script>
window.location="'.CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[3].'-0-'.$this->tconfig.$z2 : 'connect.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'"
</script>';
                    break;
                case 'no' : // refus
                    $out = '
<br />
<p class="text-center"><strong>'.$web[518].'</strong></p>';
                    break;
                default :
                    $out = '
<script> window.location="'.CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng).'" </script>';
            }
            return array('USR', $out);
        }
        // enregistrement nouveau ou modif
        if ($setusercookie == 1 && empty($ctrl_spam)) {
            $aconnect  = isset($_COOKIE[CNCT_COOKIE]) ? explode(CONNECTOR, gy_decrypt(CRYPT_KEY, $_COOKIE[CNCT_COOKIE])) : array(0, '');
            $is_pseudo = !empty($aconnect[1]);
            $pseudok   = ($this->uuser == 'old' && $userprefs[1] == $upseudo && $is_pseudo) || $this->uuser == 'new' ? true : false;
            if ($pseudok) {
                $msg    = '';
                $dbmail = array();
                $bad    = array('.', '..', 'index.php');
                $files  = scandir(MSGREP);
                $files  = array_diff($files, $bad);
                foreach ($files as $file) {
                    if ($file == $upseudo.DBEXT) {
                        if ($this->uuser == 'new') {
                            $pseudok = false;
                            $msg     = $web[231];
                            break;                
                        }
                    }
                    else {
                        $userdb = ReadDBFields(MSGREP.$file);
                        if (!empty($userdb[1][3])) $dbmail[] = $userdb[1][3];
                    }
                }
            } 
            else $msg = $web[404];        
            if ($pseudok && ($this->uuser == 'new' || ($this->modif == 1 && $userprefs[2] != $uemail)) && in_array($uemail, $dbmail)) {
                $pseudok    = false;
                $msg       .= ' - '.$web[557];
                $err_uemail = true;
                $err_uid    = $uuid != $uuid2 ? true: false;
				$err_uid    = $uuid != '' ? true: false;
                $err_uid2   = $uuid != $uuid2 ? true: false;
            }
            if ($pseudok) {
                $continue = true;
                if (($serviz[100] != '0' && $serviz[88] == 'on' && ($userprefs[1] == '' || $serviz[98] == '')) && $err_crypt) {
                    $msg     .= ' - '.$web[529];
                    $continue = false;
                }
                if ($continue) {
                    if ($uextavatar != $preprot) {
                        $imghttps   = 1 != preg_match('!^https!', $uextavatar) ? false : true;
                        $uextavatar = $ishttps && !$imghttps ? 'inc/img/avatars/'.basename($uextavatar) : $uextavatar;
                        $avaexist   = $ishttps && !$imghttps ? CHEMIN.$uextavatar : $uextavatar;
                        if (file_exists($avaexist)) {
                            list($wimg, $himg, $timg, $aimg) = getimagesize($avaexist);
                            if ($wimg == 0 || $himg == 0 || $wimg > 64 || $himg > 64) {
                                $msg .= ' - '.$web[628];
                                $continue = false;
                            }
                        }
                    }
                }
                if ($continue && empty($erreur)) {
                    $uuid    = empty($uuid) && $this->uuser == 'new' ? GenerateUID() : $uuid;
					$apachepassword  = Md5Crypt::apache ($uuid, $salt ='');
                    $userdb  = is_file(MSGREP.$upseudo.DBEXT) ? ReadDBFields(MSGREP.$upseudo.DBEXT) : array();
                    $lng     = $ulang;
                    $uavaext = !empty($userdb[1][22]) ? $userdb[1][22] : 'user21';
                    $uimgext = !empty($userdb[1][23]) ? $userdb[1][23] : 'user22';
                    $usign     = str_replace(chr(10), '<br />', RemoveConnector(stripslashes($usign)));
                    $cryptpass = $apachepassword;
                    if (is_file(CHEMIN.'data/config/.htpasswd')) {
                        $htpasswd = @file(CHEMIN.'data/config/.htpasswd');
                        $protoht  = trim($htpasswd[0]);
                        if (!empty($protoht)) $cryptpass = $protoht == '#:0-1-0' ? gy_crypt(CRYPT_KEY, $uuid) : $apachepassword;
                    }
                    $userdb[0][0] = $this->uuser == 'new' 
                      ? (in_array($members[19], array('1', '2')) ? gy_crypt(CRYPT_KEY, $uuid) : md5($uuid)) 
                      : ($this->modif == '1' && empty($uuid) ? $userdb[0][0] : md5($uuid));
                    $userdb[0][1]  = $cryptpass;
                    $userdb[1][0]  = '0';
                    $userdb[1][1]  = $ulang;    // langue
                    $userdb[1][2]  = $upseudo;  // pseudo
                    $userdb[1][3]  = $uemail;   // email
                    $userdb[1][4]  = '';        // inutilisé
                    $userdb[1][5]  = $ulang;    // langue
                    $userdb[1][6]  = $ushow;    // afficher pseudo dans connectés
                    $userdb[1][7]  = $usign;    // signature forum
                    $userdb[1][8]  = $this->modif == '1' && empty($uuid) ? $userdb[0][0] : md5($uuid);   // pass
                    $userdb[1][9]  = $avatar;                                                            // avatar
                    $userdb[1][10] = $uwebsite == 'https://' || $uwebsite == 'http://' ? '' : $uwebsite; // site web
                    $userdb[1][11] = '';    // inutilisé
                    $userdb[1][12] = '';    // éditeur
                    $userdb[1][13] = 'on';  // 'on' par défaut
                    $userdb[1][14] = is_file(MSGREP.$upseudo.DBEXT) && !empty($userprefs[13]) ? $userprefs[13] : GetCurrentDateTime(); // date enregistrement
                    $userdb[1][15] = is_file(MSGREP.$upseudo.DBEXT) && !empty($userprefs[14]) ? $userprefs[14] : GetCurrentDateTime(); // date dernière connexion
                    $userdb[1][16] = $uextavatar == $preprot || $avatar != 'ava-00' || $uavaext == 'on' ? '' : $uextavatar;        // avatar externe
                    $userdb[1][17] = is_file(MSGREP.$upseudo.DBEXT) ? $userprefs[14] : GetCurrentDateTime();                           // date déconnexion
                    $userdb[1][18] = @$userdb[1][18];   // pas autorisé MP
                    $userdb[1][19] = @$userdb[1][19];   // pas autorisé email
                    $userdb[1][20] = @$userdb[1][20];   // pas autorisé profils membres
                    $userdb[1][21] = @$userdb[1][21];   // pas autorisé liste des membres
                    $userdb[1][22] = $uavaext;         // interdire avatar externe
                    $userdb[1][23] = $uimgext;         // interdire image signature externe
                    $userdb[1][24] = $umailct;         // membres peuvent envoyer email
                    $userdb[1][25] = $umpct;           // membres peuvent envoyer MP
                    $userdb[1][26] = $usendmail;       // avertir par email arrivée MP
                    $userdb[1][27] = $usendct;         // accusé lecture MP
                    $userdb[1][28] = $this->uuser == 'new' || $rgpd == 'on' ? date('Ymd') : @$userdb[1][28]; // acceptation RGPD
                    $userdb[1][29] = '';               // inutilisé
                    $userdb[1][30] = $uredir;          // redirection vers l'accueil
                    $userdb[1][31] = $members[19] == '' ? 'ok' : ($this->uuser == 'new' ? $members[19] : $userdb[1][31]);   // flag inscription 
                    
                    if ($this->uuser == 'new') {
                        $padmin = trim($serviz[31]) == '' ? (trim($serviz[176]) == '' ? $user[0] : $serviz[176]) : $serviz[31];
                        if ($serviz[31] != '') {
                            $userdb[2][0] = $padmin.CONNECTOR.GetCurrentDateTime().CONNECTOR.'&nbsp; '.
                            $web[253].' '.sprintf($web[167], stripslashes($site[($lang[0] == $lng ? 0 : 11)]), $upseudo).CONNECTOR.'new'.CONNECTOR.
                                CONNECTOR.CONNECTOR.CONNECTOR.$serviz[43].CONNECTOR.substr($page[23], 4);
                        }
                        if (!empty($upseudo) && $ushow == 'on' && $serviz[111] == 'on') {
                            $regusers    = ReadDBFields(DBIPSTATS);
                            $usersonline = array();
                            $myIP        = '';
                            foreach ($regusers as $reg) {
                                if (!empty($reg[2])) {
                                    if ($upseudo == $reg[2]) $myIP = $reg[0];
                                    $usersonline[] = array($reg[2], $reg[0]);
                                }
                            }
                            @usort($usersonline,'AsciiCompare');
                            $uniqueusers   = array();
                            $uniqueusers[] = $usersonline[0];
                            for ($i = 1; $i < count($usersonline); $i++) {
                                if ($usersonline[$i][0] != $usersonline[$i-1][0]) {
                                    $uniqueusers[] = $usersonline[$i];
                                }
                            }
                            $visits  = ReadDBFields(USERREP.'visitsofday.dtb');
                            $today   = date('Ymd');
                            $visits1 = array();
                            foreach ($visits as $visit) if ($visit[1] == $today) $visits1[] = $visit;
                            $found = false;
                            foreach ($uniqueusers as $unuser) {
                                foreach ($visits1 as $visit) if ($visit[0] == $unuser[0]) $found = true;
                                if (!$found && !empty($unuser[0])) $visits1[] = array($unuser[0], $today);
                            }
                            WriteDBFields(USERREP.'visitsofday.dtb', $visits1);
                        }
                    }
                    if (!empty($userprefs[4])) include CHEMIN.'inc/lang/'.$userprefs[4].'-web'.INCEXT;
                    else include CHEMIN.'inc/lang/'.$lng.'-web'.INCEXT;
                    if ($this->uuser == 'new' && $supervision[11] == 'on') {
                        $sujet = $web[608];
                        $bodycorps  = $web[253].' '.$user[0].'<br /><br />'.sprintf($web[609], $upseudo, stripslashes($site[($lang[0] == $lng ? 0 : 11)])).'<br /><br />';
                        $bodycorps .= $web[455].getIPadress($site[6] == 'on' ? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']): $_SERVER['REMOTE_ADDR']).'<br /><br />';
                        $to    = $user[1];
                        eMailHtmlTo($sujet, $bodycorps, $to);
                    }
                    if ($members[19] == '' || $this->modif == '1') {
                        WriteDBFields(MSGREP.$upseudo.DBEXT, $userdb);
                        $userdata = '';
                        for ($i = 1; $i < 32; $i++) {
                            $stri      = sprintf("%02d", $i - 1);
                            $strlen    = empty($userdb[1][$i]) ? '03' : sprintf("%02d", strlen($userdb[1][$i]));
                            $userdata .= $stri.$strlen.(empty($userdb[1][$i]) ? 'off' : $userdb[1][$i]);
                        }
// avant                        $usertime = !empty($members[21]) ? intval($members[21]) : 157680000;
// avant                        $expires  = (is_file(CHEMIN.'admin/'.REDACREP.$userprefs[1].INCEXT) || $userprefs[1] == $serviz[31] || $userprefs[1] == $serviz[176]) ? 157680000 : $usertime;
//  avant               setcookie(USER_COOKIE, gy_crypt(CRYPT_KEY, $userdata), time() + $expires, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
						setcookie(USER_COOKIE, gy_crypt(CRYPT_KEY, $userdata), strtotime( '+365 days' ), '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
                        $userconnect = gy_crypt(CRYPT_KEY, time().CONNECTOR.$userprefs[1].CONNECTOR.date('Ymdhi'));
 // avant               setcookie(CNCT_COOKIE, $userconnect, time() + $expires, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
						setcookie(CNCT_COOKIE, $userconnect, strtotime( '+365 days' ), '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
                        $usermess = $web[161];
                        if ($this->uuser == 'old' && !empty($uuid)) {
                            UpdateCollaboratorPass($upseudo, md5($uuid));
                            UpdateProtectMember($upseudo, $cryptpass);
                        }
                        $out .= '
<br />
<p class="text-center"><b>'.$usermess.'</b></p>';
                        $out .= '
<br />
<script>
window.setTimeout(\'window.location="'.CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[3].'-14-'.$this->tconfig.$z2 : 'compte.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'"\', 3000);
</script>';
                        return array('USR', $out);
                    }
                    WriteDBFields(MSGREP.$upseudo.DBEXT, $userdb);
                    if ($members[19] == '1') { // confirmation par mail
                        $to    = $uemail;
                        $sujet = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' - '.$web[523];
                        $img   = explode('|', $page[4]);
                        $urli  = substr($img[0], 0, 1) == 'I' ? CHEMIN.'img/' : $meskin.'img/';
                        $logo  = $skn_logo != '' && empty($page[4]) ? $skn_logo : $urli.$img[1];
                        $bodycorps  = '<div style="margin:auto;padding:8px;width:600px;border: solid 1px #000000;border-radius:4px;">';
                        $bodycorps .= '<p style="text-align:center;"><img src="'.$site[3].$logo.'" style="max-width:580px;" /></p><hr />';
                        $bodycorps .= '<p style="text-align:left;font:12pt Arial;">';
                        $bodycorps .= $web[245].sprintf($web[524], $upseudo, stripslashes($site[($lang[0] == $lng ? 0 : 11)])).'<br /><br />';
                        $bodycorps .= $web[49].' <b>'.$upseudo.'</b><br />'.$web[300].' : <b>'.$uuid.'</b><br /><br />';
                        $bodycorps .= $web[525];
                        $bodycorps .= '<a href="'.$site[3].($site['URLR'] == 'on' ? $lng.'-'.$urlrw[15].'-1-ok-'.$upseudo.'-'.md5($uuid).'-'.$this->tconfig.$z2 : $site['USR'].'.php?lng='.$lng.'&suite=ok&upseudo='.$upseudo.'&uuid='.md5($uuid).'&amp;tconfig='.$this->tconfig.$z2).'">'.stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'</a><br />';
                        $bodycorps .= $web[235].'<br />'.$user[0].'<br />';
                        $bodycorps .= '</p></div>';
                        eMailHtmlTo($sujet, $bodycorps, $to);
                        $response = '<br /><p style="text-align:center;"><strong>'.$web[513].'</strong></p>';
                    }
                    elseif ($members[19] == '2') { // confirmation webmaster
                        $sujet = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' - '.ucfirst($web[321]);
                        $img   = explode('|', $page[4]);
                        $urli  = substr($img[0], 0, 1) == 'I' ? CHEMIN.'img/' : $meskin.'img/';
                        $logo  = $skn_logo != '' && empty($page[4]) ? $skn_logo : $urli.$img[1];
                        $bodycorps  = '<div style="margin:auto;padding:8px;width:600px;border: solid 1px #000000;border-radius:4px;">';
                        $bodycorps .= '<p style="text-align:center;"><img src="'.$site[3].$logo.'" style="max-width:580px;" /></p><hr />';
                        $bodycorps .= '<p style="text-align:left;font:12pt Arial;">';
                        $bodycorps .= sprintf($web[520], $upseudo, $uemail, stripslashes($site[($lang[0] == $lng ? 0 : 11)])).'<hr />';
                        $bodycorps .= $web[521].' : ';
                        $bodycorps .= '<a href="'.$site[3].($site['URLR'] == 'on'
                            ? $lng.'-'.$urlrw[15].'-2-go-'.$upseudo.'-'.$this->tconfig.$z2 
                            : $site['USR'].'.php?lng='.$lng.'&suite=go&upseudo='.$upseudo.'&amp;tconfig='.$this->tconfig.$z2).'">'
                            .$site[3].($site['URLR'] == 'on'? $lng.'-'.$urlrw[15].'-2-go-'.$upseudo: 'user.php?lng='.$lng.'&suite=go&upseudo='.$upseudo).'</a><br />';
                        $bodycorps .= $web[522].' : ';
                        $bodycorps .= '<a href="'.$site[3].($site['URLR'] == 'on' 
                            ? $lng.'-'.$urlrw[15].'-2-no-'.$upseudo.'-'.$this->tconfig.$z2 
                            : $site['USR'].'.php?lng='.$lng.'&suite=no&upseudo='.$upseudo.'&amp;tconfig='.$this->tconfig.$z2).'">'
                            .$site[3].($site['URLR'] == 'on'? $lng.'-'.$urlrw[15].'-2-no-'.$upseudo: 'user.php?lng='.$lng.'&suite=no&upseudo='.$upseudo).'</a>';
                        eMailHtmlTo($sujet, $bodycorps);
                        $response = '<br /><p style="text-align:center;"><strong>'.$web[514].'</strong></p>';
                    }
                    $out .= $response;
                    return array('USR', $out);
                }
            }
            if (!empty($msg)) $out .= '<script>alert("'.addslashes($msg).'");</script>';
        }
        // suppression des préférences
        if ($unreg == 1) {
            $dbmsg = ReadDBFields(MSGREP.$userprefs[1].DBEXT);
            if ($userprefs[1] == $dbmsg[1][2]) {
                $username = $userprefs[1];
                DeleteMemberProtect($username);
                DestroyMemberCol($username);
                DestroyMember($username);
                DestroyDBFile(CHEMIN.'admin/'.REDACREP.$username.INCEXT);
                DestroyDBFile(MSGREP.$username.DBEXT);
                DestroyDBFile(COUNTMSGREP.$username.DBEXT);
                DeleteAdmin($username);
                if ($supervision[11] == 'on') {
                    $sujet = $web[610];
                    $bodycorps  = $web[253].' '.$user[0].'<br /><br />'.sprintf($web[611], $username, stripslashes($site[($lang[0] == $lng ? 0 : 11)])).'<br /><br />';
                    $bodycorps .= $web[455].getIPadress($site[6] == 'on' ? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']): $_SERVER['REMOTE_ADDR']).'<br /><br />';
                    $to    = $user[1];
                    eMailHtmlTo($sujet, $bodycorps, $to);
                }
                $userprefs = array_fill(0, 31, '');
                if (isset($_COOKIE[USER_COOKIE])) @setcookie(USER_COOKIE, '', -1, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
                if (isset($_COOKIE[PREF_COOKIE])) @setcookie(PREF_COOKIE, '', -1, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
                if (isset($_COOKIE[CNCT_COOKIE])) @setcookie(CNCT_COOKIE, '', -1, $_DOMAIN, $_SECURE, $_HTTPONLY);
                if (isset($_COOKIE[COOKIE_COOKIE])) @setcookie(COOKIE_COOKIE, '', -1, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
                $usermess = $web[163];
                $out .= '
<br />
<p class="text-center"><b>'.$username.' '.$usermess.'...!</b></p>
<br />';
                $out .= '
<script>
window.setTimeout(\'window.location="'.CHEMIN.($site['URLR'] == 'on'? $lng.'-'.$urlrw[0]: 'index.php?lng='.$lng).'"\', 3000);
</script>';
            }
            return array('USR', $out);
        }
        // cas normal inscription ou modif
        if (empty($userprefs[7])) $userprefs[7] = GenerateUID();
        else $dbmsg = ReadDBFields(MSGREP.$userprefs[1].DBEXT);
        $headinc .= '
<script>
function VerifyForm() {
	var sto = "";
	var erreur = false;
	var modif = "'.$this->modif.'";
	var uid   = document.userprefnow.uuid.value;
	var uid2  = document.userprefnow.uuid2.value;
	var uweb  = document.userprefnow.uwebsite.value;
	var uext  = document.userprefnow.uextavatar.value;
	regexp = /^[-a-zA-Z0-9_]{2,'.$serviz[148].'}$/;
	if (!regexp.test(document.userprefnow.upseudo.value)) {
		sto += "  - '.str_replace('"', '\"', stripslashes($web[266])).'\n";
		document.userprefnow.upseudo.className = "errorInputText";
		erreur = true;
	}
	if (modif == "1" && uid != "") {
        regexp = /^\S{5,40}$/;
		if (!regexp.test(uid)) {
			sto += "  - '.str_replace('"', '\"', stripslashes($web[302])).'\n";
			document.userprefnow.uuid.className = "errorInputText";
			erreur = true;
		}
	}
	if (uid != uid2) {
		sto += "  - '.str_replace('"', '\"', stripslashes($web[466])).'\n";
		document.userprefnow.uuid.className = "errorInputText";
		document.userprefnow.uuid2.className = "errorInputText";
		erreur = true;
	}
	regexp = /^[^\.\s]+(\.[^\.\s]+)*@[^\.\s]+(\.[^\.\s]+)+$/;
	if (!regexp.test(document.userprefnow.uemail.value)) {
		sto += "  - '.str_replace('"', '\"', stripslashes($web[42])).'\n";
		document.userprefnow.uemail.className = "errorInputText";
		erreur = true;
	}';
        $headinc .= '
	regexp = /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
    if (uweb != "http://" && uweb != "https://" && !regexp.test(uweb)) {
        sto += "  - '.str_replace('"', '\"', stripslashes($web[545].$web[51].$web[554])).'\n";
		document.userprefnow.uwebsite.className = "errorInputText";
		document.userprefnow.uwebsite.value = "http://";
        erreur = true;
    }';
        if ($page[23] != 'none') {
            $headinc .= '
	regexp = /(http|https|inc/img/avatars):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
    if (uext != "'.$preprot.'" && !regexp.test(uext)) {
        sto += "  - '.str_replace('"', '\"', stripslashes($web[545].$web[627].$web[554])).'\n";
		document.userprefnow.uextavatar.className = "errorInputText";
		document.userprefnow.uextavatar.value = "'.$preprot.'";
        erreur = true;
    }';
        }
        if ($serviz[100] != '0' && ($serviz[98] == '' || $userprefs[1] == '') && $crypt) {
            $headinc .= '
    regexp = /^[a-zA-Z0-9_]{2,20}$/;
	if (!regexp.test(document.userprefnow.crypt.value)) {
		sto += "  - '.str_replace('"', '\"', stripslashes($web[529])).'\n";
		document.userprefnow.crypt.className = "errorInputText";
		erreur = true;
	}';
        }
        $headinc .= '
	if (erreur) {
        sto = "'.str_replace('"', '\"', stripslashes($web[265])).'\n\n" + sto;
		alert(sto);
		return false;
	}
}
</script>';
        if (!empty($erreur)) $out .= displayErrorMsg($web[638], $erreur);
        $out .= '
<div class="text-center w-100 m-auto user-form">
	<p class="text-center">'.$web[156].'</p>
	<p class="text-center">'.$web[157].'</p>
	<hr />
  <div class="text-left w-100">';
        if (!empty($usermess)) {
            $out .= '
	<p class="text-center"><b>'.$usermess.'</b></p>
	<br />';
        }
        if ($userprefs[1] != '') $value_pseudo = 'value="'.$userprefs[1].'" readonly="readonly"';
        else $value_pseudo = 'value="'.$upseudo.'"';
        $out .= '
    <style>
    .testuid .input-group-text, .confirmuid .input-group-text { margin-top: 0.4rem; height: 3.35rem; }
    .testuid .input-group-text i, .confirmuid .input-group-text i { font-size: 1.5rem; }
    .testuid .popuserpwd { padding-right: 14px; }
    .userselect { background-color: inherit; }
    </style>
    <script>
        $("#testuid").on("keyup", function(e) {
          var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\\\W).*$", "ig");
          var mediumRegex = new RegExp("^(?=.{8,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
          var okRegex = new RegExp("(?=.{8,}).*", "g");
          if (okRegex.test($(this).val()) === false) {
            $("#testuid-strength").removeClass().addClass("alert alert-error").html("'.$help[21].'");
          } else if (strongRegex.test($(this).val())) {
            $("#testuid-strength").removeClass().addClass("alert alert-success").html("'.$help[22].'");
          } else if (mediumRegex.test($(this).val())) {
            $("#testuid-strength").removeClass().addClass("alert alert-info").html("'.$help[23].'");
          } else {
            $("#testuid-strength").removeClass().addClass("alert alert-error").html("'.$help[24].'");
          }
          return true;
        });
      });
    </script>
	<form name="userprefnow" 
      action="'.($site['URLR'] == 'on' 
        ? $lng.'-'.$urlrw[15].'-'.$this->tconfig.$z2 
        : $site['USR'].'.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'" 
        method="post" 
        onsubmit="return VerifyForm(); return false;">
	  <input type="hidden" name="setusercookie" value="1" />';
        if ($this->modif == '1') {
            $out .= '
	  <input type="hidden" name="modif" value="1" />
	  <input type="hidden" name="uuser" value="old" />';
        } 
        else
            $out .= '
	  <input type="hidden" name="uuser" value="'.$this->uuser.'" />
      <span class="d-none"><input type="text" name="code_pseudo" value="" /></span>';
        $out .= '
	  <fieldset class="mt-4"><legend>'.$web[377].'</legend>
        <div class="form-row">
		  <div class="col-md-5">&bull; '.$web[49].'</div>
		  <div class="col-md-5">
            <input type="text"  class="'.($err_pseudo ? 'errorInputText' : 'texte').' form-control" name="upseudo" '.$value_pseudo.' />
          </div>
        </div>
        <div class="form-row">
		  <div class="col-md-12" id="testuid-strength"></div>
        </div>
        <div class="form-row">
		  <div class="col-md-5">&bull; '.$web[300].' :</div>
		  <div class="col-md-5 col-10 pr-0">
            <div class="testuid input-group mb-3 pr-0 w-100">
              <input 
                id="testuid" 
                type="password" 
                class="'.($err_uid ? 'errorInputText' : 'texte').' form-control float-left" 
                name="uuid" 
                value="'.($this->modif == '1' ? '' : ($userprefs[1] != '' ? $userprefs[7] : $uuid)).'" 
              />
              <div id="testuid-eye" class="input-group-append c-pointer">
                <span class="input-group-text"><i id="testuid-i" class="far fa-eye"></i></span>
              </div>
              <script>
                document.getElementById("testuid-eye").addEventListener("click", function(e){
                  var pwd = document.getElementById("testuid");
                  var eye = document.getElementById("testuid-i");
                  if(pwd.getAttribute("type") == "password") {
                    pwd.setAttribute("type", "text");
                    eye.setAttribute("class", "far fa-eye-slash");
                  } else {
                    pwd.setAttribute("type", "password");
                    eye.setAttribute("class", "far fa-eye");
                  }
                });
              </script>
            </div>
          </div>
		  <div class="col-md-2 col-2 px-0">
            '.($this->modif == '1' ? '
            <script>
              $(document).ready(function(){
                $(\'.popuserpwd\').popover({
                  title:"'.$web[300].'",
                  content:"'.$help[3].'",
                  trigger:"hover",
                  placement:"auto",
                  html:true
                });
              });
            </script>
            <div class="overpop">&nbsp;
              <a class="popuserpwd float-left" title="">
                <i class="far fa-comment-dots"></i>
              </a>
            </div>
            ' : '').'
          </div>
        </div>
        <div class="form-row">
		  <div class="col-md-5">&bull; '.$web[467].' :</div>
		  <div class="col-md-5 col-10 pr-0">
            <div class="confirmuid input-group mb-3 px-0 w-100">
              <input 
                id="confirmuid" 
                type="password" 
                class="'.($err_uid ? 'errorInputText' : 'texte').' form-control inputuid float-left" 
                name="uuid2" 
                value="'.($this->modif == '1' ? '' : ($userprefs[1] != '' ? $userprefs[7] : $uuid2)).'" 
              />
              <div id="confirmuid-eye" class="input-group-append c-pointer">
                <span class="input-group-text"><i id="confirmuid-i" class="far fa-eye"></i></span>
              </div>
              <script>
                document.getElementById("confirmuid-eye").addEventListener("click", function(e){
                  var pwd = document.getElementById("confirmuid");
                  var eye = document.getElementById("confirmuid-i");
                  if(pwd.getAttribute("type") == "password") {
                    pwd.setAttribute("type", "text");
                    eye.setAttribute("class", "far fa-eye-slash");
                  } else {
                    pwd.setAttribute("type", "password");
                    eye.setAttribute("class", "far fa-eye");
                  }
                });
              </script>
            </div>
          </div>
		  <div class="col-md-1 col-1 px-0">&nbsp;</div>
        </div>
        <div class="form-row">
		  <div class="col-md-5">&bull; '.$web[50].'</div>
		  <div class="col-md-5">
            <input 
              type="text" 
              class="'.($err_uemail ? 'errorInputText' : 'texte').' form-control" 
              name="uemail" 
              value="'.(empty($userprefs[2]) ? $uemail : $userprefs[2]).'" 
            />
          </div>
        </div>
        <div class="form-row">
		  <div class="col-md-5">&bull; '.$web[51].'</div>
		  <div class="col-md-5">
            <input 
              type="text" 
              class="'.($err_uweb ? 'errorInputText' : 'texte').' form-control" 
              name="uwebsite" 
              value="'.(empty($userprefs[9]) ? $uwebsite : $userprefs[9]).'" 
            />
          </div>
        </div>
        <div class="form-row">
		  <div class="col-md-5">&bull; '.$web[219].'</div>
		  <div class="col-md-6">
            <textarea name="usign" class="form-control">'.(empty($userprefs[6]) ? $usign : stripslashes($userprefs[6])).'</textarea>
          </div>
	    </div>';
        $out .= '
        <div class="form-row mt-4">
          <div class="col-10 col-sm-10 col-md-7"><label for="ushow">&bull; '.$web[213].'</label></div>
          <div class="col-2 col-sm-2 col-md-2 form-check pt-1">
            <input type="checkbox" class="form-check-input" id="ushow" name="ushow"'.Checked($userprefs[5] == 'on' || $ushow == 'on').' />
          </div>
        </div>
        <div class="form-row">
          <div class="col-10 col-sm-10 col-md-7"><label for="umailct">&bull; '.$web[654].'</label></div>
          <div class="col-2 col-sm-2 col-md-2 form-check pt-1">
              <input type="checkbox" class="form-check-input" id="umailct" name="umailct"'.Checked($userprefs[23] == 'on' || $umailct == 'on').' />
          </div>
        </div>';
        if ($serviz[18] != 'on') { $out .= '
        <div class="form-row">
          <div class="col-10 col-sm-10 col-md-7"><label for="umpct">&bull; '.$web[655].'</label></div>
          <div class="col-2 col-sm-2 col-md-2 form-check pt-1">
            <input type="checkbox" class="form-check-input" id="umpct" name="umpct"'.Checked($userprefs[24] == 'on' || $umpct == 'on').' />
          </div>
        </div>
        <div class="form-row">
          <div class="col-10 col-sm-10 col-md-7"><label for="usendmail">&bull; '.$web[656].'</label></div>
          <div class="col-2 col-sm-2 col-md-2 form-check pt-1">
            <input type="checkbox" class="form-check-input" id="usendmail" name="usendmail"'.Checked($userprefs[25] == 'on' || $usendmail == 'on').' />
          </div>
        </div>
        <div class="form-row">
          <div class="col-10 col-sm-10 col-md-7"><label for="usendct">&bull; '.$web[657].'</label></div>
          <div class="col-2 col-sm-2 col-md-2 form-check pt-1">
            <input type="checkbox" class="form-check-input" id="usendct" name="usendct"'.Checked($userprefs[26] == 'on' || $usendct == 'on').' />
          </div>
	    </div>';
        }
        $out .= '
	  </fieldset>
	  <fieldset class="pt-2"><legend>'.$web[378].'</legend>';
        if ($lang[1] != '') $out .= '
        <div class="form-row">
		  <div class="col-7 col-sm-7 col-md-5">&bull; '.$web[155].'</div>
		  <div class="col-5 col-sm-5 col-md-7">
			<select class="userselect custom-select-sm form-control-lg" name="ulang">
			  <option value="'.$lang[0].'"'.Selected($ulang == $lang[0] || $userprefs[0] == $lang[0]).'>'.$lang[0].'</option>
			  <option value="'.$lang[1].'"'.Selected($ulang == $lang[1] || $userprefs[0] == $lang[1]).'>'.$lang[1].'</option>
			</select>
		  </div>
	    </div>
          ';
        else $out .= '
		  <input type="hidden" name="ulang" value="'.$lang[0].'" />';
        $out .= '
        <div class="form-row">
		  <div class="col-5 col-sm-5 col-md-5">
            <label for="uredir">&bull; '.$web[475].'</label>
          </div>
		  <div class="col-4 col-sm-2 col-md-4 form-check">
            <input type="checkbox" class="mt-2 form-check-input" id="uredir" name="uredir"'.Checked($uredir == 'on' || $userprefs[29]).' />
          </div>
		</div>
        <div class="w-auto">';
        if ($page[23] != 'none') {
            $out .= '
              <fieldset class="w-auto m-auto usermainava"><legend>'.$web[303].'</legend>
                <div class="table-responsive-md divuserava">
                  <table class="table table-borderless m-auto tbluseravaW">
                    <tr>
            ';
            @include CHEMIN.'inc/img/avatars/'.$page[23].'.inc';
            $nbrimgavatar = 0; $j = $imgavatars;
            for ($i = 1; $i < $imgavatars; $i++) {
                $ava  = substr('0'.(string)$i, -2);
                $out .= '
			          <td class="text-center">
                        <label for="'.$ava.'">
                          <i class="ava-'.$ava.'" title="'.$page[23].'-'.$ava.'"></i>
			              <div>
                            <input id="'.$ava.'" type="radio" value="ava-'.$ava.'" name="avatar"'.Checked($avatar == 'ava-'.$ava || $userprefs[8] == 'ava-'.$ava).' />
                          </div>
                        </label>
			          </td>';
                $nbrimgavatar++; $j--;
                if ($nbrimgavatar == 7 && $j != 0) {
                    $nbrimgavatar = 0;
                    $out .= '
                    </tr>
		            <tr>
            ';
                }
            }
            $vid = 7 - $imgavatars%7 ;
            if ($vid != 7) {
                for ($i = 0; $i < $vid; $i++)
                    $out .= '
			          <td class="text-center">&nbsp;</td>';
            }
            $out .= '
		            </tr>
		          </table>
		        </div>';
            $out .= '
              </fieldset>';
            if ($userprefs[21] != 'on') {
                $out .= '
                <div class="text-center divuseravaextW">
                  <label>
                    <input 
                      type="radio" 
                      value="ava-00" name="avatar"
                      '.Checked(($avatar == 'ava-00' && $uextavatar != $preprot) || ($userprefs[8] == 'ava-00' || (!empty($userprefs[15]) && $userprefs[15] != $preprot))).' 
                    />
                    &nbsp;'.$web[627].' :
                  </label>
                  <script>
                    $(document).ready(function(){
                      $(\'.popavaext\').popover({
                        title:"'.$web[627].'",
                        content:"'.$help[8].'",
                        trigger:"hover",
                        placement:"auto",
                        html:true
                      });
                    });
                  </script>
                  <span class="overpop">&nbsp;
                    <a class="popavaext" title="">
                      <i class="far fa-comment-dots"></i>
                    </a>
                  </span>
                  <div class="w-100">
                    <input 
                      type="text" 
                      class="'.($err_uext ? 'errorInputText' : 'texte').' userextavaW form-control" 
                      name="uextavatar" 
                      value="'.($uextavatar == $preprot ? (empty($userprefs[15]) ? $preprot : $userprefs[15]) : $uextavatar).'" 
                    />
                  </div>
                </div>
                <div class="clearfix"></div>';
                if ($userprefs[8] == 'ava-00' && !empty($userprefs[15]))
                    $out .= '
                <div class="text-center w-100 m-auto">
                  <img src="'.$userprefs[15].'" alt="" title="'.$userprefs[1].'" />
                </div>';
            }
        }
        $out .= '
		</div>
	  </fieldset>';
        if (!empty($serviz[177]) && !OK_RGPD_date($userprefs[1])) $out .= '
	  <br />
	  <fieldset><legend>'.$web[14].'</legend>
        <p><strong>'.$web[646].'</strong></p>
        <p>'.$web[684].'</p>
        <div>
          <label for="rgpd">'.$web[415].' : </label><input type="checkbox" id="rgpd" name="rgpd"'.Checked($rgpd).' />
        </div>
      </fieldset>
	  <br />';
        if ($serviz[100] != '0' && $serviz[88] == 'on' && ($userprefs[1] == '' || $serviz[98] == '')) {
            $lbl = $serviz[100] == '3' ? $web[642] : '';
            $out .= '
              <div class="text-center">'.dsp_crypt(0, 1, 3, 2, $lbl, $serviz[115] == 'on' ? $web[643] : '').'<div>';
            if ($serviz[100] == '1') $out .= '<br />';
            $out .= '
              <div class="text-center">'.($serviz[100] == '3' ? '' : $web[530]).'
                <input 
                  class="form-control mx-auto '.($err_crypt ? 'errorInputText' : 'texte').' inputcrypt" 
                  type="text" 
                  name="crypt" 
                  onpaste="return false;");" 
                />
              </div>';
        }
        $out .= '
	  <p class="text-center">'.SubmitButton($web[154]).'</p>
	</form>
  </div>
</div>';
        if ($userprefs[1] != '') { // suppression des préférences
            $out .= '
<hr />
<div class="text-center">
  <form name="userbye" 
    action="'.CHEMIN.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[15].'-'.$this->tconfig.$z2 
      : $site['USR'].'.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'" 
      method="post">
	<input type="hidden" name="unreg" value="1" />
'.SubmitButton($web[162]).'
  </form>
</div>';
        }
        return array('USR', $out);
    }
    
    public function CMPT_ok($pseudo, $lng) {
        global $site, $urlrw, $serviz, $web, $z2, $members;
        $out = '';
        if ($serviz[28] != 'on') {
            $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
            $out = '
    <div class="text-center web342">'.$web[143].'
      <p class="text-center">
        '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
      </p>
    </div>';
            $this->topmess = $web[317];
            return array('CMPT', $out);
        }
        elseif (empty($pseudo)) {
            $href2 = CHEMIN.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[15].'-5-new-'.$this->tconfig.$z2 
              : $site['USR'].'.php?lng='.$lng.'&uuser=new&tconfig='.$this->tconfig.$z2);
            $out .= '
        <div class="text-center web342">'.$web[342];
            if ($members[19] != '0' && $members[19] != '3') {
                $out .= '
          <p class="text-center">
            '.SubmitButton($web[160], 'self.location.href=\''.$href2.'\'').'
          </p>';
            }
            $out .= '
        </div>';
            $this->topmess = $web[317];
            return array('CMPT', $out);
        }
        return array('');
    }
    
    public function CMPT_content() {        
        global $lang, $lng, $web, $help, $serviz, $urlrw, $site, $z2, $userprefs, 
               $page, $members, $templates, $xposbox;
        $usermess = import('usermess');
        $action   = import('action', 'POST');
        if ($action == 1) {
            $list = ReadDBFields(NEWSLET_LIST);
            for ($i = 0; $i < count($list); $i++) {
                if ($list[$i][0] == $userprefs[1]) {
                    $list[$i][1] = $userprefs[2];
                    break;
                }
            }
            WriteDBFields(NEWSLET_LIST, $list);
        }
    	if (!OK_RGPD_date($userprefs[1])) 
		{
			setRGPDdate();
		}
        $out = '
<br />';
        if (!empty($usermess)) $out .= '
<p class="text-center"><b>'.$usermess.'</b></p>
<br />';
        $out .= '
<div class="table-responsive-md">
<table class="table bord text-center w-98 m-auto">
  <tr>
    <td class="forum2 w-45 cmptlbl">'.$web[49].'</td>
    <td class="rep cmptlbl"><b>'.$userprefs[1].'</b></td>
  </tr>
  <tr class="cmptlbl">
    <td class="forum2 w-45 cmptlbl">'.$web[50].'</td>
	<td class="rep cmptlbl"><b>'.$userprefs[2].'</b>';
        $list = ReadDBFields(NEWSLET_LIST);
        for ($i = 0; $i < count($list); $i++) {
            if ($list[$i][0] == $userprefs[1] && $list[$i][1] != $userprefs[2]) {
                $out .= '
      <script>
        $(document).ready(function(){
          $(\'.popsyncmail\').popover({
            title:"'.$web[50].'",
            content:"'.$help[9].'",
            trigger:"hover",
            placement:"auto",
            html:true
          });
        });
      </script>
      <span class="overpop float-right">&nbsp;
        <a class="popsyncmail" title="">&nbsp;
          <i class="far fa-comment-dots"></i>
        </a>
      </span>
	  <div class="float-right cmptsyncmail">
		<form name="syncmail" 
            action="'.CHEMIN.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[3].'-14-'.$this->tconfig.$z2 
              : 'compte.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'" 
              method="post">
		  <input type="hidden" name="action" value="1" />
		  '.SubmitButton($web[631]).'
	    </form>
      </div>
      <div class="clearfix"></div>';
                break;
            }
        }
        $out .= '
    </td>
  </tr>
  <tr>
    <td class="forum2 w-45 cmptlbl">'.$web[51].'</td>
	<td class="rep cmptlbl"><b>'.($userprefs[9] == '' ? $web[316] : $userprefs[9]).'</b></td>
  </tr>
  <tr>
    <td class="forum2 w-45 cmptlbl">'.$web[219].'</td>
	<td class="rep cmptlbl"><b>'.($userprefs[6] != '' ? stripslashes($userprefs[6]) : $web[309]).'</b></td>
  </tr>
  <tr>
    <td class="forum2 w-45 cmptlbl">'.$web[155].'</td>
	<td class="rep cmptlbl"><b>'.$userprefs[0].'</b></td>
  </tr>';
        if ($page[23] != 'none') {
                $out .= '
  <tr>
    <td class="forum2 w-45 align-middle cmptlbl">'.$web[311].'</td>
    <td class="rep valignMiddle cmptlbl">';
            if (!empty($userprefs[15]) || $userprefs[8] != 'ava-00') {
                if ($userprefs[8] == 'ava-00' && !empty($userprefs[15])) $out .= '
      <img src="'.$userprefs[15].'" alt="'.$userprefs[1].'" title="'.$userprefs[1].'" />';
                else $out .= '
      <i class="'.$userprefs[8].' ava-img" title="'.$userprefs[1].'"></i>';
            }
            elseif ($userprefs[8] == 'ava-00') {
                $out .= '
	  <i class="ava-00 ava-img" title="'.$web[305].'"></i>';
            }
            $out .= '
	</td>
  </tr>';
        }
        $out .= '
  <tr>
    <td class="forum2 cmptlbl">'.$web[312].'</td>
	<td class="rep cmptlbl"><b>'.$web[$userprefs[5] != '' ? 313 : 314].'</b></td>
  </tr>
  <tr>
	<td class="forum2 cmptlbl">'.$web[654].'</td>
	<td class="rep cmptlbl"><b>'.$web[$userprefs[23] != '' ? 313 : 314].'</b></td>
  </tr>
  <tr>
	<td class="forum2 cmptlbl">'.$web[655].'</td>
	<td class="rep cmptlbl"><b>'.$web[$userprefs[24] != '' ? 313 : 314].'</b></td>
  </tr>
  <tr>
	<td class="forum2 cmptlbl">'.$web[656].'</td>
	<td class="rep cmptlbl"><b>'.$web[$userprefs[25] != '' ? 313 : 314].'</b></td>
  </tr>
  <tr>
	<td class="forum2 cmptlbl">'.$web[657].'</td>
	<td class="rep cmptlbl"><b>'.$web[$userprefs[26] != '' ? 313 : 314].'</b></td>
  </tr>
  <tr>
	<td class="forum2 cmptlbl">'.$web[475].'</td>
	<td class="rep cmptlbl"><b>'.$web[$userprefs[29] != '' ? 313 : 314].'</b></td>
  </tr>
  <tr>
    <td class="forum2 cmptlbl">'.$web[625].'</td>
	<td class="rep cmptlbl"><b>'.(isset($userprefs[13]) ? FormatDate($userprefs[13]) : '???').'</b></td>
  </tr>
  <tr>
    <td class="forum2 cmptlbl">'.$web[626].'</td>
    <td class="rep cmptlbl"><b>'.(isset($userprefs[14]) ? FormatDate($userprefs[14]) : '???').'</b></td>
  </tr>
  <tr>
    <td class="forum2 cmptlbl">'.$web[694].'</td>
    <td class="rep cmptlbl"><b>'.(isset($userprefs[27]) ? FormatDate($userprefs[27]) : '???').'</b></td>
  </tr>
</table>
</div>
<div class="text-center">
  <form 
    name="userpref2" 
    action="'.CHEMIN.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[15].'-6-1-old-'.$this->tconfig.$z2 
      : $site['USR'].'.php?lng='.$lng.'&amp;modif=1&amp;uuser=old&amp;tconfig='.$this->tconfig.$z2).'" 
      method="post">
    '.SubmitButton($web[308]).'
  </form>
  <hr />
  <form 
    name="userbye" 
    action="'.CHEMIN.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[15].'-7-1-'.$this->tconfig.$z2 
      : $site['USR'].'.php?lng='.$lng.'&amp;unreg=1&amp;tconfig='.$this->tconfig.$z2).'" 
      method="post">
    '.SubmitButton($web[162]).'
  </form>
</div>';
        $this->topmess = $web[317].' - '.$userprefs[1];
        return array('CMPT', $out);
    }
    
    public function CNCT_ok($pseudo, $lng) {
        global $site, $urlrw, $serviz, $web;
        if (is_file(MSGREP.'.dtb')) @unlink(MSGREP.'.dtb');
        $out = '';
        if ($serviz[28] != 'on') {
            $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
            $out = '
    <div class="text-center web342">'.$web[143].'
      <p class="text-center">
        '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
      </p>
    </div>';
            $this->topmess = $web[630];
            return array('CNCT', $out);
        }
		
        return array('');
    }
    
    public function CNCT_content() {
        global $lang, $lng, $web, $serviz, $urlrw, $site, $z2, $userprefs, $members, $headinc, $nom, 
               $drtuser, $supervision, $_DOMAIN, $_SECURE, $_HTTPONLY, $page, $charset, $cryptos;
        CreateDir(TEMPREP);
        $connect    = import('connect');
        $pseudo     = import('pseudo');
        $pseudo     = 1 == preg_match('/^[-a-zA-Z0-9_]{2,'.$serviz[148].'}$/', $pseudo) ? $pseudo : '';
        $uid        = import('uid');
        $uuser      = import('uuser');
        $err_pseudo = empty($pseudo) && $connect == 'on' && $uuser == 'old' ? true: false;
        $err_uid    = (empty($uid) || !ctrlPasswordOK($uid)) && $connect == 'on' && $uuser == 'old' ? true: false;
        $err_crypt  = $serviz[100] != '0' && ($userprefs[1] == '' || $serviz[98] == '') && $connect == 'on' && $uuser == 'old' ? true: false;
        $erreur     = '';
        $erreur    .= $err_pseudo ? '<li>'.$web[266].'</li>': '';
        $erreur    .= $err_uid ? '<li>'.$web[302].'</li>': '';
        $erreur    .= $err_crypt ? '<li>'.$web[529].'</li>': '';
        $out        = '';

        $this->topmess = $web[630];
        switch ($connect) {
        case 'on':
            if ($connect == 'on' && $uuser == 'new') {
                $userdest = $site['URLR'] == 'on' 
                  ? $lng.'-'.$urlrw[15].'-5-new-'.$this->tconfig.$z2 
                  : $site['USR'].'.php?lng='.$lng.'&uuser=new&tconfig='.$this->tconfig.$z2;
                $out = '
<script> setTimeout(\'window.location="'.CHEMIN.$userdest.'"\', 300); </script>';
                return array('CNCT', $out, $web[630]);
            }
            if ($members[19] == '0' && !FileDBExist(MSGREP.$pseudo.DBEXT)) {
                $out = '
<script>
alert("'.addslashes($web[512]).'");
history.back();
</script>';
                return array('CNCT', $out);
            }
            if (is_file(TEMPREP.'cnt-'.$pseudo.DBEXT)) {
                $dbw = ReadDBFields(TEMPREP.'cnt-'.$pseudo.DBEXT);
                $dt  = GetCurrentDateTime() - $dbw[0][2];
                if ($dt > 60) {
                    unlink(TEMPREP.'cnt-'.$pseudo.DBEXT);
                } 
                else {
                    $ntc = $site['NTC'];
                    $nt  = $dbw[0][0];
                    if ($nt == $ntc) {
                        $location = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[3].'-16-'.$this->tconfig.$z2 : 'lostpass.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2);
                        $out = '
<script>
alert("'.sprintf($web[614].$web[615], $pseudo).'");
window.location="'.$location.'";
</script>';
                        $out .= '<br /><p class="text-center"><strong>'.sprintf($web[614], $pseudo, $site['NTC']).'</strong>';
                        return array('CNCT', $out);
                    }
                }
            }
            if (is_file(TEMPREP.$pseudo.DBEXT)) {
                switch($members[19]) {
                case 1 :
                    $out = '
<br />
<p class="text-center"><strong>'.$web[513].'</strong></p>';
                    break;
                case 2 :
                    $out = '
<br />
<p class="text-center"><strong>'.$web[514].'</strong></p>';
                    break;
                }
                return array('CNCT', $out);
            }
            else {
                if (!empty($pseudo) && FileDBExist(MSGREP.$pseudo.DBEXT) && $uuser != 'new') {
                    $userdb = ReadDBFields(MSGREP.$pseudo.DBEXT);
                    if (isset($userdb[1][31]) && 'ok' != $userdb[1][31] && $pseudo != '') {
                        $out = '
<script>
alert("'.addslashes($web[679]).'");
history.back();
</script>';
                        $out .= '<p class="text-center"><br />'.$web[679].'</p>';
                        return array('CNCT', $out);
                    } 
                    elseif (isset($userdb[1][13]) && 'off' == $userdb[1][13] && $pseudo != '') {
                        $out = '
<script>
alert("'.addslashes($web[620]).'");
history.back();
</script>';
                        $out .= '<p class="text-center"><br />'.$web[620].'</p>';
                        return array('CNCT', $out);
                    } 
                    elseif (empty($userdb[1][3])) {
                        $location = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[15].'-3-'.$pseudo.'-'.$uuser.'-'.$this->tconfig.$z2 : $site['USR'].'.php?lng='.$lng.'&upseudo='.$pseudo.'&uuser='.$uuser.'&tconfig='.$this->tconfig.$z2);
                        $out = '
<script>
alert("'.addslashes($pseudo.stripslashes($web[374])).'");
window.location="'.$location.'"
</script>';
                        $out .= '<p class="text-center"><br /><strong>'.$pseudo.stripslashes($web[374]).'</strong></p>';
                        return array('CNCT', $out);
                    }
                    elseif (isset($userdb[1][8]) && md5($uid) == $userdb[1][8] && $pseudo != '') {
                        $userprefs[0]  = $userdb[1][1];
                        $userprefs[1]  = $userdb[1][2];
                        $userprefs[2]  = $userdb[1][3];
                        $userprefs[3]  = '';
                        $userprefs[4]  = $userdb[1][5];
                        $userprefs[5]  = $userdb[1][6];
                        $userprefs[6]  = $userdb[1][7];
                        $userprefs[7]  = $userdb[1][8];
                        $userprefs[8]  = $userdb[1][9];
                        $userprefs[9]  = $userdb[1][10];
                        $userprefs[10] = '';
                        $userprefs[11] = $userdb[1][12];
                        $userprefs[12] = $userdb[1][13];
                        $userprefs[13] = $userdb[1][14];
                        $userprefs[14] = GetCurrentDateTime();
                        $userprefs[15] = $userdb[1][16];
                        $userprefs[16] = $userdb[1][17];
                        $userprefs[17] = $userdb[1][18];
                        $userprefs[18] = $userdb[1][19];
                        $userprefs[19] = $userdb[1][20];
                        $userprefs[20] = $userdb[1][21];
                        $userprefs[21] = $userdb[1][22];
                        $userprefs[22] = $userdb[1][23];
                        $userprefs[23] = $userdb[1][24];
                        $userprefs[24] = $userdb[1][25];
                        $userprefs[25] = $userdb[1][26];
                        $userprefs[26] = $userdb[1][27];
                        $userprefs[27] = $userdb[1][28];
                        $userprefs[28] = '';
                        $userprefs[29] = $userdb[1][30];
                        $userprefs[30] = $userdb[1][31];
                        $userdata      = '';
                        for ($i = 0; $i < count($userprefs); $i++) {
                            $stri      = sprintf("%02d", $i);
                            $strlen    = empty($userprefs[$i]) ? '03' : sprintf("%02d", strlen($userprefs[$i]));
                            $userdata .= $stri.$strlen.(empty($userprefs[$i]) ? 'off' : $userprefs[$i]);
                        }
// avant                      $usertime = !empty($members[21]) ? intval($members[21]) : 157680000;
// avant                     $expires  = (is_file(CHEMIN.'admin/'.REDACREP.$userprefs[1].INCEXT) || $userprefs[1] == $serviz[31] || $userprefs[1] == $serviz[176]) ? 157680000 : $usertime;
// avant                      setcookie(USER_COOKIE, gy_crypt(CRYPT_KEY, $userdata), time() + $expires, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
						@setcookie(USER_COOKIE, gy_crypt(CRYPT_KEY, $userdata), strtotime( '+365 days' ), '/', $_DOMAIN, $_SECURE, $_HTTPONLY);	
                        $userconnect = gy_crypt(CRYPT_KEY, time().CONNECTOR.$userprefs[1].CONNECTOR.date('Ymdhi'));
 // avant               setcookie(CNCT_COOKIE, $userconnect, time() + $expires, '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
						setcookie(CNCT_COOKIE, $userconnect, strtotime( '+365 days' ), '/', $_DOMAIN, $_SECURE, $_HTTPONLY);
                        DejaVote(DBIPSTATS);
                        $userdb[0][0]  = $userprefs[7];
                        $userdb[1][15] = $userprefs[14];
                        WriteDBFields(MSGREP.$userprefs[1].DBEXT, $userdb);
                        if (!defined('NO_CRYPT') && $serviz[100] > 0) include CRYPTREP.$cryptos[$serviz[100]].'.php';
                        if (is_file(TEMPREP.'cnt-'.$pseudo.DBEXT)) unlink(TEMPREP.'cnt-'.$pseudo.DBEXT);
                        $out .= '
<br />
<p class="text-center"><b>'.sprintf($web[167], stripslashes($site[($lang[0] == $lng ? 0 : 11)]).'...!', $userprefs[1]).'</b></p>';
                        $location = CHEMIN.($userprefs[29] == 'on' ? ($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng) : ($site['URLR'] == 'on' ? $lng.'-'.$urlrw[3].'-14-'.$this->tconfig.$z2 : 'compte.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2));
                        $out .= '
<br />
<script> window.setTimeout(\'window.location="'.$location.'"\', 2000); </script>';
                        return array('CNCT', $out);
                    }
                    elseif (md5($uid) == $userdb[0][0] && $pseudo != '' && $uuser == 'old') {
                        $location = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[15].'-3-'.$pseudo.'-'.$uuser.'-'.$this->tconfig.$z2 : $site['USR'].'.php?lng='.$lng.'&upseudo='.$pseudo.'&uuser='.$uuser.'&tconfig='.$this->tconfig.$z2);
                        $out .= '
<script>
alert("'.$pseudo.addslashes(stripslashes($web[374])).'");
window.location="'.$location.'"
</script>';
                        $out .= '<p class="text-center"><br /><strong>'.$pseudo.stripslashes($web[374]).'</strong>';
                        return array('CNCT', $out);
                    }
                    elseif ($pseudo != '') {
                        if ($supervision[12] == 'on') {
                            $uuid  = strlen($uid) > 3 ? substr($uid, 0, strlen($uid) - 3).'***' : '***';
                            $sujet = $web[606];
  //         				$body  = $web[253].' '.$user[0].'<br /><br />'.sprintf($web[607], $pseudo, $uuid).'<br /><br />';	                 
							$bodycorps   = $web[253].' '.$userprefs[2].'<br /><br />'.sprintf($web[607], $pseudo, $uuid).'<br /><br />';
                            $bodycorps .= $web[455].getIPadress($site[6] == 'on' ? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']): $_SERVER['REMOTE_ADDR']).'<br /><br />';
                            eMailHtmlTo($sujet, $bodycorps);
                        }
                        $ntc = $site['NTC'];
                        if ($ntc > 0) { // contrôle du nombre de tentatives
                            if (is_file(TEMPREP.'cnt-'.$pseudo.DBEXT)) {
                                $dbw = ReadDBFields(TEMPREP.'cnt-'.$pseudo.DBEXT);
                                $nt  = $dbw[0][0];
                            }
                            else {
                                $dbw = array();
                                $nt  = 0;
                            }
                            $nt++;
                            $dbw[0][0] = $nt;
                            $dbw[0][1] = getIPadress($site[6] == 'on' ? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']): $_SERVER['REMOTE_ADDR']);
                            $dbw[0][2] = GetCurrentDateTime();
                            WriteDBFields(TEMPREP.'cnt-'.$pseudo.DBEXT, $dbw);
                            if ($nt == $ntc - 1) {
                                $location = $site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng;
                                $out .= '
<script>
alert("'.sprintf($web[613], $pseudo, $ntc).'");
history.back();
<script>';
                                $out .= '<p class="text-center"><br />'.sprintf($web[613], $pseudo, $ntc).'</p>';
                                return array('CNCT', $out);
                            }
                            if ($nt == $ntc) {
                                $location = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[3].'-16-'.$this->tconfig.$z2 : 'lostpass.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2);
                                $out .= '
<script>
alert("'.sprintf($web[614], $pseudo).'");
window.location="'.$location.'";
</script>';
                                $out .= '<p class="text-center"><br />'.sprintf($web[614], $pseudo).'</p>';
                                return array('CNCT', $out);
                            }
                        }
                        $location = ($site['URLR'] == 'on') ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng;
                        $out .= '
<script>
alert("'.addslashes($web[231]).'");
history.back();
</script>';
                        $out .= '<p class="text-center"><br />'.$web[231].'</p>';
                        return array('CNCT', $out);
                    }
                }
                else {
                    if ($members[19] == '3' || ($pseudo != '' && $uid != '')) {
                        $userdest = $site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng;
                        $out .= '
<script> setTimeout(\'window.location="'.CHEMIN.$userdest.'"\', 300); <script>';
                    }
                }
            }
            return array('CNCT', $out);
            break;
        case 'off':
            $dbuser        = $userprefs[1];
            $userdb        = ReadDBFields(MSGREP.$userprefs[1].DBEXT);
            $userdb[1][17] = GetCurrentDateTime();
            WriteDBFields(MSGREP.$userprefs[1].DBEXT, $userdb);
            $userprefs = array_fill(0, 31, '');
            ClearCookies();
            DejaVote(DBIPSTATS);
            $topmess = getLabel($nom[$lng == $lang[0] ? 34 : 35]);
            $topmess = mb_substr($topmess, 0, 1, $charset) == '§' ? mb_substr($topmess, 1, mb_strlen($topmess ,$charset) ,$charset) : $topmess;
            $out .= '
<br />
<p class="text-center"><b>'.$web[376].$dbuser.'...!</b></p>
<br />';
            $location = CHEMIN.($site['URLR'] == 'on'? $lng.'-'.$urlrw[0]: 'index.php?lng='.$lng);
            $out .= '
<script> setTimeout(\'window.location="'.$location.'"\', 3000); </script>';
            return array('CNCT', $out);
            break;	
        case 'no' :
            if (!empty($userprefs[1])) {
                $out .= '
<div class="text-center m-auto mainConnect">
  <fieldset>'.
    fbox_user().'
  </fieldset>
</div>';
                return array('CNCT', $out);
                break;
            }
            else {
                $location = CHEMIN.($site['URLR'] == 'on'? $lng.'-'.$urlrw[0]: 'index.php?lng='.$lng);
                $out .= '
<script> setTimeout(\'window.location="'.$location.'"\', 3000); </script>';
                return array('CNCT', $out);
                break;
            }
        }
        $headinc .= '
<script>
function VerifyForm() {
    var sto = "";
    var messok = "";
    var erreur = false;
    var largeur = 300;
	var uid = document.userin.uid.value;

    regexp = /^[-a-zA-Z0-9_]{2,'.$serviz[148].'}$/;
    if (!regexp.test(document.userin.pseudo.value)) {
        sto += "  - '.addslashes($web[266]).'\n";
		document.userin.pseudo.className = "errorInputText";
        erreur = true;
    }
	/*regexp = /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).{8,40}$/;*/
    regexp = /^\S{5,40}$/;
	if (!regexp.test(uid)) {
		sto += \'  - '.addslashes($web[302]).'\n\';
		document.userin.uid.className = "errorInputText";
		erreur = true;
	}
	';
        $headinc .= '
    if (erreur == true) {
        sto = "'.addslashes($web[638]).'\n\n" + sto;
        alert(sto);
        return false;
    }
}
</script>';
        if (!empty($erreur))
            $out .= displayErrorMsg($web[638], $erreur);
        if ($members[19] != "0" && $members[19] != "3") {
            $out .= '
<div class="text-center">
  <form name="usernew" action="'.CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[3].'-0-'.$this->tconfig.$z2 : 'connect.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'" method="post">
    <input type="hidden" name="connect" value="on"/>
    <input type="hidden" name="uuser" value="new"/>
    '.SubmitButton($web[160]).'
  </form>
</div>
<hr />';
        }
        $out .= '
<style>
#uid-eye, #pseudo-user { margin-top: 0.4rem; height: 3.35rem; }
#uid-eye i, #pseudo-user i { font-size: 1.5rem; }
</style>
';
        $out .= '
<form name="userin" 
    action="'.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[3].'-0-'.$this->tconfig.$z2 : 'connect.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'" 
    method="post" 
    onsubmit="return VerifyForm(); return false;">
  <input type="hidden"  name="connect" value="on" />
  <input type="hidden" name="uuser" value="old"/>
  <div class="m-auto userCnct">
    <div class="input-group mb-3">
      <div id="pseudo-user" class="input-group-prepend">
        <span class="input-group-text"><i class="fas fa-user"></i></span>
      </div>
      <input 
        class="'.($err_pseudo ? 'errorInputText' : 'texte').' form-control" 
        type="text" 
        name="pseudo" 
        placeholder="'.$web[49].'" 
        value="'.$pseudo.'" 
      />
    </div>
    <div class="input-group mb-3">
      <input 
        id="uid" 
        type="password" 
        class="uid '.($err_uid ? 'errorInputText' : 'texte').' form-control float-left" 
        name="uid" 
        placeholder="'.$web[300].'" 
        value="'.$uid.'" 
      />
      <div id="uid-eye" class="input-group-append c-pointer" title="'.$web[473].'">
        <span class="input-group-text"><i id="uid-i" class="far fa-eye"></i></span>
      </div>
<script>
  document.getElementById("uid-eye").addEventListener("click", function(e){
    var pwd = document.getElementById("uid");
    var eye = document.getElementById("uid-i");
    if(pwd.getAttribute("type") == "password") {
      pwd.setAttribute("type", "text");
      eye.setAttribute("class", "far fa-eye-slash");
    } else {
      pwd.setAttribute("type", "password");
      eye.setAttribute("class", "far fa-eye");
    }
  });
</script>
    </div>';
$out .= '
    <div class="text-center">'.SubmitButton($web[52]).'</div>
  </div>
</form>
<div class="text-center">
  <a href="'.CHEMIN.($site['URLR'] == 'on'
    ? $lng.'-'.$urlrw[3].'-16-'.$this->tconfig.$z2 
    : 'lostpass.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'" class="lostconnect">'.$web[592].'
  </a>
</div>';
        return array('CNCT', $out);
    }
    
    public function LOST_content() {
        global $lang, $lng, $web, $serviz, $urlrw, $site, $z2, $userprefs, $members, $headinc, 
               $drtuser, $supervision, $_DOMAIN, $_SECURE, $_HTTPONLY, $page, $charset;
        CreateDir(TEMPREP);
		$out = '';
        $action    = import('action');
        $pseudo    = import('pseudo');
        $email     = import('email');
        $ask       = import('ask');
        $err_email = (empty($email) || 1 != preg_match('/^[^\.\s]+(\.[^\.\s]+)*@[^\.\s]+(\.[^\.\s]+)+$/', $email)) && $ask == 1;
        $erreur    = '';
        $erreur   .= $err_email ? '<li>'.$web[42].'</li>': '';
        if ($action != 'lost' && !empty($action)) {
            $out .= BeginJavascript().'
alert("'.addslashes($web[599]).'");
window.location="'.CHEMIN.($site['URLR'] == 'on'? $lng.'-'.$urlrw[0]: 'index.php?lng='.$lng).'";
'.EndJavascript();
            $this->topmess = $web[594];
            return array('LOST', $out);
        }
        if ($action == 'lost') {
            if (is_file(MSGREP.$pseudo.DBEXT)) {
                $userdb = ReadDBFields(MSGREP.$pseudo.DBEXT);
                if (isset($userdb[1][3]) && $userdb[1][3] != $email) {
                    $out .= BeginJavascript().'
alert("'.addslashes($web[599]).'");
window.setTimeout(\'window.location="'.CHEMIN.($site['URLR'] == 'on'? $lng.'-'.$urlrw[0]: 'index.php?lng='.$lng).'"\', 1000);
'.EndJavascript();
                    $this->topmess = $web[594];
                    return array('LOST', $out);
                }
            }
            $newpass   = GenerateUID();
//            $cryptpass = crypt($newpass, '');
//            $decrypt   = crypt($newpass, '');
			$apachepassword  = Md5Crypt::apache ($newpass, $salt ='');
			$cryptpass = $apachepassword;
			 $decrypt   = $apachepassword;
            if (is_file(CHEMIN.'data/config/.htpasswd')) {
                $htpasswd = @file(CHEMIN.'data/config/.htpasswd');
                $protoht  = trim($htpasswd[0]);
                if (!empty($protoht)) {
                    $cryptpass = $protoht == '#:0-1-0' ? gy_crypt(CRYPT_KEY, $newpass) : $apachepassword;
                    $decrypt   = $protoht == '#:0-1-0' ? $newpass : $apachepassword;
                }
            }
            $userdb[0][0] = md5($newpass);
            $userdb[0][1] = $apachepassword;
            $userdb[1][8] = md5($newpass);
            WriteDBFields(MSGREP.$pseudo.DBEXT, $userdb);
            $numava = isset($userdb[2][8]) ? $userdb[2][8] : substr($page[23], 4);
            $padmin = trim($serviz[31]) == '' ? (trim($serviz[176]) == '' ? $user[0] : $serviz[176]) : $serviz[31];
            $msg = array(
                $padmin,
                GetCurrentDateTime(),
                $web[253].' '.sprintf($web[612], $pseudo),
                'new',
                '',
                '',
                '',
                $serviz[43],
                $numava);
            AppendDBFields(MSGREP.$pseudo.DBEXT, $msg);
            UpdateCollaboratorPass($pseudo, md5($newpass));
            UpdateProtectMember($pseudo, $decrypt);
            $sujet = $web[600];
            $bodycorps  = $web[601];
            $bodycorps .= $web[457].'<b>'.$pseudo.'</b><br /><br />';
            $bodycorps .= $web[602].'<b>'.$newpass.'</b><br /><br />';
            $bodycorps .= $web[603].'<br /><br /><a href="'.$site[3].'">'.$site[3].'</a>';
            eMailHtmlTo($sujet, $bodycorps, $email);
            if (is_file(TEMPREP.'cnt-'.$pseudo.DBEXT)) unlink(TEMPREP.'cnt-'.$pseudo.DBEXT);
            $email = import('email');
            $out .= '<div class="text-center afterlost">'.$web[604].'<br />'.$email.'</div>';
            $this->topmess = $web[594];
            return array('LOST', $out);
        }
        if ($ask == 1 && empty($erreur)) {
            $continue = true;
            $bad      = array('.', '..', 'index.php');
            $files    = scandir(TEMPREP);
            $files    = array_diff($files, $bad);
            foreach ($files as $file) {
                if (is_file(TEMPREP.$file) && substr($file, -3) == 'dtb') {
                    $userdb = ReadDBFields(TEMPREP.$file);
                    if (isset($userdb[1][3])) {
                        if ($email == $userdb[1][3]) {
                            $out .= BeginJavascript().'
alert("'.addslashes($web[596]).'");
                            '.EndJavascript();
                            $continue = false;
                            $erreur  .= '<li>'.$web[596].'</li>';
                        }
                    }
                }
            }
            if ($continue) {
                $pseudo = '';
                $files  = scandir(MSGREP);
                $files  = array_diff($files, $bad);
                foreach ($files as $file) {
                    if (is_file(MSGREP.$file) && substr($file, -3) == 'dtb') {
                        $userdb = ReadDBFields(MSGREP.$file);
                        if (isset($userdb[1][3])) {
                            if ($email == $userdb[1][3]) {
                                $pseudo = $userdb[1][2];
                                break;
                            }
                        }
                    }
                }
            }
            if ($continue) {
                if ($pseudo == '') {
                    $out .= BeginJavascript().'
alert("'.addslashes($web[595]).'");
'.EndJavascript();
                    $continue = false;
                    $erreur  .= '<li>'.$web[595].'</li>';
                }
            }
            if ($continue) {
                $sujet = $web[597];
                $bodycorps  = $web[253].' '.$pseudo.',<br /><br />';
                $bodycorps .= sprintf($web[598], stripslashes($site[($lang[0] == $lng ? 0 : 11)]), getIPadress($site[6] == 'on' ? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']): $_SERVER['REMOTE_ADDR']));
                $bodycorps .= '<a href="'.$site[3].'lostpass.php?lng='.$lng.'&amp;pseudo='.$pseudo.'&amp;email='.$email.'&amp;action=lost&amp;tconfig='.$this->tconfig.$z2.'">'.$site[3].'lostpass.php?lng='.$lng.'&amp;pseudo='.$pseudo.'&email='.$email.'&action=lost</a>';
                eMailHtmlTo($sujet, $bodycorps, $email);
                $email = import('email');
                $out .= '<div class="text-center afterlost">'.$web[605].'<br />'.$email.'</div>';
                $this->topmess = $web[594];
                return array('LOST', $out);
            } 
            else {
                $out .= BeginJavascript().'
window.location="'.CHEMIN.($site['URLR'] == 'on'? $lng.'-'.$urlrw[0]: 'index.php?lng='.$lng).'";
'.EndJavascript();
                $this->topmess = $web[594];
                return array('LOST', $out);
            }
        }
        $headinc .= '
<script>
function VerifyForm() {
    var sto    = "";
    var erreur = false;
    regexp = /^[^\.\s]+(\.[^\.\s]+)*@[^\.\s]+(\.[^\.\s]+)+$/;
    if (!regexp.test(document.userlostpass.email.value)) {
		sto += \'  - '.addslashes($web[42]).'\n\';
		document.userlostpass.email.className = "errorInputText";
        erreur = true;
    }
    if (erreur == true) {
		sto = \''.addslashes($web[638]).'\n\n\' + sto;
        alert(sto);
        return false;
    }
}
</script>';
        if (empty($action) && empty($ask)) {
            if (!empty($erreur)) $out .= displayErrorMsg($web[638], $erreur);
            $out .= '
<p class="text-center">'.$web[593].'</p>
<div class="text-center m-auto">
  <style>
    .mlost i { font-size: 1.5rem; width: 1.85rem; }
    .mlost .input-group-text { margin-top: 4px; ; height: 3.4rem; }
  </style>
  <form 
      name="userlostpass" 
      action="'.($site['URLR'] == 'on'
        ? $lng.'-'.$urlrw[3].'-161-1-'.$this->tconfig.$z2 
        : 'lostpass.php?lng='.$lng.'&amp;ask=1&amp;tconfig='.$this->tconfig.$z2).'" 
      method="POST" 
      onsubmit="return VerifyForm(); return false;">
    <div class="mlost input-group mb-3 w-100 mx-auto" style="max-width:320px;">
      <div class="input-group-prepend">
        <span class="input-group-text"><i class="fas fa-at"></i></i></span>
      </div>
      <input class="form-control texte" type="text" name="email" placeholder="'.$web[13].'" />
    </div>
	'.SubmitButton($web[52]).'
  </form>
</div><br />';
            $this->topmess = $web[594];
            return array('LOST', $out);
        }
		else return array('LOST', $out);
    }
    
    public function MEMB_ok($pseudo, $lng) {
        global $serviz, $userprefs, $web;
        if ($serviz[28] != 'on' || $userprefs[19] == 'on' || $userprefs[20] == 'on' || !isAccessGranted($serviz[179], $pseudo)) {
            $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
            $out = '
    <div class="text-center web342">'.$web[143].'
      <p class="text-center">
        '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
      </p>
    </div>';
            $this->topmess = $web[673];
            return array('MEMB', $out);
        }
        return array('');
    }

    protected function GetStatus($pseudo) {
        global $web, $serviz;
        if ($pseudo == $serviz[31] || $pseudo == $serviz[176]) return strtoupper($web[144]);
        $status = '';
        if (is_file(CHEMIN.'admin/'.REDACREP.$pseudo.INCEXT)) {
            include CHEMIN.'admin/'.REDACREP.$pseudo.INCEXT;
            $status = ' / '.$drtuser[30];
        }
        $admins = ReadDBFields(ADMINS);
        foreach ($admins as $adm)
            if ($pseudo == $adm[0]) {
                return ucfirst($adm[1]).$status;
            }
        return ucfirst($web[322]);
    }
    
    public function MEMB_content() {
        global $lng, $web, $serviz, $urlrw, $site, $z2, $userprefs, $drtuser, $page;
        $upseudo = import('upseudo');
        $mb      = array();
        if (is_file(MSGREP.$upseudo.DBEXT)) {
            $userdb = ReadDBFields(MSGREP.$upseudo.DBEXT);
            $avatar = ($userdb[1][9] == 'ava-00' || substr($userdb[1][9], 0, 4) != 'ava-' ? 'ava-00' : $userdb[1][9]);
            $avatar = $userdb[1][16] != '' ? $userdb[1][16] : $avatar;
            $mb     = array(
              'pseudo' => $upseudo,
              'status' => $this->GetStatus($upseudo),
              'avatar' => $avatar, 
              'web'    => $userdb[1][10],
              'mail'   => $userdb[1][3],
              'reg'    => substr(FormatDate($userdb[1][14]), 0, 10), 
              'date'   => substr(FormatDate($userdb[1][15]), 0, 10),
              'okmail' => $userdb[1][24],
              'okmp'   => $userdb[1][25]
            );
        }
        if (!empty($mb)) {
            $out .= '
<div class="m-auto prfAdmButton">';
            if ($drtuser[10] == 'on' || $serviz[31] == $userprefs[1] || $serviz[176] == $userprefs[1]) $out .= '
  <div class="float-right">
    <form name="compte" action="'.CHEMIN.'admin/admin.php?lng='.$lng.'&pg=compte&edit='.$upseudo.'" target="_blank" method="POST">
      '.SubmitButton($web[144]).'
    </form>
  </div>
  <div class="clearfix"></div>
  <br />';
            $out .= '
  <div class="table-responsive-md">
  <table class="table w-100 prftblborder">
    <tr class="rep">
	  <td class="text-center prftblborder prfheadtbl">
	    '.$web[673].' '.$web[208].' <b>'.$upseudo.'</b>
	  </td>
    </tr>
  </table>
  </div>
  <div class="table-responsive-md">
  <table class="table w-100 prftblborder">
	<tr>
	  <td class="w-45 text-center prftblborder">
	    '.$web[108].'<br /><b>'.$mb['status'].'</b><br /><br />'.$web[667].'
	  </td>
	  <td class="text-center align-bottom prftblborder">'.$web[515].' '.$web[208].' '.$upseudo.'</div>
	</tr>
	<tr>
	  <td class="text-center align-middle prftblborder prfheadtbl">';
            if (substr($mb['avatar'], 0, 4) == 'ava-') $out .= '
        <i class="'.$mb['avatar'].' ava-img"></i>';
            else $out .= '
	    <img src="'.$mb['avatar'].'" alt="" title="" class="mbrlistimgava" />';
            $out .= '
	  </td>
	  <td class="text-center align-top prftblborder">
        <div class="table-responsive-md">
	    <table class="table table-borderless m-auto">
		  <tr>
            <td class="text-right">'.$web[669].' </td>
            <td>'.$mb['reg'].'</td>
          </tr>
		  <tr>
            <td class="text-right">'.$web[674].' </td>
            <td>'.$mb['date'].'</td>
          </tr>
		</table>
        </div>
        <div class="table-responsive-md">
	    <table class="table table-borderless m-auto">
          <tr>
			<td class="text-center prfwebsite">
			  '.$web[304].' : '.(empty($mb['web']) ? '&nbsp;' : '
                <a href="'.$mb['web'].'" title="" target="_blank">
                  <i class="fas fa-globe align-middle prfimgmail" title="'.$web[304].'"></i>
                </a>').'
			</td>
		  </tr>
		</table>
        </div>
	  </td>
	</tr>
	<tr class="rep">
	  <td class="text-center prftblborder prfwebsite">'.$web[13].' ';
            if ($mb['okmail'] == 'on' && $userprefs[18] != 'on') $out .= 
              displayWriteEmail('mbprf', $mb['pseudo'], $mb['mail'], ' <i class="fas fa-at" title="'.$web[173].' '.$mb['pseudo'].'"></i>');
            $out .= '
	  </td>
	  <td class="text-center prftblborder prfwebsite">'.$web[668].' ';
            $act = ($serviz[18] == 'on' ? '0' : '1');
            if ($mb['okmp'] == 'on' && $userprefs[17] != 'on') $out .= '
		<a href="'.CHEMIN.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[13].'-2-'.$act.'-'.$mb['pseudo'].'-'.$userprefs[1].'-'.$this->tconfig.$z2 
            : 'tinymsg.php?lng='.$lng.'&amp;action='.$act.'&amp;to='.$mb['pseudo'].'&amp;from='.$userprefs[1].'&amp;tconfig='.$this->tconfig.$z2).'" 
            title="'.$web[214].'">
	      <i class="far fa-envelope" title="'.$web[214].' '.$mb['pseudo'].'"></i>
		</a>';
            $out .= '
	  </td>
	</tr>
  </table>
  </div>
</div>
<br />
<div class="text-center">
  '.SubmitButton(
     $web[135].' '.$web[664], 
     'self.location.href=\''.CHEMIN.($site['URLR'] == 'on' 
       ? $lng.'-'.$urlrw[15].'-9-'.$this->tconfig.$z2 
       : 'memberlist.php?lng='.$lng.'&tconfig='.$this->tconfig.$z2).'\''
    ).'
</div>';
            $this->topmess = $web[673];
            return array('MEMB', $out);
        }
    }
    
    public function LSTMB_ok($pseudo, $lng) {
        global $serviz, $userprefs, $web;
        if ($serviz[28] != 'on' || $userprefs[19] == 'on' || $userprefs[20] == 'on' || !isAccessGranted($serviz[179], $pseudo)) {
            $href1 = CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[0] : 'index.php?lng='.$lng);
            $out = '
    <div class="text-center web342">'.$web[143].'
      <p class="text-center">
        '.SubmitButton($web[135], 'self.location.href=\''.$href1.'\'').'
      </p>
    </div>';
            $this->topmess = $web[664];
            return array('LSTMB', $out);
        }
        return array('');
    }
    
    public function LSTMB_content() {
        global $lng, $web, $serviz, $urlrw, $site, $z2, $userprefs, $page, $xposbox;
        $dbusers    = array();
        $spseudo    = '';
        $file_users = opendir(MSGREP);
        while ($nomfichier = readdir($file_users)) {
            if (substr($nomfichier, -3) == 'dtb') {
                $pseudo = str_replace(substr($nomfichier, -4), '', $nomfichier);
                $userdb = ReadDBFields(MSGREP.$nomfichier);
                $okgrp  = $serviz[180] == 'on' ? true : isAccessGranted($serviz[179], $pseudo);
                if (($okgrp || $userprefs[1] == $serviz[31] || $userprefs[1] == $serviz[176])
                    && @$userdb[1][13] == 'on' 
                    && (empty($userdb[1][31]) || (!empty($userdb[1][31]) 
                    && $userdb[1][31] != '1' 
                    && $userdb[1][31] != 2))) {
                    $avatar    = ($userdb[1][9] == 'ava-00' || substr($userdb[1][9], 0, 4) != 'ava-' ? 'ava-00' : $userdb[1][9]);
                    $avatar    = $userdb[1][16] != '' ? $userdb[1][16] : $avatar;
                    $dbusers[] = array(
                      'tri'    => strtoupper(substr($pseudo, 0, 1)),
                      'pseudo' => $pseudo, 
                      'status' => $this->GetStatus($pseudo),
                      'avatar' => $avatar, 
                      'web'    => $userdb[1][10], 
                      'mail'   => $userdb[1][3],
                      'reg'    => substr(FormatDate($userdb[1][14]), 0, 10), 
                      'date'   => substr(FormatDate($userdb[1][15]), 0, 10),
                      'okprf'  => $userdb[1][20],
                      'okmail' => $userdb[1][24],
                      'okmp'   => $userdb[1][25]
                    );
                    if ($userprefs[1] == $pseudo) {
                        $userprefs[17] = $userdb[1][18];
                        $userprefs[18] = $userdb[1][19];
                    }
                }
            }
        }
        closedir($file_users);
        sort($dbusers);
        $mblist = array();
        $scope  = import('scope');
        $search = import('search');
        $idpg   = import('idpg');
        if (!empty($scope)) {
            switch ($scope) {
            case 'ALL' :
                $mblist = $dbusers;
                break;
            case 'SEARCH' :
                $mblist = $dbusers;
                $nbdb   = count($mblist);
                $key    = 0;
                for ($i = 0; $i < $nbdb; $i++)
                    if (false !== stripos($mblist[$i]['pseudo'], $search) || false !== stripos($mblist[$i]['mail'], $search)) {
                        $key     = $i;
                        $spseudo = $mblist[$i]['pseudo'];
                        break;
                    }
                $idpg = floor($key / $serviz[75]) + 1;
                break;
            default :
                foreach ($dbusers as $user) if ($user['tri'] == $scope) $mblist[] = $user;
            }
        }
        else {
            $mblist = $dbusers;
            $scope  = 'ALL';
        }
        $nbdb = count($mblist);
        if (empty($idpg)) $idpg = 1;
        else while ($nbdb <= ($idpg - 1) * $serviz[75]) $idpg--;
        $navbar = GetNavBar(($site['URLR'] == 'on' ? $lng.'-'.$urlrw[15].'-910-'.$scope.'-'.$this->tconfig.'-' : 'memberlist.php?lng='.$lng.'&amp;scope='.$scope.'&amp;tconfig='.$this->tconfig.'&amp;idpg='), $nbdb, $idpg, $serviz[75], $z2);
        $out = '
<div class="mbrlistmain">
  <form name="frmsearch" 
      action="'.CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[15].'-9-'.$this->tconfig.$z2 : 'memberlist.php?lng='.$lng.'&amp;tconfig='.$this->tconfig.$z2).'" 
      method="POST">
    <input type="hidden" name="scope" value="SEARCH" />
    <div class="table-responsive-md">
    <table class="w-100">
      <tr class="forum">
        <td>'.$web[665].'</td>
	    <td><input type="text" class="form-control texte" name="search" size="40" /></td>
	    <td>'.SubmitButton($web[58]).'</td>
      </tr>
    </table>
    </div>
  </form>
  <div class="rep text-center">|';
        for ($i=65; $i<91; $i++) $out .= '
    <a href="'.CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[15].'-91-'.chr($i).'-'.$this->tconfig.$z2 : 'memberlist.php?lng='.$lng.'&amp;scope='.chr($i).'&amp;tconfig='.$this->tconfig.$z2).'" title="'.chr($i).'"><b><i>'.chr($i).'</i></b></a> |';
        $out .= '
    <a href="'.CHEMIN.($site['URLR'] == 'on' ? $lng.'-'.$urlrw[15].'-91-ALL-'.$this->tconfig.$z2 : 'memberlist.php?lng='.$lng.'&amp;scope=ALL&amp;tconfig='.$this->tconfig.$z2).'" title="'.$web[666].'"><b><i>'.$web[666].'</i></b></a> |
  </div>';
        if ($navbar != '')
            $out .= '
  <br />
  '.$navbar.'
  <br />';
        $out .= '
  <div class="table-responsive-md">
  <table class="table w-100">
    <tr class="forum2">
	  <th class="align-middle text-center">'.$web[667].'</th>
	  <th class="align-middle text-center">'.$web[172].'</th>
	  <th class="align-middle text-center">'.trim(substr($web[292], 0, -1)).'</th>
	  <th class="align-middle text-center">'.$web[304].'</th>
	  <th class="align-middle text-center">'.$web[13].'</th>
	  <th class="align-middle text-center">'.$web[668].'</th>
	  <th class="align-middle text-center">'.$web[669].'<br />'.$web[674].'</th>
	</tr>';
        for ($i = $serviz[75]*($idpg-1); $i < Min($serviz[75]*$idpg, $nbdb); $i++) {
            $mb = $mblist[$i];
            $out .= '
    <a id="'.$mb['pseudo'].'"></a>
	<tr class="'.($i%2 ? 'rep' : 'quest').'">
	  <td class="align-middle text-center">';
            if (substr($mb['avatar'], 0, 4) == 'ava-') $out .= '
        <i class="'.$mb['avatar'].' ava-img"></i>';
            else $out .= '
	    <img src="'.$mb['avatar'].'" alt="" title="" class="mbrlistimgava" />';
            $out .= '
	  </td>
	  <td class="'.($spseudo == $mb['pseudo'] ? 'forum ' : '').'align-middle text-center">
	    '.($mb['okprf'] == '' 
          ? '<a href="'.CHEMIN.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[15].'-92-'.$mb['pseudo'].'-'.$this->tconfig.$z2 
            : 'memberprf.php?lng='.$lng.'&amp;upseudo='.$mb['pseudo'].'&amp;tconfig='.$this->tconfig.$z2).'" title="'.$mb['pseudo'].'">' 
          : '').
		'<b>'.$mb['pseudo'].'</b>'.($mb['okprf'] == '' ? '</a>' : '').'
	  </td>
	  <td class="align-middle text-center">'.$mb['status'].'</td>
	  <td class="align-middle text-center">
	    '.(empty($mb['web']) 
          ? '<a href="#">&nbsp;</a>' 
          : '<a href="'.$mb['web'].'" title="" target="_blank"><i class="fas fa-globe align-middle" title="'.$web[304].'"></i></a>').'
	  </td>
	  <td class="align-middle text-center">';
            if ($mb['okmail'] == 'on' && $userprefs[18] != 'on')
                $out .= displayWriteEmail('mblst'.$i, $mb['pseudo'], $mb['mail'], 
		  ' <i class="fas fa-at align-middle" title="'.$web[173].' '.$mb['pseudo'].'"></i>');
            $out .= '
	  </td>
	  <td class="align-middle text-center">';
            $act = ($serviz[18] == 'on' ? '0' : '1');
            if ($mb['okmp'] == 'on' && $userprefs[17] != 'on') $out .= '
		<a href="'.CHEMIN.($site['URLR'] == 'on' 
          ? $lng.'-'.$urlrw[13].'-2-'.$act.'-'.$mb['pseudo'].'-'.$userprefs[1].'-'.$this->tconfig.$z2 
          : 'tinymsg.php?lng='.$lng.'&amp;action='.$act.'&amp;to='.$mb['pseudo'].'&amp;from='.$userprefs[1].'&amp;tconfig='.$this->tconfig.$z2).'" title="'.$web[214].'">
	      <i class="far fa-envelope align-middle" title="'.$web[214].' '.$mb['pseudo'].'"></i>
		</a>';
            $out .= '
	  </td>
	  <td class="align-middle text-center">'.$mb['reg'].'<br />'.$mb['date'].'</td>
	</div>';
        }
        $out .= '
  </table>
  </div>';
        if ($navbar != '')
            $out .= '
  <br />
  '.$navbar.'
  <br />';
        $out .= '
</div>';
        $this->topmess = $web[664];
        return array('LSTMB', $out);
    }
    
    protected function UpdateFicForm($fic, $form, $id) {
        $array      = explode("\n", fread(fopen($fic, "r"), filesize($fic)));
        $delete     = array_pop($array);
        $new        = $form;
        $array[$id] = $new;
        $fhandle    = fopen($fic,"w");
        for($a=0; $a< count($array); $a++) {
            fwrite($fhandle,stripslashes($array[$a])."\n");
        }
        fclose($fhandle);
    }
    
    public function TMSG_content() {
        global $lng, $web, $serviz, $urlrw, $site, $z2, $userprefs, $page, $souriez, $lang;
        $action     = import('action');
        $action2    = import('action2');
        $fic        = import('fic');
        $form       = import('form');
        $ancienmsg  = addslashes(import('ancienmsg'));
        $anciendate = import('anciendate');
        $msg        = addslashes(import('msg'));
        $maxcar     = '1500'; // max length of tiny messages in chr
        $from       = KeepGoodChars(CutLongWord(import('from'),20));
        $to         = KeepGoodChars(CutLongWord(import('to'),20));
        $id         = import('id');
        $out        = '';
        $this->topmess = $web[215].' - '.$userprefs[1];
        if (empty($userprefs[1])) {
            $out = '<strong>'.$web[357].'</strong><br /><br />'.$web[358].'<br />';
            return array('TMSG', $out);
        }
        elseif ($serviz[18] == 'on' && empty($action2)) {
            $out = '<strong>'.$web[357].'</strong>';
            return array('TMSG', $out);
        }
        $action = ($action == 0 ? 3 : $action);
        if ($action == 1) {  // Envoyer un tiny
            $from = $userprefs[1];
            if (empty($to)) {
                $out = '<strong>'.$web[357].'</strong><br /><br />'.$web[359].'<br />';
                return array('TMSG', $out);
            }
            $out .= JavaScriptfile(CHEMIN.'inc/tinymsg.js').'
<form name="nmsgr" 
    action="'.($site['URLR'] == 'on' ? 
      $lng.'-'.$urlrw[13].'-2-2-'.$to.'-'.$from.'-'.$this->tconfig.$z2 : 
      'tinymsg.php?lng='.$lng.'&amp;action=2&amp;to='.$to.'&amp;from='.$from.'&amp;tconfig='.$this->tconfig.$z2).'" 
    method="post">
  <input type="hidden" name="action2" value="'.$action.'" />
  <p class="textLeft">'.$web[208].'&nbsp;&nbsp;<strong>'.$from.'</strong><br />'.$web[204].'&nbsp;&nbsp;<strong>'.$to.'</strong></p>';
            if (!empty($userprefs[1]) && FileDBExist(MSGREP.$userprefs[1].DBEXT)) {
                $dbmsg = ReadDBFields(MSGREP.$userprefs[1].DBEXT);
                if ($id != '') {
                    if ($dbmsg[0][0] == $userprefs[7]) {
                        if ($dbmsg[$id][3] == 'new') {
                            $dbmsg[$id][3] = 'lu';
                            $this->UpdateFicForm(MSGREP.$userprefs[1].DBEXT, implode(CONNECTOR, $dbmsg[$id]), $id);
                        }
                    }
                    $out .= '
  <p class="text-left"><input type="hidden" name="anciendate" value="'.$dbmsg[$id][1].'" /></p>
  <p class="text-center">'.$web[216].'</p>
  <input type="hidden" name="ancienmsg" value="'.stripslashes($dbmsg[$id][2]).'" />
  <div class="rep w-100 m-auto tdrepmsg">'.stripslashes(souriez($dbmsg[$id][2])).'</div>';
                }
            }
            $out .= '
  <p class="text-center">'.$web[56].'</p>
  <div class="text-center">
    <textarea name="msg" rows="8" class="w-100 msgtinysend"></textarea>
  </div>
  <div class="text-center align-middle">
    <a href="javascript:format(\'b\')" class="c-pointer" title="'.$web[98].'"><i class="fas fa-bold"></i></a>&nbsp;
    <a href="javascript:format(\'i\')" class="c-pointer" title="'.$web[100].'"><i class="fas fa-italic"></i></a>&nbsp;
    <a href="javascript:format(\'u\')" class="c-pointer" title="'.$web[99].'"><i class="fas fa-underline"></i></a>&nbsp;
    <a href="javascript:dolink(\'href=http://\')" class="c-pointer" title="'.$web[170].'"><i class="fas fa-link"></i></a>&nbsp;  &nbsp;';
            $dsm = '';
			$num = '';
            for ($i = 0; $i < count($souriez); $i++) 
                $dsm .= '
    <a href="JavaScript:AddSmiley'.$num.'(\''.$souriez[$i][0].'\')" class="c-pointer" title="'.$souriez[$i][0].'  => '.$souriez[$i][2].'">
      '.$souriez[$i][1].'
    </a>&nbsp;';
            $out .= $dsm.'
  </div>';
            $href1 = CHEMIN.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[13].'-3-3-'.$this->tconfig.$z2 
              : 'tinymsg.php?lng='.$lng.'&amp;action=3&amp;tconfig='.$this->tconfig.$z2);
            $out .= '
  <p class="text-center">'.SubmitButton($web[52]).'&nbsp;'.SubmitButton($web[205], 'self.location.href=\''.$href1.'\'', '', '', 'reset').'</p>
</form>
<script> document.nmsgr.msg.focus(); </script>
<p class="text-center">
  <a href="'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[13].($serviz[18] != 'on' ? '-3-3-' : '-32-3-3-').$this->tconfig.$z2 
      : 'tinymsg.php?lng='.$lng.'&amp;action=3&amp;action2=3&amp;tconfig='.$this->tconfig.$z2).'">
    <button type="button" class="btn btn-info btn-lg"><i class="fas fa-undo-alt" title="'.$web[135].'"></i></button>
  </a>
</p>';
        }
        elseif ($action == 2) { // Voir un tiny
            $from = $userprefs[1];
            if (empty($to)) {
                $out = '<p><strong>'.$web[357].'</strong><br /><br />'.$web[359].'</p>';
                return array('TMSG', $out);
            }
            if (strlen($msg) > $maxcar) {
                $out = '
<script>
  alert("'.strlen($msg).' '.$web[408].' '.$maxcar.')");
  history.back();
</script>';
                return array('TMSG', $out);
            }
            $msg   = str_replace(chr(10), '<br />', $msg);
            $msg   = str_replace(chr(13), '', $msg);
            $msg   = str_replace('[b]',   '<b>', $msg);
            $msg   = str_replace('[/b]',  '</b>', $msg);
            $msg   = str_replace('[i]',   '<i>', $msg);
            $msg   = str_replace('[/i]',  '</i>', $msg);
            $msg   = str_replace('[u]',   '<u>', $msg);
            $msg   = str_replace('[/u]',  '</u>', $msg);
            $msg   = str_replace('[l]href=','<a href="', $msg);
            $msg   = str_replace('[/l]',  '" target="_blank"><i class="fas fa-globe" title="'.$web[304].'"></i></a>', $msg);
            $msg   = str_replace('"',     "'", $msg);
            $dbmsg = array();
            if (!empty($to) && FileDBExist(MSGREP.$to.DBEXT)) {
                $dbmsg[0] = $from;
                $dbmsg[1] = GetCurrentDateTime();
                $dbmsg[2] = RemoveConnector(stripslashes($msg));
                $dbmsg[3] = 'new';
                $dbmsg[4] = '';
                $dbmsg[5] = RemoveConnector(stripslashes($ancienmsg));
                $dbmsg[6] = $anciendate;
                $dbmsg[7] = $userprefs[8];
                AppendDBFields(MSGREP.$to.DBEXT, $dbmsg);
                $dbmsg[0] = $to;
                $dbmsg[1] = GetCurrentDateTime();
                $dbmsg[2] = RemoveConnector(stripslashes($msg));
                $dbmsg[3] = 'lu';
                $dbmsg[4] = 'send';
                $dbmsg[5] = RemoveConnector(stripslashes($ancienmsg));
                $dbmsg[6] = $anciendate;
                $dbmsg[7] = $userprefs[8];
                AppendDBFields(MSGREP.$from.DBEXT, $dbmsg);
                $dbmsg2 = ReadDBFields(MSGREP.$to.DBEXT);
                $eto    = $dbmsg2[1][3];
                $sujet  = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' - '.$web[670];
                $bodycorps  = '<div style="margin:auto;padding:8px;width:440px;border: solid 1px #000000;border-radius:4px;">';
                $bodycorps  .= '<p style="text-align:left;font:12pt Arial;">';
                $bodycorps  .= $web[245].sprintf($web[672], $to, $from).'<a href="'.$site[3].'" title="'.$site[3].'">'.$site[3].'</a>';
                $bodycorps  .= '</p></div>';
                eMailHtmlTo($sujet, $bodycorps, $eto);
                $out .= '
    <br />
	<div class="m-auto text-center">
	  <p>'.$web[332].'<br /><br />
	    <a href="'.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[13].($serviz[18] != 'on' ? '-3-3-' : '-32-3-3-').$this->tconfig.$z2 
            : 'tinymsg.php?lng='.$lng.'&amp;action=3&amp;action2=3&amp;tconfig='.$this->tconfig.$z2).'">'.$web[135].'
          <button type="button" class="btn btn-info btn-lg"><i class="fas fa-undo-alt" title="'.$web[135].'"></i></button>
	    </a>
	  </p>
    </div>
    <form name="suit2" 
      action="'.($site['URLR'] == 'on' 
        ? $lng.'-'.$urlrw[13].'-3-3-'.$this->tconfig.$z2 
        : 'tinymsg.php?lng='.$lng.'&amp;action=3&amp;tconfig='.$this->tconfig.$z2).'" method="post">
      <input type="hidden" name="action2" value="3" />
    </form>
<script> setTimeout("document.suit2.submit();",4000);</script>';
            }
            else {
                $out .= '
    <br />
    <p class="text-center">'.$web[230].'</p>';
            }
        }
        elseif ($action == 3) { // Gérer les tiny
            $nbrtinymsg = ReadDBFields(MSGREP.$userprefs[1].DBEXT);
            if(count($nbrtinymsg) > ($serviz[48]+2)) {
                DeleteDBFieldById(MSGREP.$userprefs[1].DBEXT, 2);
            }
            $dbmsg = Array();
            if (!empty($userprefs[1]) && FileDBExist(MSGREP.$userprefs[1].DBEXT)) {
                $dbmsg = ReadDBFields(MSGREP.$userprefs[1].DBEXT);
                if ($dbmsg[0][0] == $userprefs[7]) {
                    $out .= '
<script>
  function selectAllMsg(opt) {
	var option = opt.name;
	var allSelected = opt.checked;
	switch (option) {
	case "delRecu_all" :
      var allMsg = document.forms["boxtinymsg"].elements["delRecu_id[]"];
      break;
	case "delSend_all" :
	  var allMsg = document.forms["boxtinymsg"].elements["delSend_id[]"];
	  break;
	default :
	  return false;
	}
	if (allMsg.length != undefined) {
	  for (i = 0; i < allMsg.length; i++) {
		allMsg[i].checked = (allSelected)? true : false;
	  }
	} else {
	  allMsg.checked = (allSelected)? true : false;
	}
	return false;
  }
  function verifyTinyMsgChecked() {
	var error = true;
	var message = "'.$web[428].'";
	if (document.forms["boxtinymsg"].elements["delRecu_id[]"]) {
	  var allMsgRecu = document.forms["boxtinymsg"].elements["delRecu_id[]"];
	  if (allMsgRecu.length != undefined) {
		for (i = 0; i < allMsgRecu.length; i++) {
		  if (allMsgRecu[i].checked) { error = false; }
		}
	  } else {
		if (allMsgRecu.checked) { error = false; }
	  }
	}
	if (document.forms["boxtinymsg"].elements["delSend_id[]"]) {
	  var allMsgSend = document.forms["boxtinymsg"].elements["delSend_id[]"];
	  if (allMsgSend.length != undefined) {
		for (i = 0; i < allMsgSend.length; i++) {
		  if (allMsgSend[i].checked) { error = false; }
		}
	  } else {
		if (allMsgSend.checked) { error = false; }
	  }
	}
	if (error) {
	  alert(message);
	  return false;
	} else {
	  return true;
	}
  }
</script>
<p class="text-center">'.$web[291].' '.$serviz[48].' '.$web[335].'</p>
<p class="text-center">
  <i class="far fa-envelope text-danger"></i> '.$web[211].'&nbsp;&nbsp;&nbsp;&nbsp;
  <i class="far fa-check-circle text-dark"></i> '.$web[209].'
</p>
<hr />
<form name="boxtinymsg" 
  action="'.($site['URLR'] == 'on' 
    ? $lng.'-'.$urlrw[13].'-3-5-'.$this->tconfig.$z2 
    : 'tinymsg.php?lng='.$lng.'&amp;action=5&amp;tconfig='.$this->tconfig.$z2).'" 
  method="post" 
  onsubmit="return verifyTinyMsgChecked(); return false;"
>
  <input type="hidden" name="action2" value="5" />
  <div class="text-center m-auto"><strong>'.$web[207].'</strong></div>
  <div class="table-responsive-md">
  <div class="d-table m-auto w-100 tbltinymain">
    <div class="forum d-table-row text-center">
      <div class="d-table-cell py-2 w-10"><strong>'.$web[206].'</strong></div>
      <div class="d-table-cell py-2 w-35"><strong>'.$web[210].'</strong></div>
      <div class="d-table-cell py-2 w-35"><strong>'.$web[325].'</strong></div>
      <div class="d-table-cell py-2 w-20">
        <div class="table-responsive-md">
        <div class="d-table m-auto">
          <div class="d-table-row">
            <div class="d-table-cell text-center"><strong>'.$web[323].'</strong></div>
          </div>
        </div>
        </div>
      </div>
    </div>';
                    $nbMsgRecu = 0;
                    for ($i = 2; $i < count($dbmsg); $i++) {
                        if (isset($dbmsg[$i][4]) && $dbmsg[$i][4] != 'send') {
                            $trcolor = $dbmsg[$i][3] == 'new' ? 'quest ' : ($dbmsg[$i][3] == 'lu' ? 'rep ' : '');
                            $out .= '
    <div class="d-table-row '.$trcolor.'text-center trmsgtiny">
      <div class="d-table-cell w-10">
        '.($dbmsg[$i][3] == 'lu' 
            ? '<i class="far fa-check-circle text-dark" title="'.$dbmsg[$i][3].'"></i>' 
            : '<i class="far fa-envelope text-danger blink" title="'.$dbmsg[$i][3].'"></i>').'
      </div>
      <div class="d-table-cell w-35 text-left tdmsglnk">
	    <a href="'.($site['URLR'] == 'on' 
            ? $lng.'-'.$urlrw[13].($serviz != 'on' ? '-4-1-' : '-42-1-1-').$i.'-'.$dbmsg[$i][0].'-'.$userprefs[1].'-'.$this->tconfig.$z2 
            : 'tinymsg.php?lng='.$lng.'&amp;action=1&amp;action2=1&amp;id='.$i.'&amp;to='.$dbmsg[$i][0].'&amp;from='.$userprefs[1].'&amp;tconfig='.$this->tconfig.$z2).'" 
            title="'.$web[140].'">
          <strong>'.$dbmsg[$i][0].'</strong>
        </a>
	  </div>
      <div class="d-table-cell w-35">'.FormatDate($dbmsg[$i][1]).'</div>
      <div class="d-table-cell w-20 text-center">
        <div class="table-responsive-md">
        <div class="d-table m-auto w-100">
          <div class="d-table-row">
            <div class="d-table-cell w-40 text-center">
              <a href="'.($site['URLR'] == 'on' 
                  ? $lng.'-'.$urlrw[13].($serviz != 'on' ? '-5-4-' : '-52-4-4-').$i.'-'.$this->tconfig.$z2 
                  : 'tinymsg.php?lng='.$lng.'&amp;action=4&amp;action2=4&amp;id='.$i.'&amp;tconfig='.$this->tconfig.$z2).'">
                <i class="far fa-eye text-primary" title="'.$web[326].'"></i>
              </a>
            </div>
            <div class="d-table-cell w60 text-center">
              <input type="checkbox" id="delRecu_id[]" name="delRecu_id[]" value="'.$i.'" />
              <i class="fas fa-times text-danger" title="'.$web[324].'"></i>
            </div>
          </div>
        </div>
        </div>
      </div>
    </div>';
                            $nbMsgRecu++;
                        }
                    }
                    $out .= '
  </div>
  </div>';
                    if ($nbMsgRecu > 0) { //Case Tout supprimer
                        $out .= '
  <div class="table-responsive-md">
  <div class="d-table m-auto w-100 tbltinymain">
	<div class="d-table-row text-center trmsgtiny">
      <div class="d-table-cell text-right"><label for="delRecu_all">'.$web[425].'</label></div>
      <div class="d-table-cell w-12">
        <input title="'.$web[426].'" type="checkbox" id="delRecu_all" name="delRecu_all" value="" onclick="selectAllMsg(this)" />
        <i class="fas fa-times text-danger" title="'.$web[324].'"></i>
      </div>
    </div>
  </div>
  </div>';
                    }
                    $out .= '
  <br />
  <div class="m-auto text-center"><strong>'.$web[327].'</strong></div>
  <div class="table-responsive-md">
  <div class="d-table m-auto w-100 tbltinymain">
    <div class="forum d-table-row text-center">
      <div class="d-table-cell py-2 w-10"><strong>'.$web[206].'</strong></div>
      <div class="d-table-cell py-2 w-35"><strong>'.$web[328].'</strong></div>
      <div class="d-table-cell py-2 w-35"><strong>'.$web[325].'</strong></div>
      <div class="d-table-cell py-2 w-20">
        <div class="table-responsive-md">
        <div class="d-table m-auto">
          <div class="d-table-row">
            <div class="d-table-cell text-center"><strong>'.$web[323].'</strong></div>
          </div>
        </div>
        </div>
      </div>
    </div>';
                    $nbMsgSend = 0;
                    for ($i = 2; $i < count($dbmsg); $i++) {
                        if ($dbmsg[$i][4] == 'send') {
                            $out .= '
    <div class="rep d-table-row text-center trmsgtiny">
      <div class="d-table-cell w-10">
        '.($dbmsg[$i][3] == 'lu' 
            ? '<i class="far fa-check-circle text-dark" title="'.$dbmsg[$i][3].'"></i>' 
            : '<i class="far fa-envelope text-danger blink" title="'.$dbmsg[$i][3].'"></i>').'
      </div>
      <div class="d-table-cell w-35 textLeft tdmsglnk"><label for="delSend_id[]"><strong>'.$dbmsg[$i][0].'</strong></label></div>
      <div class="d-table-cell w-35">'.FormatDate($dbmsg[$i][1]).'</div>
      <div class="d-table-cell w-20 text-center">
        <div class="table-responsive-md">
        <div class="d-table m-auto w-100">
          <div class="d-table-row">
            <div class="d-table-cell w-40">
              <a href="'.($site['URLR'] == 'on' 
                  ? $lng.'-'.$urlrw[13].($serviz != 'on' ? '-5-6-' : '-52-6-6-').$i.'-'.$this->tconfig.$z2 
                  : 'tinymsg.php?lng='.$lng.'&amp;action=4&amp;action2=4&amp;id='.$i.'&amp;tconfig='.$this->tconfig.$z2).'">
                <i class="far fa-eye text-primary" title="'.$web[326].'"></i>
              </a>
            </div>
            <div class="d-table-cell width60">
              <input type="checkbox" id="delSend_id[]" name="delSend_id[]" value="'.$i.'" />
              <i class="fas fa-times text-danger" title="'.$web[324].'"></i>
            </div>
          </div>
        </div>
        </div>
      </div>
    </div>';
                            $nbMsgSend++;
                        }
                    }
                    $out .= '
  </div>
  </div>';
                    if ($nbMsgSend > 0) { //Case Tout supprimer
                        $out .= '
  <div class="table-responsive-md">
  <div class="d-table m-auto w-100 tbltinymain">
	<div class="d-table-row text-center trmsgtiny">
      <div class="d-table-cell text-right"><label for="delSend_all">'.$web[425].'</label></div>
      <div class="d-table-cell w-12">
        <input title="'.$web[427].'" type="checkbox" id="delSend_all" name="delSend_all" value="" onclick="selectAllMsg(this)" />
        <i class="fas fa-times text-danger" title="'.$web[324].'"></i>
      </div>
    </div>
  </div>
  </div>';
                    }
                    if (($nbMsgSend + $nbMsgRecu) > 0) { //Case Tout supprimer
                        $out .= '
  <div class="m-auto text-right btntinymain">
    '.SubmitButton($web[424]).' <i class="fas fa-level-up-alt align-middle pr-5"></i>
  </div>';
                    }
                    $out .= '
</form>';
                }
            }
        }
        elseif ($action == 4) { // Répondre à  ou Supprimer un tiny
            if (empty($id)) {
                $out = 'tinymsg.php : action=4 empty id';
                return array('TMSG', $out);
            }
            $dbmsg = array();
            if (!empty($userprefs[1]) && FileDBExist(MSGREP.$userprefs[1].DBEXT)) {
                $dbmsg = ReadDBFields(MSGREP.$userprefs[1].DBEXT);
                if ($dbmsg[0][0] == $userprefs[7]) {
                    $sendRR = $dbmsg[$id][3] == 'lu' ? true : false;
                    $dbmsg[$id][3] = 'lu';
                    $this->UpdateFicForm(MSGREP.$userprefs[1].DBEXT, implode(CONNECTOR, $dbmsg[$id]), $id);
                    $out .= '
    <p class="text-center"><b>'.$web[330]." ".$dbmsg[$id][0].'</b> '.$web[7]." ".FormatDate($dbmsg[$id][1]).'</p>';
			$imgavexp = $page[23] != 'none' ? $dbmsg[$id][7] != '' ? $dbmsg[$id][7] : 'ava-00' : '';
			$out .= '
    <br />
	<fieldset>
      <div class="table-responsive-md">
      <table class="table table-borderless w-98 m-auto">
        <tr class="rep">
          <td class="w-10 text-center">
            '.($page[23] != 'none' ?  '<img src="'.CHEMIN.'inc/img/avatars/'.$imgavexp.'" alt="'.$dbmsg[$id][0].'" title="'.$dbmsg[$id][0].'" />' : '&nbsp;').'
          </td>
          <td class="w-90"><p>'.stripslashes(souriez($dbmsg[$id][2])).'</p></td>
        </tr>
      </table>
      </div>';
                    if($dbmsg[$id][5] != '') {
                        $imgavdest = ($page[23] != 'none' ? ($userprefs[8] != '' ? $userprefs[8] : 'ava-00') : '');
                        $out .= '
      <div class="table-responsive-md">
      <table class="table table-borderless w-98 m-auto">
        <tr class="forum text-center">
          <td><p>'.$web[329]." ".FormatDate($dbmsg[$id][6]).'</p></td>
        </tr>
      </table>
      </div>
      <div class="table-responsive-md">
      <table class="table table-borderless w-98 m-auto">
        <tr>
          <td class="w-10 text-center">
            '.($page[23] != 'none' ?  '<img src="inc/img/avatars/'.($page[23] != 'none' ? $imgavdest : '&nbsp;').'" alt="Avatar '.$dbmsg[$id][0].'" />' : '&nbsp;').'
          </td>
          <td class="w-90"><p>'.stripslashes(souriez($dbmsg[$id][5])).'</p></td>
        </tr>
      </table>
      </div>';
                    }
                    $out .= '
	</fieldset>
	<div class="text-center divcmdtiny">
	  <div class="float-left">
	    <form name="msg_answer" 
            action="'.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[13].'-4-1-'.$id.'-'.$dbmsg[$id][0].'-'.$userprefs[1].'-'.$this->tconfig.$z2 
              : 'tinymsg.php?lng='.$lng.'&amp;action=1&amp;id='.$id.'&amp;to='.$dbmsg[$id][0].'&amp;from='.$userprefs[1].'&amp;tconfig='.$this->tconfig.$z2).'" method="post" 
            class="frmcmdtiny">
          <input type="hidden" name="action2" value="1" />
		  '.SubmitButton($web[140]).'
	    </form>
	  </div>
	  <div>
	    <form name="msg_del" 
            action="'.($site['URLR'] == 'on' 
              ? $lng.'-'.$urlrw[13].'-3-5-'.$this->tconfig.$z2 
              : 'tinymsg.php?lng='.$lng.'&amp;action=5&amp;tconfig='.$this->tconfig.$z2).'" 
            class="frmcmdtiny" 
            method="post">
          <input type="hidden" name="action2" value="5" />
	      <input type="hidden" name="delRecu_id[]" value="'.$id.'" />
		  '.SubmitButton($web[324]).'
	    </form>
	  </div>
	</div>';
                    if ($dbmsg[1][27] == 'on' && $sendRR) {
                        $dbmsg2 = ReadDBFields(MSGREP.$dbmsg[$id][0].DBEXT);
                        $to     = $dbmsg2[1][3];
                        $sujet  = stripslashes($site[($lang[0] == $lng ? 0 : 11)]).' - '.$web[670];
                        $bodycorps  = '<div style="margin:auto;padding:8px;width:440px;border: solid 1px #000000;border-radius:4px;">';
                        $bodycorps  .= '<p style="text-align:left;font:12pt Arial;">';
                        $bodycorps  .= $web[245].sprintf($web[671], $dbmsg2[1][2], $userprefs[1]).'<a href="'.$site[3].'" title="'.$site[3].'">'.$site[3].'</a><br /><br />';
                        $bodycorps  .= $web[56].'<br /><br />'.$dbmsg[$id][2].'<br /><br />';
                        $bodycorps  .= '</p></div>';
                        eMailHtmlTo($sujet, $bodycorps, $to);
                    }
                }
            }
            $out .= '
  <p class="text-center">
    <a href="'.($site['URLR'] == 'on' 
        ? $lng.'-'.$urlrw[13].($serviz[18] != 'on' ? '-3-3-' : '-32-3-3-').$this->tconfig.$z2 
        : 'tinymsg.php?lng='.$lng.'&amp;action=3&amp;action2=3&amp;tconfig='.$this->tconfig.$z2).'" title="">
      <button type="button" class="btn btn-info btn-lg"><i class="fas fa-undo-alt" title="'.$web[135].'"></i></button>
	</a>
  </p>';
        }
        elseif ($action == 5) { // Supprimer des tiny
            $id      = array();
            $id_recu = (isset($_POST['delRecu_id']) && is_array($_POST['delRecu_id']))? $_POST['delRecu_id'] : array();
            $id_send = (isset($_POST['delSend_id']) && is_array($_POST['delSend_id']))? $_POST['delSend_id'] : array();
            $id      = array_merge($id_recu, $id_send);
            if (!empty($userprefs[1]) && FileDBExist(MSGREP.$userprefs[1].DBEXT) && !empty($id)) { //Vérification de l'existence du membre
                $dbmsg = array();
                $dbmsg = ReadDBFields(MSGREP.$userprefs[1].DBEXT);
                if ($dbmsg[0][0] == $userprefs[7]) { //Vérification du mot de passe
                    for ($i = 0; $i < count($id); $i++) {
                        unset($dbmsg[$id[$i]]);
                    }
                    $dbmsg = array_values($dbmsg);
                    WriteDBFields(MSGREP.$userprefs[1].DBEXT, $dbmsg);
                }
            }
            $out .= '
  <p class="text-center">'.$web[331].'</p>
  <br />
  <p class="text-center">
    <a href="'.($site['URLR'] == 'on' 
        ? $lng.'-'.$urlrw[13].($serviz[18] != 'on' ? '-3-3-' : '-32-3-3-').$this->tconfig.$z2 
        : 'tinymsg.php?lng='.$lng.'&amp;action=3&amp;action2=3&amp;tconfig='.$this->tconfig.$z2).'" title="">
      <button type="button" class="btn btn-info btn-lg"><i class="fas fa-undo-alt" title="'.$web[135].'"></i></button>
	</a>
  </p>
  <form name="suit5" 
    action="'.($site['URLR'] == 'on' 
      ? $lng.'-'.$urlrw[13].'-3-3-'.$this->tconfig.$z2 
      : 'tinymsg.php?lng='.$lng.'&amp;action=3&amp;tconfig='.$this->tconfig.$z2).'" 
    method="post">
    <input type="hidden" name="action2" value="3" />
  </form>
  <script> setTimeout("document.suit5.submit();",2400); </script>';
        }
        elseif ($action == 6) {
            if (empty($id)) {
                $out = 'tinymsg.php : action=6 empty id';
                return array('TMSG', $out);
            }
            $dbmsg = Array();
            if (!empty($userprefs[1]) && FileDBExist(MSGREP.$userprefs[1].DBEXT)) {
                $dbmsg = ReadDBFields(MSGREP.$userprefs[1].DBEXT);
                if ($dbmsg[0][0] == $userprefs[7]) {
                    $dbmsg[$id][3] = 'lu';
                    $this->UpdateFicForm(MSGREP.$userprefs[1].DBEXT, implode(CONNECTOR, $dbmsg[$id]), $id);
                    $out .= '
      <p class="text-center">'.$web[328]." : &nbsp;" .' <strong>'.$dbmsg[$id][0].'</strong> '." &nbsp;".$web[7]." &nbsp;".FormatDate($dbmsg[$id][1]).'</p>
      <div class="rep w-98 divreptiny">'.stripslashes(souriez($dbmsg[$id][2])).'</div>';
                }
            }
            $out .= '
  <div class="text-center">
  <hr />
    <a href="'.($site['URLR'] == 'on' 
        ? $lng.'-'.$urlrw[13].($serviz[18] != 'on' ? '-3-3-' : '-32-3-3-').$this->tconfig.$z2 
        : 'tinymsg.php?lng='.$lng.'&amp;action=3&amp;action2=3&amp;tconfig='.$this->tconfig.$z2).'">
      <button type="button" class="btn btn-info btn-lg"><i class="fas fa-undo-alt" title="'.$web[135].'"></i></button>
	</a>
  </div>';
        }
        return array('TMSG', $out);
    }
}
?>
